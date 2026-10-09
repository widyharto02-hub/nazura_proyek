<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\BarangMasukDetail;
use App\Models\BarangKeluarDetail;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RiwayatMutasiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = collect();

            $masuk = BarangMasukDetail::selectRaw("
                'MASUK' as jenis,
                barang_masuk.tanggal,
                barang_masuk_detail.id_barang,
                barang_masuk_detail.jumlah as qty,
                barang.nama as nama_barang,
                CASE
                    WHEN supplier.nama IS NOT NULL THEN CONCAT('Supplier: ', supplier.nama)
                    ELSE CONCAT('Barang Masuk #', barang_masuk.id)
                END as ref
            ");
            $masuk = $masuk->join('barang_masuk', 'barang_masuk_detail.id_masuk', '=', 'barang_masuk.id')
                ->leftJoin('supplier', 'supplier.id', '=', 'barang_masuk.id_supplier')
                ->join('barang', 'barang_masuk_detail.id_barang', '=', 'barang.id');

            if ($request->id_barang) {
                $masuk->where('barang_masuk_detail.id_barang', $request->id_barang);
            }

            $keluar = BarangKeluarDetail::selectRaw("
                'KELUAR' as jenis,
                barang_keluar.tanggal,
                barang_keluar_detail.id_barang,
                barang_keluar_detail.jumlah as qty,
                barang.nama as nama_barang,
                CASE
                    WHEN kavling_peta.kode_kavling IS NOT NULL THEN CONCAT('Unit: ', kavling_peta.kode_kavling)
                    WHEN lokasi_kavling.nama_kavling IS NOT NULL THEN CONCAT('Perumahan: ', lokasi_kavling.nama_kavling)
                    ELSE CONCAT('Barang Keluar #', barang_keluar.id)
                END as ref
            ");
            $keluar = $keluar->join('barang_keluar', 'barang_keluar_detail.id_barang_keluar', '=', 'barang_keluar.id')
                ->leftJoin('kavling_peta', 'barang_keluar.id_kavling', '=', 'kavling_peta.id_kavling')
                ->leftJoin('lokasi_kavling', 'barang_keluar.id_lokasi', '=', 'lokasi_kavling.id_lokasi')
                ->join('barang', 'barang_keluar_detail.id_barang', '=', 'barang.id');

            if ($request->id_barang) {
                $keluar->where('barang_keluar_detail.id_barang', $request->id_barang);
            }

            if ($request->jenis === 'MASUK') {
                $data = $masuk->orderBy('tanggal', 'desc')->get();
            } elseif ($request->jenis === 'KELUAR') {
                $data = $keluar->orderBy('tanggal', 'desc')->get();
            } else {
                $data = $masuk->union($keluar)->orderBy('tanggal', 'desc')->get();
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('jenis', function ($row) {
                    $badge = $row->jenis === 'MASUK' ? 'success' : 'danger';
                    return '<span class="badge badge-' . $badge . '">' . $row->jenis . '</span>';
                })
                ->rawColumns(['jenis'])
                ->make(true);
        }

        $barangList = Barang::orderBy('nama')->get();
        return view('admin.gudang.riwayat_mutasi.index', compact('barangList'));
    }
}
