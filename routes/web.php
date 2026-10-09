<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\Gudang\PengajuanBarangController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PembayaranJasaController;
use App\Http\Controllers\PembayaranTukangController;
use App\Http\Controllers\Master\BarangController;
use App\Http\Controllers\Master\SatuanController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Gudang\RiwayatMutasiController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\Laporan\JasaHarianController;
use App\Http\Controllers\Laporan\PemakaianPerProyekController;
use App\Http\Controllers\Laporan\PemakaianPerUnitController;
use App\Http\Controllers\Laporan\PemasukanBarangController;
use App\Http\Controllers\Laporan\PengeluaranBarangController;
use App\Http\Controllers\Laporan\ProgresUnitController;
use App\Http\Controllers\Laporan\SisaJasaPerPekerjaanController;
use App\Http\Controllers\Laporan\SisaJasaPerProyekController;
use App\Http\Controllers\Laporan\StokBarangController;
use App\Http\Controllers\Pengaturan\HakAksesController;
use App\Http\Controllers\Pengaturan\AktifitasController;
use App\Http\Controllers\Pengaturan\LogHargaBarangController;
use App\Http\Controllers\Pengaturan\MasterProjectController;
use App\Http\Controllers\Pengaturan\PenggunaController;
use App\Http\Controllers\Pengaturan\SettingProyekController;
use App\Http\Controllers\Pengaturan\TukangController;
use App\Http\Controllers\RekapPengeluaranController;
use App\Http\Controllers\MonitoringPembangunanController;
use App\Http\Controllers\SiteplanController;
use App\Http\Middleware\LogAktifitasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::group(['middleware' => 'guest'], function () {
    Route::get('admin/login', [AuthController::class, 'getLogin'])->name('login');
    Route::post('admin/post-login', [AuthController::class, 'postLogin'])->name('admin.loginPost');
});

