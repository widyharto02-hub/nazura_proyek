<?php

namespace App\Http\Controllers\Laporan;

use App\Models\BarangMasuk;
use Carbon\Carbon;

class PemasukanBarangController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Pemasukan Barang';
    }

    protected function reportFolder(): string
    {
        return 'pemasukan_barang';
    }

    protected function reportKey(): string
    {
        return 'pemasukan-barang';
    }

    protected function reportFilename(): string
    {
        return 'laporan_pemasukan_barang';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Tanggal', 'Supplier', 'Penerima', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Beli', 'Sub Total', 'Keterangan'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Pemasukan Barang';
    }

    protected function moneyColumns(): array
    {
        return [7, 8];
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $no = 1;
        $this->grandTotal = 0;

        $list = BarangMasuk::with(['supplier', 'detail.barang.satuan'])
            ->whereBetween('tanggal', [$period['dari'], $period['sampai']])
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($list as $bm) {
            foreach ($bm->detail as $det) {
                $harga = (int) ($det->harga_beli ?? 0);
                $subTotal = (int) ($det->sub_total ?? ($det->jumlah * $harga));
                $this->grandTotal += $subTotal;

                $rows[] = [
                    $no++,
                    Carbon::parse($bm->tanggal)->translatedFormat('d F Y'),
                    $bm->supplier?->nama ?? '-',
                    $bm->nama_penerima ?? '-',
                    $det->barang?->nama ?? 'Barang #' . $det->id_barang,
                    $this->qty($det->jumlah),
                    $det->barang?->satuan?->nama_satuan ?? '-',
                    $harga,
                    $subTotal,
                    $det->keterangan ?: '-',
                ];
            }
        }

        return $rows;
    }
}
