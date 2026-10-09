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
                                    <h3 class="font-weight-bold text-xl mb-0">Riwayat Aktifitas Pengguna</h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th width="50px">No</th>
                                            <th width="170px">Waktu</th>
                                            <th width="160px">Pengguna</th>
                                            <th width="100px">Aktivitas</th>
                                            <th>Keterangan</th>
                                            <th width="90px">Method</th>
                                            <th>URL</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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
                ajax: "{{ route('aktifitas.index') }}",
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
                        data: 'pengguna',
                        name: 'user.surname',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'aktivitas',
                        name: 'aktivitas',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'method',
                        name: 'method',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'url',
                        name: 'url',
                        orderable: false,
                        searchable: false
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
