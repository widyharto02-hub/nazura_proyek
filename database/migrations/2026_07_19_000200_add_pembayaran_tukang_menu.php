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

        $monitoringJasa = DB::table('menu_proyek')
            ->where('url', 'pembayaran-jasa.index')
            ->first();

        $parentId = $monitoringJasa?->id_parent ?? 0;
        $urutan = $monitoringJasa ? (int) $monitoringJasa->urutan + 1 : 100;
        $menuId = DB::table('menu_proyek')
            ->where('url', 'pembayaran-tukang.index')
            ->value('id');

        DB::table('menu_proyek')
            ->where('id_parent', $parentId)
            ->where('urutan', '>=', $urutan)
            ->when($menuId, fn ($query) => $query->where('id', '!=', $menuId))
            ->increment('urutan');

        $menuData = [
            'id_parent' => $parentId,
            'title' => 'Pembayaran Tukang',
            'icon' => 'fa-money-check-alt',
            'jenis_menu' => 'Internal',
            'urutan' => $urutan,
            'lihat' => 1,
            'tambah' => 1,
            'edit' => 1,
            'hapus' => 1,
        ];

        if ($menuId) {
            DB::table('menu_proyek')->where('id', $menuId)->update($menuData);
        } else {
            $menuId = DB::table('menu_proyek')->insertGetId($menuData + [
                'url' => 'pembayaran-tukang.index',
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            $sourceAccess = $monitoringJasa
                ? DB::table('hak_akses_proyek')
                    ->where('id_user', $userId)
                    ->where('id_menu', $monitoringJasa->id)
                    ->first()
                : null;

            DB::table('hak_akses_proyek')->updateOrInsert(
                ['id_user' => $userId, 'id_menu' => $menuId],
                [
                    'lihat' => $sourceAccess?->lihat ?? 1,
                    'tambah' => $sourceAccess?->tambah ?? 0,
                    'edit' => $sourceAccess?->edit ?? 0,
                    'hapus' => $sourceAccess?->hapus ?? 0,
                ]
            );
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek')) {
            return;
        }

        $menu = DB::table('menu_proyek')
            ->where('url', 'pembayaran-tukang.index')
            ->first();

        if (!$menu) {
            return;
        }

        DB::table('hak_akses_proyek')->where('id_menu', $menu->id)->delete();
        DB::table('menu_proyek')->where('id', $menu->id)->delete();
        DB::table('menu_proyek')
            ->where('id_parent', $menu->id_parent)
            ->where('urutan', '>', $menu->urutan)
            ->decrement('urutan');
    }
};
