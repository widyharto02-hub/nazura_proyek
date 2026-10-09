<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek')) {
            return;
        }

        $menuIds = DB::table('menu_proyek')
            ->where(function ($query) {
                $query->where('url', 'input-po.index')
                    ->orWhereRaw('LOWER(title) = ?', ['input po']);
            })
            ->pluck('id');

        if ($menuIds->isEmpty()) {
            return;
        }

        DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
        DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek') || !Schema::hasTable('users')) {
            return;
        }

        $parentId = DB::table('menu_proyek')
            ->where('id_parent', 0)
            ->whereRaw('LOWER(title) = ?', ['pembelian'])
            ->value('id');

        if (!$parentId) {
            return;
        }

        $menuId = DB::table('menu_proyek')
            ->where('url', 'input-po.index')
            ->value('id');

        if (!$menuId) {
            $menuId = DB::table('menu_proyek')->insertGetId([
                'id_parent' => $parentId,
                'title' => 'Input PO',
                'url' => 'input-po.index',
                'icon' => 'fa-file-import',
                'jenis_menu' => 'Internal',
                'urutan' => 1,
                'lihat' => 1,
                'tambah' => 1,
                'edit' => 1,
                'hapus' => 1,
            ]);
        }

        foreach (DB::table('users')->pluck('id') as $userId) {
            $exists = DB::table('hak_akses_proyek')
                ->where('id_user', $userId)
                ->where('id_menu', $menuId)
                ->exists();

            if (!$exists) {
                DB::table('hak_akses_proyek')->insert([
                    'id_user' => $userId,
                    'id_menu' => $menuId,
                    'lihat' => 1,
                    'tambah' => 0,
                    'edit' => 0,
                    'hapus' => 0,
                ]);
            }
        }
    }
};
