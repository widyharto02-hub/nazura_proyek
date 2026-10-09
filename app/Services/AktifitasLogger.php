<?php

namespace App\Services;

use App\Models\Aktifitas;
use App\Models\User;
use Illuminate\Http\Request;

class AktifitasLogger
{
    public function log(?User $user, string $aktivitas, string $keterangan, ?Request $request = null): void
    {
        Aktifitas::create([
            'id_user' => $user?->id,
            'aktivitas' => $aktivitas,
            'keterangan' => $keterangan,
            'method' => $request?->method(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function logFromRequest(Request $request): void
    {
        $user = $request->user();

        if (! $user || ! $request->route()) {
            return;
        }

        [$aktivitas, $keterangan] = $this->resolveActivity($request);

        if (! $aktivitas || ! $keterangan) {
            return;
        }

        $this->log($user, $aktivitas, $keterangan, $request);
    }

    private function resolveActivity(Request $request): array
    {
        $controllerClass = $request->route()->getControllerClass();
        $actionMethod = $request->route()->getActionMethod();

        if (! $controllerClass || ! $actionMethod) {
            return [null, null];
        }

        $specialActivity = $this->resolveSpecialActivity($request, $controllerClass, $actionMethod);

        if ($specialActivity !== null) {
            return $specialActivity;
        }

        $resource = $this->resolveResourceLabel($controllerClass, $request);
        $reference = $this->resolveReference($request);

        if (str_starts_with($actionMethod, 'store')) {
            $keterangan = "Menambahkan data {$resource}";

            if ($reference) {
                $keterangan .= " dengan referensi {$reference}";
            }

            return ['tambah', $keterangan . '.'];
        }

        if (str_starts_with($actionMethod, 'update')) {
            $keterangan = "Memperbarui data {$resource}";

            if ($reference) {
                $keterangan .= " dengan referensi {$reference}";
            }

            return ['edit', $keterangan . '.'];
        }

        if (str_starts_with($actionMethod, 'destroy') || str_starts_with($actionMethod, 'delete')) {
            $keterangan = "Menghapus data {$resource}";

            if ($reference) {
                $keterangan .= " dengan referensi {$reference}";
            }

            return ['hapus', $keterangan . '.'];
        }

        return [null, null];
    }

    private function resolveSpecialActivity(Request $request, string $controllerClass, string $actionMethod): ?array
    {
        if ($controllerClass === 'App\Http\Controllers\BarangMasukController' && $actionMethod === 'storePayment') {
            $reference = $this->resolveReference($request) ?? 'ID ' . $request->route('barang_masuk', $request->route('id'));

            return ['tambah', "Menambahkan pembayaran untuk transaksi Barang Masuk dengan referensi {$reference}."];
        }

        if ($controllerClass === 'App\Http\Controllers\Pengaturan\HakAksesController' && $actionMethod === 'updateHakAkses') {
            $targetUserId = $request->input('id_user') ?: $request->query('id_user');
            $reference = $targetUserId ? 'ID User ' . $targetUserId : 'pengguna yang dipilih';

            return ['edit', "Memperbarui data Hak Akses untuk {$reference}."];
        }

        if ($controllerClass === 'App\Http\Controllers\Pengaturan\MasterProjectController' && $actionMethod === 'updateMaterial') {
            $reference = $this->resolveReference($request);
            $keterangan = 'Memperbarui material pada Master Project';

            if ($reference) {
                $keterangan .= " dengan referensi {$reference}";
            }

            return ['edit', $keterangan . '.'];
        }

        if ($controllerClass === 'App\Http\Controllers\Pengaturan\MasterProjectController' && $actionMethod === 'updateJasa') {
            $reference = $this->resolveReference($request);
            $keterangan = 'Memperbarui jasa pada Master Project';

            if ($reference) {
                $keterangan .= " dengan referensi {$reference}";
            }

            return ['edit', $keterangan . '.'];
        }

        return null;
    }

    private function resolveResourceLabel(string $controllerClass, Request $request): string
    {
        $map = [
            'App\Http\Controllers\Master\BarangController' => 'Barang',
            'App\Http\Controllers\Master\SupplierController' => 'Supplier',
            'App\Http\Controllers\Master\SatuanController' => 'Satuan',
            'App\Http\Controllers\BarangMasukController' => 'Barang Masuk',
            'App\Http\Controllers\BarangKeluarController' => 'Barang Keluar',
            'App\Http\Controllers\Gudang\PengajuanBarangController' => 'Pengajuan Barang',
            'App\Http\Controllers\Pengaturan\PenggunaController' => 'Pengguna',
            'App\Http\Controllers\Pengaturan\HakAksesController' => 'Hak Akses',
            'App\Http\Controllers\Pengaturan\TukangController' => 'Tukang',
            'App\Http\Controllers\Pengaturan\SettingProyekController' => 'Setting Proyek',
            'App\Http\Controllers\Pengaturan\MasterProjectController' => 'Master Project',
            'App\Http\Controllers\PembayaranJasaController' => 'Pembayaran Jasa',
            'App\Http\Controllers\PembayaranTukangController' => 'Pembayaran Tukang',
        ];

        if (isset($map[$controllerClass])) {
            return $map[$controllerClass];
        }

        $routeName = (string) $request->route()->getName();
        $baseName = explode('.', $routeName)[0] ?? 'data';

        return ucwords(str_replace('-', ' ', $baseName));
    }

    private function resolveReference(Request $request): ?string
    {
        $fields = [
            'nama',
            'surname',
            'username',
            'nama_satuan',
            'nama_pekerjaan',
            'nama_penerima',
            'tanggal',
            'kode',
        ];

        foreach ($fields as $field) {
            $value = $request->input($field);

            if (filled($value)) {
                return '"' . $value . '"';
            }
        }

        foreach ($request->route()->parameters() as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return 'ID ' . $parameter->getKey();
            }

            if (is_scalar($parameter) && filled($parameter)) {
                return 'ID ' . $parameter;
            }
        }

        return null;
    }
}

