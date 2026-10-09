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

        $parent = DB::table('menu_proyek')
            ->where('id_parent', 0)
            ->whereRaw('LOWER(title) = ?', ['pembelian'])
            ->first();

        $barangMasuk = DB::table('menu_proyek')
            ->where('url', 'barang-masuk.index')
            ->first();

        if ($barangMasuk) {
            DB::table('menu_proyek')
                ->where('id', $barangMasuk->id)
                ->update([
                    'id_parent' => 0,
                    'icon' => $barangMasuk->icon ?: ($parent->icon ?? 'fa-boxes'),
                    'urutan' => $parent->urutan ?? $barangMasuk->urutan,
                ]);
        }

        if ($parent) {
            DB::table('hak_akses_proyek')->where('id_menu', $parent->id)->delete();
            DB::table('menu_proyek')->where('id', $parent->id)->delete();
        }
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
            $maxOrder = (int) DB::table('menu_proyek')->where('id_parent', 0)->max('urutan');
            $parentId = DB::table('menu_proyek')->insertGetId([
                'id_parent' => 0,
                'title' => 'Pembelian',
                'url' => '#',
                'icon' => 'fa-shopping-cart',
                'jenis_menu' => 'Internal',
                'urutan' => $maxOrder + 1,
                'lihat' => 1,
                'tambah' => 0,
                'edit' => 0,
                'hapus' => 0,
            ]);

            foreach (DB::table('users')->pluck('id') as $userId) {
                DB::table('hak_akses_proyek')->updateOrInsert(
                    ['id_user' => $userId, 'id_menu' => $parentId],
                    ['lihat' => 1, 'tambah' => 0, 'edit' => 0, 'hapus' => 0]
                );
            }
        }

        $barangMasuk = DB::table('menu_proyek')->where('url', 'barang-masuk.index')->first();
        if ($barangMasuk) {
            DB::table('menu_proyek')->where('id', $barangMasuk->id)->update([
                'id_parent' => $parentId,
            ]);
        }
    }
};
