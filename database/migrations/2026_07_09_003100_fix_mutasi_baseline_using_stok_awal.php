<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang') || !Schema::hasTable('mutasi_stok')) {
            return;
        }

        $barangList = DB::table('barang')->get(['id', 'stok_awal']);

        foreach ($barangList as $barang) {
            $baseline = DB::table('mutasi_stok')
                ->where('id_barang', $barang->id)
                ->whereNull('id_barang_masuk')
                ->whereNull('id_barang_keluar')
                ->whereNull('id_realisasi_bahan')
                ->orderBy('id')
                ->first();

            if (!$baseline) {
                continue;
            }

            $stokAwal = (int) ($barang->stok_awal ?? 0);
            if ($stokAwal <= 0 || (int) $baseline->jumlah === $stokAwal) {
                continue;
            }

            DB::table('mutasi_stok')
                ->where('id', $baseline->id)
                ->update([
                    'jumlah' => $stokAwal,
                    'stok_sisa' => $stokAwal,
                ]);

            $running = 0;
            $mutasiList = DB::table('mutasi_stok')
                ->where('id_barang', $barang->id)
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get();

            foreach ($mutasiList as $mutasi) {
                $running += (int) $mutasi->jumlah;

                DB::table('mutasi_stok')
                    ->where('id', $mutasi->id)
                    ->update(['stok_sisa' => $running]);

                if ($mutasi->id_barang_masuk || $mutasi->id_barang_keluar) {
                    DB::table('history_barang')
                        ->where('id_mutasi_stok', $mutasi->id)
                        ->update(['stok' => $running]);
                }
            }

            DB::table('barang')
                ->where('id', $barang->id)
                ->update(['stok' => $running]);
        }
    }

    public function down(): void
    {
        // Data correction only.
    }
};
