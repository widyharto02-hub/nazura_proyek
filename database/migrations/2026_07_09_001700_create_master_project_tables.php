<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_project')) {
            Schema::create('master_project', function (Blueprint $table) {
                $table->id();
                $table->string('jenis_proyek');
                $table->string('jenis_pekerjaan', 30)->default('pembangunan_unit');
            });
        }

        if (!Schema::hasTable('master_project_detail')) {
            Schema::create('master_project_detail', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_master_project');
                $table->string('tipe', 20);
                $table->unsignedBigInteger('id_barang')->nullable();
                $table->string('nama_item')->nullable();
                $table->string('satuan')->nullable();
                $table->decimal('qty', 18, 2)->default(0);
                $table->unsignedBigInteger('harga_satuan')->default(0);
                $table->unsignedBigInteger('sub_total')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('master_project_detail');
        Schema::dropIfExists('master_project');
    }
};
