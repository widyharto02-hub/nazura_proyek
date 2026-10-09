<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\PembayaranTukang;
use App\Models\Tukang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PembayaranTukangController extends Controller
{
    private const BUKTI_DIR = 'assets/pembayaran-tukang/bukti';

    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = PembayaranTukang::with('tukang')->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal)->format('d-m-Y');
                })
                ->addColumn('nama_tukang', function ($row) {
                    return e($row->tukang?->nama ?? '-');
                })
                ->editColumn('nama_pekerjaan', function ($row) {
                    return e($row->nama_pekerjaan);
                })
                ->editColumn('keterangan', function ($row) {
                    return e($row->keterangan ?: '-');
                })
                ->editColumn('nominal', function ($row) {
                    return 'Rp ' . number_format((int) $row->nominal, 0, ',', '.');
                })
                ->addColumn('bukti_bayar_view', function ($row) {
                    if (!$row->bukti_bayar) {
                        return '<span class="text-muted">-</span>';
                    }

                    $url = asset(self::BUKTI_DIR . '/' . $row->bukti_bayar);
                    $name = $row->bukti_bayar_original_name ?: $row->bukti_bayar;

                    return '<a href="' . e($url) . '" target="_blank" rel="noopener" class="btn btn-info btn-sm" title="' . e($name) . '"><i class="fas fa-eye"></i> Lihat</a>';
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $btn = '<div class="d-flex justify-content-center">';

                    $btn .= '<a href="' . e(route('pembayaran-tukang.nota', $row->id)) . '" target="_blank" class="btn btn-info btn-sm mx-1" title="Cetak Nota"><i class="fas fa-print"></i></a>';

                    if ($permissions['edit']) {
                        $btn .= '<button type="button" class="btn btn-primary btn-sm mx-1 edit-button" data-url="' . e(route('pembayaran-tukang.edit', $row->id)) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e(route('pembayaran-tukang.destroy', $row->id)) . '" method="POST" style="display:inline;">'
                            . csrf_field() . method_field('DELETE')
                            . '<button type="submit" class="delete-button btn btn-danger btn-sm mx-1">Hapus</button></form>';
                    }

                    return $btn . '</div>';
                })
                ->rawColumns(['bukti_bayar_view', 'action'])
                ->make(true);
        }

        $tukangList = Tukang::orderBy('nama')->get();
        $today = now()->toDateString();

        return view('admin.pembayaran_tukang.index', compact('permissions', 'tukangList', 'today'));
    }

    public function nota(string $id)
    {
        Carbon::setLocale('id');

        $data = PembayaranTukang::with('tukang')->findOrFail($id);
        $company = [
            'nama_perusahaan' => \App\Models\Konfigurasi::value('nama_perusahaan') ?: 'Dealaska',
            'alamat' => \App\Models\Konfigurasi::value('alamat') ?: '',
            'telp' => \App\Models\Konfigurasi::value('telp') ?: '',
            'kota' => \App\Models\Konfigurasi::value('kota_penandatanganan') ?: '',
            'nama_penandatangan' => \App\Models\Konfigurasi::value('nama_penandatangan_1') ?: '',
            'jabatan_penandatangan' => \App\Models\Konfigurasi::value('jabatan_penandatangan_1') ?: '',
        ];

        $pdf = \App::make('tcpdf');
        $pdf->SetTitle('Nota-' . $id);
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();
        
        $logoPath = public_path('assets/logo-dealaska.jpg');
        $logo = file_exists($logoPath) ? '<img src="' . $logoPath . '" width="120">' : '';

        $html = '
        <div style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px;">' . $logo . '</div>
        <div style="text-align: center; font-size: 16px; font-weight: bold; text-decoration: underline; margin: 20px 0;">KWITANSI PEMBAYARAN TUKANG</div>
        <table style="width: 100%; font-size: 13px;">
            <tr><td width="140">No. Nota</td><td width="10">:</td><td>PT-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT) . '</td></tr>
            <tr><td>Tanggal</td><td>:</td><td>' . Carbon::parse($data->tanggal)->translatedFormat('d F Y') . '</td></tr>
            <tr><td>Nama Tukang</td><td>:</td><td>' . e($data->tukang?->nama ?? '-') . '</td></tr>
            <tr><td>Nama Pekerjaan</td><td>:</td><td>' . e($data->nama_pekerjaan) . '</td></tr>
            <tr><td>Keterangan</td><td>:</td><td>' . e($data->keterangan ?: '-') . '</td></tr>
        </table>
        <div style="border: 1px solid #000; padding: 10px; margin: 20px 0;">
            <div style="font-size: 12px;">Telah diterima sejumlah :</div>
            <div style="font-size: 18px; font-weight: bold;">Rp ' . number_format((int)$data->nominal, 0, ',', '.') . '</div>
        </div>
        <div style="font-style: italic; margin-bottom: 20px;">( ' . ucfirst($this->terbilang((int)$data->nominal)) . ' rupiah )</div>
        <table style="width: 100%;">
            <tr>
                <td width="60%"></td>
                <td width="40%" style="text-align: center;">
                    <div>' . $company['kota'] . ', ' . Carbon::parse($data->tanggal)->translatedFormat('d F Y') . '</div>
                    <div style="height: 60px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">' . $company['nama_penandatangan'] . '</div>
                    <div>' . $company['jabatan_penandatangan'] . '</div>
                </td>
            </tr>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Nota_Pembayaran_Tukang_' . $id . '.pdf', 'I');
        exit;
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

        return trim($convert($nilai)) ?: 'nol';
    }

    public function edit(string $id)
    {
        $data = PembayaranTukang::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $data->id,
                'tanggal' => $data->tanggal,
                'id_tukang' => $data->id_tukang,
                'nama_pekerjaan' => $data->nama_pekerjaan,
                'keterangan' => $data->keterangan,
                'nominal' => number_format((int) $data->nominal, 0, ',', '.'),
                'bukti_bayar_name' => $data->bukti_bayar_original_name ?: $data->bukti_bayar,
                'bukti_bayar_url' => $data->bukti_bayar ? asset(self::BUKTI_DIR . '/' . $data->bukti_bayar) : null,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules(), $this->messages());
        $nominal = $this->parseCurrency($request->nominal);
        $this->validateNominal($nominal);

        DB::beginTransaction();
        $storedFile = null;

        try {
            $filePayload = $this->storeProof($request);
            $storedFile = $filePayload['bukti_bayar'] ?? null;

            PembayaranTukang::create([
                'tanggal' => $request->tanggal,
                'id_tukang' => $request->id_tukang,
                'nama_pekerjaan' => $request->nama_pekerjaan,
                'keterangan' => $request->keterangan,
                'nominal' => $nominal,
                ...$filePayload,
            ]);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteProofFile($storedFile);
            Log::error('PembayaranTukangController@store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data.',
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $data = PembayaranTukang::findOrFail($id);
        $request->validate($this->rules(), $this->messages());
        $nominal = $this->parseCurrency($request->nominal);
        $this->validateNominal($nominal);

        DB::beginTransaction();
        $storedFile = null;
        $oldFile = $data->bukti_bayar;

        try {
            $filePayload = $this->storeProof($request);
            $storedFile = $filePayload['bukti_bayar'] ?? null;

            $payload = [
                'tanggal' => $request->tanggal,
                'id_tukang' => $request->id_tukang,
                'nama_pekerjaan' => $request->nama_pekerjaan,
                'keterangan' => $request->keterangan,
                'nominal' => $nominal,
            ];

            $data->update(array_merge($payload, $filePayload));
            DB::commit();

            if ($storedFile && $oldFile && $oldFile !== $storedFile) {
                $this->deleteProofFile($oldFile);
            }

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->deleteProofFile($storedFile);
            Log::error('PembayaranTukangController@update Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui data.',
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $data = PembayaranTukang::findOrFail($id);
        $fileName = $data->bukti_bayar;

        DB::beginTransaction();

        try {
            $data->delete();
            DB::commit();
            $this->deleteProofFile($fileName);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PembayaranTukangController@destroy Error: ' . $e->getMessage(), [
                'id' => $id,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data.',
            ], 500);
        }
    }

    private function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'id_tukang' => 'required|exists:tukang,id',
            'nama_pekerjaan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:2000',
            'nominal' => 'required',
            'bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    private function messages(): array
    {
        return [
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'id_tukang.required' => 'Nama tukang wajib dipilih.',
            'id_tukang.exists' => 'Nama tukang tidak valid.',
            'nama_pekerjaan.required' => 'Nama pekerjaan wajib diisi.',
            'nama_pekerjaan.max' => 'Nama pekerjaan maksimal 255 karakter.',
            'keterangan.max' => 'Keterangan maksimal 2000 karakter.',
            'nominal.required' => 'Nominal wajib diisi.',
            'bukti_bayar.mimes' => 'Bukti bayar harus berupa JPG, JPEG, PNG, atau PDF.',
            'bukti_bayar.max' => 'Ukuran bukti bayar maksimal 2MB.',
        ];
    }

    private function validateNominal(int $nominal): void
    {
        if ($nominal <= 0) {
            throw ValidationException::withMessages([
                'nominal' => ['Nominal harus lebih besar dari 0.'],
            ]);
        }
    }

    private function parseCurrency($value): int
    {
        return (int) preg_replace('/\D+/', '', (string) $value);
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

        $path = public_path(self::BUKTI_DIR . '/' . basename($fileName));

        if (is_file($path)) {
            @unlink($path);
        }
    }
}
