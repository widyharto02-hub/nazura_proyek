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
            ->whereRaw('LOWER(title) = ?', ['gudang'])
            ->value('id');

        if (!$parentId) {
            $maxOrder = (int) DB::table('menu_proyek')
                ->where('id_parent', 0)
                ->max('urutan');

            $parentId = DB::table('menu_proyek')->insertGetId([
                'id_parent'   => 0,
                'title'       => 'Gudang',
                'url'         => '#',
                'icon'        => 'fa-warehouse',
                'jenis_menu'  => 'Internal',
                'urutan'      => $maxOrder + 1,
                'lihat'       => 1,
                'tambah'      => 0,
                'edit'        => 0,
                'hapus'       => 0,
            ]);
        } else {
            DB::table('menu_proyek')
                ->where('id', $parentId)
                ->update([
                    'icon'       => 'fa-warehouse',
                    'jenis_menu' => 'Internal',
                    'lihat'      => 1,
                ]);
        }

        $children = [
            [
                'title'  => 'Supplier',
                'url'    => 'supplier.index',
                'icon'   => 'fa-truck',
                'urutan' => 1,
            ],
            [
                'title'  => 'Satuan',
                'url'    => 'satuan.index',
                'icon'   => 'fa-ruler-combined',
                'urutan' => 2,
            ],
            [
                'title'  => 'Barang',
                'url'    => 'barang.index',
                'icon'   => 'fa-box-open',
                'urutan' => 3,
            ],
        ];

        $userIds = DB::table('users')->pluck('id');

        foreach ($children as $child) {
            $menuId = DB::table('menu_proyek')
                ->where('url', $child['url'])
                ->value('id');

            if ($menuId) {
                DB::table('menu_proyek')
                    ->where('id', $menuId)
                    ->update([
                        'id_parent'   => $parentId,
                        'title'       => $child['title'],
                        'icon'        => $child['icon'],
                        'jenis_menu'  => 'Internal',
                        'urutan'      => $child['urutan'],
                        'lihat'       => 1,
                        'tambah'      => 1,
                        'edit'        => 1,
                        'hapus'       => 1,
                    ]);
            } else {
                $menuId = DB::table('menu_proyek')->insertGetId([
                    'id_parent'   => $parentId,
                    'title'       => $child['title'],
                    'url'         => $child['url'],
                    'icon'        => $child['icon'],
                    'jenis_menu'  => 'Internal',
                    'urutan'      => $child['urutan'],
                    'lihat'       => 1,
                    'tambah'      => 1,
                    'edit'        => 1,
                    'hapus'       => 1,
                ]);
            }

            foreach ($userIds as $userId) {
                $exists = DB::table('hak_akses_proyek')
                    ->where('id_user', $userId)
                    ->where('id_menu', $menuId)
                    ->exists();

                if (!$exists) {
                    DB::table('hak_akses_proyek')->insert([
                        'id_user' => $userId,
                        'id_menu' => $menuId,
                        'lihat'   => 1,
                        'tambah'  => 0,
                        'edit'    => 0,
                        'hapus'   => 0,
                    ]);
                }
            }
        }

        foreach ($userIds as $userId) {
            $exists = DB::table('hak_akses_proyek')
                ->where('id_user', $userId)
                ->where('id_menu', $parentId)
                ->exists();

            if (!$exists) {
                DB::table('hak_akses_proyek')->insert([
                    'id_user' => $userId,
                    'id_menu' => $parentId,
                    'lihat'   => 1,
                    'tambah'  => 0,
                    'edit'    => 0,
                    'hapus'   => 0,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('menu_proyek') || !Schema::hasTable('hak_akses_proyek')) {
            return;
        }

        $menuIds = DB::table('menu_proyek')
            ->whereIn('url', ['supplier.index', 'satuan.index', 'barang.index'])
            ->pluck('id');

        if ($menuIds->isNotEmpty()) {
            DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
            DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
        }

        $parentId = DB::table('menu_proyek')
            ->where('id_parent', 0)
            ->whereRaw('LOWER(title) = ?', ['gudang'])
            ->value('id');

        if ($parentId) {
            $hasChildren = DB::table('menu_proyek')->where('id_parent', $parentId)->exists();

            if (!$hasChildren) {
                DB::table('hak_akses_proyek')->where('id_menu', $parentId)->delete();
                DB::table('menu_proyek')->where('id', $parentId)->delete();
            }
        }
    }
};
