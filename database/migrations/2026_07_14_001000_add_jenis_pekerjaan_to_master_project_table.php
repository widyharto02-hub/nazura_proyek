<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_project') || Schema::hasColumn('master_project', 'jenis_pekerjaan')) {
            return;
        }

        Schema::table('master_project', function (Blueprint $table) {
            $table->string('jenis_pekerjaan', 30)->default('pembangunan_unit')->after('jenis_proyek');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('master_project') || !Schema::hasColumn('master_project', 'jenis_pekerjaan')) {
            return;
        }

        Schema::table('master_project', function (Blueprint $table) {
            $table->dropColumn('jenis_pekerjaan');
        });
    }
};
