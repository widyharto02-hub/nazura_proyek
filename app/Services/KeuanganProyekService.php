<?php

namespace App\Services;

use App\Models\KategoriTransaksi;
use App\Models\Pengeluaran;
use Illuminate\Support\Facades\Log;

class KeuanganProyekService
{
    public const SUMBER_BARANG_MASUK = 'BARANG_MASUK_PEMBAYARAN';
    public const SUMBER_PEMBAYARAN_JASA = 'PEMBAYARAN_JASA';
    public const SUMBER_PEMBAYARAN_TUKANG = 'PEMBAYARAN_TUKANG';

    public const KATEGORI = [
        self::SUMBER_BARANG_MASUK => '4001',
        self::SUMBER_PEMBAYARAN_JASA => '4002',
        self::SUMBER_PEMBAYARAN_TUKANG => '4003',
    ];

    /**
     * Catat pengeluaran proyek ke tabel keuangan bersama.
     */
    public static function catat(string $sumber, int $refId, string $tanggal, int $nominal, string $keterangan = '', ?int $idProject = null, ?string $lampiran = null): ?int
    {
        $kategoriId = self::kategoriId($sumber);

        if (!$kategoriId) {
            Log::error('KeuanganProyekService: kategori transaksi tidak ditemukan untuk sumber ' . $sumber);

            return null;
        }

        $row = Pengeluaran::create([
            'app_source' => 'PROYEK',
            'id_project' => $idProject,
            'sumber' => $sumber,
            'ref_id' => $refId,
            'tanggal' => $tanggal,
            'nominal' => $nominal,
            'id_kategori_transaksi' => $kategoriId,
            'id_metode_bayar' => 1,
            'id_bank' => 0,
            'lampiran' => $lampiran ?? '',
            'keterangan' => $keterangan !== '' ? $keterangan : $sumber,
        ]);

        return $row->id;
    }

    /**
     * Samakan ulang catatan keuangan dengan pembaruan di tabel proyek.
     */
    public static function sinkron(string $sumber, int $refId, string $tanggal, int $nominal, string $keterangan = '', ?int $idProject = null): void
    {
        $row = self::cari($sumber, $refId);

        if (!$row) {
            self::catat($sumber, $refId, $tanggal, $nominal, $keterangan, $idProject);

            return;
        }

        $row->update([
            'tanggal' => $tanggal,
            'nominal' => $nominal,
            'keterangan' => $keterangan !== '' ? $keterangan : $sumber,
            'id_project' => $idProject,
        ]);
    }

    public static function hapus(string $sumber, int $refId): void
    {
        self::cari($sumber, $refId)?->delete();
    }

    public static function cari(string $sumber, int $refId): ?Pengeluaran
    {
        return Pengeluaran::where('sumber', $sumber)->where('ref_id', $refId)->first();
    }

    private static function kategoriId(string $sumber): ?int
    {
        $kode = self::KATEGORI[$sumber] ?? null;

        if (!$kode) {
            return null;
        }

        return KategoriTransaksi::where('kode', $kode)->value('id');
    }
}