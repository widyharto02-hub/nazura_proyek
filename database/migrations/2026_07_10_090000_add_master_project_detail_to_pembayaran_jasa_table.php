<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pembayaran_jasa') || Schema::hasColumn('pembayaran_jasa', 'id_master_project_detail')) {
            return;
        }

        Schema::table('pembayaran_jasa', function (Blueprint $table) {
            $table->unsignedBigInteger('id_master_project_detail')->nullable()->after('id_setting_proyek');
            $table->index('id_master_project_detail');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pembayaran_jasa') || !Schema::hasColumn('pembayaran_jasa', 'id_master_project_detail')) {
            return;
        }

        Schema::table('pembayaran_jasa', function (Blueprint $table) {
            $table->dropIndex(['id_master_project_detail']);
            $table->dropColumn('id_master_project_detail');
        });
    }
};
