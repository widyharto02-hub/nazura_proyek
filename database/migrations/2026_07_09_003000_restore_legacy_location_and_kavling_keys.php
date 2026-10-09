<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lokasi_kavling') && !Schema::hasColumn('lokasi_kavling', 'id_lokasi')) {
            Schema::table('lokasi_kavling', function (Blueprint $table) {
                $table->unsignedInteger('id_lokasi')->nullable()->after('id');
            });

            DB::table('lokasi_kavling')->update([
                'id_lokasi' => DB::raw('id'),
            ]);

            Schema::table('lokasi_kavling', function (Blueprint $table) {
                $table->unique('id_lokasi', 'lokasi_kavling_id_lokasi_unique');
            });
        }

        if (Schema::hasTable('kavling_peta') && !Schema::hasColumn('kavling_peta', 'id_kavling')) {
            Schema::table('kavling_peta', function (Blueprint $table) {
                $table->unsignedInteger('id_kavling')->nullable()->after('id');
            });

            DB::table('kavling_peta')->update([
                'id_kavling' => DB::raw('id'),
            ]);

            Schema::table('kavling_peta', function (Blueprint $table) {
                $table->unique('id_kavling', 'kavling_peta_id_kavling_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kavling_peta') && Schema::hasColumn('kavling_peta', 'id_kavling')) {
            Schema::table('kavling_peta', function (Blueprint $table) {
                $table->dropUnique('kavling_peta_id_kavling_unique');
                $table->dropColumn('id_kavling');
            });
        }

        if (Schema::hasTable('lokasi_kavling') && Schema::hasColumn('lokasi_kavling', 'id_lokasi')) {
            Schema::table('lokasi_kavling', function (Blueprint $table) {
                $table->dropUnique('lokasi_kavling_id_lokasi_unique');
                $table->dropColumn('id_lokasi');
            });
        }
    }
};
