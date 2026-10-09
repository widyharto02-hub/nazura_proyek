<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('barang_masuk')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                if (!Schema::hasColumn('barang_masuk', 'total_harga')) {
                    $table->unsignedBigInteger('total_harga')->default(0)->after('nama_penerima');
                }

                if (!Schema::hasColumn('barang_masuk', 'jum_item')) {
                    $table->unsignedInteger('jum_item')->default(0)->after('total_harga');
                }

                if (!Schema::hasColumn('barang_masuk', 'terbayar')) {
                    $table->unsignedBigInteger('terbayar')->default(0)->after('jum_item');
                }

                if (!Schema::hasColumn('barang_masuk', 'status')) {
                    $table->unsignedTinyInteger('status')->default(0)->after('terbayar');
                }
            });

            DB::statement("
                UPDATE barang_masuk bm
                LEFT JOIN (
                    SELECT id_masuk, COALESCE(SUM(sub_total), 0) AS total_harga, COUNT(*) AS jum_item
                    FROM barang_masuk_detail
                    GROUP BY id_masuk
                ) detail ON detail.id_masuk = bm.id
                SET
                    bm.total_harga = COALESCE(detail.total_harga, 0),
                    bm.jum_item = COALESCE(detail.jum_item, 0),
                    bm.terbayar = COALESCE(bm.terbayar, 0),
                    bm.status = CASE
                        WHEN COALESCE(bm.terbayar, 0) >= COALESCE(detail.total_harga, 0) AND COALESCE(detail.total_harga, 0) > 0 THEN 1
                        ELSE 0
                    END
            ");
        }

        if (!Schema::hasTable('barang_masuk_pembayaran')) {
            Schema::create('barang_masuk_pembayaran', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_barang_masuk');
                $table->date('tanggal_pembayaran');
                $table->unsignedBigInteger('jumlah_bayar');
                $table->text('keterangan')->nullable();
                $table->string('bukti_bayar')->nullable();
                $table->string('bukti_bayar_original_name')->nullable();
                $table->string('bukti_bayar_extension', 10)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk_pembayaran');

        if (Schema::hasTable('barang_masuk')) {
            Schema::table('barang_masuk', function (Blueprint $table) {
                $columns = ['status', 'terbayar', 'jum_item', 'total_harga'];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('barang_masuk', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
