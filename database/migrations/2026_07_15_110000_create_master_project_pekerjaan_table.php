<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_project_pekerjaan')) {
            Schema::create('master_project_pekerjaan', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_master_project');
                $table->string('nama_pekerjaan');
            });
        }

        if (Schema::hasTable('master_project_detail') && !Schema::hasColumn('master_project_detail', 'id_master_project_pekerjaan')) {
            DB::statement('ALTER TABLE master_project_detail ADD COLUMN id_master_project_pekerjaan BIGINT UNSIGNED NULL AFTER id_master_project');
            DB::statement('ALTER TABLE master_project_detail ADD INDEX master_project_detail_pekerjaan_idx (id_master_project_pekerjaan)');
        }

        if (!Schema::hasTable('master_project') || !Schema::hasTable('master_project_detail')) {
            return;
        }

        $projectIds = DB::table('master_project_detail')
            ->select('id_master_project')
            ->distinct()
            ->pluck('id_master_project');

        foreach ($projectIds as $projectId) {
            $pekerjaanId = DB::table('master_project_pekerjaan')
                ->where('id_master_project', $projectId)
                ->where('nama_pekerjaan', 'Pekerjaan Utama')
                ->value('id');

            if (!$pekerjaanId) {
                $pekerjaanId = DB::table('master_project_pekerjaan')->insertGetId([
                    'id_master_project' => $projectId,
                    'nama_pekerjaan' => 'Pekerjaan Utama',
                ]);
            }

            DB::table('master_project_detail')
                ->where('id_master_project', $projectId)
                ->whereNull('id_master_project_pekerjaan')
                ->update([
                    'id_master_project_pekerjaan' => $pekerjaanId,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('master_project_detail') && Schema::hasColumn('master_project_detail', 'id_master_project_pekerjaan')) {
            DB::statement('ALTER TABLE master_project_detail DROP INDEX master_project_detail_pekerjaan_idx');
            DB::statement('ALTER TABLE master_project_detail DROP COLUMN id_master_project_pekerjaan');
        }

        Schema::dropIfExists('master_project_pekerjaan');
    }
};
