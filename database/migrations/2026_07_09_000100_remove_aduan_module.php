<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menu_proyek') && Schema::hasTable('hak_akses_proyek')) {
            $menuIds = DB::table('menu_proyek')
                ->whereRaw('LOWER(title) LIKE ?', ['%aduan%'])
                ->orWhereRaw('LOWER(url) LIKE ?', ['%aduan%'])
                ->pluck('id');

            if ($menuIds->isNotEmpty()) {
                DB::table('hak_akses_proyek')->whereIn('id_menu', $menuIds)->delete();
                DB::table('menu_proyek')->whereIn('id', $menuIds)->delete();
            }
        }

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('file_proses_aduan');
        Schema::dropIfExists('file_aduan');
        Schema::dropIfExists('aduan_proses');
        Schema::dropIfExists('aduan');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::create('aduan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_customer')->nullable();
            $table->unsignedBigInteger('id_kavling')->nullable();
            $table->string('no_kontrak')->nullable();
            $table->text('isi_aduan')->nullable();
            $table->dateTime('tanggal')->nullable();
            $table->string('no_aduan')->nullable();
            $table->integer('stt_aduan')->default(0);
            $table->timestamps();
        });

        Schema::create('aduan_proses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_aduan')->nullable();
            $table->dateTime('tgl_update')->nullable();
            $table->text('catatan')->nullable();
            $table->integer('stt_proses_aduan')->default(0);
            $table->timestamps();
        });

        Schema::create('file_aduan', function (Blueprint $table) {
            $table->id('id_file_aduan');
            $table->unsignedBigInteger('id_aduan')->nullable();
            $table->unsignedBigInteger('id_customer')->nullable();
            $table->string('no_kontrak')->nullable();
            $table->string('nama_file')->nullable();
            $table->timestamps();
        });

        Schema::create('file_proses_aduan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_aduan')->nullable();
            $table->string('nama_file')->nullable();
            $table->timestamps();
        });
    }
};
