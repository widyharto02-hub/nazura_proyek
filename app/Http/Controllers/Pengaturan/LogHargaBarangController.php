<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\LogHargaBarang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogHargaBarangController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = LogHargaBarang::with(['barang', 'user'])
                ->orderByDesc('created_at')
                ->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('waktu', function ($row) {
                    return $row->created_at
                        ? Carbon::parse($row->created_at)->timezone(config('app.timezone'))->format('d-m-Y H:i:s')
                        : '-';
                })
                ->addColumn('barang', function ($row) {
                    return $row->barang?->nama ?: 'Barang tidak ditemukan';
                })
                ->addColumn('pengguna', function ($row) {
                    return $row->user?->surname ?: $row->user?->username ?: 'System';
                })
                ->editColumn('proses', function ($row) {
                    return strtoupper((string) $row->proses);
                })
                ->editColumn('qty_lama', fn ($row) => $row->qty_lama ?? '-')
                ->editColumn('harga_lama', fn ($row) => $row->harga_lama !== null ? number_format((int) $row->harga_lama, 0, ',', '.') : '-')
                ->editColumn('qty_baru', fn ($row) => $row->qty_baru ?? '-')
                ->editColumn('harga_baru_input', fn ($row) => $row->harga_baru_input !== null ? number_format((int) $row->harga_baru_input, 0, ',', '.') : '-')
                ->editColumn('harga_sebelum', fn ($row) => number_format((int) $row->harga_sebelum, 0, ',', '.'))
                ->editColumn('harga_sesudah', fn ($row) => number_format((int) $row->harga_sesudah, 0, ',', '.'))
                ->editColumn('stok_sebelum', fn ($row) => number_format((int) $row->stok_sebelum, 0, ',', '.'))
                ->editColumn('stok_sesudah', fn ($row) => number_format((int) $row->stok_sesudah, 0, ',', '.'))
                ->make(true);
        }

        return view('admin.pengaturan.log_harga_barang.index', compact('permissions'));
    }
}
