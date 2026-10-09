<?php

namespace App\Http\Controllers\Laporan;

use App\Models\PembayaranTukang;
use Carbon\Carbon;

class JasaHarianController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Jasa Harian Tukang';
    }

    protected function reportFolder(): string
    {
        return 'jasa_harian';
    }

    protected function reportKey(): string
    {
        return 'jasa-harian';
    }

    protected function reportFilename(): string
    {
        return 'laporan_jasa_harian';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Tanggal', 'Tukang', 'Nama Pekerjaan', 'Keterangan', 'Nominal'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Jasa Harian';
    }

    protected function moneyColumns(): array
    {
        return [5];
    }

    protected function buildRows(array $period): array
    {
        $this->grandTotal = 0;
        $rows = [];

        $list = PembayaranTukang::with('tukang')
            ->whereBetween('tanggal', [$period['dari'], $period['sampai']])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($list as $pt) {
            $this->grandTotal += (int) $pt->nominal;

            $rows[] = [
                $rows ? count($rows) + 1 : 1,
                Carbon::parse($pt->tanggal)->translatedFormat('d F Y'),
                $pt->tukang?->nama ?? '-',
                $pt->nama_pekerjaan ?? '-',
                $pt->keterangan ?: '-',
                (int) $pt->nominal,
            ];
        }

        return $rows;
    }
}
