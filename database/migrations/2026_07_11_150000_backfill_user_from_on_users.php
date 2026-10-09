<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'user_from')) {
            return;
        }

        DB::table('users')
            ->whereNull('user_from')
            ->update(['user_from' => 2]);
    }

    public function down(): void
    {
        // No-op: original NULL rows are not tracked, so rolling back would risk
        // clearing legitimate user_from values.
    }
};
