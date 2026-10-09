<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menu_proyek')) {
            return;
        }

        $dashboardId = DB::table('menu_proyek')->where('url', 'dashboard')->value('id');
        $siteplanId = DB::table('menu_proyek')->where('url', 'siteplan-unit-ready.index')->value('id');
        $barangMasukId = DB::table('menu_proyek')->where('url', 'barang-masuk.index')->value('id');
        $barangKeluarId = DB::table('menu_proyek')->where('url', 'barang-keluar.index')->value('id');
        $pengajuanBarangId = DB::table('menu_proyek')->where('url', 'pengajuan-barang.index')->value('id');
        $monitoringId = DB::table('menu_proyek')->where('url', 'monitoring-pembangunan.index')->value('id');
        $gudangId = DB::table('menu_proyek')->whereRaw('LOWER(title) = ?', ['gudang'])->value('id');
        $pengaturanId = DB::table('menu_proyek')->whereRaw('LOWER(title) = ?', ['pengaturan'])->value('id');

        if ($dashboardId) {
            DB::table('menu_proyek')->where('id', $dashboardId)->update([
                'id_parent' => 0,
                'title' => 'Dashboard',
                'urutan' => 1,
            ]);
        }

        if ($siteplanId) {
            DB::table('menu_proyek')->where('id', $siteplanId)->update([
                'id_parent' => 0,
                'title' => 'Siteplan',
                'urutan' => 2,
            ]);
        }

        if ($barangMasukId) {
            DB::table('menu_proyek')->where('id', $barangMasukId)->update([
                'id_parent' => 0,
                'title' => 'Barang Masuk',
                'urutan' => 3,
            ]);
        }

        if ($barangKeluarId) {
            DB::table('menu_proyek')->where('id', $barangKeluarId)->update([
                'id_parent' => 0,
                'title' => 'Barang Keluar',
                'urutan' => 4,
            ]);
        }

        if ($pengajuanBarangId) {
            DB::table('menu_proyek')->where('id', $pengajuanBarangId)->update([
                'id_parent' => 0,
                'title' => 'Pengajuan Barang',
                'urutan' => 5,
            ]);
        }

        if ($monitoringId) {
            DB::table('menu_proyek')->where('id', $monitoringId)->update([
                'id_parent' => 0,
                'title' => 'Monitoring',
                'urutan' => 6,
            ]);
        }

        if ($gudangId) {
            DB::table('menu_proyek')->where('id', $gudangId)->update([
                'id_parent' => 0,
                'title' => 'Gudang',
                'urutan' => 7,
            ]);
        }

        if ($pengaturanId) {
            DB::table('menu_proyek')->where('id', $pengaturanId)->update([
                'id_parent' => 0,
                'title' => 'Pengaturan',
                'urutan' => 8,
            ]);
        }
    }

    public function down(): void
    {
    }
};
