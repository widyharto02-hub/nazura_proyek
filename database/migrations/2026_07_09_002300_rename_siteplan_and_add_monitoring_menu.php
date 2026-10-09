<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek') || !Schema::hasTable('users')) {
            return;
        }

        $siteplanMenu = DB::table('menu_proyek')->where('url', 'siteplan-unit-ready.index')->first();

        if ($siteplanMenu) {
            DB::table('menu_proyek')->where('id', $siteplanMenu->id)->update([
                'title' => 'Siteplan',
                'icon' => 'fa-map',
            ]);
        }

        $menuId = DB::table('menu_proyek')->where('url', 'monitoring-pembangunan.index')->value('id');
        $urutan = $siteplanMenu ? ((int) $siteplanMenu->urutan + 1) : 100;

        if ($menuId) {
            DB::table('menu_proyek')->where('id', $menuId)->update([
                'id_parent' => 0,
                'title' => 'Monitoring Pembangunan',
                'url' => 'monitoring-pembangunan.index',
                'icon' => 'fa-clipboard-list',
                'jenis_menu' => 'Internal',
                'urutan' => $urutan,
                'lihat' => 1,
                'tambah' => 0,
                'edit' => 0,
                'hapus' => 0,
            ]);
        } else {
            $menuId = DB::table('menu_proyek')->insertGetId([
                'id_parent' => 0,
                'title' => 'Monitoring Pembangunan',
                'url' => 'monitoring-pembangunan.index',
                'icon' => 'fa-clipboard-list',
                'jenis_menu' => 'Internal',
                'urutan' => $urutan,
                'lihat' => 1,
                'tambah' => 0,
                'edit' => 0,
                'hapus' => 0,
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('hak_akses_proyek')->updateOrInsert(
                ['id_user' => $userId, 'id_menu' => $menuId],
                ['lihat' => 1, 'tambah' => 0, 'edit' => 0, 'hapus' => 0]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek')) {
            return;
        }

        DB::table('menu_proyek')->where('url', 'siteplan-unit-ready.index')->update([
            'title' => 'Siteplan UR',
        ]);

        $menuIds = DB::table('menu_proyek')->where('url', 'monitoring-pembangunan.index')->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
            DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
        }
    }
};
