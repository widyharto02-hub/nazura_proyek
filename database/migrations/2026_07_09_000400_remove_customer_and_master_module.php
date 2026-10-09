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
                ->whereIn('title', ['Customer', 'Master', 'Bank', 'Serah Terima Kunci'])
                ->orWhere('url', 'like', 'customer-data.%')
                ->orWhere('url', 'like', 'serah-terima-kunci.%')
                ->orWhere('url', 'like', 'bank.%')
                ->pluck('id');

            if ($menuIds->isNotEmpty()) {
                DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
                DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
            }
        }

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('serah_terima_kunci');
        Schema::dropIfExists('bank');
        Schema::dropIfExists('nasabah');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        //
    }
};
