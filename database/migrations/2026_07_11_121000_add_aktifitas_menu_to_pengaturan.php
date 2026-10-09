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

        $parentId = DB::table('menu_proyek')
            ->where('id_parent', 0)
            ->whereRaw('LOWER(title) = ?', ['pengaturan'])
            ->value('id');

        if (!$parentId) {
            return;
        }

        $menuId = DB::table('menu_proyek')->where('url', 'aktifitas.index')->value('id');

        if ($menuId) {
            DB::table('menu_proyek')->where('id', $menuId)->update([
                'id_parent' => $parentId,
                'title' => 'Aktifitas',
                'icon' => 'fa-history',
                'jenis_menu' => 'Internal',
                'urutan' => 6,
                'lihat' => 1,
                'tambah' => 0,
                'edit' => 0,
                'hapus' => 0,
            ]);
        } else {
            $menuId = DB::table('menu_proyek')->insertGetId([
                'id_parent' => $parentId,
                'title' => 'Aktifitas',
                'url' => 'aktifitas.index',
                'icon' => 'fa-history',
                'jenis_menu' => 'Internal',
                'urutan' => 6,
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

        $menuIds = DB::table('menu_proyek')
            ->where('url', 'aktifitas.index')
            ->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
            DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
        }
    }
};
