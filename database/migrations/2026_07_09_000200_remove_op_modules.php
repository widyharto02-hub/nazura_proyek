<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_proyek') && Schema::hasTable('hak_akses_proyek')) {
            $menuIds = DB::table('menu_proyek')
                ->whereIn('title', ['OP Bangunan', 'OP Jalan', 'OP Saluran', 'ST Bangunan'])
                ->orWhere('url', 'like', 'proyek-bangunan.%')
                ->orWhere('url', 'like', 'jenis-pekerjaan-bangunan.%')
                ->orWhere('url', 'like', 'proyek-jalan.%')
                ->orWhere('url', 'like', 'jalan.%')
                ->orWhere('url', 'like', 'jenis-pekerjaan-jalan.%')
                ->orWhere('url', 'like', 'proyek-saluran.%')
                ->orWhere('url', 'like', 'saluran.%')
                ->orWhere('url', 'like', 'jenis-pekerjaan-saluran.%')
                ->orWhere('url', 'like', 'st-bangunan.%')
                ->pluck('id');

            if ($menuIds->isNotEmpty()) {
                DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
                DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
            }
        }

        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('foto_proyek_saluran');
        Schema::dropIfExists('foto_proyek_jalan');
        Schema::dropIfExists('foto_proyek_bangunan');
        Schema::dropIfExists('proyek_saluran_detail_kerja');
        Schema::dropIfExists('proyek_saluran_detail');
        Schema::dropIfExists('proyek_saluran');
        Schema::dropIfExists('proyek_jalan_detail_kerja');
        Schema::dropIfExists('proyek_jalan_detail');
        Schema::dropIfExists('proyek_jalan');
        Schema::dropIfExists('proyek_bangunan_detail_kerja');
        Schema::dropIfExists('proyek_bangunan_detail');
        Schema::dropIfExists('proyek_bangunan_unit');
        Schema::dropIfExists('proyek_bangunan');
        Schema::dropIfExists('jenis_pekerjaan_saluran');
        Schema::dropIfExists('jenis_pekerjaan_jalan');
        Schema::dropIfExists('jenis_pekerjaan_bangunan');
        Schema::dropIfExists('saluran');
        Schema::dropIfExists('jalan');
        Schema::dropIfExists('opname_barang');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        //
    }
};
