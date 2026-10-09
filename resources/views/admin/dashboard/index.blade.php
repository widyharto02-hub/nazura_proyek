@extends('admin.layout_admin')

@section('content')
    <div class="content-wrapper">
        <style>
            .dashboard-stat {
                border-radius: 14px;
                overflow: hidden;
                min-height: 145px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            }

            .dashboard-stat .inner {
                padding: 20px 22px;
            }

            .dashboard-stat h3 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 6px;
            }

            .dashboard-stat p {
                margin-bottom: 0;
                font-weight: 600;
            }

            .dashboard-stat small {
                display: block;
                margin-top: 6px;
                opacity: 0.9;
            }
        </style>
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold">Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="small-box bg-info dashboard-stat">
                            <div class="inner">
                                <h3>{{ number_format($jumlahUnit, 0, ',', '.') }}</h3>
                                <p>Jumlah Unit</p>
                                <small>Total seluruh unit rumah</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="small-box bg-secondary dashboard-stat">
                            <div class="inner">
                                <h3>{{ number_format($unitBelumDiSet, 0, ',', '.') }}</h3>
                                <p>Unit Belum di Set</p>
                                <small>Belum masuk Setting Proyek</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="small-box bg-warning dashboard-stat">
                            <div class="inner">
                                <h3>{{ number_format($unitProgresPembangunan, 0, ',', '.') }}</h3>
                                <p>Unit Progres Pembangunan</p>
                                <small>Status proyek proses</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="small-box bg-success dashboard-stat">
                            <div class="inner">
                                <h3>{{ number_format($bangunanSelesai, 0, ',', '.') }}</h3>
                                <p>Bangunan Selesai</p>
                                <small>Status proyek selesai</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="small-box bg-primary dashboard-stat">
                            <div class="inner">
                                <h3>{{ number_format($jumlahItemBarangGudang, 0, ',', '.') }}</h3>
                                <p>Jumlah Item Barang Gudang</p>
                                <small>Total master barang aktif di gudang</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-md-6 col-12">
                        <div class="small-box bg-danger dashboard-stat">
                            <div class="inner">
                                <h3>Rp {{ number_format($jumlahTotalNominalBarangGudang, 0, ',', '.') }}</h3>
                                <p>Jumlah Total Nominal Barang Gudang</p>
                                <small>Akumulasi stok x harga beli</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Barang dengan Stok Sudah Masuk Minimal</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th width="60px">No</th>
                                        <th>Nama Barang</th>
                                        <th width="130px">Satuan</th>
                                        <th width="120px">Stok</th>
                                        <th width="140px">Stok Minimal</th>
                                        <th width="180px">Harga Beli</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($barangStokMinimal as $index => $barang)
                                        <tr style="background-color: #f8d7da;">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $barang->nama }}</td>
                                            <td>{{ $barang->satuan?->nama_satuan ?? '-' }}</td>
                                            <td>{{ number_format((int) $barang->stok, 0, ',', '.') }}</td>
                                            <td>{{ number_format((int) $barang->stok_minimal, 0, ',', '.') }}</td>
                                            <td>Rp {{ number_format((int) $barang->harga_beli, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada barang yang menyentuh stok minimal.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
