<?php

namespace App\Http\Controllers\Laporan;

use App\Models\BarangKeluar;
use Carbon\Carbon;

class PengeluaranBarangController extends BaseLaporanController
{
    protected function reportTitle(): string
    {
        return 'Laporan Pengeluaran Barang';
    }

    protected function reportFolder(): string
    {
        return 'pengeluaran_barang';
    }

    protected function reportKey(): string
    {
        return 'pengeluaran-barang';
    }

    protected function reportFilename(): string
    {
        return 'laporan_pengeluaran_barang';
    }

    protected function getHeaders(): array
    {
        return ['No', 'Tanggal', 'Unit / Proyek', 'Jenis', 'Tukang', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Satuan', 'Sub Total', 'Keterangan'];
    }

    protected function footerLabel(): ?string
    {
        return 'Total Pengeluaran Barang';
    }

    protected function moneyColumns(): array
    {
        return [8, 9];
    }

    protected function hasLokasiFilter(): bool
    {
        return true;
    }

    protected function buildRows(array $period): array
    {
        $rows = [];
        $no = 1;
        $this->grandTotal = 0;

        $query = BarangKeluar::with(['kavling', 'lokasi', 'masterProject', 'tukang', 'detail.barang.satuan'])
            ->whereBetween('tanggal', [$period['dari'], $period['sampai']]);

        if (!empty($period['lokasi'])) {
            $query->where('id_lokasi', $period['lokasi']);
        }

        if (!empty($period['unit'])) {
            $query->where('id_kavling', $period['unit']);
        }

        $list = $query->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($list as $bk) {
            $ref = $bk->id_kavling
                ? ($bk->kavling?->kode_kavling ?? '-') . ($bk->lokasi?->nama_kavling ? ' / ' . $bk->lokasi->nama_kavling : '')
                : ($bk->masterProject?->jenis_proyek ?? 'Proyek #' . $bk->id_project);
            $jenis = $bk->id_project ? 'Non Unit' : 'Pembangunan Unit';
            $tukang = $bk->tukang?->nama ?? '-';

            foreach ($bk->detail as $det) {
                $harga = (int) ($det->barang?->harga_beli ?? 0);
                $subTotal = (int) $det->jumlah * $harga;
                $this->grandTotal += $subTotal;

                $rows[] = [
                    $no++,
                    Carbon::parse($bk->tanggal)->translatedFormat('d F Y'),
                    $ref,
                    $jenis,
                    $tukang,
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
