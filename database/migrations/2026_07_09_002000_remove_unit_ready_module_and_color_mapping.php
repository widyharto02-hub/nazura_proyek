<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_proyek') && Schema::hasTable('hak_akses_proyek')) {
            $unitReadyMenuIds = DB::table('menu_proyek')
                ->where('url', 'unit-ready.index')
                ->pluck('id');

            $siteplanMenuId = DB::table('menu_proyek')
                ->where('url', 'siteplan-unit-ready.index')
                ->value('id');

            if ($siteplanMenuId) {
                DB::table('menu_proyek')
                    ->where('id', $siteplanMenuId)
                    ->update([
                        'id_parent' => 0,
                        'title' => 'Siteplan UR',
                    ]);
            }

            if ($unitReadyMenuIds->isNotEmpty()) {
                DB::table('hak_akses_proyek')->whereIn('id_menu', $unitReadyMenuIds)->delete();
                DB::table('menu_proyek')->whereIn('id', $unitReadyMenuIds)->delete();
            }
        }

        if (Schema::hasTable('kavling_peta') && Schema::hasColumn('kavling_peta', 'status_ready')) {
            Schema::table('kavling_peta', function (Blueprint $table) {
                $table->dropColumn('status_ready');
            });
        }

        Schema::dropIfExists('progres_unit_ready');
    }

    public function down(): void
    {
        if (!Schema::hasTable('kavling_peta')) {
            return;
        }

        if (!Schema::hasColumn('kavling_peta', 'status_ready')) {
            Schema::table('kavling_peta', function (Blueprint $table) {
                $table->unsignedBigInteger('status_ready')->nullable()->after('stt_cicilan');
            });
        }
    }
};
