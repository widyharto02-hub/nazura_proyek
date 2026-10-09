<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('setting_proyek')) {
            return;
        }

        Schema::create('setting_proyek', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lokasi');
            $table->unsignedBigInteger('id_kavling')->unique();
            $table->unsignedBigInteger('id_master_project');
            $table->unsignedBigInteger('id_tukang')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_proyek');
    }
};
