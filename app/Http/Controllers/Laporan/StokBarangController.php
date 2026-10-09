<?php

namespace App\Http\Controllers\Laporan;

use App\Models\Barang;

class StokBarangController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Rincian Stok Barang';
    }

    protected function reportFolder(): string
    {
        return 'stok_barang';
    }

    protected function reportKey(): string
    {
        return 'stok-barang';
    }

    protected function reportFilename(): string
    {
        return 'laporan_rincian_stok_barang';
    }

    protected function getHeaders(): array
    {
        return ['No', 'SKU', 'Nama Barang', 'Satuan', 'Stok Awal', 'Stok Minimal', 'Stok Saat Ini', 'Harga Beli', 'Nilai Stok', 'Deskripsi'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Nilai Stok';
    }

    protected function moneyColumns(): array
    {
        return [7, 8];
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $this->grandTotal = 0;

        $list = Barang::with('satuan')->orderBy('nama')->get();

        foreach ($list as $barang) {
            $nilai = (int) $barang->stok * (int) $barang->harga_beli;
            $this->grandTotal += $nilai;

            $rows[] = [
                count($rows) + 1,
                $barang->sku ?: '-',
                $barang->nama ?? '-',
                $barang->satuan?->nama_satuan ?? '-',
                $this->qty($barang->stok_awal),
                $this->qty($barang->stok_minimal),
                $this->qty($barang->stok),
                (int) $barang->harga_beli,
                $nilai,
                $barang->deskripsi ?: '-',
            ];
        }

        return $rows;
    }
}
