@extends('admin.layout_admin')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header p-3">
                                <div class="d-flex align-content-center justify-content-between">
                                    <h3 class="font-weight-bold text-xl mb-0">Log Perubahan Harga Barang</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped data-table">
                                        <thead>
                                            <tr>
                                                <th width="50px">No</th>
                                                <th width="160px">Waktu</th>
                                                <th width="170px">Barang</th>
                                                <th width="120px">Pengguna</th>
                                                <th width="90px">Proses</th>
                                                <th width="90px">Stok Sebelum</th>
                                                <th width="110px">Harga Sebelum</th>
                                                <th width="80px">Qty Lama</th>
                                                <th width="100px">Harga Lama</th>
                                                <th width="80px">Qty Baru</th>
                                                <th width="120px">Harga Baru</th>
                                                <th width="90px">Stok Sesudah</th>
                                                <th width="110px">Harga Sesudah</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

@push('scripts')
    <script>
        $(function() {
            $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                scrollX: true,
                ajax: "{{ route('log-harga-barang.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'waktu',
                        name: 'created_at',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'barang',
                        name: 'barang.nama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'pengguna',
                        name: 'user.surname',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'proses',
                        name: 'proses',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'stok_sebelum',
                        name: 'stok_sebelum',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga_sebelum',
                        name: 'harga_sebelum',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'qty_lama',
                        name: 'qty_lama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga_lama',
                        name: 'harga_lama',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'qty_baru',
                        name: 'qty_baru',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga_baru_input',
                        name: 'harga_baru_input',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'stok_sesudah',
                        name: 'stok_sesudah',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'harga_sesudah',
                        name: 'harga_sesudah',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        orderable: false,
                        searchable: true
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }]
            });
        });
    </script>
@endpush
