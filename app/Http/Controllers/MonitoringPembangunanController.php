<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\BarangKeluar;
use App\Models\MasterProject;
use App\Models\MasterProjectDetail;
use App\Models\PembayaranJasa;
use App\Models\SettingProyek;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MonitoringPembangunanController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();
        $mode = $request->get('mode', 'unit');

        if ($request->ajax()) {
            $materialTotals = MasterProjectDetail::selectRaw('id_master_project, SUM(sub_total) as total')
                ->where('tipe', 'material')
                ->groupBy('id_master_project')
                ->pluck('total', 'id_master_project');

            $jasaTotals = MasterProjectDetail::selectRaw('id_master_project, SUM(sub_total) as total')
                ->where('tipe', 'jasa')
                ->groupBy('id_master_project')
                ->pluck('total', 'id_master_project');

            $realisasiTotals = BarangKeluar::query()
                ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
                ->join('barang as b', 'd.id_barang', '=', 'b.id')
                ->selectRaw('barang_keluar.id_kavling, SUM(d.jumlah * b.harga_beli) as total')
                ->groupBy('barang_keluar.id_kavling')
                ->pluck('total', 'barang_keluar.id_kavling');

            $realisasiJasaTotals = PembayaranJasa::query()
                ->selectRaw('id_setting_proyek, SUM(jumlah_bayar) as total')
                ->groupBy('id_setting_proyek')
                ->pluck('total', 'id_setting_proyek');

            if ($mode === 'non-unit') {
                $data = MasterProject::query()
                    ->where('jenis_pekerjaan', 'non_unit')
                    ->orderBy('id', 'desc')
                    ->get();

                $realisasiNonUnitTotals = BarangKeluar::query()
                    ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
                    ->join('barang as b', 'd.id_barang', '=', 'b.id')
                    ->whereNotNull('barang_keluar.id_project')
                    ->selectRaw('barang_keluar.id_project, SUM(d.jumlah * b.harga_beli) as total')
                    ->groupBy('barang_keluar.id_project')
                    ->pluck('total', 'barang_keluar.id_project');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('project_label', function ($row) {
                        return '<div class="font-weight-bold">' . e($row->jenis_proyek ?? '-') . '</div><small class="text-muted">Project Non Unit</small>';
                    })
                    ->addColumn('project_type', function ($row) {
                        return $row->jenis_proyek ?? '-';
                    })
                    ->addColumn('realisasi_bahan_persen', function ($row) use ($materialTotals, $realisasiNonUnitTotals) {
                        $rabMaterial = (int) ($materialTotals[$row->id] ?? 0);
                        $realisasiMaterial = (int) ($realisasiNonUnitTotals[$row->id] ?? 0);
                        return $this->formatPersen($this->calculatePercentage($realisasiMaterial, $rabMaterial));
                    })
                    ->addColumn('realisasi_jasa_persen', function ($row) {
                        return $this->formatPersen(0);
                    })
                    ->addColumn('realisasi_total_persen', function ($row) use ($materialTotals, $jasaTotals, $realisasiNonUnitTotals) {
                        $rabMaterial = (int) ($materialTotals[$row->id] ?? 0);
                        $rabJasa = (int) ($jasaTotals[$row->id] ?? 0);
                        $realisasiMaterial = (int) ($realisasiNonUnitTotals[$row->id] ?? 0);
                        $persenTotal = $this->calculatePercentage($realisasiMaterial, $rabMaterial + $rabJasa);
                        $className = $persenTotal >= 30 ? 'bg-success text-white font-weight-bold' : '';

                        return '<span class="d-inline-block px-2 py-1 rounded ' . $className . '">' . e($this->formatPersen($persenTotal)) . '</span>';
                    })
                    ->addColumn('status_proyek', function () {
                        return 'Project Non Unit';
                    })
                    ->addColumn('action', function ($row) {
                        $detailUrl = route('monitoring-pembangunan.non-unit.detail', $row->id);
                        return '<a href="' . e($detailUrl) . '" class="btn btn-primary btn-sm">Detail</a>';
                    })
                    ->rawColumns(['project_label', 'realisasi_total_persen', 'action'])
                    ->make(true);
            }

            $data = SettingProyek::with(['lokasi', 'kavling', 'masterProject', 'tukang'])->orderBy('id', 'desc')->get();

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
                ->addColumn('nama_tukang', function ($row) {
                    return $row->tukang?->nama ?? '-';
                })
                ->addColumn('realisasi_bahan_persen', function ($row) use ($materialTotals, $realisasiTotals) {
                    $rabMaterial = (int) ($materialTotals[$row->id_master_project] ?? 0);
                    $realisasiMaterial = (int) ($realisasiTotals[$row->id_kavling] ?? 0);
                    return $this->formatPersen($this->calculatePercentage($realisasiMaterial, $rabMaterial));
                })
                ->addColumn('realisasi_jasa_persen', function ($row) use ($jasaTotals, $realisasiJasaTotals) {
                    $rabJasa = (int) ($jasaTotals[$row->id_master_project] ?? 0);
                    $realisasiJasa = (int) ($realisasiJasaTotals[$row->id] ?? 0);
                    return $this->formatPersen($this->calculatePercentage($realisasiJasa, $rabJasa));
                })
                ->addColumn('realisasi_total_persen', function ($row) use ($materialTotals, $jasaTotals, $realisasiTotals, $realisasiJasaTotals) {
                    $rabMaterial = (int) ($materialTotals[$row->id_master_project] ?? 0);
                    $rabJasa = (int) ($jasaTotals[$row->id_master_project] ?? 0);
                    $realisasiMaterial = (int) ($realisasiTotals[$row->id_kavling] ?? 0);
                    $realisasiJasa = (int) ($realisasiJasaTotals[$row->id] ?? 0);
                    $persenTotal = $this->calculatePercentage($realisasiMaterial + $realisasiJasa, $rabMaterial + $rabJasa);
                    $className = $persenTotal >= 30 ? 'bg-success text-white font-weight-bold' : '';

                    return '<span class="d-inline-block px-2 py-1 rounded ' . $className . '">' . e($this->formatPersen($persenTotal)) . '</span>';
                })
                ->addColumn('status_proyek', function ($row) {
                    return $this->getStatusLabel((int) $row->status);
                })
                ->addColumn('action', function ($row) {
                    $detailUrl = route('monitoring-pembangunan.detail', $row->id);
                    return '<a href="' . e($detailUrl) . '" class="btn btn-primary btn-sm">Detail</a>';
                })
                ->rawColumns(['unit_rumah', 'realisasi_total_persen', 'action'])
                ->make(true);
        }

        return view('admin.monitoring_pembangunan.index', compact('permissions'));
    }

    public function detail($id)
    {
        $setting = SettingProyek::with(['kavling', 'lokasi', 'masterProject', 'tukang'])->findOrFail($id);
        $detailData = $this->buildDetailDataForUnit($setting);

        return view('admin.monitoring_pembangunan.detail', [
            'mode' => 'unit',
            'detailData' => $detailData,
        ]);
    }

    public function detailNonUnit($id)
    {
        $project = MasterProject::findOrFail($id);
        $detailData = $this->buildDetailDataForNonUnit($project);

        return view('admin.monitoring_pembangunan.detail', [
            'mode' => 'non-unit',
            'detailData' => $detailData,
        ]);
    }

    private function buildDetailDataForUnit(SettingProyek $setting): array
    {
        $rabItems = MasterProjectDetail::query()
            ->with('barang')
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'material')
            ->orderBy('id')
            ->get();

        $realisasiItems = BarangKeluar::query()
            ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
            ->leftJoin('barang as b', 'd.id_barang', '=', 'b.id')
            ->where('barang_keluar.id_kavling', $setting->id_kavling)
            ->selectRaw("d.id_barang, COALESCE(b.nama, CONCAT('Barang #', d.id_barang)) as nama_barang, COALESCE(b.harga_beli, 0) as harga_satuan, SUM(d.jumlah) as total_jumlah")
            ->groupBy('d.id_barang', 'b.nama', 'b.harga_beli')
            ->get()
            ->keyBy('id_barang');

        $rows = [];
        $usedBarang = [];
        $totals = [
            'rab_qty' => 0,
            'rab_sub_total' => 0,
            'realisasi_qty' => 0,
            'realisasi_sub_total' => 0,
        ];
        $no = 1;

        foreach ($rabItems as $item) {
            $realisasi = $item->id_barang ? $realisasiItems->get($item->id_barang) : null;
            $rabQty = (float) ($item->qty ?? 0);
            $hargaSatuan = (int) ($item->harga_satuan ?? 0);
            $rabSubTotal = (int) ($item->sub_total ?? round($rabQty * $hargaSatuan));
            $realisasiQty = (float) ($realisasi->total_jumlah ?? 0);
            $realisasiHargaSatuan = $realisasi ? (int) ($realisasi->harga_satuan ?? 0) : $hargaSatuan;
            $realisasiSubTotal = (int) round($realisasiQty * $realisasiHargaSatuan);

            $rows[] = [
                'no' => $no++,
                'nama_barang' => $item->nama_item ?: ($item->barang?->nama ?? '-'),
                'rab' => $rabQty,
                'harga_satuan' => $hargaSatuan,
                'rab_sub_total' => $rabSubTotal,
                'realisasi' => $realisasiQty,
                'realisasi_sub_total' => $realisasiSubTotal,
                'presentase_realisasi' => $this->calculatePercentage($realisasiSubTotal, $rabSubTotal),
            ];

            if ($item->id_barang) {
                $usedBarang[] = (string) $item->id_barang;
            }

            $totals['rab_qty'] += $rabQty;
            $totals['rab_sub_total'] += $rabSubTotal;
            $totals['realisasi_qty'] += $realisasiQty;
            $totals['realisasi_sub_total'] += $realisasiSubTotal;
        }

        foreach ($realisasiItems as $idBarang => $item) {
            if (in_array((string) $idBarang, $usedBarang, true)) {
                continue;
            }

            $realisasiQty = (float) ($item->total_jumlah ?? 0);
            $hargaSatuan = (int) ($item->harga_satuan ?? 0);
            $realisasiSubTotal = (int) round($realisasiQty * $hargaSatuan);

            $rows[] = [
                'no' => $no++,
                'nama_barang' => $item->nama_barang ?: '-',
                'rab' => 0,
                'harga_satuan' => $hargaSatuan,
                'rab_sub_total' => 0,
                'realisasi' => $realisasiQty,
                'realisasi_sub_total' => $realisasiSubTotal,
                'presentase_realisasi' => 0,
            ];

            $totals['realisasi_qty'] += $realisasiQty;
            $totals['realisasi_sub_total'] += $realisasiSubTotal;
        }

        $totals['presentase_realisasi'] = $this->calculatePercentage(
            $totals['realisasi_sub_total'],
            $totals['rab_sub_total']
        );

        $materialTotal = (int) $totals['rab_sub_total'];
        $jasaTotal = (int) MasterProjectDetail::query()
            ->where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'jasa')
            ->sum('sub_total');
        $realisasiMaterialTotal = (int) $totals['realisasi_sub_total'];
        $realisasiJasaTotal = (int) PembayaranJasa::query()
            ->where('id_setting_proyek', $setting->id)
            ->sum('jumlah_bayar');

        return [
            'summary' => [
                'entity_label' => 'Unit Rumah',
                'unit_rumah' => $setting->kavling?->kode_kavling ?? '-',
                'lokasi_perumahan' => $setting->lokasi?->nama_kavling ?? '-',
                'type_rumah' => $setting->masterProject?->jenis_proyek ?? '-',
                'nama_tukang' => $setting->tukang?->nama ?? '-',
                'status_proyek' => $this->getStatusLabel((int) $setting->status),
                'persen_bahan' => $this->calculatePercentage($realisasiMaterialTotal, $materialTotal),
                'persen_jasa' => $this->calculatePercentage($realisasiJasaTotal, $jasaTotal),
                'persen_total' => $this->calculatePercentage($realisasiMaterialTotal + $realisasiJasaTotal, $materialTotal + $jasaTotal),
            ],
            'items' => $rows,
            'totals' => $totals,
        ];
    }

    private function buildDetailDataForNonUnit(MasterProject $project): array
    {
        $rabItems = MasterProjectDetail::query()
            ->with('barang')
            ->where('id_master_project', $project->id)
            ->where('tipe', 'material')
            ->orderBy('id')
            ->get();

        $realisasiItems = BarangKeluar::query()
            ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
            ->leftJoin('barang as b', 'd.id_barang', '=', 'b.id')
            ->where('barang_keluar.id_project', $project->id)
            ->selectRaw("d.id_barang, COALESCE(b.nama, CONCAT('Barang #', d.id_barang)) as nama_barang, COALESCE(b.harga_beli, 0) as harga_satuan, SUM(d.jumlah) as total_jumlah")
            ->groupBy('d.id_barang', 'b.nama', 'b.harga_beli')
            ->get()
            ->keyBy('id_barang');

        $rows = [];
        $usedBarang = [];
        $totals = [
            'rab_qty' => 0,
            'rab_sub_total' => 0,
            'realisasi_qty' => 0,
            'realisasi_sub_total' => 0,
        ];
        $no = 1;

        foreach ($rabItems as $item) {
            $realisasi = $item->id_barang ? $realisasiItems->get($item->id_barang) : null;
            $rabQty = (float) ($item->qty ?? 0);
            $hargaSatuan = (int) ($item->harga_satuan ?? 0);
            $rabSubTotal = (int) ($item->sub_total ?? round($rabQty * $hargaSatuan));
            $realisasiQty = (float) ($realisasi->total_jumlah ?? 0);
            $realisasiHargaSatuan = $realisasi ? (int) ($realisasi->harga_satuan ?? 0) : $hargaSatuan;
            $realisasiSubTotal = (int) round($realisasiQty * $realisasiHargaSatuan);

            $rows[] = [
                'no' => $no++,
                'nama_barang' => $item->nama_item ?: ($item->barang?->nama ?? '-'),
                'rab' => $rabQty,
                'harga_satuan' => $hargaSatuan,
                'rab_sub_total' => $rabSubTotal,
                'realisasi' => $realisasiQty,
                'realisasi_sub_total' => $realisasiSubTotal,
                'presentase_realisasi' => $this->calculatePercentage($realisasiSubTotal, $rabSubTotal),
            ];

            if ($item->id_barang) {
                $usedBarang[] = (string) $item->id_barang;
            }

            $totals['rab_qty'] += $rabQty;
            $totals['rab_sub_total'] += $rabSubTotal;
            $totals['realisasi_qty'] += $realisasiQty;
            $totals['realisasi_sub_total'] += $realisasiSubTotal;
        }

        foreach ($realisasiItems as $idBarang => $item) {
            if (in_array((string) $idBarang, $usedBarang, true)) {
                continue;
            }

            $realisasiQty = (float) ($item->total_jumlah ?? 0);
            $hargaSatuan = (int) ($item->harga_satuan ?? 0);
            $realisasiSubTotal = (int) round($realisasiQty * $hargaSatuan);

            $rows[] = [
                'no' => $no++,
                'nama_barang' => $item->nama_barang ?: '-',
                'rab' => 0,
                'harga_satuan' => $hargaSatuan,
                'rab_sub_total' => 0,
                'realisasi' => $realisasiQty,
                'realisasi_sub_total' => $realisasiSubTotal,
                'presentase_realisasi' => 0,
            ];

            $totals['realisasi_qty'] += $realisasiQty;
            $totals['realisasi_sub_total'] += $realisasiSubTotal;
        }

        $totals['presentase_realisasi'] = $this->calculatePercentage(
            $totals['realisasi_sub_total'],
            $totals['rab_sub_total']
        );

        $materialTotal = (int) $totals['rab_sub_total'];
        $jasaTotal = (int) MasterProjectDetail::query()
            ->where('id_master_project', $project->id)
            ->where('tipe', 'jasa')
            ->sum('sub_total');
        $realisasiMaterialTotal = (int) $totals['realisasi_sub_total'];

        return [
            'summary' => [
                'entity_label' => 'Project Non Unit',
                'unit_rumah' => '-',
                'lokasi_perumahan' => '-',
                'type_rumah' => $project->jenis_proyek ?? '-',
                'nama_tukang' => '-',
                'status_proyek' => 'Project Non Unit',
                'persen_bahan' => $this->calculatePercentage($realisasiMaterialTotal, $materialTotal),
                'persen_jasa' => 0,
                'persen_total' => $this->calculatePercentage($realisasiMaterialTotal, $materialTotal + $jasaTotal),
            ],
            'items' => $rows,
            'totals' => $totals,
        ];
    }

    private function getStatusLabel(int $status): string
    {
        return match ($status) {
            1 => 'Sudah Disetting',
            2 => 'Proses',
            3 => 'Selesai',
            default => 'Belum di Setting',
        };
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    private function formatQty($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    }

    public function formatPersen($value): string
    {
        return $this->formatQty($value) . '%';
    }

    private function calculatePercentage($actual, $target): float
    {
        $targetValue = (float) $target;
        if ($targetValue <= 0) {
            return 0;
        }

        return ((float) $actual / $targetValue) * 100;
    }
}

