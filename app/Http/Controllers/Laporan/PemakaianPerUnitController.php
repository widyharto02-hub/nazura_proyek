<?php

namespace App\Http\Controllers\Laporan;

use App\Models\BarangKeluar;

class PemakaianPerUnitController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Pemakaian Barang per Unit';
    }

    protected function reportFolder(): string
    {
        return 'pemakaian_per_unit';
    }

    protected function reportKey(): string
    {
        return 'pemakaian-per-unit';
    }

    protected function reportFilename(): string
    {
        return 'laporan_pemakaian_per_unit';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Unit', 'Lokasi', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Satuan', 'Total'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Pemakaian';
    }

    protected function moneyColumns(): array
    {
        return [6, 7];
    }

    protected function hasLokasiFilter(): bool
    {
        return true;
    }

    protected function buildRows(array $period): array
    {
        $this->grandTotal = 0;

        $query = BarangKeluar::with(['kavling', 'lokasi', 'detail.barang.satuan'])
            ->whereNotNull('id_kavling')
            ->whereBetween('tanggal', [$period['dari'], $period['sampai']]);

        if (!empty($period['lokasi'])) {
            $query->where('id_lokasi', $period['lokasi']);
        }

        if (!empty($period['unit'])) {
            $query->where('id_kavling', $period['unit']);
        }

        $list = $query->orderBy('id_lokasi')
            ->orderBy('id_kavling')
            ->get();

        $grouped = [];
        foreach ($list as $bk) {
            foreach ($bk->detail as $det) {
                $key = $bk->id_kavling . '-' . $det->id_barang;
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'unit' => $bk->kavling?->kode_kavling ?? '-',
                        'lokasi' => $bk->lokasi?->nama_kavling ?? '-',
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
                $g['unit'],
                $g['lokasi'],
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