Route::middleware(['auth', LogAktifitasMiddleware::class])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('siteplan', SiteplanController::class)->only(['index', 'show']);
    Route::get('siteplan/cetak/pdf/{id_lokasi}', [SiteplanController::class, 'cetakPDF'])->name('siteplan.cetak.pdf');
    Route::get('siteplan/cetak/jpg/{id_lokasi}', [SiteplanController::class, 'cetakJPG'])->name('siteplan.cetak.jpg');
    Route::get('monitoring-pembangunan', [MonitoringPembangunanController::class, 'index'])->name('monitoring-pembangunan.index');
    Route::get('monitoring-pembangunan/{id}/detail', [MonitoringPembangunanController::class, 'detail'])->name('monitoring-pembangunan.detail');
    Route::get('monitoring-pembangunan/non-unit/{id}/detail', [MonitoringPembangunanController::class, 'detailNonUnit'])->name('monitoring-pembangunan.non-unit.detail');
    Route::get('pembayaran-jasa', [PembayaranJasaController::class, 'index'])->name('pembayaran-jasa.index');
    Route::get('pembayaran-jasa/setting/{setting}', [PembayaranJasaController::class, 'showSetting'])->name('pembayaran-jasa.show-setting');
    Route::get('pembayaran-jasa/setting/{setting}/services', [PembayaranJasaController::class, 'services'])->name('pembayaran-jasa.services');
    Route::get('pembayaran-jasa/setting/{setting}/summary', [PembayaranJasaController::class, 'summary'])->name('pembayaran-jasa.summary');
    Route::get('pembayaran-jasa/setting/{setting}/history', [PembayaranJasaController::class, 'history'])->name('pembayaran-jasa.history');
    Route::get('pembayaran-jasa/setting/{setting}/service/{detail}/summary', [PembayaranJasaController::class, 'serviceSummary'])->name('pembayaran-jasa.service-summary');
    Route::get('pembayaran-jasa/setting/{setting}/service/{detail}/history', [PembayaranJasaController::class, 'serviceHistory'])->name('pembayaran-jasa.service-history');
    Route::get('pembayaran-jasa/{pembayaran_jasa}/edit', [PembayaranJasaController::class, 'edit'])->name('pembayaran-jasa.edit');
    Route::post('pembayaran-jasa', [PembayaranJasaController::class, 'store'])->name('pembayaran-jasa.store');
    Route::put('pembayaran-jasa/{pembayaran_jasa}', [PembayaranJasaController::class, 'update'])->name('pembayaran-jasa.update');
    Route::delete('pembayaran-jasa/{pembayaran_jasa}', [PembayaranJasaController::class, 'destroy'])->name('pembayaran-jasa.destroy');
    Route::get('pembayaran-jasa/{pembayaran_jasa}/nota', [PembayaranJasaController::class, 'nota'])->name('pembayaran-jasa.nota');
    Route::post('pembayaran-jasa/setting/{setting}/custom-service', [PembayaranJasaController::class, 'storeCustomService'])->name('pembayaran-jasa.custom-service.store');
    Route::put('pembayaran-jasa/custom-service/{custom}', [PembayaranJasaController::class, 'updateCustomService'])->name('pembayaran-jasa.custom-service.update');
    Route::delete('pembayaran-jasa/custom-service/{custom}', [PembayaranJasaController::class, 'destroyCustomService'])->name('pembayaran-jasa.custom-service.destroy');
    Route::resource('pembayaran-tukang', PembayaranTukangController::class)->except(['create', 'show']);
    Route::get('pembayaran-tukang/{pembayaran_tukang}/nota', [PembayaranTukangController::class, 'nota'])->name('pembayaran-tukang.nota');
    Route::resource('rekap-pengeluaran', RekapPengeluaranController::class)->only(['index']);
    Route::get('rekap-pengeluaran/data', [RekapPengeluaranController::class, 'data'])->name('rekap-pengeluaran.data');
    Route::get('rekap-pengeluaran/export/{tipe}', [RekapPengeluaranController::class, 'export'])->name('rekap-pengeluaran.export');

    Route::prefix('master')->group(function () {
        Route::get('barang/{barang}/history', [BarangController::class, 'history'])->name('barang.history');
        Route::resource('barang', BarangController::class);
        Route::resource('supplier', SupplierController::class);
        Route::resource('satuan', SatuanController::class);
    });

    Route::prefix('gudang')->group(function () {
        Route::resource('barang-masuk', BarangMasukController::class);
        Route::get('barang-masuk/{barang_masuk}/nota', [BarangMasukController::class, 'nota'])->name('barang-masuk.nota');
        Route::get('barang-masuk/{barang_masuk}/payment-info', [BarangMasukController::class, 'paymentInfo'])->name('barang-masuk.payment-info');
        Route::post('barang-masuk/{barang_masuk}/payment', [BarangMasukController::class, 'storePayment'])->name('barang-masuk.payment.store');
        Route::post('barang-masuk/validasi-jumlah', [BarangMasukController::class, 'validateJumlah'])->name('barang-masuk.validate-jumlah');
        Route::get('barang-keluar/unit-by-lokasi/{id}', [BarangKeluarController::class, 'getUnitsByLokasi'])->name('barang-keluar.unit-by-lokasi');
        Route::get('barang-keluar/pekerjaan-by-project/{id}', [BarangKeluarController::class, 'getPekerjaanByProject'])->name('barang-keluar.pekerjaan-by-project');
        Route::resource('barang-keluar', BarangKeluarController::class);
        Route::get('barang-keluar/{barang_keluar}/nota', [BarangKeluarController::class, 'nota'])->name('barang-keluar.nota');
        Route::resource('pengajuan-barang', PengajuanBarangController::class);
        Route::get('riwayat-mutasi', [RiwayatMutasiController::class, 'index'])->name('riwayat-mutasi.index');
    });

    Route::prefix('laporan')->group(function () {
        Route::get('pengeluaran-barang', [PengeluaranBarangController::class, 'index'])->name('pengeluaran-barang.index');
        Route::get('pengeluaran-barang/data', [PengeluaranBarangController::class, 'data'])->name('pengeluaran-barang.data');
        Route::get('pengeluaran-barang/export/{tipe}', [PengeluaranBarangController::class, 'export'])->name('pengeluaran-barang.export');

        Route::get('pemasukan-barang', [PemasukanBarangController::class, 'index'])->name('pemasukan-barang.index');
        Route::get('pemasukan-barang/data', [PemasukanBarangController::class, 'data'])->name('pemasukan-barang.data');
        Route::get('pemasukan-barang/export/{tipe}', [PemasukanBarangController::class, 'export'])->name('pemasukan-barang.export');

        Route::get('pemakaian-per-unit', [PemakaianPerUnitController::class, 'index'])->name('pemakaian-per-unit.index');
        Route::get('pemakaian-per-unit/data', [PemakaianPerUnitController::class, 'data'])->name('pemakaian-per-unit.data');
        Route::get('pemakaian-per-unit/export/{tipe}', [PemakaianPerUnitController::class, 'export'])->name('pemakaian-per-unit.export');

        Route::get('pemakaian-per-proyek', [PemakaianPerProyekController::class, 'index'])->name('pemakaian-per-proyek.index');
        Route::get('pemakaian-per-proyek/data', [PemakaianPerProyekController::class, 'data'])->name('pemakaian-per-proyek.data');
        Route::get('pemakaian-per-proyek/export/{tipe}', [PemakaianPerProyekController::class, 'export'])->name('pemakaian-per-proyek.export');

        Route::get('jasa-harian', [JasaHarianController::class, 'index'])->name('jasa-harian.index');
        Route::get('jasa-harian/data', [JasaHarianController::class, 'data'])->name('jasa-harian.data');
        Route::get('jasa-harian/export/{tipe}', [JasaHarianController::class, 'export'])->name('jasa-harian.export');

        Route::get('sisa-jasa-per-pekerjaan', [SisaJasaPerPekerjaanController::class, 'index'])->name('sisa-jasa-per-pekerjaan.index');
        Route::get('sisa-jasa-per-pekerjaan/data', [SisaJasaPerPekerjaanController::class, 'data'])->name('sisa-jasa-per-pekerjaan.data');
        Route::get('sisa-jasa-per-pekerjaan/export/{tipe}', [SisaJasaPerPekerjaanController::class, 'export'])->name('sisa-jasa-per-pekerjaan.export');

        Route::get('sisa-jasa-per-proyek', [SisaJasaPerProyekController::class, 'index'])->name('sisa-jasa-per-proyek.index');
        Route::get('sisa-jasa-per-proyek/data', [SisaJasaPerProyekController::class, 'data'])->name('sisa-jasa-per-proyek.data');
        Route::get('sisa-jasa-per-proyek/export/{tipe}', [SisaJasaPerProyekController::class, 'export'])->name('sisa-jasa-per-proyek.export');

        Route::get('progres-unit', [ProgresUnitController::class, 'index'])->name('progres-unit.index');
        Route::get('progres-unit/data', [ProgresUnitController::class, 'data'])->name('progres-unit.data');
        Route::get('progres-unit/export/{tipe}', [ProgresUnitController::class, 'export'])->name('progres-unit.export');

        Route::get('stok-barang', [StokBarangController::class, 'index'])->name('stok-barang.index');
        Route::get('stok-barang/data', [StokBarangController::class, 'data'])->name('stok-barang.data');
        Route::get('stok-barang/export/{tipe}', [StokBarangController::class, 'export'])->name('stok-barang.export');

        Route::get('get-units', [\App\Http\Controllers\Laporan\BaseLaporanController::class, 'getUnitsByLokasi'])->name('laporan.get-units');
    });

    Route::resource('pengaturan/pengguna', PenggunaController::class);
    Route::resource('pengaturan/hak-akses', HakAksesController::class);
    Route::resource('pengaturan/aktifitas', AktifitasController::class)->only(['index']);
    Route::resource('pengaturan/log-harga-barang', LogHargaBarangController::class)->only(['index']);
    Route::resource('pengaturan/tukang', TukangController::class);
    Route::get('pengaturan/setting-proyek/units-by-lokasi/{id}', [SettingProyekController::class, 'getUnitsByLokasi'])->name('setting-proyek.units-by-lokasi');
    Route::resource('pengaturan/setting-proyek', SettingProyekController::class)->except(['create', 'show']);
    Route::get('pengaturan/master-project/{master_project}/pekerjaan', [MasterProjectController::class, 'pekerjaanIndex'])->name('master-project.pekerjaan.index');
    Route::post('pengaturan/master-project/{master_project}/pekerjaan', [MasterProjectController::class, 'storePekerjaan'])->name('master-project.pekerjaan.store');
    Route::get('pengaturan/master-project/pekerjaan/{pekerjaan}/edit', [MasterProjectController::class, 'editPekerjaan'])->name('master-project.pekerjaan.edit');
    Route::put('pengaturan/master-project/pekerjaan/{pekerjaan}', [MasterProjectController::class, 'updatePekerjaan'])->name('master-project.pekerjaan.update');
    Route::delete('pengaturan/master-project/pekerjaan/{pekerjaan}', [MasterProjectController::class, 'destroyPekerjaan'])->name('master-project.pekerjaan.destroy');
    Route::get('pengaturan/master-project/pekerjaan/{pekerjaan}/material', [MasterProjectController::class, 'material'])->name('master-project.material');
    Route::put('pengaturan/master-project/pekerjaan/{pekerjaan}/material', [MasterProjectController::class, 'updateMaterial'])->name('master-project.material.update');
    Route::get('pengaturan/master-project/pekerjaan/{pekerjaan}/jasa', [MasterProjectController::class, 'jasa'])->name('master-project.jasa');
    Route::put('pengaturan/master-project/pekerjaan/{pekerjaan}/jasa', [MasterProjectController::class, 'updateJasa'])->name('master-project.jasa.update');
    Route::resource('pengaturan/master-project', MasterProjectController::class)->except(['create', 'show']);
    Route::get('get-hak-akses', [HakAksesController::class, 'getHakAkses'])->name('admin.getHakAkses');
    Route::put('updateHakAkses', [HakAksesController::class, 'updateHakAkses'])->name('admin.updateHakAkses');

    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');
});

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh.csrf');

Route::get('/paksa-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/')->with('success', 'Anda telah logout.');
});


