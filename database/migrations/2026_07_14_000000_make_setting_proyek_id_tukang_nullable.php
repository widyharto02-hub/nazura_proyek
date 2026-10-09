<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('setting_proyek') || !Schema::hasColumn('setting_proyek', 'id_tukang')) {
            return;
        }

        DB::statement('ALTER TABLE setting_proyek MODIFY id_tukang BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('setting_proyek') || !Schema::hasColumn('setting_proyek', 'id_tukang')) {
            return;
        }

        DB::statement('ALTER TABLE setting_proyek MODIFY id_tukang BIGINT UNSIGNED NOT NULL');
    }
};
