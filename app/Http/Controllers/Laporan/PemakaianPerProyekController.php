<?php

namespace App\Http\Controllers\Laporan;

use App\Models\BarangKeluar;

class PemakaianPerProyekController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Pemakaian Barang per Proyek';
    }

    protected function reportFolder(): string
    {
        return 'pemakaian_per_proyek';
    }

    protected function reportKey(): string
    {
        return 'pemakaian-per-proyek';
    }

    protected function reportFilename(): string
    {
        return 'laporan_pemakaian_per_proyek';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Proyek', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Satuan', 'Total'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Pemakaian';
    }

    protected function moneyColumns(): array
    {
        return [5, 6];
    }

    protected function buildRows(array $period): array
    {
        $this->grandTotal = 0;

        $list = BarangKeluar::with(['masterProject', 'detail.barang.satuan'])
            ->whereNotNull('id_project')
            ->whereBetween('tanggal', [$period['dari'], $period['sampai']])
            ->orderBy('id_project')
            ->get();

        $grouped = [];
        foreach ($list as $bk) {
            foreach ($bk->detail as $det) {
                $key = $bk->id_project . '-' . $det->id_barang;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'proyek' => $bk->masterProject?->jenis_proyek ?? 'Proyek #' . $bk->id_project,
                        'nama_barang' => $det->barang?->nama ?? 'Barang #' . $det->id_barang,
                        'satuan' => $det->barang?->satuan?->nama_satuan ?? '-',
                        'harga' => (int) ($det->barang?->harga_beli ?? 0),
                        'qty' => 0,
                    ];
                }
                $grouped[$key]['qty'] += (int) $det->jumlah;
            }
        }

        $rows = [];
        $no = 1;
        foreach ($grouped as $g) {
            $total = $g['qty'] * $g['harga'];
            $this->grandTotal += $total;

            $rows[] = [
                $no++,
                $g['proyek'],
                $g['nama_barang'],
                $this->qty($g['qty']),
                $g['satuan'],
                $g['harga'],
                $total,
            ];
        }

        return $rows;
    }
}
