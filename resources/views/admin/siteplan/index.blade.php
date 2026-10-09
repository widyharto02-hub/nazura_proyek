<!-- resources/views/admin/bank/index.blade.php -->
@extends('admin.layout_admin')
@section('content')
    <style>
        .legend {
            position: fixed;
            top: 90px;
            right: 30px;
            padding: 12px;
            font-size: 14px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.18);
            transition: right 0.3s ease;
            width: 220px;
            z-index: 20;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .legend-color {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            border-radius: 4px;
            border: 1px solid #999;
            flex-shrink: 0;
        }

        .toggle-btn {
            width: 100%;
            padding: 6px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            margin-top: 6px;
        }

        .show-btn {
            position: fixed;
            top: 110px;
            right: 30px;
            padding: 8px 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.3);
            cursor: pointer;
            display: none;
            z-index: 20;
        }

        .svg-container {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            border: 2px solid #d1cfcf;
            position: relative;
        }

        .svg-container svg {
            width: 100%;
            height: 100%;
            cursor: grab;
            transition: transform 0.1s ease-out;
        }
    </style>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-xl text-dark">Siteplan</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12 col-12">

                        <!-- ================================================================================================== -->

                        <div class="card card-primary card-outline card-outline-tabs">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                    @foreach ($lokasiKavling as $index => $kav)
                                        <li class="nav-item">
                                            <a class="nav-link {{ $index == 0 ? 'active' : '' }}"
                                                id="custom-tabs-four-{{ $kav->id_lokasi }}-tab" data-toggle="pill"
                                                href="#custom-tabs-four-{{ $kav->id_lokasi }}" role="tab">
                                                {{ $kav->nama_kavling }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-four-tabContent">
                                    @foreach ($lokasiKavling as $index => $kav)
                                        <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
                                            id="custom-tabs-four-{{ $kav->id_lokasi }}" role="tabpanel">

                                            <a href="{{ route('siteplan.cetak.pdf', $kav->id_lokasi) }}" target="_blank"
                                                class="btn btn-warning btn-sm">Cetak Denah
                                                PDF</a>
                                            <a href="{{ route('siteplan.cetak.jpg', $kav->id_lokasi) }}" target="_blank"
                                                class="btn btn-info btn-sm">Download Denah
                                                JPG</a>

                                            {{-- SVG Container khusus lokasi ini --}}
                                            <div class="svg-container mt-3">
                                                <button class="reset-button btn btn-success btn-sm"
                                                    style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                                                    Reset Siteplan
                                                </button>

                                                {{-- SVG Header --}}
                                                @if ($kav->masterSvg)
                                                    {!! str_replace(['[[lebar]]', '[[tinggi]]'], ['100%', '100%'], $kav->masterSvg->header_svg) !!}
                                                @endif

                                            {{-- Loop kavling --}}
                                            @foreach ($kav->kavlingPeta as $pt)
                                                @php
                                                    $warna = '#ffffff';
                                                    if ($pt->settingProyek) {
                                                        $warna = match ((int) $pt->settingProyek->status) {
                                                            1 => '#ffd54f',
                                                            2 => '#ff9800',
                                                            3 => '#28a745',
                                                            default => '#ffffff',
                                                        };
                                                    }
                                                @endphp
                                                @if ($pt->jenis_map == 'polygon')
                                                    <a href="javascript:void(0);" class="detail-button"
                                                        data-id="{{ $pt->id_kavling }}"
                                                        data-url="{{ route('siteplan.show', $pt->id_kavling) }}">
                                                            {!! str_replace(
                                                                ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                                                                [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                                                                $kav->masterSvg->polygon_svg,
                                                            ) !!}
                                                        </a>
                                                    @elseif ($pt->jenis_map == 'path')
                                                        <a href="javascript:void(0);" class="detail-button"
                                                            data-id="{{ $pt->id_kavling }}"
                                                            data-url="{{ route('siteplan.show', $pt->id_kavling) }}">
                                                            {!! str_replace(
                                                                ['[[1]]', '[[2]]', '[[3]]', '[[4]]'],
                                                                [$pt->map, $warna, $pt->matrik, $pt->kode_kavling],
                                                                $kav->masterSvg->path_svg,
                                                            ) !!}
                                                        </a>
                                                    @endif
                                                @endforeach

                                                {{-- SVG Footer --}}
                                                @if ($kav->masterSvg)
                                                    {!! $kav->masterSvg->footer_svg !!}
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- /.card -->
                        </div>
                    </div>

                    <button class="show-btn btn-xs" id="show-btn" onclick="toggleLegend()">Show</button>

                    <div class="legend" id="legend">
                        <div class="font-weight-bold mb-2">Keterangan Warna</div>
                        @foreach ($legend as $item)
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: {{ $item['color'] }}"></div>
                                {{ $item['label'] }}
                            </div>
                        @endforeach
                        <button class="toggle-btn" onclick="toggleLegend()">Hide</button>
                    </div>

                </div>
            </div>
        </section>
    </div><!-- /.container-fluid -->
    <!-- /.content-wrapper -->

    <div class="modal fade" id="modalDetail" data-focus="false" tabindex="-1" role="dialog"
        aria-labelledby="modalDetailLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white font-weight-bold" id="modalDetailLabel">Detail Data Kavling</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card card-primary card-outline card-outline-tabs">
                        <div class="card-header p-0 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="custom-tabs-four-101-tab" data-toggle="pill"
                                        href="#custom-tabs-four-101">Data Unit Rumah</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-four-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-four-101">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Perumahan</label>
                                        <div class="col-sm-3">
                                            <input type="text" name="nama_kavling" id="nama_kavling"
                                                class="form-control" readonly>
                                        </div>
                                        <label class="col-sm-2 col-form-label">Kode Kavling</label>
                                        <div class="col-sm-2">
                                            <input type="text" name="kode_kavling" id="kode_kavling"
                                                class="form-control" readonly>
                                        </div>
                                    </div>



                                    <hr>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Status Proyek</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="status_proyek" class="form-control" readonly>
                                        </div>
                                        <label class="col-sm-2 col-form-label">Type Rumah</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="type_rumah" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Nama Tukang</label>
                                        <div class="col-sm-3">
                                            <input type="text" id="nama_tukang" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <hr>
                                    <div class="form-group row mb-2">
                                        <div class="col-sm-12">
                                            <label class="font-weight-bold mb-2">Monitoring Material</label>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nama Barang</th>
                                                    <th width="140px">RAB</th>
                                                    <th width="140px">Realisasi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="monitoringDetailBody">
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Belum ada data.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Keluar</button>
                    </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleLegend() {
            var legend = document.getElementById('legend');
            var showBtn = document.getElementById('show-btn');

            if (legend.style.right === '30px' || legend.style.right === '') {
                legend.style.right = '-260px';
                showBtn.style.display = 'block';
            } else {
                legend.style.right = '30px';
                showBtn.style.display = 'none';
            }
        }

        $(document).on('click', '.detail-button', function() {
            var url = $(this).data('url');

            $.get(url, function(response) {
                if (response.status === 'success') {

                    const formatNumber = (number) => {
                        let num = parseFloat(number);
                        return isNaN(num) ? 0 : num.toLocaleString('id-ID');
                    };

                    $('#nama_kavling').val(response.data.lokasi.nama_kavling);
                    $('#kode_kavling').val(response.data.kode_kavling);
                    $('#panjang_kanan').val(response.data.panjang_kanan);
                    $('#panjang_kiri').val(response.data.panjang_kiri);
                    $('#lebar_depan').val(response.data.lebar_depan);
                    $('#lebar_belakang').val(response.data.lebar_belakang);
                    $('#luas_tanah').val(response.data.luas_tanah);
                    $('#luas_bangunan').val(response.data.luas_bangunan);
                    $('#hrg_meter').val(formatNumber(response.data.hrg_meter));
                    $('#tipe_bangunan').val(response.data.tipe_bangunan);
                    $('#hrg_jual').val(formatNumber(response.data.hrg_jual));
                    $('#daya_listrik').val(formatNumber(response.data.daya_listrik));
                    $('#keterangan').val(response.data.keterangan);
                    $('#no_sertifikat').val(response.data.no_sertifikat);
                    $('#status_proyek').val(getStatusLabel(response.data.setting_proyek ? response.data.setting_proyek
                        .status : null));
                    $('#type_rumah').val(response.data.setting_proyek && response.data.setting_proyek
                        .master_project ? response.data.setting_proyek.master_project.jenis_proyek : '-');
                    $('#nama_tukang').val(response.data.setting_proyek && response.data.setting_proyek.tukang ?
                        response.data.setting_proyek.tukang.nama : '-');

                    let monitoringHtml = '';
                    (response.monitoring_items || []).forEach(function(item) {
                        monitoringHtml += `
                            <tr>
                                <td>${item.nama_barang ?? '-'}</td>
                                <td>${item.rab ?? '0'}</td>
                                <td>${item.realisasi ?? '0'}</td>
                            </tr>
                        `;
                    });

                    if (!monitoringHtml) {
                        monitoringHtml =
                            '<tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>';
                    }

                    $('#monitoringDetailBody').html(monitoringHtml);

                    // $('#nama_lengkap').val(response.data.customer.nama_lengkap);
                    // $('#nik').val(response.data.customer.nik);
                    // $('#no_telp').val(response.data.customer.no_telp);
                    // $('#tempat_lahir').val(response.data.customer.tempat_lahir);
                    // $('#tgl_lahir').val(response.data.customer.tgl_lahir);
                    // $('#jenis_kelamin').val(response.data.customer.jenis_kelamin);
                    // $('#alamat_ktp').val(response.data.customer.alamat_ktp);
                    // $('#alamat_domisili').val(response.data.customer.alamat_domisili);
                    // $('#npwp').val(response.data.customer.npwp);
                    // $('#jenis_pembelian').val(response.data.customer.jenis_pembelian);

                    $('#custom-tabs-four-101-tab').tab('show');
                    $('#modalDetail').modal('show');
                }
            });
        });

        function getStatusLabel(status) {
            switch (parseInt(status, 10)) {
                case 1:
                    return 'Sudah Disetting';
                case 2:
                    return 'Proses';
                case 3:
                    return 'Selesai';
                default:
                    return 'Belum di Setting';
            }
        }

        $('#modalForm').on('hidden.bs.modal', function() {
            $('#formData')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#jenis_kelamin').val('').trigger('change');
            $('#status').val('').trigger('change');

            let submitBtn = $('#submitBtn');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.addClass('d-none');
            btnText.text('Simpan');
            submitBtn.prop('disabled', false);

            $('#status_proyek').val('');
            $('#type_rumah').val('');
            $('#nama_tukang').val('');
        });

        function formatTanggalIndo(tanggal) {
            if (!tanggal) return '-';
            const bulanIndo = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            let d = new Date(tanggal);
            let tgl = d.getDate();
            let bln = bulanIndo[d.getMonth()];
            let thn = d.getFullYear();
            return `${tgl} ${bln} ${thn}`;
        }
    </script>
    <script src={{ asset('assets/svg_1.js') }}></script>
@endpush

