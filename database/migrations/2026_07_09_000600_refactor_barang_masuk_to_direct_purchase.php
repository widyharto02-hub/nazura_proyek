<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang_masuk')) {
            return;
        }

        if (!Schema::hasColumn('barang_masuk', 'id_supplier')) {
            DB::statement('ALTER TABLE barang_masuk ADD COLUMN id_supplier INT NULL AFTER tanggal');
        }

        if (Schema::hasTable('input_po')) {
            DB::statement('
                UPDATE barang_masuk
                LEFT JOIN input_po ON input_po.id = barang_masuk.id_po
                SET barang_masuk.id_supplier = input_po.id_supplier
                WHERE barang_masuk.id_supplier IS NULL
            ');
        }

        DB::statement('ALTER TABLE barang_masuk MODIFY id_po INT NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('barang_masuk')) {
            return;
        }

        DB::statement('UPDATE barang_masuk SET id_po = 0 WHERE id_po IS NULL');
        DB::statement('ALTER TABLE barang_masuk MODIFY id_po INT NOT NULL');

        if (Schema::hasColumn('barang_masuk', 'id_supplier')) {
            DB::statement('ALTER TABLE barang_masuk DROP COLUMN id_supplier');
        }
    }
};
