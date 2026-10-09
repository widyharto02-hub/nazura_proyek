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
                                    <h3 class="font-weight-bold text-lg">Rekap Pengeluaran Mingguan</h3>
                                    <div class="d-flex">
                                        <a href="#" target="_blank" class="btn btn-sm mr-1 btn-export-excel" style="background-color: #28a745; border-color: #28a745; color: #ffffff;"><i class="fas fa-file-excel"></i><span class="ml-1">Excel</span></a>
                                        <a href="#" target="_blank" class="btn btn-sm btn-export-pdf" style="background-color: #dc3545; border-color: #dc3545; color: #ffffff;"><i class="fas fa-file-pdf"></i><span class="ml-1">PDF</span></a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="mb-1">Pilih Minggu</label>
                                        <select id="filter_minggu" class="form-control select-week">
                                            @php
                                                $currentKey = $week['minggu_key'];
                                                $now = \Carbon\Carbon::now();
                                                $options = [];
                                                for ($i = 0; $i < 12; $i++) {
                                                    $start = $now->copy()->subWeeks($i)->startOfWeek(\Carbon\Carbon::MONDAY);
                                                    $end = $start->copy()->addDays(6);
                                                    $key = $start->format('o') . '-W' . $start->format('W');
                                                    $options[$key] = $start->translatedFormat('d F Y') . ' s/d ' . $end->translatedFormat('d F Y');
                                                }
                                            @endphp
                                            @foreach ($options as $key => $label)
                                                <option value="{{ $key }}" @selected($currentKey === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="mb-1">Filter Jenis</label>
                                        <select id="filter_jenis" class="form-control select-jenis">
                                            <option value="">Semua Jenis</option>
                                            <option value="Barang Masuk">Barang Masuk</option>
                                            <option value="Jasa Harian">Jasa Harian</option>
                                            <option value="Pembayaran Jasa">Pembayaran Jasa</option>
                                        </select>
                                    </div>
                                </div>

                                <div id="rekap_container">
                                    @include('admin.rekap_pengeluaran._table', [
                                        'entries' => $entries,
                                        'summary' => $summary,
                                    ])
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
        $(document).ready(function() {
            $('.select-week, .select-jenis').select2({
                theme: "bootstrap4",
                width: '100%'
            });
        });

        $(function() {
            const dataUrl = "{{ route('rekap-pengeluaran.data') }}";

            function updateExportLinks() {
                const week = $('#filter_minggu').val();
                const jenis = $('#filter_jenis').val();
                const excelParams = $.param({ minggu: week, jenis: jenis, format: 'excel' });
                const pdfParams = $.param({ minggu: week, jenis: jenis, format: 'pdf' });
                $('.btn-export-excel').attr('href', "{{ route('rekap-pengeluaran.export', 'export') }}" + '?' + excelParams);
                $('.btn-export-pdf').attr('href', "{{ route('rekap-pengeluaran.export', 'export') }}" + '?' + pdfParams);
            }

            updateExportLinks();

            function loadData() {
                const minggu = $('#filter_minggu').val();
                const jenis = $('#filter_jenis').val();

                $.ajax({
                    url: dataUrl,
                    type: 'GET',
                    data: { minggu: minggu, jenis: jenis },
                    success: function(res) {
                        $('#rekap_container').html(res.html);
                        updateExportLinks();
                    }
                });
            }

            $('#filter_minggu, #filter_jenis').on('change', function() {
                loadData();
            });
        });
    </script>
@endpush
