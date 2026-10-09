<?php
namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Models\HakAkses;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $permissions = HakAksesController::getUserPermissions();

        if ($request->ajax()) {
            $data = User::orderBy('id', 'desc')->where('user_from', 2);

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('role', function ($row) {
                    switch ($row->role) {
                        case 1:
                            return '<span class="badge bg-info text-dark">Marketing</span>';
                        case 2:
                            return '<span class="badge bg-success">Admin</span>';
                        case 3:
                            return '<span class="badge bg-warning text-dark">Manager</span>';
                        case 4:
                            return '<span class="badge bg-warning text-dark">Super Admin</span>';
                        default:
                            return '<span class="badge bg-secondary">Tidak Diketahui</span>';
                    }
                })

                ->addColumn('action', function ($row) use ($permissions) {
                    $editUrl   = route('pengguna.edit', $row->id);
                    $deleteUrl = route('pengguna.destroy', $row->id);

                    $btn = '<div class="d-flex justify-content-center">';
                    if ($permissions['edit']) {
                        $btn .= '<button class="btn btn-primary btn-sm mx-1 edit-button"
                                data-id="' . e($row->id) . '"
                                data-url="' . e($editUrl) . '">Edit</button>';
                    }

                    if ($permissions['hapus']) {
                        $btn .= '<form action="' . e($deleteUrl) . '" method="POST" style="display:inline;">
                    ' . csrf_field() . method_field('DELETE') . '
                    <button type="submit" class="delete-button btn btn-danger btn-sm mx-1">
                        Hapus
                    </button>
                    </form>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })

                ->rawColumns(['role', 'action'])
                ->make(true);
        }

        return view('admin.pengaturan.pengguna.index', compact('permissions'));
    }

    public function edit($id)
    {
        $list = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $list,
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'surname'  => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'status'   => 'required|in:AKTIF,BLOKIR',
        ];

        $messages = [
            'surname.required'  => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'status.required'   => 'Status wajib dipilih.',
            'status.in'         => 'Status tidak valid.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $db = [
                'surname'   => $request->surname,
                'username'  => $request->username,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'status'    => $request->status,
                'role'      => 2,
                'user_from' => 2,
            ];

            $user = User::create($db);

            $menus = Menu::all();

            foreach ($menus as $menu) {
                $akses = [
                    'id_user' => $user->id,
                    'id_menu' => $menu->id,
                ];

                $akses['lihat']  = 1;
                $akses['tambah'] = 0;
                $akses['edit']   = 0;
                $akses['hapus']  = 0;

                HakAkses::create($akses);
            }

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PenggunaController@store: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan data.']);
        }
    }

    public function update(Request $request, $id)
    {
        $data = User::findOrFail($id);

        $rules = [
            'surname'  => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->ignore($data->id)
                    ->where(fn($q) => $q->where('user_from', 2)),
            ],
            'email'    => [
                'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($data->id)
                    ->where(fn($q) => $q->where('user_from', 2)),
            ],
            'password' => 'nullable|string|min:6',
            'status'   => 'required|in:AKTIF,BLOKIR',
        ];

        $messages = [
            'surname.required'  => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.min'      => 'Password minimal 6 karakter.',
            'status.required'   => 'Status wajib dipilih.',
            'status.in'         => 'Status tidak valid.',
        ];

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            $db = [
                'surname'  => $request->surname,
                'username' => $request->username,
                'email'    => $request->email,
                'status'   => $request->status,
            ];

            if (! empty($request->password)) {
                $db['password'] = Hash::make($request->password);
            }

            $data->update($db);

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PenggunaController@update: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'user_id' => $id,
                'request' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui data.']);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = User::findOrFail($id);
            HakAkses::where('id_user', $data->id)->delete();

            $data->delete();

            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PenggunaController@destroy: ' . $e->getMessage(), [
                'trace'   => $e->getTraceAsString(),
                'user_id' => $id,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus data.']);
        }
    }
}
