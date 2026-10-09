<?php

namespace App\Http\Controllers\Laporan;

use App\Models\BarangKeluar;
use App\Models\MasterProjectDetail;
use App\Models\PembayaranJasa;
use App\Models\SettingProyek;
use Illuminate\Support\Facades\DB;

class ProgresUnitController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Progres Unit';
    }

    protected function reportFolder(): string
    {
        return 'progres_unit';
    }

    protected function reportKey(): string
    {
        return 'progres-unit';
    }

    protected function reportFilename(): string
    {
        return 'laporan_progres_unit';
    }

    protected function hasLokasiFilter(): bool
    {
        return true;
    }

    protected function getHeaders(): array
    {
        return ['No', 'Unit', 'Lokasi', 'Type Rumah', 'Tukang', 'Real. Bahan', 'Real. Jasa', 'Real. Total', 'Status'];
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $no = 1;

        $query = SettingProyek::with(['lokasi', 'kavling', 'masterProject', 'tukang']);

        if (!empty($period['lokasi'])) {
            $query->whereHas('lokasi', function ($q) use ($period) {
                $q->where('id_lokasi', $period['lokasi']);
            });
        }

        if (!empty($period['unit'])) {
            $query->where('id_kavling', $period['unit']);
        }

        $settings = $query->orderBy('id')->get();

        foreach ($settings as $setting) {
            $rabMaterial = (int) MasterProjectDetail::where('id_master_project', $setting->id_master_project)
                ->where('tipe', 'material')
                ->sum('sub_total');

            $rabJasa = (int) MasterProjectDetail::where('id_master_project', $setting->id_master_project)
                ->where('tipe', 'jasa')
                ->sum('sub_total');

            $realisasiMaterial = (int) BarangKeluar::query()
                ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
                ->join('barang as b', 'd.id_barang', '=', 'b.id')
                ->where('barang_keluar.id_kavling', $setting->id_kavling)
                ->whereBetween('barang_keluar.tanggal', [$period['dari'], $period['sampai']])
                ->sum(DB::raw('d.jumlah * b.harga_beli'));

            $realisasiJasa = (int) PembayaranJasa::where('id_setting_proyek', $setting->id)
                ->whereBetween('tanggal_pembayaran', [$period['dari'], $period['sampai']])
                ->sum('jumlah_bayar');

            $persenBahan = $this->persen($realisasiMaterial, $rabMaterial);
            $persenJasa = $this->persen($realisasiJasa, $rabJasa);
            $persenTotal = $this->persen($realisasiMaterial + $realisasiJasa, $rabMaterial + $rabJasa);

            $rows[] = [
                $no++,
                $setting->kavling?->kode_kavling ?? '-',
                $setting->lokasi?->nama_kavling ?? '-',
                $setting->masterProject?->jenis_proyek ?? '-',
                $setting->tukang?->nama ?? '-',
                $this->persenLabel($persenBahan),
                $this->persenLabel($persenJasa),
                $this->persenLabel($persenTotal),
                $this->statusLabel((int) $setting->status),
            ];
        }

        return $rows;
    }
}
