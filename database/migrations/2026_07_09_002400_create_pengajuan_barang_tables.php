<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengajuan_barang')) {
            Schema::create('pengajuan_barang', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->unsignedBigInteger('id_supplier');
                $table->unsignedBigInteger('total_harga')->default(0);
                $table->unsignedInteger('jum_item')->default(0);
                $table->unsignedTinyInteger('status')->default(0);
                $table->text('keterangan')->nullable();
            });
        }

        if (!Schema::hasTable('pengajuan_barang_detail')) {
            Schema::create('pengajuan_barang_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pengajuan');
                $table->unsignedBigInteger('id_barang');
                $table->unsignedInteger('jumlah')->default(0);
                $table->unsignedBigInteger('harga_beli')->default(0);
                $table->unsignedBigInteger('sub_total')->default(0);
                $table->text('keterangan')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_barang_detail');
        Schema::dropIfExists('pengajuan_barang');
    }
};
