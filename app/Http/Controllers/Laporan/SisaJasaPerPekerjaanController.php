<?php

namespace App\Http\Controllers\Laporan;

use App\Models\MasterProjectDetail;
use App\Models\MasterProjectPekerjaan;
use App\Models\PembayaranJasa;

class SisaJasaPerPekerjaanController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Sisa / Telah Dibayar Jasa Tukang - Per Jenis Pekerjaan';
    }

    protected function reportFolder(): string
    {
        return 'sisa_jasa_per_pekerjaan';
    }

    protected function reportKey(): string
    {
        return 'sisa-jasa-per-pekerjaan';
    }

    protected function reportFilename(): string
    {
        return 'laporan_sisa_jasa_per_pekerjaan';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Project', 'Jenis Pekerjaan', 'RAB Jasa', 'Sudah Dibayar', 'Sisa'];
    }

    protected function moneyColumns(): array
    {
        return [3, 4, 5];
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $no = 1;

        $pekerjaans = MasterProjectPekerjaan::with('project')->orderBy('id_master_project')->orderBy('id')->get();

        foreach ($pekerjaans as $pekerjaan) {
            $detailIds = MasterProjectDetail::where('id_master_project_pekerjaan', $pekerjaan->id)
                ->where('tipe', 'jasa')
                ->pluck('id');

            $rab = (int) MasterProjectDetail::where('id_master_project_pekerjaan', $pekerjaan->id)
                ->where('tipe', 'jasa')
                ->sum('sub_total');

            $dibayar = (int) PembayaranJasa::whereBetween('tanggal_pembayaran', [$period['dari'], $period['sampai']])
                ->whereIn('id_master_project_detail', $detailIds)
                ->sum('jumlah_bayar');

            $sisa = max($rab - $dibayar, 0);

            if ($rab <= 0 && $dibayar <= 0) {
                continue;
            }

            $rows[] = [
                $no++,
                $pekerjaan->project?->jenis_proyek ?? '-',
                $pekerjaan->nama_pekerjaan ?? '-',
                $rab,
                $dibayar,
                $sisa,
            ];
        }

        return $rows;
    }
}
