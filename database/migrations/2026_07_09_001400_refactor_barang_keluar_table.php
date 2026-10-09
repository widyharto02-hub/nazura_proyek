<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang_keluar')) {
            return;
        }

        if (!Schema::hasColumn('barang_keluar', 'id_lokasi')) {
            DB::statement('ALTER TABLE barang_keluar ADD COLUMN id_lokasi INT NULL AFTER id_project');
        }

        if (!Schema::hasColumn('barang_keluar', 'id_kavling')) {
            DB::statement('ALTER TABLE barang_keluar ADD COLUMN id_kavling INT NULL AFTER id_lokasi');
        }

        if (!Schema::hasColumn('barang_keluar', 'id_tukang')) {
            DB::statement('ALTER TABLE barang_keluar ADD COLUMN id_tukang BIGINT UNSIGNED NULL AFTER id_kavling');
        }

        DB::statement('ALTER TABLE barang_keluar MODIFY id_project INT NULL');
        DB::statement("ALTER TABLE barang_keluar MODIFY lampiran VARCHAR(255) NOT NULL DEFAULT ''");

        if (Schema::hasTable('mutasi_stok') && !Schema::hasColumn('mutasi_stok', 'id_barang_keluar')) {
            DB::statement('ALTER TABLE mutasi_stok ADD COLUMN id_barang_keluar BIGINT UNSIGNED NULL AFTER id_barang_masuk');
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('barang_keluar')) {
            return;
        }

        if (Schema::hasTable('mutasi_stok') && Schema::hasColumn('mutasi_stok', 'id_barang_keluar')) {
            DB::statement('ALTER TABLE mutasi_stok DROP COLUMN id_barang_keluar');
        }

        DB::statement('UPDATE barang_keluar SET id_project = 0 WHERE id_project IS NULL');
        DB::statement('ALTER TABLE barang_keluar MODIFY id_project INT NOT NULL');

        if (Schema::hasColumn('barang_keluar', 'id_tukang')) {
            DB::statement('ALTER TABLE barang_keluar DROP COLUMN id_tukang');
        }
        if (Schema::hasColumn('barang_keluar', 'id_kavling')) {
            DB::statement('ALTER TABLE barang_keluar DROP COLUMN id_kavling');
        }
        if (Schema::hasColumn('barang_keluar', 'id_lokasi')) {
            DB::statement('ALTER TABLE barang_keluar DROP COLUMN id_lokasi');
        }
    }
};
