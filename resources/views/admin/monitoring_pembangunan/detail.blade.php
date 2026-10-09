@extends('admin.layout_admin')
@section('content')
    @php
        $summary = $detailData['summary'];
        $items = $detailData['items'];
        $totals = $detailData['totals'];
    @endphp

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-xl text-dark">Detail Monitoring {{ $mode === 'non-unit' ? 'Project Non Unit' : 'Pembangunan' }}</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('monitoring-pembangunan.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <label class="mb-1">{{ $summary['entity_label'] ?? 'Unit Rumah' }}</label>
                                        <input type="text" class="form-control" value="{{ $summary['unit_rumah'] }}" readonly>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <label class="mb-1">{{ $mode === 'non-unit' ? 'Lokasi Perumahan' : 'Lokasi Perumahan' }}</label>
                                        <input type="text" class="form-control" value="{{ $summary['lokasi_perumahan'] }}" readonly>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-3">
                                        <label class="mb-1">{{ $mode === 'non-unit' ? 'Nama Project' : 'Type Rumah' }}</label>
                                        <input type="text" class="form-control" value="{{ $summary['type_rumah'] }}" readonly>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-3">
                                        <label class="mb-1">Nama Tukang</label>
                                        <input type="text" class="form-control" value="{{ $summary['nama_tukang'] }}" readonly>
                                    </div>
                                    <div class="col-md-2 col-sm-6 mb-3">
                                        <label class="mb-1">Status Proyek</label>
                                        <input type="text" class="form-control" value="{{ $summary['status_proyek'] }}" readonly>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <label class="mb-1">% Realisasi Bahan terhadap RAB</label>
                                        <input type="text" class="form-control font-weight-bold"
                                            value="{{ number_format($summary['persen_bahan'], 2, ',', '.') }}%" readonly>
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <label class="mb-1">% Realisasi Jasa terhadap RAB</label>
                                        <input type="text" class="form-control font-weight-bold"
                                            value="{{ number_format($summary['persen_jasa'], 2, ',', '.') }}%" readonly>
                                    </div>
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <label class="mb-1">% Realisasi Total terhadap RAB</label>
                                        <input type="text"
                                            class="form-control font-weight-bold {{ $summary['persen_total'] >= 30 ? 'bg-success text-white border-success' : '' }}"
                                            value="{{ number_format($summary['persen_total'], 2, ',', '.') }}%" readonly>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th width="60px">No</th>
                                                <th>Nama Barang</th>
                                                <th width="110px">RAB</th>
                                                <th width="150px">Harga Satuan</th>
                                                <th width="170px">Sub Total</th>
                                                <th width="110px">Realisasi</th>
                                                <th width="170px">Sub Total</th>
                                                <th width="140px">Presentase Realisasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($items as $item)
                                                <tr>
                                                    <td>{{ $item['no'] }}</td>
                                                    <td>{{ $item['nama_barang'] }}</td>
                                                    <td class="text-right">{{ number_format($item['rab'], 2, ',', '.') }}</td>
                                                    <td class="text-right">Rp {{ number_format($item['harga_satuan'], 0, ',', '.') }}</td>
                                                    <td class="text-right">Rp {{ number_format($item['rab_sub_total'], 0, ',', '.') }}</td>
                                                    <td class="text-right">{{ number_format($item['realisasi'], 2, ',', '.') }}</td>
                                                    <td class="text-right">Rp {{ number_format($item['realisasi_sub_total'], 0, ',', '.') }}</td>
                                                    <td class="text-right">{{ number_format($item['presentase_realisasi'], 2, ',', '.') }}%</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">Belum ada data monitoring material.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr class="font-weight-bold bg-light">
                                                <td colspan="2" class="text-center">Total</td>
                                                <td class="text-right">{{ number_format($totals['rab_qty'], 2, ',', '.') }}</td>
                                                <td class="text-center">-</td>
                                                <td class="text-right">Rp {{ number_format($totals['rab_sub_total'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($totals['realisasi_qty'], 2, ',', '.') }}</td>
                                                <td class="text-right">Rp {{ number_format($totals['realisasi_sub_total'], 0, ',', '.') }}</td>
                                                <td class="text-right">{{ number_format($totals['presentase_realisasi'], 2, ',', '.') }}%</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
