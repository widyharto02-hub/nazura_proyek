<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('aktifitas');

        Schema::create('aktifitas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_user')->nullable()->index();
            $table->string('aktivitas', 50);
            $table->text('keterangan');
            $table->string('method', 10)->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktifitas');
    }
};
