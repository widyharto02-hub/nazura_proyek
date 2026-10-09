<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_tukang')) {
            return;
        }

        Schema::create('pembayaran_tukang', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('id_tukang');
            $table->string('nama_pekerjaan');
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('nominal')->default(0);
            $table->string('bukti_bayar')->nullable();
            $table->string('bukti_bayar_original_name')->nullable();
            $table->string('bukti_bayar_extension', 10)->nullable();

            $table->index('tanggal');
            $table->index('id_tukang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_tukang');
    }
};
