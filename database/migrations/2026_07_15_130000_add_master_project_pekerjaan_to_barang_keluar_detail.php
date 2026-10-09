<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang_keluar_detail') || Schema::hasColumn('barang_keluar_detail', 'id_master_project_pekerjaan')) {
            return;
        }

        DB::statement('ALTER TABLE barang_keluar_detail ADD COLUMN id_master_project_pekerjaan BIGINT UNSIGNED NULL AFTER id_barang');
        DB::statement('ALTER TABLE barang_keluar_detail ADD INDEX barang_keluar_detail_pekerjaan_idx (id_master_project_pekerjaan)');
    }

    public function down(): void
    {
        if (!Schema::hasTable('barang_keluar_detail') || !Schema::hasColumn('barang_keluar_detail', 'id_master_project_pekerjaan')) {
            return;
        }

        DB::statement('ALTER TABLE barang_keluar_detail DROP INDEX barang_keluar_detail_pekerjaan_idx');
        DB::statement('ALTER TABLE barang_keluar_detail DROP COLUMN id_master_project_pekerjaan');
    }
};
