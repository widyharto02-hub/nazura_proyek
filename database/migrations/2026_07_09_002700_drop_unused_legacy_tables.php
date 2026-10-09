<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'input_po_pembayaran',
            'input_po_detail',
            'input_po',
            'progres_pembangunan_detail',
            'progres_list_pembangunan',
            'progres_list_penjualan',
            'hak_akses_perumahan',
            'file_nasabah',
            'konfigurasi_media',
            'kavling_perusahaan',
            'rekap_akad',
            'ci_sessions',
            'content',
            'invoice',
            'kategori',
            'opname',
            'panduan_list',
            'perusahaan',
            'template',
            'throttle',
            'transaksi',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Legacy cleanup only. Table recreation is intentionally omitted.
    }
};
