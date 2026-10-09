<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barang_keluar_detail') && !Schema::hasColumn('barang_keluar_detail', 'keterangan')) {
            DB::statement('ALTER TABLE barang_keluar_detail ADD COLUMN keterangan TEXT NULL AFTER jumlah');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('barang_keluar_detail') && Schema::hasColumn('barang_keluar_detail', 'keterangan')) {
            DB::statement('ALTER TABLE barang_keluar_detail DROP COLUMN keterangan');
        }
    }
};
