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

        $monitoringMenu = DB::table('menu_proyek')->where('url', 'monitoring-pembangunan.index')->first();
        $parentId = $monitoringMenu?->id_parent ?? 0;
        $urutan = $monitoringMenu ? ((int) $monitoringMenu->urutan + 1) : 100;

        $menuId = DB::table('menu_proyek')->where('url', 'pembayaran-jasa.index')->value('id');

        if ($menuId) {
            DB::table('menu_proyek')->where('id', $menuId)->update([
                'id_parent' => $parentId,
                'title' => 'Pembayaran Jasa',
                'url' => 'pembayaran-jasa.index',
                'icon' => 'fa-hand-holding-usd',
                'jenis_menu' => 'Internal',
                'urutan' => $urutan,
                'lihat' => 1,
                'tambah' => 1,
                'edit' => 1,
                'hapus' => 1,
            ]);
        } else {
            $menuId = DB::table('menu_proyek')->insertGetId([
                'id_parent' => $parentId,
                'title' => 'Pembayaran Jasa',
                'url' => 'pembayaran-jasa.index',
                'icon' => 'fa-hand-holding-usd',
                'jenis_menu' => 'Internal',
                'urutan' => $urutan,
                'lihat' => 1,
                'tambah' => 1,
                'edit' => 1,
                'hapus' => 1,
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            DB::table('hak_akses_proyek')->updateOrInsert(
                ['id_user' => $userId, 'id_menu' => $menuId],
                ['lihat' => 1, 'tambah' => 0, 'edit' => 0, 'hapus' => 0]
            );
        }

        DB::table('menu_proyek')->whereRaw('LOWER(title) = ?', ['gudang'])->update(['urutan' => $urutan + 1]);
        DB::table('menu_proyek')->whereRaw('LOWER(title) = ?', ['pengaturan'])->update(['urutan' => $urutan + 2]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek')) {
            return;
        }

        $menuIds = DB::table('menu_proyek')->where('url', 'pembayaran-jasa.index')->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
            DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
        }
    }
};
