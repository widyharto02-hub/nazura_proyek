@extends('admin.layout_admin')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid"></div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header p-3">
                                <h3 class="font-weight-bold text-xl mb-0">Monitoring Pembangunan</h3>
                            </div>
                            <div class="card-body">
                                <ul class="nav nav-tabs mb-3" id="monitoringTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="unit-tab" data-toggle="tab" href="#unit-pane" role="tab">Project Unit Rumah</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="non-unit-tab" data-toggle="tab" href="#non-unit-pane" role="tab">Project Non Unit</a>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="unit-pane" role="tabpanel">
                                        <table class="table table-bordered table-striped data-table-unit w-100">
                                            <thead>
                                                <tr>
                                                    <th width="50px">No</th>
                                                    <th>Unit Rumah</th>
                                                    <th>Type Rumah</th>
                                                    <th>Realisasi Bahan</th>
                                                    <th>Realisasi Jasa</th>
                                                    <th>Realisasi Total</th>
                                                    <th>Status Proyek</th>
                                                    <th width="100px">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="tab-pane fade" id="non-unit-pane" role="tabpanel">
                                        <table class="table table-bordered table-striped data-table-non-unit w-100">
                                            <thead>
                                                <tr>
                                                    <th width="50px">No</th>
                                                    <th>Project Non Unit</th>
                                                    <th>Nama Project</th>
                                                    <th>Realisasi Bahan</th>
                                                    <th>Realisasi Jasa</th>
                                                    <th>Realisasi Total</th>
                                                    <th>Status Proyek</th>
                                                    <th width="100px">Action</th>
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
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('.data-table-unit').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('monitoring-pembangunan.index', ['mode' => 'unit']) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'unit_rumah',
                        name: 'unit_rumah',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'type_rumah',
                        name: 'type_rumah',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'realisasi_bahan_persen',
                        name: 'realisasi_bahan_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'realisasi_jasa_persen',
                        name: 'realisasi_jasa_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'realisasi_total_persen',
                        name: 'realisasi_total_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_proyek',
                        name: 'status_proyek',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }, {
                    targets: [3, 4, 5, 7],
                    className: 'text-center'
                }]
            });

            $('.data-table-non-unit').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('monitoring-pembangunan.index', ['mode' => 'non-unit']) }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'project_label',
                        name: 'project_label',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'project_type',
                        name: 'project_type',
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'realisasi_bahan_persen',
                        name: 'realisasi_bahan_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'realisasi_jasa_persen',
                        name: 'realisasi_jasa_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'realisasi_total_persen',
                        name: 'realisasi_total_persen',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_proyek',
                        name: 'status_proyek',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                columnDefs: [{
                    targets: 0,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                }, {
                    targets: [3, 4, 5, 7],
                    className: 'text-center'
                }]
            });
        });
    </script>
@endpush
