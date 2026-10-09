<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barang_masuk_lampiran')) {
            return;
        }

        Schema::create('barang_masuk_lampiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_barang_masuk');
            $table->string('file_name');
            $table->string('file_original_name')->nullable();
            $table->string('file_extension', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk_lampiran');
    }
};
