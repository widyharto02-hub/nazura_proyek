<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang') || !Schema::hasTable('satuan')) {
            return;
        }

        $currentCount = DB::table('barang')->count();

        if ($currentCount >= 50) {
            return;
        }

        $satuanMap = DB::table('satuan')
            ->pluck('id', 'nama_satuan')
            ->mapWithKeys(fn ($id, $nama) => [strtoupper($nama) => $id]);

        $pcsId = $satuanMap['PCS'] ?? $satuanMap->first();
        $setId = $satuanMap['SET'] ?? $pcsId;
        $unitId = $satuanMap['UNIT'] ?? $pcsId;

        if (!$pcsId) {
            return;
        }

        $candidates = [
            ['nama' => 'Bor Listrik 10mm', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 385000, 'sku' => 'BRG-051', 'deskripsi' => null],
            ['nama' => 'Mata Bor Beton Set', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 128000, 'sku' => 'BRG-052', 'deskripsi' => null],
            ['nama' => 'Selang Air 1/2 Inch 20m', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 142000, 'sku' => 'BRG-053', 'deskripsi' => null],
            ['nama' => 'Tandon Air 500 Liter', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 1285000, 'sku' => 'BRG-054', 'deskripsi' => null],
            ['nama' => 'Klem Pipa 3/4 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 2800, 'sku' => 'BRG-055', 'deskripsi' => null],
            ['nama' => 'Mesin Gerinda 4 Inch', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 465000, 'sku' => 'BRG-056', 'deskripsi' => null],
            ['nama' => 'Pisau Keramik', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 18500, 'sku' => 'BRG-057', 'deskripsi' => null],
            ['nama' => 'Lem PVC 400gr', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 38500, 'sku' => 'BRG-058', 'deskripsi' => null],
            ['nama' => 'Masker Safety', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 12000, 'sku' => 'BRG-059', 'deskripsi' => null],
            ['nama' => 'Sarung Tangan Kerja', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 17500, 'sku' => 'BRG-060', 'deskripsi' => null],
        ];

        foreach ($candidates as $item) {
            if (DB::table('barang')->count() >= 50) {
                break;
            }

            $exists = DB::table('barang')->where('nama', $item['nama'])->exists();

            if (!$exists) {
                DB::table('barang')->insert($item);
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('barang')) {
            return;
        }

        DB::table('barang')->whereIn('sku', [
            'BRG-051',
            'BRG-052',
            'BRG-053',
            'BRG-054',
            'BRG-055',
            'BRG-056',
            'BRG-057',
            'BRG-058',
            'BRG-059',
            'BRG-060',
        ])->delete();
    }
};
