<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\Barang;
use App\Models\HistoryBarang;
use App\Models\Satuan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = Barang::orderBy('nama', 'asc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('stok', fn($row) => number_format((int) $row->stok, 0, ',', '.'))
                ->editColumn('stok_minimal', fn($row) => number_format((int) $row->stok_minimal, 0, ',', '.'))
                ->editColumn('harga_beli', fn($row) => 'Rp. ' . number_format((int) $row->harga_beli, 0, ',', '.'))
                ->addColumn('satuan', function ($row) {
                    $satuan = Satuan::find($row->id_satuan);
                    return $satuan ? $satuan->nama_satuan : '';
                })
                ->setRowClass(function ($row) {
                    return (int) $row->stok <= (int) $row->stok_minimal ? 'table-danger stok-minimal-row' : '';
                })
                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl = route('barang.edit', $row->id);
                    $deleteUrl = route('barang.destroy', $row->id);
                    $historyUrl = route('barang.history', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    $btn .= '<button class="btn btn-info btn-sm mx-1 history-button" data-url="' . e($historyUrl) . '" data-nama="' . e($row->nama) . '">History</button>';

                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button" data-id="' . e($row->id) . '" data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="delete-button btn btn-danger btn-sm mx-1">Hapus</button></form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $satuans = Satuan::all();

        return view('admin.master.barang.index', compact('permissions', 'satuans'));
    }

    public function history($id)
    {
        Carbon::setLocale('id');

        $barang = Barang::findOrFail($id);
        $history = HistoryBarang::where('id_barang', $id)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal ? Carbon::parse($item->tanggal)->translatedFormat('d F Y') : '-',
                    'masuk' => number_format((int) $item->masuk, 0, ',', '.'),
                    'keluar' => number_format((int) $item->keluar, 0, ',', '.'),
                    'stok' => number_format((int) $item->stok, 0, ',', '.'),
                ];
            })
            ->values()
            ->prepend([
                'tanggal' => 'Stok Awal',
                'masuk' => number_format((int) $barang->stok_awal, 0, ',', '.'),
                'keluar' => number_format(0, 0, ',', '.'),
                'stok' => number_format((int) $barang->stok_awal, 0, ',', '.'),
            ])
            ->values()
            ->map(function ($item, $index) {
                $item['no'] = $index + 1;

                return $item;
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'nama_barang' => $barang->nama,
            'history' => $history,
        ]);
    }

    public function edit($id)
    {
        $list = Barang::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $list,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'nama' => 'required|unique:barang,nama',
            'id_satuan' => 'required',
            'stok' => 'required',
            'stok_minimal' => 'required',
            'harga_beli' => 'required',
        ];

        $messages = [
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.unique' => 'Nama barang sudah digunakan.',
            'id_satuan.required' => 'Satuan wajib dipilih.',
            'stok.required' => 'Stok wajib diisi.',
            'stok_minimal.required' => 'Stok minimal wajib diisi.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $db = [
                'nama' => $request->nama,
                'id_satuan' => $request->id_satuan,
                'sku' => null,
                'stok_awal' => str_replace('.', '', $request->stok),
                'stok_minimal' => str_replace('.', '', $request->stok_minimal),
                'stok' => str_replace('.', '', $request->stok),
                'harga_beli' => str_replace('.', '', $request->harga_beli),
                'deskripsi' => null,
            ];

            Barang::create($db);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangController@store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan data.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $data = Barang::findOrFail($id);

        $rules = [
            'nama' => 'required|unique:barang,nama,' . $data->id . ',id',
            'id_satuan' => 'required',
            'stok' => 'required',
            'stok_minimal' => 'required',
            'harga_beli' => 'required',
        ];

        $messages = [
            'nama.required' => 'Nama barang wajib diisi.',
            'nama.unique' => 'Nama barang sudah digunakan.',
            'id_satuan.required' => 'Satuan wajib dipilih.',
            'stok.required' => 'Stok wajib diisi.',
            'stok_minimal.required' => 'Stok minimal wajib diisi.',
            'harga_beli.required' => 'Harga beli wajib diisi.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $db = [
                'nama' => $request->nama,
                'id_satuan' => $request->id_satuan,
                'sku' => $data->sku,
                'stok_awal' => str_replace('.', '', $request->stok),
                'stok_minimal' => str_replace('.', '', $request->stok_minimal),
                'stok' => str_replace('.', '', $request->stok),
                'harga_beli' => str_replace('.', '', $request->harga_beli),
                'deskripsi' => $data->deskripsi,
            ];

            $data->update($db);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangController@update Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui data.'], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = Barang::findOrFail($id);

            $data->delete();

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BarangController@destroy Error: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data.'], 500);
        }
    }
}
