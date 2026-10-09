<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_harga_barang', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_barang')->index();
            $table->unsignedInteger('id_barang_masuk')->nullable()->index();
            $table->unsignedInteger('id_user')->nullable()->index();
            $table->string('proses', 20);
            $table->integer('stok_sebelum')->default(0);
            $table->integer('harga_sebelum')->default(0);
            $table->integer('qty_lama')->nullable();
            $table->integer('harga_lama')->nullable();
            $table->integer('qty_baru')->nullable();
            $table->integer('harga_baru_input')->nullable();
            $table->integer('stok_sesudah')->default(0);
            $table->integer('harga_sesudah')->default(0);
            $table->text('keterangan');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_harga_barang');
    }
};
