<?php

namespace App\Http\Controllers\Laporan;

use App\Models\MasterProjectDetail;
use App\Models\PembayaranJasa;
use App\Models\SettingProyek;

class SisaJasaPerProyekController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Sisa / Telah Dibayar Jasa Tukang - Per Proyek';
    }

    protected function reportFolder(): string
    {
        return 'sisa_jasa_per_proyek';
    }

    protected function reportKey(): string
    {
        return 'sisa-jasa-per-proyek';
    }

    protected function reportFilename(): string
    {
        return 'laporan_sisa_jasa_per_proyek';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Unit', 'Lokasi', 'Type Rumah', 'RAB Jasa', 'Sudah Dibayar', 'Sisa'];
    }

    protected function moneyColumns(): array
    {
        return [4, 5, 6];
    }

    protected function hasLokasiFilter(): bool
    {
        return true;
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $no = 1;

        $query = SettingProyek::with(['lokasi', 'kavling', 'masterProject']);

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
            $rab = (int) MasterProjectDetail::where('id_master_project', $setting->id_master_project)
                ->where('tipe', 'jasa')
                ->sum('sub_total');

            $dibayar = (int) PembayaranJasa::where('id_setting_proyek', $setting->id)
                ->whereBetween('tanggal_pembayaran', [$period['dari'], $period['sampai']])
                ->sum('jumlah_bayar');

            $sisa = max($rab - $dibayar, 0);

            $rows[] = [
                $no++,
                $setting->kavling?->kode_kavling ?? '-',
                $setting->lokasi?->nama_kavling ?? '-',
                $setting->masterProject?->jenis_proyek ?? '-',
                $rab,
                $dibayar,
                $sisa,
            ];
        }

        return $rows;
    }
}
