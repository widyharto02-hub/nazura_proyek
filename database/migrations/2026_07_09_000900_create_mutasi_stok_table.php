<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mutasi_stok')) {
            return;
        }

        Schema::create('mutasi_stok', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();
            $table->unsignedBigInteger('id_barang_masuk')->nullable();
            $table->unsignedBigInteger('id_realisasi_bahan')->nullable();
            $table->unsignedBigInteger('id_barang');
            $table->integer('jumlah')->default(0);
            $table->integer('stok_sisa')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_stok');
    }
};
