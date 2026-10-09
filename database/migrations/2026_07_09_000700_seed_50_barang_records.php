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

        $satuanMap = DB::table('satuan')
            ->pluck('id', 'nama_satuan')
            ->mapWithKeys(fn ($id, $nama) => [strtoupper($nama) => $id]);

        $pcsId = $satuanMap['PCS'] ?? $satuanMap->first();
        $setId = $satuanMap['SET'] ?? $pcsId;
        $unitId = $satuanMap['UNIT'] ?? $pcsId;

        if (!$pcsId) {
            return;
        }

        $items = [
            ['nama' => 'Semen Portland 40kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 72000, 'sku' => 'BRG-001', 'deskripsi' => null],
            ['nama' => 'Pasir Cor Halus', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 285000, 'sku' => 'BRG-002', 'deskripsi' => null],
            ['nama' => 'Batu Split 1/2', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 315000, 'sku' => 'BRG-003', 'deskripsi' => null],
            ['nama' => 'Besi Beton 10mm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 98000, 'sku' => 'BRG-004', 'deskripsi' => null],
            ['nama' => 'Besi Beton 12mm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 138000, 'sku' => 'BRG-005', 'deskripsi' => null],
            ['nama' => 'Kawat Bendrat 1kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 28500, 'sku' => 'BRG-006', 'deskripsi' => null],
            ['nama' => 'Bata Ringan AAC', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 9200, 'sku' => 'BRG-007', 'deskripsi' => null],
            ['nama' => 'Mortar Instan 40kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 68500, 'sku' => 'BRG-008', 'deskripsi' => null],
            ['nama' => 'Keramik Lantai 40x40', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 64000, 'sku' => 'BRG-009', 'deskripsi' => null],
            ['nama' => 'Keramik Dinding 25x40', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 59000, 'sku' => 'BRG-010', 'deskripsi' => null],
            ['nama' => 'Granit 60x60', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 148000, 'sku' => 'BRG-011', 'deskripsi' => null],
            ['nama' => 'Pipa PVC 1/2 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 24500, 'sku' => 'BRG-012', 'deskripsi' => null],
            ['nama' => 'Pipa PVC 3/4 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 31500, 'sku' => 'BRG-013', 'deskripsi' => null],
            ['nama' => 'Pipa PVC 1 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 45200, 'sku' => 'BRG-014', 'deskripsi' => null],
            ['nama' => 'Elbow PVC 1/2 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 3500, 'sku' => 'BRG-015', 'deskripsi' => null],
            ['nama' => 'Tee PVC 3/4 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 5200, 'sku' => 'BRG-016', 'deskripsi' => null],
            ['nama' => 'Stop Kran 1/2 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 28500, 'sku' => 'BRG-017', 'deskripsi' => null],
            ['nama' => 'Kabel NYM 2x1.5', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 485000, 'sku' => 'BRG-018', 'deskripsi' => null],
            ['nama' => 'Kabel NYM 2x2.5', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 735000, 'sku' => 'BRG-019', 'deskripsi' => null],
            ['nama' => 'Pipa Conduit 20mm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 18500, 'sku' => 'BRG-020', 'deskripsi' => null],
            ['nama' => 'Box MCB 4 Group', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 118000, 'sku' => 'BRG-021', 'deskripsi' => null],
            ['nama' => 'MCB 6A', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 38500, 'sku' => 'BRG-022', 'deskripsi' => null],
            ['nama' => 'MCB 10A', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 39500, 'sku' => 'BRG-023', 'deskripsi' => null],
            ['nama' => 'Saklar Tunggal', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 18500, 'sku' => 'BRG-024', 'deskripsi' => null],
            ['nama' => 'Stop Kontak Duplex', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 24500, 'sku' => 'BRG-025', 'deskripsi' => null],
            ['nama' => 'Lampu LED 12 Watt', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 26500, 'sku' => 'BRG-026', 'deskripsi' => null],
            ['nama' => 'Fitting Lampu E27', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 7500, 'sku' => 'BRG-027', 'deskripsi' => null],
            ['nama' => 'Cat Tembok Putih 5kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 96000, 'sku' => 'BRG-028', 'deskripsi' => null],
            ['nama' => 'Cat Tembok Eksterior 20kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 385000, 'sku' => 'BRG-029', 'deskripsi' => null],
            ['nama' => 'Plamir Tembok 20kg', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 71500, 'sku' => 'BRG-030', 'deskripsi' => null],
            ['nama' => 'Thinner 1 Liter', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 28000, 'sku' => 'BRG-031', 'deskripsi' => null],
            ['nama' => 'Kuas 3 Inch', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 18500, 'sku' => 'BRG-032', 'deskripsi' => null],
            ['nama' => 'Roll Cat 9 Inch', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 42000, 'sku' => 'BRG-033', 'deskripsi' => null],
            ['nama' => 'Paku Beton 5cm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 16500, 'sku' => 'BRG-034', 'deskripsi' => null],
            ['nama' => 'Paku Kayu 7cm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 17500, 'sku' => 'BRG-035', 'deskripsi' => null],
            ['nama' => 'Sekrup Gypsum 6x1', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 24500, 'sku' => 'BRG-036', 'deskripsi' => null],
            ['nama' => 'Gypsum Board 9mm', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 89000, 'sku' => 'BRG-037', 'deskripsi' => null],
            ['nama' => 'Rangka Hollow 2x4', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 78500, 'sku' => 'BRG-038', 'deskripsi' => null],
            ['nama' => 'Engsel Pintu 4 Inch', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 22500, 'sku' => 'BRG-039', 'deskripsi' => null],
            ['nama' => 'Handle Pintu Stainless', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 68500, 'sku' => 'BRG-040', 'deskripsi' => null],
            ['nama' => 'Kunci Pintu Set', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 125000, 'sku' => 'BRG-041', 'deskripsi' => null],
            ['nama' => 'Closet Duduk', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 985000, 'sku' => 'BRG-042', 'deskripsi' => null],
            ['nama' => 'Wastafel Keramik', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 465000, 'sku' => 'BRG-043', 'deskripsi' => null],
            ['nama' => 'Shower Set', 'id_satuan' => $setId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 285000, 'sku' => 'BRG-044', 'deskripsi' => null],
            ['nama' => 'Floor Drain Stainless', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 19500, 'sku' => 'BRG-045', 'deskripsi' => null],
            ['nama' => 'Torx Screw 8x2', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 33500, 'sku' => 'BRG-046', 'deskripsi' => null],
            ['nama' => 'Waterpass 60cm', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 92000, 'sku' => 'BRG-047', 'deskripsi' => null],
            ['nama' => 'Meteran 5 Meter', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 48500, 'sku' => 'BRG-048', 'deskripsi' => null],
            ['nama' => 'Gerobak Dorong', 'id_satuan' => $unitId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 745000, 'sku' => 'BRG-049', 'deskripsi' => null],
            ['nama' => 'Ember Cor 20 Liter', 'id_satuan' => $pcsId, 'stok_awal' => 0, 'stok_minimal' => 0, 'stok' => 0, 'harga_beli' => 32000, 'sku' => 'BRG-050', 'deskripsi' => null],
        ];

        foreach ($items as $item) {
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

        DB::table('barang')->whereIn('nama', [
            'Semen Portland 40kg',
            'Pasir Cor Halus',
            'Batu Split 1/2',
            'Besi Beton 10mm',
            'Besi Beton 12mm',
            'Kawat Bendrat 1kg',
            'Bata Ringan AAC',
            'Mortar Instan 40kg',
            'Keramik Lantai 40x40',
            'Keramik Dinding 25x40',
            'Granit 60x60',
            'Pipa PVC 1/2 Inch',
            'Pipa PVC 3/4 Inch',
            'Pipa PVC 1 Inch',
            'Elbow PVC 1/2 Inch',
            'Tee PVC 3/4 Inch',
            'Stop Kran 1/2 Inch',
            'Kabel NYM 2x1.5',
            'Kabel NYM 2x2.5',
            'Pipa Conduit 20mm',
            'Box MCB 4 Group',
            'MCB 6A',
            'MCB 10A',
            'Saklar Tunggal',
            'Stop Kontak Duplex',
            'Lampu LED 12 Watt',
            'Fitting Lampu E27',
            'Cat Tembok Putih 5kg',
            'Cat Tembok Eksterior 20kg',
            'Plamir Tembok 20kg',
            'Thinner 1 Liter',
            'Kuas 3 Inch',
            'Roll Cat 9 Inch',
            'Paku Beton 5cm',
            'Paku Kayu 7cm',
            'Sekrup Gypsum 6x1',
            'Gypsum Board 9mm',
            'Rangka Hollow 2x4',
            'Engsel Pintu 4 Inch',
            'Handle Pintu Stainless',
            'Kunci Pintu Set',
            'Closet Duduk',
            'Wastafel Keramik',
            'Shower Set',
            'Floor Drain Stainless',
            'Torx Screw 8x2',
            'Waterpass 60cm',
            'Meteran 5 Meter',
            'Gerobak Dorong',
            'Ember Cor 20 Liter',
        ])->delete();
    }
};
