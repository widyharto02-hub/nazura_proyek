<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pembayaran_jasa')) {
            return;
        }

        Schema::create('pembayaran_jasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_setting_proyek');
            $table->unsignedBigInteger('id_tukang');
            $table->date('tanggal_pembayaran');
            $table->unsignedBigInteger('jumlah_bayar')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->string('bukti_bayar_original_name')->nullable();
            $table->string('bukti_bayar_extension', 10)->nullable();

            $table->index('id_setting_proyek');
            $table->index('id_tukang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_jasa');
    }
};
