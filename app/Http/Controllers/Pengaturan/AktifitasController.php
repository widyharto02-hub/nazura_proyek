<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\Aktifitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AktifitasController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Aktifitas::with('user')->orderByDesc('created_at')->orderByDesc('id');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('waktu', function ($row) {
                    return $row->created_at
                        ? Carbon::parse($row->created_at)->timezone(config('app.timezone'))->format('d-m-Y H:i:s')
                        : '-';
                })
                ->addColumn('pengguna', function ($row) {
                    return $row->user?->surname ?: $row->user?->username ?: 'System';
                })
                ->editColumn('aktivitas', function ($row) {
                    return strtoupper((string) $row->aktivitas);
                })
                ->editColumn('method', function ($row) {
                    return $row->method ?: '-';
                })
                ->editColumn('url', function ($row) {
                    return $row->url ?: '-';
                })
                ->rawColumns([])
                ->make(true);
        }

        return view('admin.pengaturan.aktifitas.index', compact('permissions'));
    }
}
