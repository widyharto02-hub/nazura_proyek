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
                                <div class="d-flex align-content-center justify-content-between">
                                    <h3 class="font-weight-bold text-lg mb-0">Monitoring Jasa</h3>
                                    <div class="d-flex align-items-center">
                                        @if ($permissions['tambah'])
                                            <button type="button" class="btn btn-sm btn-primary" id="btnTambahPembayaran">
                                                <i class="fas fa-plus"></i> Tambah Pembayaran
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped data-table w-100">
                                    <thead>
                                        <tr>
                                            <th width="50px">No</th>
                                            <th>Unit Rumah</th>
                                            <th>Type Rumah</th>
                                            <th width="160px">RAB Jasa</th>
                                            <th width="160px">Total Bayar</th>
                                            <th width="160px">Sisa Bayar</th>
                                            <th width="130px">Status</th>
                                            <th width="180px">Action</th>
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

    <div class="modal fade" id="serviceListModal" tabindex="-1" role="dialog" data-focus="false"
        aria-labelledby="serviceListModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white font-weight-bold" id="serviceListModalLabel">Daftar Jasa RAB</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="mb-1">Unit</label>
                            <input type="text" id="service_list_unit" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Type Rumah</label>
                            <input type="text" id="service_list_type" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Status</label>
                            <input type="text" id="service_list_status" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="mb-1">RAB Jasa</label>
                            <input type="text" id="service_list_rab" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Total Bayar</label>
                            <input type="text" id="service_list_total" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Sisa Bayar</label>
                            <input type="text" id="service_list_sisa" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th>Jenis Jasa</th>
                                    <th width="160px">RAB</th>
                                    <th width="160px">Realisasi</th>
                                    <th width="160px">Sisa</th>
                                    <th width="120px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="serviceListBody">
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data jasa pada RAB.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalForm" tabindex="-1" role="dialog" data-focus="false"
        aria-labelledby="modalFormLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form id="formData" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white font-weight-bold" id="modalFormLabel"></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="primary_id" name="primary_id">

                        <div class="form-group row">
                            <label for="id_setting_proyek" class="col-sm-3 col-form-label">Unit</label>
                            <div class="col-sm-9">
                                <select name="id_setting_proyek" id="id_setting_proyek" class="form-control select-unit">
                                    <option value=""></option>
                                    @foreach ($settingProyekList as $setting)
                                        <option value="{{ $setting->id }}">
                                            {{ $setting->lokasi?->nama_kavling ?? '-' }} - {{ $setting->kavling?->kode_kavling ?? '-' }} -
                                            {{ $setting->masterProject?->jenis_proyek ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="id_master_project_detail" class="col-sm-3 col-form-label">Jasa RAB</label>
                            <div class="col-sm-9">
                                <select name="id_master_project_detail" id="id_master_project_detail" class="form-control select-jasa">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Lokasi Perumahan</label>
                            <div class="col-sm-9">
                                <input type="text" id="summary_lokasi" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Type Rumah</label>
                            <div class="col-sm-3">
                                <input type="text" id="summary_type" class="form-control" readonly>
                            </div>
                            <label class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-3">
                                <input type="text" id="summary_status" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama Jasa</label>
                            <div class="col-sm-9">
                                <input type="text" id="summary_nama_jasa" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">RAB Jasa</label>
                            <div class="col-sm-3">
                                <input type="text" id="summary_rab_jasa" class="form-control" readonly>
                            </div>
                            <label class="col-sm-3 col-form-label">Sudah Dibayar</label>
                            <div class="col-sm-3">
                                <input type="text" id="summary_total_bayar" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Sisa Bayar</label>
                            <div class="col-sm-3">
                                <input type="text" id="summary_sisa_bayar" class="form-control" readonly>
                            </div>
                        </div>

                        <hr>

                        <div class="form-group row">
                            <label for="tanggal_pembayaran" class="col-sm-3 col-form-label">Tanggal Bayar</label>
                            <div class="col-sm-3">
                                <input type="date" id="tanggal_pembayaran" name="tanggal_pembayaran" class="form-control"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <label for="id_tukang" class="col-sm-3 col-form-label">Tukang</label>
                            <div class="col-sm-3">
                                <select name="id_tukang" id="id_tukang" class="form-control select-tukang">
                                    <option value=""></option>
                                    @foreach ($tukangList as $tukang)
                                        <option value="{{ $tukang->id }}">{{ $tukang->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="jumlah_bayar" class="col-sm-3 col-form-label">Nominal Bayar</label>
                            <div class="col-sm-3">
                                <input type="text" id="jumlah_bayar" name="jumlah_bayar" class="form-control format-number">
                            </div>
                            <label for="bukti_bayar" class="col-sm-3 col-form-label">Bukti Bayar</label>
                            <div class="col-sm-3">
                                <input type="file" id="bukti_bayar" name="bukti_bayar" class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <label class="col-sm-3 col-form-label">Preview Bukti</label>
                            <div class="col-sm-9">
                                <div id="proofPreview"
                                    class="border rounded d-flex align-items-center justify-content-center bg-light"
                                    style="min-height: 150px;">
                                    <span class="text-muted">Belum ada file</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                                aria-hidden="true"></span>
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" data-focus="false"
        aria-labelledby="historyModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white font-weight-bold" id="historyModalLabel">Detail Pembayaran Jasa</h5>
                    <div class="d-flex align-items-center">
                        @if ($permissions['tambah'])
                            <button type="button" class="btn btn-sm btn-warning mr-2" id="btnTambahDariDetail">
                                <i class="fas fa-plus"></i> Tambah Pembayaran
                            </button>
                        @endif
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="mb-1">Unit</label>
                            <input type="text" id="history_unit" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Type Rumah</label>
                            <input type="text" id="history_type" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Status</label>
                            <input type="text" id="history_status" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="mb-1">Nama Jasa</label>
                            <input type="text" id="history_nama_jasa" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="mb-1">RAB Jasa</label>
                            <input type="text" id="history_rab_jasa" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Total Bayar</label>
                            <input type="text" id="history_total_bayar" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="mb-1">Sisa Bayar</label>
                            <input type="text" id="history_sisa_bayar" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="130px">Tanggal</th>
                                    <th>Tukang</th>
                                    <th width="160px">Nominal Bayar</th>
                                    <th>Keterangan</th>
                                    <th width="90px">Bukti</th>
                                    <th width="140px">Action</th>
                                </tr>
                            </thead>
                            <tbody id="historyBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada pembayaran jasa.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const permissions = @json($permissions);
        const showActionColumn = (permissions['tambah'] == 1 || permissions['edit'] == 1 || permissions['hapus'] == 1);
        const servicesUrlTemplate = @json(route('pembayaran-jasa.services', ['setting' => '__SETTING__']));
        const summaryUrlTemplate = @json(route('pembayaran-jasa.summary', ['setting' => '__SETTING__']));
        const historyUrlTemplate = @json(route('pembayaran-jasa.history', ['setting' => '__SETTING__']));
        const serviceSummaryUrlTemplate = @json(route('pembayaran-jasa.service-summary', ['setting' => '__SETTING__', 'detail' => '__DETAIL__']));
        const serviceHistoryUrlTemplate = @json(route('pembayaran-jasa.service-history', ['setting' => '__SETTING__', 'detail' => '__DETAIL__']));
        const editUrlTemplate = @json(route('pembayaran-jasa.edit', ['pembayaran_jasa' => '__ID__']));
        const updateUrlTemplate = @json(route('pembayaran-jasa.update', ['pembayaran_jasa' => '__ID__']));
        const destroyUrlTemplate = @json(route('pembayaran-jasa.destroy', ['pembayaran_jasa' => '__ID__']));
        let currentServiceListSettingId = '';
        let currentHistorySettingId = '';
        let currentHistoryDetailId = '';
        let preserveServiceListContext = false;
        let preserveHistoryContext = false;

        function buildUrl(template, replacements = {}) {
            let url = template;
            Object.keys(replacements).forEach(function(key) {
                url = url.replace(key, replacements[key]);
            });

            return url;
        }

        function paymentPreviewHtml(fileUrl, fileType) {
            if (!fileUrl) {
                return '<span class="text-muted">Belum ada file</span>';
            }

            if (fileType === 'pdf') {
                return `<button type="button" class="btn btn-outline-secondary btn-sm btn-preview-proof" data-file="${fileUrl}" data-filetype="pdf"><i class="fas fa-file-pdf text-danger"></i> Lihat PDF</button>`;
            }

            return `<button type="button" class="btn p-0 border-0 bg-transparent btn-preview-proof" data-file="${fileUrl}" data-filetype="image"><img src="${fileUrl}" class="img-fluid rounded" style="max-height: 140px; object-fit: cover;"></button>`;
        }

        function showAjaxError(xhr, fallbackMessage = 'Terjadi kesalahan pada server.') {
            const message = xhr.responseJSON?.message || fallbackMessage;

            if (typeof audio !== 'undefined') {
                audio.play();
            }

            toastr.error(message, 'GAGAL!', {
                progressBar: true,
                timeOut: 4000,
                positionClass: 'toast-bottom-right',
            });
        }

        function resetForm() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#id_setting_proyek').val('').trigger('change');
            $('#id_master_project_detail').html('<option value=""></option>').val('').trigger('change');
            $('#id_tukang').val('').trigger('change');
            $('#tanggal_pembayaran').val('{{ date('Y-m-d') }}');
            $('#summary_lokasi, #summary_type, #summary_status, #summary_nama_jasa, #summary_rab_jasa, #summary_total_bayar, #summary_sisa_bayar').val('');
            $('#proofPreview').html('<span class="text-muted">Belum ada file</span>');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        }

        function fillFormSummary(summary = {}) {
            $('#summary_lokasi').val(summary.lokasi_perumahan || '-');
            $('#summary_type').val(summary.type_rumah || '-');
            $('#summary_status').val(summary.status || '-');
            $('#summary_nama_jasa').val(summary.nama_jasa || '-');
            $('#summary_rab_jasa').val(summary.rab_jasa || 'Rp 0');
            $('#summary_total_bayar').val(summary.total_bayar || 'Rp 0');
            $('#summary_sisa_bayar').val(summary.sisa_bayar || 'Rp 0');
        }

        function fillServiceListSummary(summary = {}) {
            $('#service_list_unit').val((summary.lokasi_perumahan || '-') + ' - ' + (summary.unit_rumah || '-'));
            $('#service_list_type').val(summary.type_rumah || '-');
            $('#service_list_status').val(summary.status || '-');
            $('#service_list_rab').val(summary.rab_jasa || 'Rp 0');
            $('#service_list_total').val(summary.total_bayar || 'Rp 0');
            $('#service_list_sisa').val(summary.sisa_bayar || 'Rp 0');
        }

        function fillHistorySummary(summary = {}) {
            $('#history_unit').val((summary.lokasi_perumahan || '-') + ' - ' + (summary.unit_rumah || '-'));
            $('#history_type').val(summary.type_rumah || '-');
            $('#history_status').val(summary.status || '-');
            $('#history_nama_jasa').val(summary.nama_jasa || 'Semua Jasa');
            $('#history_rab_jasa').val(summary.rab_jasa || 'Rp 0');
            $('#history_total_bayar').val(summary.total_bayar || 'Rp 0');
            $('#history_sisa_bayar').val(summary.sisa_bayar || 'Rp 0');
        }

        function renderServiceOptions(services, selectedId = '') {
            const select = $('#id_master_project_detail');
            select.html('<option value=""></option>');

            services.forEach(function(service) {
                const option = new Option(service.nama_jasa, String(service.id), false, String(service.id) === String(selectedId));
                $(option).attr('data-rab', service.rab);
                select.append(option);
            });

            select.trigger('change.select2');
        }

        function loadServiceOptions(settingId, selectedId = '') {
            if (!settingId) {
                renderServiceOptions([], '');
                return $.Deferred().resolve().promise();
            }

            return $.get(buildUrl(servicesUrlTemplate, {
                '__SETTING__': settingId
            }), function(response) {
                renderServiceOptions(response.services || [], selectedId);
            }).fail(function(xhr) {
                renderServiceOptions([], '');
                showAjaxError(xhr, 'Gagal memuat daftar jasa.');
            });
        }

        function loadServiceSummary(settingId, detailId) {
            if (!settingId || !detailId) {
                fillFormSummary({});
                return;
            }

            $.get(buildUrl(serviceSummaryUrlTemplate, {
                '__SETTING__': settingId,
                '__DETAIL__': detailId
            }), function(response) {
                fillFormSummary(response.data || {});
            }).fail(function(xhr) {
                fillFormSummary({});
                showAjaxError(xhr, 'Gagal memuat ringkasan jasa.');
            });
        }

        function renderServiceList(summary, services) {
            fillServiceListSummary(summary || {});

            const tbody = $('#serviceListBody');
            tbody.empty();

            if (!services.length) {
                tbody.html('<tr><td colspan="6" class="text-center text-muted">Belum ada data jasa pada RAB.</td></tr>');
                return;
            }

            services.forEach(function(service, index) {
                tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${service.nama_jasa}</td>
                        <td class="text-right">${service.rab}</td>
                        <td class="text-right">${service.realisasi}</td>
                        <td class="text-right">${service.sisa}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-primary btn-xs btn-service-detail" data-setting-id="${summary.id_setting_proyek}" data-detail-id="${service.id}">
                                Detail
                            </button>
                        </td>
                    </tr>
                `);
            });
        }

        function openServiceListModal(settingId) {
            currentServiceListSettingId = String(settingId || '');

            $.get(buildUrl(servicesUrlTemplate, {
                '__SETTING__': settingId
            }), function(response) {
                renderServiceList(response.summary || {}, response.services || []);
                $('#serviceListModal').modal('show');
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Tombol pembayaran gagal membuka daftar jasa.');
            });
        }

        function openCreateModal(settingId = '', detailId = '') {
            resetForm();
            $('#modalFormLabel').text('Tambah Pembayaran Jasa');

            if (settingId) {
                $('#id_setting_proyek').val(String(settingId)).trigger('change.select2');
                loadServiceOptions(settingId, detailId).done(function() {
                    if (detailId) {
                        $('#id_master_project_detail').val(String(detailId)).trigger('change');
                        loadServiceSummary(settingId, detailId);
                    }
                });
            }

            $('#modalForm').modal('show');
        }

        function openEditModal(id) {
            resetForm();
            $('#modalFormLabel').text('Edit Pembayaran Jasa');

            $.get(buildUrl(editUrlTemplate, {
                '__ID__': id
            }), function(response) {
                const data = response.data;
                $('#primary_id').val(data.id);
                $('#id_setting_proyek').val(String(data.id_setting_proyek)).trigger('change.select2');

                loadServiceOptions(data.id_setting_proyek, data.id_master_project_detail).done(function() {
                    $('#id_master_project_detail').val(String(data.id_master_project_detail)).trigger('change');
                });

                $('#id_tukang').val(String(data.id_tukang)).trigger('change');
                $('#tanggal_pembayaran').val(data.tanggal_pembayaran);
                $('#jumlah_bayar').val(new Intl.NumberFormat('id-ID').format(data.jumlah_bayar || 0));
                $('#keterangan').val(data.keterangan || '');
                fillFormSummary(data.summary || {});
                $('#proofPreview').html(paymentPreviewHtml(data.bukti_url, data.bukti_type));
                $('#modalForm').modal('show');
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Gagal memuat data pembayaran jasa.');
            });
        }

        function renderHistory(summary, payments) {
            fillHistorySummary(summary || {});

            const tbody = $('#historyBody');
            tbody.empty();

            if (!payments.length) {
                tbody.html('<tr><td colspan="7" class="text-center text-muted">Belum ada pembayaran jasa.</td></tr>');
                return;
            }

            payments.forEach(function(payment, index) {
                let actionHtml = '-';
                if (permissions['edit'] == 1 || permissions['hapus'] == 1) {
                    actionHtml = '<div class="d-flex justify-content-center">';
                    if (permissions['edit'] == 1) {
                        actionHtml += `<button type="button" class="btn btn-primary btn-xs mx-1 btn-edit-payment" data-id="${payment.id}">Edit</button>`;
                    }
                    if (permissions['hapus'] == 1) {
                        actionHtml += `<button type="button" class="btn btn-danger btn-xs mx-1 btn-delete-payment" data-id="${payment.id}">Hapus</button>`;
                    }
                    actionHtml += '</div>';
                }

                const buktiHtml = payment.bukti_url ?
                    `<button type="button" class="btn btn-xs btn-outline-secondary btn-preview-proof" data-file="${payment.bukti_url}" data-filetype="${payment.bukti_type}">Lihat</button>` :
                    '<span class="text-muted">-</span>';

                tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${payment.tanggal_pembayaran}</td>
                        <td>${payment.nama_tukang}</td>
                        <td class="text-right">Rp ${payment.jumlah_bayar}</td>
                        <td>${payment.keterangan}</td>
                        <td class="text-center">${buktiHtml}</td>
                        <td class="text-center">${actionHtml}</td>
                    </tr>
                `);
            });
        }

        function loadHistory(settingId, detailId = '') {
            currentHistorySettingId = String(settingId || '');
            currentHistoryDetailId = String(detailId || '');

            const url = detailId ? buildUrl(serviceHistoryUrlTemplate, {
                '__SETTING__': settingId,
                '__DETAIL__': detailId
            }) : buildUrl(historyUrlTemplate, {
                '__SETTING__': settingId
            });

            $.get(url, function(response) {
                renderHistory(response.summary || {}, response.payments || []);
                $('#btnTambahDariDetail').toggle(!!detailId);
                $('#historyModal').modal('show');
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Gagal memuat riwayat pembayaran jasa.');
            });
        }

        function refreshOpenPanels() {
            $('.data-table').DataTable().ajax.reload(null, false);

            if (currentServiceListSettingId) {
                $.get(buildUrl(servicesUrlTemplate, {
                    '__SETTING__': currentServiceListSettingId
                }), function(response) {
                    renderServiceList(response.summary || {}, response.services || []);
                }).fail(function(xhr) {
                    showAjaxError(xhr, 'Gagal memperbarui daftar jasa.');
                });
            }

            if (currentHistorySettingId) {
                loadHistory(currentHistorySettingId, currentHistoryDetailId);
            }
        }

        $(function() {
            $('.select-unit').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Unit',
                dropdownParent: $('#modalForm')
            });

            $('.select-jasa').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Jasa RAB',
                dropdownParent: $('#modalForm')
            });

            $('.select-tukang').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Pilih Tukang',
                dropdownParent: $('#modalForm')
            });

            $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('pembayaran-jasa.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'unit_rumah',
                        name: 'unit_rumah',
                        searchable: true
                    },
                    {
                        data: 'type_rumah',
                        name: 'type_rumah',
                        searchable: true
                    },
                    {
                        data: 'rab_jasa',
                        name: 'rab_jasa',
                        searchable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'total_bayar',
                        name: 'total_bayar',
                        searchable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'sisa_bayar',
                        name: 'sisa_bayar',
                        searchable: false,
                        className: 'text-right'
                    },
                    {
                        data: 'status_pembayaran',
                        name: 'status_pembayaran',
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        visible: showActionColumn,
                        className: 'text-center'
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

        $('#btnTambahPembayaran').on('click', function() {
            openCreateModal();
        });

        $('#btnTambahDariDetail').on('click', function() {
            if (!currentHistorySettingId || !currentHistoryDetailId) {
                return;
            }

            preserveHistoryContext = true;
            $('#historyModal').modal('hide');
            openCreateModal(currentHistorySettingId, currentHistoryDetailId);
        });

        $('#id_setting_proyek').on('change', function() {
            const settingId = $(this).val();
            renderServiceOptions([], '');
            fillFormSummary({});

            if (!settingId) {
                return;
            }

            loadServiceOptions(settingId);
        });

        $('#id_master_project_detail').on('change', function() {
            loadServiceSummary($('#id_setting_proyek').val(), $(this).val());
        });

        $(document).on('click', '.btn-open-payment', function() {
            openServiceListModal($(this).data('setting-id'));
        });

        $(document).on('click', '.btn-service-detail', function() {
            preserveServiceListContext = true;
            $('#serviceListModal').modal('hide');
            loadHistory($(this).data('setting-id'), $(this).data('detail-id'));
        });

        $(document).on('click', '.btn-open-history', function() {
            loadHistory($(this).data('setting-id'));
        });

        $(document).on('click', '.btn-edit-payment', function() {
            preserveHistoryContext = true;
            $('#historyModal').modal('hide');
            openEditModal($(this).data('id'));
        });

        $(document).on('input', '.format-number', function() {
            let value = $(this).val().replace(/[^\d]/g, '');
            $(this).val(value ? new Intl.NumberFormat('id-ID').format(value) : '');
        });

        $(document).on('keypress', '.format-number', function(e) {
            if (e.which < 48 || e.which > 57) {
                e.preventDefault();
            }
        });

        $(document).on('change', '#bukti_bayar', function() {
            const file = this.files[0];
            if (!file) {
                $('#proofPreview').html('<span class="text-muted">Belum ada file</span>');
                return;
            }

            if (file.type === 'application/pdf') {
                $('#proofPreview').html(paymentPreviewHtml(URL.createObjectURL(file), 'pdf'));
                return;
            }

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#proofPreview').html(paymentPreviewHtml(e.target.result, 'image'));
                };
                reader.readAsDataURL(file);
                return;
            }

            $('#proofPreview').html('<span class="text-muted">File tidak didukung</span>');
        });

        $(document).on('click', '.btn-preview-proof', function() {
            $('#modalLampiran .modal-body').html('');
            const file = $(this).data('file');
            const fileType = $(this).data('filetype');

            if (fileType === 'pdf') {
                $('#modalLampiran .modal-dialog').removeClass('modal-sm modal-dialog-centered').addClass('modal-lg');
                $('#modalLampiran .modal-body').html(`<iframe src="${file}" class="w-100" style="height:80vh;"></iframe>`);
            } else {
                $('#modalLampiran .modal-dialog').removeClass('modal-lg').addClass('modal-dialog-centered modal-dialog-scrollable');
                $('#modalLampiran .modal-body').html(`<div class="d-flex justify-content-center"><img src="${file}" class="img-fluid" style="max-height:90vh; object-fit:contain;"></div>`);
            }

            $('#modalLampiran').modal('show');
        });

        $('#modalForm').on('hidden.bs.modal', function() {
            resetForm();
            const submitBtn = $('#submitBtn');
            submitBtn.find('.spinner-border').addClass('d-none');
            submitBtn.find('.button-text').text('Simpan');
            submitBtn.prop('disabled', false);
        });

        $('#serviceListModal').on('hidden.bs.modal', function() {
            if (preserveServiceListContext) {
                preserveServiceListContext = false;
                return;
            }

            currentServiceListSettingId = '';
        });

        $('#historyModal').on('hidden.bs.modal', function() {
            if (preserveHistoryContext) {
                preserveHistoryContext = false;
                return;
            }

            currentHistorySettingId = '';
            currentHistoryDetailId = '';
            $('#btnTambahDariDetail').hide();
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            const spinner = submitBtn.find('.spinner-border');
            const btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            const id = $('#primary_id').val();
            const url = id ? buildUrl(updateUrlTemplate, {
                '__ID__': id
            }) : "{{ route('pembayaran-jasa.store') }}";
            const formData = new FormData(this);
            formData.append('_method', id ? 'PUT' : 'POST');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#modalForm').modal('hide');
                    audio.play();
                    toastr.success(response.message || 'Data berhasil disimpan.', 'BERHASIL', {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: 'toast-bottom-right',
                    });
                    refreshOpenPanels();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        audio.play();
                        toastr.error('Ada inputan yang salah!', 'GAGAL!', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right',
                        });

                        $.each(xhr.responseJSON.errors || {}, function(key, val) {
                            const input = $('#' + key);
                            input.addClass('is-invalid');
                            input.parent().find('.invalid-feedback').remove();
                            input.parent().append('<span class="invalid-feedback d-block" role="alert"><strong>' + val[0] + '</strong></span>');
                        });
                    } else {
                        audio.play();
                        toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan data.', 'GAGAL!', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right',
                        });
                    }

                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-delete-payment', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Pembayaran jasa ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: buildUrl(destroyUrlTemplate, {
                        '__ID__': id
                    }),
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        _method: 'DELETE'
                    },
                    success: function() {
                        audio.play();
                        toastr.success('Pembayaran jasa berhasil dihapus.', 'BERHASIL', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right',
                        });
                        refreshOpenPanels();
                    },
                    error: function(xhr) {
                        audio.play();
                        toastr.error(xhr.responseJSON?.message || 'Gagal menghapus pembayaran jasa.', 'GAGAL!', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right',
                        });
                    }
                });
            });
        });
    </script>
@endpush
