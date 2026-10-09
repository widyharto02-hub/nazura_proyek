<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('history_barang')) {
            return;
        }

        Schema::create('history_barang', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('id_barang');
            $table->unsignedBigInteger('id_barang_masuk')->nullable();
            $table->unsignedBigInteger('id_barang_keluar')->nullable();
            $table->unsignedBigInteger('id_mutasi_stok')->nullable();
            $table->string('tipe', 20);
            $table->integer('masuk')->default(0);
            $table->integer('keluar')->default(0);
            $table->integer('stok')->default(0);

            $table->index('id_barang');
            $table->index('id_barang_masuk');
            $table->index('id_barang_keluar');
            $table->index('id_mutasi_stok');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_barang');
    }
};
