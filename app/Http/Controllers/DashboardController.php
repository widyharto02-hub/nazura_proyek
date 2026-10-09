<?php
namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KavlingPeta;
use App\Models\SettingProyek;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahUnit = KavlingPeta::count();
        $unitBelumDiSet = max(0, $jumlahUnit - SettingProyek::count());
        $unitProgresPembangunan = SettingProyek::where('status', 2)->count();
        $bangunanSelesai = SettingProyek::where('status', 3)->count();

        $jumlahItemBarangGudang = Barang::count();
        $jumlahTotalNominalBarangGudang = (int) Barang::selectRaw('COALESCE(SUM(stok * harga_beli), 0) as total')->value('total');

        $barangStokMinimal = Barang::with('satuan')
            ->whereColumn('stok', '<=', 'stok_minimal')
            ->orderBy('stok', 'asc')
            ->orderBy('nama')
            ->get();

        return view('admin.dashboard.index', compact(
            'jumlahUnit',
            'unitBelumDiSet',
            'unitProgresPembangunan',
            'bangunanSelesai',
            'jumlahItemBarangGudang',
            'jumlahTotalNominalBarangGudang',
            'barangStokMinimal'
        ));
    }

}
