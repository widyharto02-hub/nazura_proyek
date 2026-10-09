<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('history_barang') || !Schema::hasTable('mutasi_stok')) {
            return;
        }

        $existingMutasiIds = DB::table('history_barang')
            ->whereNotNull('id_mutasi_stok')
            ->pluck('id_mutasi_stok')
            ->all();

        $mutasiList = DB::table('mutasi_stok')
            ->where(function ($query) {
                $query->whereNotNull('id_barang_masuk')
                    ->orWhereNotNull('id_barang_keluar');
            })
            ->when(!empty($existingMutasiIds), function ($query) use ($existingMutasiIds) {
                $query->whereNotIn('id', $existingMutasiIds);
            })
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get();

        foreach ($mutasiList as $mutasi) {
            $isMasuk = !empty($mutasi->id_barang_masuk);

            DB::table('history_barang')->insert([
                'tanggal' => $mutasi->tanggal,
                'id_barang' => $mutasi->id_barang,
                'id_barang_masuk' => $mutasi->id_barang_masuk,
                'id_barang_keluar' => $mutasi->id_barang_keluar,
                'id_mutasi_stok' => $mutasi->id,
                'tipe' => $isMasuk ? 'MASUK' : 'KELUAR',
                'masuk' => $isMasuk ? max((int) $mutasi->jumlah, 0) : 0,
                'keluar' => $isMasuk ? 0 : abs((int) $mutasi->jumlah),
                'stok' => (int) $mutasi->stok_sisa,
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('history_barang')) {
            return;
        }

        DB::table('history_barang')
            ->whereNotNull('id_mutasi_stok')
            ->delete();
    }
};
