<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\JasaCustom;
use App\Models\MasterProjectDetail;
use App\Models\PembayaranJasa;
use App\Models\SettingProyek;
use App\Models\Tukang;
use App\Services\KeuanganProyekService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PembayaranJasaController extends Controller
{
    private const BUKTI_DIR = 'assets/pembayaran-jasa/bukti';

    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->orderBy('id', 'desc')->get();
            $rabJasaTotals = $this->getRabJasaTotals();
            $paidTotals = $this->getPaidTotals();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('unit_rumah', function ($row) {
                    $unit = e($row->kavling?->kode_kavling ?? '-');
                    $lokasi = e($row->lokasi?->nama_kavling ?? '-');

                    return '<div class="font-weight-bold">' . $unit . '</div><small class="text-muted">' . $lokasi . '</small>';
                })
                ->addColumn('type_rumah', function ($row) {
                    return $row->masterProject?->jenis_proyek ?? '-';
                })
                ->addColumn('rab_jasa', function ($row) use ($rabJasaTotals) {
                    return $this->formatRupiah((int) ($rabJasaTotals[$row->id_master_project] ?? 0));
                })
                ->addColumn('total_bayar', function ($row) use ($paidTotals) {
                    return $this->formatRupiah((int) ($paidTotals[$row->id] ?? 0));
                })
                ->addColumn('sisa_bayar', function ($row) use ($rabJasaTotals, $paidTotals) {
                    $rab = (int) ($rabJasaTotals[$row->id_master_project] ?? 0);
                    $paid = (int) ($paidTotals[$row->id] ?? 0);

                    return $this->formatRupiah(max($rab - $paid, 0));
                })
                ->addColumn('status_pembayaran', function ($row) use ($rabJasaTotals, $paidTotals) {
                    $summary = $this->buildStatusSummary(
                        (int) ($rabJasaTotals[$row->id_master_project] ?? 0),
                        (int) ($paidTotals[$row->id] ?? 0)
                    );

                    return $summary['badge'];
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $btn = '<div class="d-flex justify-content-center">';

                    if ($permissions['tambah'] || $permissions['edit']) {
                        $btn .= '<a href="' . route('pembayaran-jasa.show-setting', ['setting' => $row->id]) . '" class="btn btn-success btn-sm mx-1">Pembayaran</a>';
                    }

                    if ($permissions['edit'] || $permissions['hapus']) {
                        $btn .= '<button type="button" class="btn btn-primary btn-sm mx-1 btn-open-history" data-setting-id="' . e($row->id) . '">Riwayat</button>';
                    }

                    $btn .= '</div>';

                    return $btn;
                })
                ->rawColumns(['unit_rumah', 'status_pembayaran', 'action'])
                ->make(true);
        }

        $settingProyekList = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])
            ->orderBy('id_lokasi')
            ->orderBy('id_kavling')
            ->get();
        $tukangList = Tukang::orderBy('nama')->get();

        return view('admin.pembayaran_jasa.index', compact('permissions', 'settingProyekList', 'tukangList'));
    }

    public function showSetting(string $id)
    {
        Carbon::setLocale('id');
        $permissions = HakAksesController::getUserPermissions();
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($id);
        $summary = $this->buildSettingSummary($setting);
        $services = $this->buildServiceRows($setting);
        $tukangList = Tukang::orderBy('nama')->get();

        return view('admin.pembayaran_jasa.show', compact('permissions', 'setting', 'summary', 'services', 'tukangList'));
    }

    public function services(string $id)
    {
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($id);

        return response()->json([
            'summary' => $this->buildSettingSummary($setting),
            'services' => $this->buildServiceRows($setting),
        ]);
    }

    public function summary(string $id)
    {
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($id);

        return response()->json([
            'data' => $this->buildSettingSummary($setting),
        ]);
    }

    public function serviceSummary(string $settingId, string $detailId)
    {
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($settingId);
        $detail = $this->resolveServiceDetail($setting, $detailId);

        return response()->json([
            'data' => $this->buildServiceSummary($setting, $detail),
        ]);
    }

    public function history(string $id)
    {
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($id);
        $payments = PembayaranJasa::with(['tukang', 'jasaDetail'])
            ->where('id_setting_proyek', $setting->id)
            ->orderByDesc('tanggal_pembayaran')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'summary' => $this->buildSettingSummary($setting),
            'payments' => $payments->map(function ($payment) {
                return $this->transformPayment($payment);
            })->values(),
        ]);
    }

    public function serviceHistory(string $settingId, string $detailId)
    {
        $setting = SettingProyek::with(['lokasi', 'kavling', 'masterProject'])->findOrFail($settingId);
        $detail = $this->resolveServiceDetailOrCustom($setting, $detailId);
        $isCustom = $detail instanceof JasaCustom;

        $payments = PembayaranJasa::with(['tukang', 'jasaDetail', 'jasaCustom'])
            ->where('id_setting_proyek', $setting->id);

        if ($isCustom) {
            $payments->where('id_jasa_custom', $detail->id);
        } else {
            $payments->where('id_master_project_detail', $detail->id);
        }

        $payments = $payments->orderByDesc('tanggal_pembayaran')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'summary' => $this->buildServiceSummary($setting, $detail),
            'payments' => $payments->map(function ($payment) {
                return $this->transformPayment($payment);
            })->values(),
        ]);
    }

    public function edit(string $id)
    {
        $payment = PembayaranJasa::with([
            'settingProyek.lokasi',
            'settingProyek.kavling',
            'settingProyek.masterProject',
            'jasaDetail',
            'tukang',
        ])->findOrFail($id);

        $summary = null;
        if ($payment->settingProyek && $payment->id_master_project_detail) {
            $detail = $this->resolveServiceDetail($payment->settingProyek, $payment->id_master_project_detail);
            $summary = $this->buildServiceSummary($payment->settingProyek, $detail, $payment->id);
        }

        return response()->json([
            'data' => [
                'id' => $payment->id,
                'id_setting_proyek' => $payment->id_setting_proyek,
                'id_master_project_detail' => $payment->id_master_project_detail,
                'id_tukang' => $payment->id_tukang,
                'tanggal_pembayaran' => $payment->tanggal_pembayaran,
                'jumlah_bayar' => (int) $payment->jumlah_bayar,
                'keterangan' => $payment->keterangan,
                'bukti_url' => $payment->bukti_bayar ? asset(self::BUKTI_DIR . '/' . $payment->bukti_bayar) : null,
                'bukti_type' => strtolower($payment->bukti_bayar_extension ?: '') === 'pdf' ? 'pdf' : 'image',
                'summary' => $summary,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules(), $this->messages());

        $setting = SettingProyek::with('masterProject')->findOrFail($request->id_setting_proyek);

        $isCustom = !empty($request->id_jasa_custom);
        if ($isCustom) {
            $detail = $this->resolveCustomServiceDetail($setting, $request->id_jasa_custom);
        } else {
            $detail = $this->resolveServiceDetail($setting, $request->id_master_project_detail);
        }

        $jumlahBayar = $this->parseCurrency($request->jumlah_bayar);
        $summary = $this->buildServiceSummary($setting, $detail);

        if ($jumlahBayar <= 0) {
            return $this->amountValidationError('Jumlah bayar harus lebih besar dari 0.');
        }

        if ($jumlahBayar > $summary['sisa_bayar_raw']) {
            return $this->amountValidationError('Jumlah bayar tidak boleh melebihi sisa jasa pada RAB.');
        }

        DB::beginTransaction();
        $storedFile = null;
        try {
            $filePayload = $this->storeProof($request);
            $storedFile = $filePayload['bukti_bayar'] ?? null;

            $pembayaran = PembayaranJasa::create([
                'id_setting_proyek' => $request->id_setting_proyek,
                'id_master_project_detail' => $isCustom ? null : $detail->id,
                'id_jasa_custom' => $isCustom ? $detail->id : null,
                'id_tukang' => $request->id_tukang,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'jumlah_bayar' => $jumlahBayar,
                'keterangan' => $request->keterangan ?? '',
                'bukti_bayar' => $filePayload['bukti_bayar'] ?? null,
                'bukti_bayar_original_name' => $filePayload['bukti_bayar_original_name'] ?? null,
                'bukti_bayar_extension' => $filePayload['bukti_bayar_extension'] ?? null,
            ]);

            app(KeuanganProyekService::class)->catat(
                KeuanganProyekService::SUMBER_PEMBAYARAN_JASA,
                $pembayaran->id,
                $request->tanggal_pembayaran,
                $jumlahBayar,
                $request->keterangan ?? 'Pembayaran jasa proyek',
                $setting->id_master_project,
                $filePayload['bukti_bayar'] ?? null
            );

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran jasa berhasil disimpan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->deleteProofFile($storedFile);
            Log::error('PembayaranJasaController@store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan pembayaran jasa.',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $payment = PembayaranJasa::findOrFail($id);

        $request->validate($this->rules(), $this->messages());

        $setting = SettingProyek::with('masterProject')->findOrFail($request->id_setting_proyek);

        $isCustom = !empty($request->id_jasa_custom);
        if ($isCustom) {
            $detail = $this->resolveCustomServiceDetail($setting, $request->id_jasa_custom);
        } else {
            $detail = $this->resolveServiceDetail($setting, $request->id_master_project_detail);
        }

        $jumlahBayar = $this->parseCurrency($request->jumlah_bayar);
        $summary = $this->buildServiceSummary($setting, $detail, $payment->id);

        if ($jumlahBayar <= 0) {
            return $this->amountValidationError('Jumlah bayar harus lebih besar dari 0.');
        }

        if ($jumlahBayar > $summary['sisa_bayar_raw']) {
            return $this->amountValidationError('Jumlah bayar tidak boleh melebihi sisa jasa pada RAB.');
        }

        DB::beginTransaction();
        $storedFile = null;
        $oldFile = $payment->bukti_bayar;
        try {
            $filePayload = $this->storeProof($request);
            $storedFile = $filePayload['bukti_bayar'] ?? null;

            $payment->update([
                'id_setting_proyek' => $request->id_setting_proyek,
                'id_master_project_detail' => $isCustom ? null : $detail->id,
                'id_jasa_custom' => $isCustom ? $detail->id : null,
                'id_tukang' => $request->id_tukang,
                'tanggal_pembayaran' => $request->tanggal_pembayaran,
                'jumlah_bayar' => $jumlahBayar,
                'keterangan' => $request->keterangan ?? '',
                'bukti_bayar' => $filePayload['bukti_bayar'] ?? $payment->bukti_bayar,
                'bukti_bayar_original_name' => $filePayload['bukti_bayar_original_name'] ?? $payment->bukti_bayar_original_name,
                'bukti_bayar_extension' => $filePayload['bukti_bayar_extension'] ?? $payment->bukti_bayar_extension,
            ]);

            app(KeuanganProyekService::class)->sinkron(
                KeuanganProyekService::SUMBER_PEMBAYARAN_JASA,
                (int) $payment->id,
                $request->tanggal_pembayaran,
                $jumlahBayar,
                $request->keterangan ?? 'Pembayaran jasa proyek',
                $setting->id_master_project
            );

            DB::commit();

            if ($storedFile && $oldFile && $oldFile !== $storedFile) {
                $this->deleteProofFile($oldFile);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pembayaran jasa berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->deleteProofFile($storedFile);
            Log::error('PembayaranJasaController@update Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui pembayaran jasa.',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $payment = PembayaranJasa::findOrFail($id);

        DB::beginTransaction();
        try {
            $fileName = $payment->bukti_bayar;

            app(KeuanganProyekService::class)->hapus(
                KeuanganProyekService::SUMBER_PEMBAYARAN_JASA,
                (int) $payment->id
            );

            $payment->delete();
            DB::commit();
            $this->deleteProofFile($fileName);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PembayaranJasaController@destroy Error: ' . $e->getMessage(), [
                'id' => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus pembayaran jasa.',
            ], 500);
        }
    }

    public function storeCustomService(Request $request)
    {
        $request->validate([
            'id_setting_proyek' => 'required|exists:setting_proyek,id',
            'nama_jasa' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'id_setting_proyek.required' => 'Unit wajib dipilih.',
            'nama_jasa.required' => 'Nama jasa wajib diisi.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
        ]);

        DB::beginTransaction();
        try {
            $custom = JasaCustom::create([
                'id_setting_proyek' => $request->id_setting_proyek,
                'nama_jasa' => $request->nama_jasa,
                'satuan' => $request->satuan,
                'harga_satuan' => $this->parseCurrency($request->harga_satuan),
                'keterangan' => $request->keterangan ?? '',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Jasa custom berhasil ditambahkan.',
                'data' => $custom,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PembayaranJasaController@storeCustomService Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan jasa custom.',
            ], 500);
        }
    }

    public function destroyCustomService(string $id)
    {
        $custom = JasaCustom::findOrFail($id);

        DB::beginTransaction();
        try {
            PembayaranJasa::where('id_jasa_custom', $custom->id)->delete();
            $custom->delete();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Jasa custom dan data pembayaran terkait berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PembayaranJasaController@destroyCustomService Error: ' . $e->getMessage(), [
                'id' => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus jasa custom.',
            ], 500);
        }
    }

    public function updateCustomService(Request $request, string $id)
    {
        $custom = JasaCustom::findOrFail($id);

        $request->validate([
            'nama_jasa' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'harga_satuan' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ], [
            'nama_jasa.required' => 'Nama jasa wajib diisi.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
        ]);

        DB::beginTransaction();
        try {
            $custom->update([
                'nama_jasa' => $request->nama_jasa,
                'satuan' => $request->satuan,
                'harga_satuan' => $this->parseCurrency($request->harga_satuan),
                'keterangan' => $request->keterangan ?? '',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Jasa custom berhasil diperbarui.',
                'data' => $custom,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PembayaranJasaController@updateCustomService Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui jasa custom.',
            ], 500);
        }
    }

    private function rules(): array
    {
        return [
            'id_setting_proyek' => 'required|exists:setting_proyek,id',
            'id_master_project_detail' => 'nullable|exists:master_project_detail,id',
            'id_jasa_custom' => 'nullable|exists:jasa_custom,id',
            'tanggal_pembayaran' => 'required|date',
            'id_tukang' => 'required|exists:tukang,id',
            'jumlah_bayar' => 'required',
            'keterangan' => 'nullable|string',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    private function messages(): array
    {
        return [
            'id_setting_proyek.required' => 'Unit wajib dipilih.',
            'id_setting_proyek.exists' => 'Unit tidak valid.',
            'id_master_project_detail.exists' => 'Jasa RAB tidak valid.',
            'id_jasa_custom.exists' => 'Jasa custom tidak valid.',
            'tanggal_pembayaran.required' => 'Tanggal bayar wajib diisi.',
            'id_tukang.required' => 'Tukang wajib dipilih.',
            'id_tukang.exists' => 'Tukang tidak valid.',
            'jumlah_bayar.required' => 'Nominal bayar wajib diisi.',
            'bukti_bayar.mimes' => 'Bukti bayar harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_bayar.max' => 'Ukuran bukti bayar maksimal 2MB.',
        ];
    }

    private function buildSettingSummary(SettingProyek $setting): array
    {
        $rabJasaDefault = (int) MasterProjectDetail::query()
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'jasa')
            ->sum('sub_total');

        $rabJasaCustom = (int) JasaCustom::query()
            ->where('id_setting_proyek', $setting->id)
            ->sum('harga_satuan');

        $rabJasa = $rabJasaDefault + $rabJasaCustom;

        $totalBayar = (int) PembayaranJasa::query()
            ->where('id_setting_proyek', $setting->id)
            ->sum('jumlah_bayar');

        $sisaBayar = max($rabJasa - $totalBayar, 0);
        $persentaseBayar = $rabJasa > 0 ? min(round(($totalBayar / $rabJasa) * 100, 2), 100) : 0;
        $status = $this->buildStatusSummary($rabJasa, $totalBayar);

        return [
            'id_setting_proyek' => $setting->id,
            'lokasi_perumahan' => $setting->lokasi?->nama_kavling ?? '-',
            'unit_rumah' => $setting->kavling?->kode_kavling ?? '-',
            'type_rumah' => $setting->masterProject?->jenis_proyek ?? '-',
            'label_unit' => ($setting->lokasi?->nama_kavling ?? '-') . ' - ' . ($setting->kavling?->kode_kavling ?? '-') . ' - ' . ($setting->masterProject?->jenis_proyek ?? '-'),
            'rab_jasa' => $this->formatRupiah($rabJasa),
            'rab_jasa_raw' => $rabJasa,
            'total_bayar' => $this->formatRupiah($totalBayar),
            'total_bayar_raw' => $totalBayar,
            'sisa_bayar' => $this->formatRupiah($sisaBayar),
            'sisa_bayar_raw' => $sisaBayar,
            'persentase_bayar' => $this->formatPersentase($persentaseBayar),
            'persentase_bayar_raw' => $persentaseBayar,
            'status' => $status['label'],
            'status_badge' => $status['badge'],
        ];
    }

    private function buildServiceRows(SettingProyek $setting)
    {
        $services = $this->getServiceDetails($setting);
        $customServices = $this->getCustomServiceDetails($setting);
        $paidTotals = $this->getServicePaidTotals($setting->id);
        $customPaidTotals = $this->getCustomServicePaidTotals($setting->id);

        $rows = $services->map(function ($detail) use ($paidTotals) {
            $rab = (int) ($detail->sub_total ?? 0);
            $realisasi = (int) ($paidTotals[$detail->id] ?? 0);
            $sisa = max($rab - $realisasi, 0);

            return [
                'id' => $detail->id,
                'tipe' => 'default',
                'nama_jasa' => $detail->nama_item ?? '-',
                'rab' => $this->formatRupiah($rab),
                'rab_raw' => $rab,
                'realisasi' => $this->formatRupiah($realisasi),
                'realisasi_raw' => $realisasi,
                'sisa' => $this->formatRupiah($sisa),
                'sisa_raw' => $sisa,
            ];
        });

        $customRows = $customServices->map(function ($detail) use ($customPaidTotals) {
            $rab = (int) ($detail->harga_satuan ?? 0);
            $realisasi = (int) ($customPaidTotals[$detail->id] ?? 0);
            $sisa = max($rab - $realisasi, 0);

            return [
                'id' => $detail->id,
                'tipe' => 'custom',
                'nama_jasa' => $detail->nama_jasa ?? '-',
                'satuan' => $detail->satuan ?? '',
                'keterangan' => $detail->keterangan ?? '',
                'rab' => $this->formatRupiah($rab),
                'rab_raw' => $rab,
                'realisasi' => $this->formatRupiah($realisasi),
                'realisasi_raw' => $realisasi,
                'sisa' => $this->formatRupiah($sisa),
                'sisa_raw' => $sisa,
            ];
        });

        return $rows->concat($customRows)->values();
    }

    private function buildServiceSummary(SettingProyek $setting, $detail, ?int $excludePaymentId = null): array
    {
        $isCustom = $detail instanceof JasaCustom;
        $rabJasa = $isCustom ? (int) ($detail->harga_satuan ?? 0) : (int) ($detail->sub_total ?? 0);
        $namaJasa = $isCustom ? ($detail->nama_jasa ?? '-') : ($detail->nama_item ?? '-');

        $paidQuery = PembayaranJasa::query()
            ->where('id_setting_proyek', $setting->id);

        if ($isCustom) {
            $paidQuery->where('id_jasa_custom', $detail->id);
        } else {
            $paidQuery->where('id_master_project_detail', $detail->id);
        }

        if ($excludePaymentId) {
            $paidQuery->where('id', '!=', $excludePaymentId);
        }

        $totalBayar = (int) $paidQuery->sum('jumlah_bayar');
        $sisaBayar = max($rabJasa - $totalBayar, 0);
        $persentaseBayar = $rabJasa > 0 ? min(round(($totalBayar / $rabJasa) * 100, 2), 100) : 0;
        $status = $this->buildStatusSummary($rabJasa, $totalBayar);

        return [
            'id_setting_proyek' => $setting->id,
            'id_master_project_detail' => $isCustom ? null : $detail->id,
            'id_jasa_custom' => $isCustom ? $detail->id : null,
            'lokasi_perumahan' => $setting->lokasi?->nama_kavling ?? '-',
            'unit_rumah' => $setting->kavling?->kode_kavling ?? '-',
            'type_rumah' => $setting->masterProject?->jenis_proyek ?? '-',
            'nama_jasa' => $namaJasa,
            'label_unit' => ($setting->lokasi?->nama_kavling ?? '-') . ' - ' . ($setting->kavling?->kode_kavling ?? '-') . ' - ' . ($setting->masterProject?->jenis_proyek ?? '-'),
            'rab_jasa' => $this->formatRupiah($rabJasa),
            'rab_jasa_raw' => $rabJasa,
            'total_bayar' => $this->formatRupiah($totalBayar),
            'total_bayar_raw' => $totalBayar,
            'sisa_bayar' => $this->formatRupiah($sisaBayar),
            'sisa_bayar_raw' => $sisaBayar,
            'persentase_bayar' => $this->formatPersentase($persentaseBayar),
            'persentase_bayar_raw' => $persentaseBayar,
            'status' => $status['label'],
            'status_badge' => $status['badge'],
        ];
    }

    private function buildStatusSummary(int $rabJasa, int $totalBayar): array
    {
        if ($totalBayar <= 0) {
            return [
                'label' => 'Belum Dibayar',
                'badge' => '<span class="badge badge-danger">Belum Dibayar</span>',
            ];
        }

        if ($rabJasa > 0 && $totalBayar < $rabJasa) {
            return [
                'label' => 'Belum Lunas',
                'badge' => '<span class="badge badge-warning">Belum Lunas</span>',
            ];
        }

        return [
            'label' => 'Lunas',
            'badge' => '<span class="badge badge-primary">Lunas</span>',
        ];
    }

    private function getRabJasaTotals()
    {
        return MasterProjectDetail::query()
            ->selectRaw('id_master_project, SUM(sub_total) as total')
            ->where('tipe', 'jasa')
            ->groupBy('id_master_project')
            ->pluck('total', 'id_master_project');
    }

    private function getPaidTotals()
    {
        return PembayaranJasa::query()
            ->selectRaw('id_setting_proyek, SUM(jumlah_bayar) as total')
            ->groupBy('id_setting_proyek')
            ->pluck('total', 'id_setting_proyek');
    }

    private function getServiceDetails(SettingProyek $setting)
    {
        return MasterProjectDetail::query()
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'jasa')
            ->orderBy('id')
            ->get();
    }

    private function getCustomServiceDetails(SettingProyek $setting)
    {
        return JasaCustom::query()
            ->where('id_setting_proyek', $setting->id)
            ->orderBy('id')
            ->get();
    }

    private function getServicePaidTotals(int $settingId)
    {
        return PembayaranJasa::query()
            ->selectRaw('id_master_project_detail, SUM(jumlah_bayar) as total')
            ->where('id_setting_proyek', $settingId)
            ->whereNotNull('id_master_project_detail')
            ->groupBy('id_master_project_detail')
            ->pluck('total', 'id_master_project_detail');
    }

    private function getCustomServicePaidTotals(int $settingId)
    {
        return PembayaranJasa::query()
            ->selectRaw('id_jasa_custom, SUM(jumlah_bayar) as total')
            ->where('id_setting_proyek', $settingId)
            ->whereNotNull('id_jasa_custom')
            ->groupBy('id_jasa_custom')
            ->pluck('total', 'id_jasa_custom');
    }

    private function resolveServiceDetail(SettingProyek $setting, $detailId): MasterProjectDetail
    {
        return MasterProjectDetail::query()
            ->where('id', $detailId)
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'jasa')
            ->firstOrFail();
    }

    private function resolveCustomServiceDetail(SettingProyek $setting, $detailId): JasaCustom
    {
        return JasaCustom::query()
            ->where('id', $detailId)
            ->where('id_setting_proyek', $setting->id)
            ->firstOrFail();
    }

    private function resolveServiceDetailOrCustom(SettingProyek $setting, $detailId)
    {
        $detail = MasterProjectDetail::query()
            ->where('id', $detailId)
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'jasa')
            ->first();

        if ($detail) {
            return $detail;
        }

        return $this->resolveCustomServiceDetail($setting, $detailId);
    }

    private function transformPayment(PembayaranJasa $payment): array
    {
        return [
            'id' => $payment->id,
            'tanggal_pembayaran' => Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('d F Y'),
            'tanggal_pembayaran_raw' => $payment->tanggal_pembayaran,
            'nama_tukang' => $payment->tukang?->nama ?? '-',
            'nama_jasa' => $payment->jasaCustom?->nama_jasa ?? $payment->jasaDetail?->nama_item ?? '-',
            'id_tukang' => $payment->id_tukang,
            'id_master_project_detail' => $payment->id_master_project_detail,
            'id_jasa_custom' => $payment->id_jasa_custom,
            'jumlah_bayar' => number_format((int) $payment->jumlah_bayar, 0, ',', '.'),
            'jumlah_bayar_raw' => (int) $payment->jumlah_bayar,
            'keterangan' => $payment->keterangan ?: '-',
            'bukti_url' => $payment->bukti_bayar ? asset(self::BUKTI_DIR . '/' . $payment->bukti_bayar) : null,
            'bukti_name' => $payment->bukti_bayar_original_name ?: $payment->bukti_bayar,
            'bukti_type' => strtolower($payment->bukti_bayar_extension ?: '') === 'pdf' ? 'pdf' : 'image',
        ];
    }

    public function nota(string $id)
    {
        Carbon::setLocale('id');

        $payment = PembayaranJasa::with([
            'settingProyek.lokasi',
            'settingProyek.kavling',
            'settingProyek.masterProject',
            'jasaDetail',
            'tukang',
        ])->findOrFail($id);

        $setting = $payment->settingProyek;

        $company = [
            'nama_perusahaan' => \App\Models\Konfigurasi::value('nama_perusahaan') ?: 'Dealaska',
            'alamat' => \App\Models\Konfigurasi::value('alamat') ?: '',
            'telp' => \App\Models\Konfigurasi::value('telp') ?: '',
            'kota' => \App\Models\Konfigurasi::value('kota_penandatanganan') ?: '',
            'nama_penandatangan' => \App\Models\Konfigurasi::value('nama_penandatangan_1') ?: '',
            'jabatan_penandatangan' => \App\Models\Konfigurasi::value('jabatan_penandatangan_1') ?: '',
        ];

        $namaJasa = $payment->jasaDetail?->nama_item ?? '-';

        $totalTerkumpul = (int) PembayaranJasa::where('id_setting_proyek', $payment->id_setting_proyek)
            ->where('id_master_project_detail', $payment->id_master_project_detail)
            ->sum('jumlah_bayar');

        $data = [
            'no_nota' => 'PJ-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'tanggal' => Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('d F Y'),
            'unit' => $setting?->kavling?->kode_kavling ?? '-',
            'lokasi' => $setting?->lokasi?->nama_kavling ?? '-',
            'type' => $setting?->masterProject?->jenis_proyek ?? '-',
            'nama_jasa' => $namaJasa,
            'tukang' => $payment->tukang?->nama ?? '-',
            'jumlah_bayar' => (int) $payment->jumlah_bayar,
            'terbilang' => $this->terbilang((int) $payment->jumlah_bayar),
            'keterangan' => $payment->keterangan ?: '-',
            'total_terkumpul' => $totalTerkumpul,
        ];

        return view('admin.pembayaran_jasa.nota', compact('company', 'data'));
    }

    private function terbilang(int $nilai): string
    {
        $angka = [
            '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
            'sepuluh', 'sebelas',
        ];

        $convert = function (int $n) use (&$convert, $angka): string {
            if ($n < 12) {
                return $angka[$n];
            }
            if ($n < 20) {
                return $convert($n - 10) . ' belas';
            }
            if ($n < 100) {
                return $convert((int) floor($n / 10)) . ' puluh ' . $angka[$n % 10];
            }
            if ($n < 200) {
                return 'seratus ' . $convert($n - 100);
            }
            if ($n < 1000) {
                return $convert((int) floor($n / 100)) . ' ratus ' . $convert($n % 100);
            }
            if ($n < 2000) {
                return 'seribu ' . $convert($n - 1000);
            }
            if ($n < 1000000) {
                return $convert((int) floor($n / 1000)) . ' ribu ' . $convert($n % 1000);
            }
            if ($n < 1000000000) {
                return $convert((int) floor($n / 1000000)) . ' juta ' . $convert($n % 1000000);
            }

            return $convert((int) floor($n / 1000000000)) . ' miliar ' . $convert($n % 1000000000);
        };

        $result = trim($convert($nilai));
        if ($nilai === 0) {
            $result = 'nol';
        }

        return $result ?: 'nol';
    }

    private function parseCurrency($value): int
    {
        return (int) str_replace('.', '', (string) $value);
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private function formatPersentase(float $value): string
    {
        $formatted = number_format($value, 2, ',', '.');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return $formatted . '%';
    }

    private function amountValidationError(string $message)
    {
        return response()->json([
            'status' => 'error',
            'errors' => [
                'jumlah_bayar' => [$message],
            ],
        ], 422);
    }

    private function storeProof(Request $request): array
    {
        if (!$request->hasFile('bukti_bayar') || !$request->file('bukti_bayar')->isValid()) {
            return [];
        }

        $file = $request->file('bukti_bayar');
        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = now()->format('YmdHis') . '_' . Str::random(12) . '.' . $extension;
        $destination = public_path(self::BUKTI_DIR);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $fileName);

        return [
            'bukti_bayar' => $fileName,
            'bukti_bayar_original_name' => $file->getClientOriginalName(),
            'bukti_bayar_extension' => $extension,
        ];
    }

    private function deleteProofFile(?string $fileName): void
    {
        if (!$fileName) {
            return;
        }

        $path = public_path(self::BUKTI_DIR . '/' . $fileName);
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
