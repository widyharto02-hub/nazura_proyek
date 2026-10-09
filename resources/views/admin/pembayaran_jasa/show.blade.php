@extends('admin.layout_admin')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Detail Pembayaran Jasa</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right">
                            <a href="{{ route('pembayaran-jasa.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0">Ringkasan Unit</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label>Lokasi Perumahan</label>
                                    <input type="text" class="form-control" value="{{ $summary['lokasi_perumahan'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label>Unit Rumah</label>
                                    <input type="text" class="form-control" value="{{ $summary['unit_rumah'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label>Type Rumah</label>
                                    <input type="text" class="form-control" value="{{ $summary['type_rumah'] }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>RAB Jasa</label>
                                    <input type="text" class="form-control" value="{{ $summary['rab_jasa'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Total Bayar</label>
                                    <input type="text" class="form-control" value="{{ $summary['total_bayar'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Sisa Bayar</label>
                                    <input type="text" class="form-control" value="{{ $summary['sisa_bayar'] }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>Persentase Bayar</label>
                                    <input type="text" class="form-control" value="{{ $summary['persentase_bayar'] }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title font-weight-bold mb-0">Daftar Jasa RAB</h3>
                            <button type="button" class="btn btn-success btn-sm" id="btnAddCustomService">
                                <i class="fas fa-plus"></i> Tambah Jasa
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Jenis Jasa</th>
                                        <th>RAB</th>
                                        <th>Realisasi</th>
                                        <th>Sisa</th>
                                        <th width="20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="serviceListBody">
                                    @forelse ($services as $index => $service)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                {{ $service['nama_jasa'] }}
                                                @if (($service['tipe'] ?? 'default') === 'custom')
                                                    <span class="badge badge-info ml-1">Custom</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="d-flex justify-content-between harga-format w-100">
                                                    <span>Rp.</span>
                                                    <span>{{ number_format($service['rab_raw'], 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="d-flex justify-content-between harga-format w-100">
                                                    <span>Rp.</span>
                                                    <span>{{ number_format($service['realisasi_raw'], 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="d-flex justify-content-between harga-format w-100">
                                                    <span>Rp.</span>
                                                    <span>{{ number_format($service['sisa_raw'], 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center">
                                                    @if (($service['tipe'] ?? 'default') === 'default')
                                                        <button type="button" class="btn btn-success btn-xs mx-1 btn-service-payment"
                                                            data-setting-id="{{ $setting->id }}" data-detail-id="{{ $service['id'] }}"
                                                            data-tipe="default">
                                                            Pembayaran
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-xs mx-1 btn-service-detail"
                                                            data-setting-id="{{ $setting->id }}" data-detail-id="{{ $service['id'] }}"
                                                            data-tipe="default">
                                                            Detail
                                                        </button>
                                                    @else
                                                        <button type="button" class="btn btn-success btn-xs mx-1 btn-service-payment"
                                                            data-setting-id="{{ $setting->id }}" data-detail-id="{{ $service['id'] }}"
                                                            data-tipe="custom">
                                                            Pembayaran
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-xs mx-1 btn-edit-custom"
                                                            data-id="{{ $service['id'] }}" data-name="{{ $service['nama_jasa'] }}"
                                                            data-satuan="{{ $service['satuan'] ?? '' }}" data-harga="{{ $service['rab_raw'] }}"
                                                            data-keterangan="{{ $service['keterangan'] ?? '' }}">
                                                            Edit
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-xs mx-1 btn-delete-custom"
                                                            data-id="{{ $service['id'] }}" data-name="{{ $service['nama_jasa'] }}">
                                                            Hapus
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada data jasa pada RAB.</td>
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

    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" data-focus="false"
        aria-labelledby="paymentModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white font-weight-bold" id="paymentModalLabel">Pembayaran Jasa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formPayment" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="id_setting_proyek" name="id_setting_proyek" value="{{ $setting->id }}">
                        <input type="hidden" id="id_master_project_detail" name="id_master_project_detail">
                        <input type="hidden" id="id_jasa_custom" name="id_jasa_custom">

                        <div class="row">
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title font-weight-bold mb-0">Ringkasan Jasa</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="mb-1">Unit</label>
                                            <input type="text" id="payment_unit" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1">Type Rumah</label>
                                            <input type="text" id="payment_type" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1">Status</label>
                                            <input type="text" id="payment_status" class="form-control" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="mb-1">Nama Jasa</label>
                                            <input type="text" id="payment_nama_jasa" class="form-control" readonly>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="small text-muted mb-1">RAB</div>
                                                <div id="payment_rab_jasa" class="font-weight-bold text-dark">Rp 0</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted mb-1">Sudah Dibayar</div>
                                                <div id="payment_total_bayar" class="font-weight-bold text-primary">Rp 0</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted mb-1">Sisa</div>
                                                <div id="payment_sisa_bayar" class="font-weight-bold text-danger">Rp 0</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="small text-muted mb-1">Persentase</div>
                                                <div id="payment_persentase_bayar" class="font-weight-bold text-success">0%</div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="font-weight-bold">Rincian Pembayaran</div>
                                            <small class="text-muted">Riwayat jasa ini</small>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="50px" class="text-center">No</th>
                                                        <th width="120px">Tgl</th>
                                                        <th>Keterangan</th>
                                                        <th width="140px" class="text-right">Nominal</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="paymentRecapBody">
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">Belum ada pembayaran.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h3 class="card-title font-weight-bold mb-0">Input Pembayaran</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="id_tukang">Nama Tukang</label>
                                            <select name="id_tukang" id="id_tukang" class="form-control">
                                                <option value="">Pilih tukang</option>
                                                @foreach ($tukangList as $tukang)
                                                    <option value="{{ $tukang->id }}">{{ $tukang->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="tanggal_pembayaran">Tanggal Bayar</label>
                                            <input type="date" id="tanggal_pembayaran" name="tanggal_pembayaran" class="form-control"
                                                value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="form-group">
                                            <label for="jumlah_bayar">Nominal Bayar</label>
                                            <input type="text" id="jumlah_bayar" name="jumlah_bayar" class="form-control format-number">
                                        </div>
                                        <div class="form-group">
                                            <label for="bukti_bayar">Upload Bukti Pembayaran</label>
                                            <input type="file" id="bukti_bayar" name="bukti_bayar" class="form-control"
                                                accept=".jpg,.jpeg,.png,.pdf">
                                        </div>
                                        <div class="form-group">
                                            <label for="keterangan">Keterangan</label>
                                            <textarea id="keterangan" name="keterangan" class="form-control" rows="3"
                                                placeholder="Tambahkan catatan pembayaran jika perlu"></textarea>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="mb-1">Preview Bukti</label>
                                            <div id="proofPreview"
                                                class="border rounded d-flex align-items-center justify-content-center bg-light"
                                                style="min-height: 180px;">
                                                <span class="text-muted">Belum ada file</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" form="formPayment" class="btn btn-success" id="submitBtn">
                        <span class="spinner-border spinner-border-sm mr-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="button-text">Simpan Pembayaran</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="historyModal" tabindex="-1" role="dialog" data-focus="false"
        aria-labelledby="historyModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white font-weight-bold" id="historyModalLabel">Detail Pembayaran Jasa</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card border-0 bg-light mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="small text-muted mb-1">Unit</div>
                                    <input type="text" id="history_unit" class="form-control bg-white" readonly>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted mb-1">Type Rumah</div>
                                    <input type="text" id="history_type" class="form-control bg-white" readonly>
                                </div>
                                <div class="col-md-4">
                                    <div class="small text-muted mb-1">Status</div>
                                    <input type="text" id="history_status" class="form-control bg-white" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="small text-muted mb-1">Nama Jasa</div>
                                    <input type="text" id="history_nama_jasa" class="form-control bg-white" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <div class="small text-muted mb-1">RAB Jasa</div>
                                    <input type="text" id="history_rab_jasa" class="form-control bg-white font-weight-bold" readonly>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted mb-1">Total Bayar</div>
                                    <input type="text" id="history_total_bayar" class="form-control bg-white font-weight-bold text-primary" readonly>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted mb-1">Sisa Bayar</div>
                                    <input type="text" id="history_sisa_bayar" class="form-control bg-white font-weight-bold text-danger" readonly>
                                </div>
                                <div class="col-md-3">
                                    <div class="small text-muted mb-1">Persentase Bayar</div>
                                    <input type="text" id="history_persentase_bayar" class="form-control bg-white font-weight-bold text-success" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="font-weight-bold mb-0">Riwayat Pembayaran</h6>
                        <small class="text-muted">Klik preview atau buka file untuk lihat bukti bayar</small>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th width="50px">No</th>
                                    <th width="130px">Tanggal</th>
                                    <th>Tukang</th>
                                    <th width="160px">Nominal Bayar</th>
                                    <th>Keterangan</th>
                                    <th width="90px">Bukti</th>
                                    <th width="90px">Nota</th>
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

    <div class="modal fade" id="customServiceModal" tabindex="-1" role="dialog"
        aria-labelledby="customServiceModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white font-weight-bold" id="customServiceModalLabel"></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formCustomService">
                    @csrf
                    <input type="hidden" id="primary_id" name="primary_id">
                    <input type="hidden" name="id_setting_proyek" value="{{ $setting->id }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama_jasa">Nama Jasa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_jasa" id="nama_jasa" class="form-control" placeholder="Masukkan nama jasa">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="satuan">Satuan</label>
                                    <input type="text" name="satuan" id="satuan" class="form-control" placeholder="Contoh: m², pcs, unit">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="harga_satuan">Harga <span class="text-danger">*</span></label>
                                    <input type="text" name="harga_satuan" id="harga_satuan" class="form-control format-number" placeholder="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keterangan_custom">Keterangan</label>
                            <textarea name="keterangan" id="keterangan_custom" class="form-control" rows="2" placeholder="Keterangan (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtnCustom">
                            <span class="spinner-border spinner-border-sm mr-2 d-none" role="status" aria-hidden="true"></span>
                            <span class="button-text">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const currentSettingId = @json((string) $setting->id);
        const storeUrl = @json(route('pembayaran-jasa.store'));
        const historyUrlTemplate = @json(route('pembayaran-jasa.history', ['setting' => '__SETTING__']));
        const serviceHistoryUrlTemplate = @json(route('pembayaran-jasa.service-history', ['setting' => '__SETTING__', 'detail' => '__DETAIL__']));
        const notaUrlTemplate = @json(route('pembayaran-jasa.nota', ['pembayaran_jasa' => '__ID__']));
        const storeCustomServiceUrl = @json(route('pembayaran-jasa.custom-service.store', ['setting' => '__SETTING__']));
        const editCustomServiceUrl = @json(route('pembayaran-jasa.custom-service.update', ['custom' => '__ID__']));
        const destroyCustomServiceUrl = @json(route('pembayaran-jasa.custom-service.destroy', ['custom' => '__ID__']));
        const servicesUrl = @json(route('pembayaran-jasa.services', ['setting' => '__SETTING__']));
        let currentDetailId = '';
        let currentTipe = 'default';

        function buildUrl(template, replacements = {}) {
            let url = template;
            Object.keys(replacements).forEach(function(key) {
                url = url.replace(key, replacements[key]);
            });

            return url;
        }

        function reloadServiceTable() {
            const url = buildUrl(servicesUrl, { '__SETTING__': currentSettingId });

            $.get(url, function(response) {
                const services = response.services || [];
                const tbody = $('#serviceListBody');
                tbody.empty();

                if (!services.length) {
                    tbody.html('<tr><td colspan="6" class="text-center text-muted">Belum ada data jasa pada RAB.</td></tr>');
                    return;
                }

                services.forEach(function(s, i) {
                    const isCustom = s.tipe === 'custom';
                    const badge = isCustom ? '<span class="badge badge-info ml-1">Custom</span>' : '';
                    let action = '';
                    if (!isCustom) {
                        action = '<button type="button" class="btn btn-success btn-xs mx-1 btn-service-payment" data-setting-id="'+currentSettingId+'" data-detail-id="'+s.id+'" data-tipe="default">Pembayaran</button><button type="button" class="btn btn-primary btn-xs mx-1 btn-service-detail" data-setting-id="'+currentSettingId+'" data-detail-id="'+s.id+'" data-tipe="default">Detail</button>';
                    } else {
                        action = '<button type="button" class="btn btn-success btn-xs mx-1 btn-service-payment" data-setting-id="'+currentSettingId+'" data-detail-id="'+s.id+'" data-tipe="custom">Pembayaran</button><button type="button" class="btn btn-primary btn-xs mx-1 btn-edit-custom" data-id="'+s.id+'" data-name="'+s.nama_jasa+'" data-satuan="'+s.satuan+'" data-harga="'+s.rab_raw+'" data-keterangan="'+s.keterangan+'">Edit</button><button type="button" class="btn btn-danger btn-xs mx-1 btn-delete-custom" data-id="'+s.id+'" data-name="'+s.nama_jasa+'">Hapus</button>';
                    }
                    tbody.append('<tr><td class="text-center">'+(i+1)+'</td><td>'+s.nama_jasa+' '+badge+'</td><td class="text-right"><div class="d-flex justify-content-between harga-format w-100"><span>Rp.</span><span>'+new Intl.NumberFormat('id-ID').format(s.rab_raw)+'</span></div></td><td class="text-right"><div class="d-flex justify-content-between harga-format w-100"><span>Rp.</span><span>'+new Intl.NumberFormat('id-ID').format(s.realisasi_raw)+'</span></div></td><td class="text-right"><div class="d-flex justify-content-between harga-format w-100"><span>Rp.</span><span>'+new Intl.NumberFormat('id-ID').format(s.sisa_raw)+'</span></div></td><td class="text-center"><div class="d-flex justify-content-center">'+action+'</div></td></tr>');
                });
            });
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

        function paymentPreviewHtml(fileUrl, fileType) {
            if (!fileUrl) {
                return '<span class="text-muted">Belum ada file</span>';
            }

            if (fileType === 'pdf') {
                return `<button type="button" class="btn btn-outline-secondary btn-sm btn-preview-proof" data-file="${fileUrl}" data-filetype="pdf"><i class="fas fa-file-pdf text-danger"></i> Lihat PDF</button>`;
            }

            return `<button type="button" class="btn p-0 border-0 bg-transparent btn-preview-proof" data-file="${fileUrl}" data-filetype="image"><img src="${fileUrl}" class="img-fluid rounded" style="max-height: 140px; object-fit: cover;"></button>`;
        }

        function resetPaymentForm() {
            $('#formPayment')[0].reset();
            $('#id_setting_proyek').val(currentSettingId);
            $('#id_master_project_detail').val(currentTipe === 'custom' ? '' : currentDetailId);
            $('#id_jasa_custom').val(currentTipe === 'custom' ? currentDetailId : '');
            $('#tanggal_pembayaran').val('{{ date('Y-m-d') }}');
            $('#proofPreview').html('<span class="text-muted">Belum ada file</span>');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#submitBtn').prop('disabled', false);
            $('#submitBtn').find('.spinner-border').addClass('d-none');
            $('#submitBtn').find('.button-text').text('Simpan Pembayaran');
        }

        function fillPaymentSummary(summary = {}) {
            $('#payment_unit').val((summary.lokasi_perumahan || '-') + ' - ' + (summary.unit_rumah || '-'));
            $('#payment_type').val(summary.type_rumah || '-');
            $('#payment_status').val(summary.status || '-');
            $('#payment_nama_jasa').val(summary.nama_jasa || '-');
            $('#payment_rab_jasa').text(summary.rab_jasa || 'Rp 0');
            $('#payment_total_bayar').text(summary.total_bayar || 'Rp 0');
            $('#payment_sisa_bayar').text(summary.sisa_bayar || 'Rp 0');
            $('#payment_persentase_bayar').text(summary.persentase_bayar || '0%');
        }

        function renderPaymentRecap(payments = []) {
            const tbody = $('#paymentRecapBody');
            tbody.empty();

            if (!payments.length) {
                tbody.html('<tr><td colspan="4" class="text-center text-muted">Belum ada pembayaran.</td></tr>');
                return;
            }

            payments.forEach(function(payment, index) {
                tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${payment.tanggal_pembayaran}</td>
                        <td>${payment.keterangan}</td>
                        <td class="text-right">Rp ${payment.jumlah_bayar}</td>
                    </tr>
                `);
            });
        }

        function prefillTukangFromFirstPayment(payments = []) {
            if (!payments.length) {
                $('#id_tukang').val('');
                return;
            }

            // API history is sorted newest first, so the earliest payment is the last item.
            const firstPayment = payments[payments.length - 1];
            $('#id_tukang').val(firstPayment.id_tukang ? String(firstPayment.id_tukang) : '');
        }

        function fillHistorySummary(summary = {}) {
            $('#history_unit').val((summary.lokasi_perumahan || '-') + ' - ' + (summary.unit_rumah || '-'));
            $('#history_type').val(summary.type_rumah || '-');
            $('#history_status').val(summary.status || '-');
            $('#history_nama_jasa').val(summary.nama_jasa || 'Semua Jasa');
            $('#history_rab_jasa').val(summary.rab_jasa || 'Rp 0');
            $('#history_total_bayar').val(summary.total_bayar || 'Rp 0');
            $('#history_sisa_bayar').val(summary.sisa_bayar || 'Rp 0');
            $('#history_persentase_bayar').val(summary.persentase_bayar || '0%');
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
                const buktiHtml = payment.bukti_url ?
                    `<div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-xs btn-outline-secondary mx-1 btn-preview-proof" data-file="${payment.bukti_url}" data-filetype="${payment.bukti_type}">Preview</button>
                        <a href="${payment.bukti_url}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary mx-1">Buka</a>
                    </div>` :
                    '<span class="text-muted">-</span>';

                const notaLink = notaUrlTemplate.replace('__ID__', payment.id);

                tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${payment.tanggal_pembayaran}</td>
                        <td>${payment.nama_tukang}</td>
                        <td class="text-right">Rp ${payment.jumlah_bayar}</td>
                        <td>${payment.keterangan}</td>
                        <td class="text-center">${buktiHtml}</td>
                        <td class="text-center">
                            <a href="${notaLink}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-info" title="Cetak Nota"><i class="fas fa-print"></i></a>
                        </td>
                    </tr>
                `);
            });
        }

        function loadHistory(detailId = '') {
            const url = detailId ? buildUrl(serviceHistoryUrlTemplate, {
                '__SETTING__': currentSettingId,
                '__DETAIL__': detailId
            }) : buildUrl(historyUrlTemplate, {
                '__SETTING__': currentSettingId
            });

            $.get(url, function(response) {
                resetPaymentForm();
                renderHistory(response.summary || {}, response.payments || []);
                $('#historyModal').modal('show');
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Gagal memuat riwayat pembayaran jasa.');
            });
        }

        function openPaymentModal(detailId = '', tipe = 'default') {
            currentDetailId = String(detailId || '');
            currentTipe = tipe;
            $('#id_master_project_detail').val(tipe === 'custom' ? '' : currentDetailId);
            $('#id_jasa_custom').val(tipe === 'custom' ? currentDetailId : '');

            const url = buildUrl(serviceHistoryUrlTemplate, {
                '__SETTING__': currentSettingId,
                '__DETAIL__': detailId
            });

            $.get(url, function(response) {
                resetPaymentForm();
                fillPaymentSummary(response.summary || {});
                renderPaymentRecap(response.payments || []);
                prefillTukangFromFirstPayment(response.payments || []);
                $('#paymentModal').modal('show');
            }).fail(function(xhr) {
                showAjaxError(xhr, 'Gagal memuat data pembayaran jasa.');
            });
        }

        $(document).on('click', '.btn-service-payment', function() {
            openPaymentModal($(this).data('detail-id'), $(this).data('tipe'));
        });

        $(document).on('click', '.btn-service-detail', function() {
            loadHistory($(this).data('detail-id'));
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

        $('#paymentModal').on('hidden.bs.modal', function() {
            currentDetailId = '';
            resetPaymentForm();
        });

        $('#formPayment').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $('#submitBtn');
            const spinner = submitBtn.find('.spinner-border');
            const btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            const formData = new FormData(this);

            $.ajax({
                url: storeUrl,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#paymentModal').modal('hide');
                    audio.play();
                    toastr.success(response.message || 'Pembayaran berhasil disimpan.', 'BERHASIL', {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: 'toast-bottom-right',
                    });
                    reloadServiceTable();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        audio.play();
                        toastr.error('Ada inputan pembayaran yang salah!', 'GAGAL!', {
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
                        showAjaxError(xhr, 'Gagal menyimpan pembayaran jasa.');
                    }

                    spinner.addClass('d-none');
                    btnText.text('Simpan Pembayaran');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-preview-proof', function() {
            $('#modalLampiran .modal-body').html('');
            const file = $(this).data('file');
            const fileType = $(this).data('filetype');
            $('#historyModal').modal('hide');

            if (fileType === 'pdf') {
                $('#modalLampiran .modal-dialog').removeClass('modal-sm modal-dialog-centered').addClass('modal-lg');
                $('#modalLampiran .modal-body').html(`<iframe src="${file}" class="w-100" style="height:80vh;"></iframe>`);
            } else {
                $('#modalLampiran .modal-dialog').removeClass('modal-lg').addClass('modal-dialog-centered modal-dialog-scrollable');
                $('#modalLampiran .modal-body').html(`<div class="d-flex justify-content-center"><img src="${file}" class="img-fluid" style="max-height:90vh; object-fit:contain;"></div>`);
            }

            $('#modalLampiran').modal('show');
        });

        $(document).on('click', '.btn-delete-custom', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'Hapus Jasa Custom?',
                text: 'Anda yakin ingin menghapus "' + name + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus!</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => {
                    return new Promise((resolve) => {
                        const confirmBtn = Swal.getConfirmButton();
                        const btnText = confirmBtn.querySelector('.swal-btn-text');

                        btnText.innerHTML =
                            `<span class="spinner-border spinner-border-sm mx-2" role="status" aria-hidden="true"></span> Menghapus...`;
                        confirmBtn.disabled = true;

                        const url = buildUrl(destroyCustomServiceUrl, { '__ID__': id });

                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(response) {
                                audio.play();
                                toastr.success(response.message || 'Jasa custom berhasil dihapus.', 'BERHASIL', {
                                    progressBar: true,
                                    timeOut: 3500,
                                    positionClass: 'toast-bottom-right',
                                });
                                reloadServiceTable();
                                Swal.close();
                            },
                            error: function(xhr) {
                                audio.play();
                                showAjaxError(xhr, 'Gagal menghapus jasa custom.');
                                btnText.innerHTML = 'Ya, Hapus!';
                                confirmBtn.disabled = false;
                            }
                        });
                    });
                }
            });
        });

        $('#btnAddCustomService').on('click', function() {
            $('#customServiceModalLabel').text('Tambah Jasa Custom');
            $('#formCustomService')[0].reset();
            $('#primary_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#submitBtnCustom').prop('disabled', false);
            $('#submitBtnCustom').find('.spinner-border').addClass('d-none');
            $('#submitBtnCustom').find('.button-text').text('Simpan');
            $('#customServiceModal').modal('show');
        });

        $(document).on('click', '.btn-edit-custom', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const satuan = $(this).data('satuan');
            const harga = $(this).data('harga');
            const keterangan = $(this).data('keterangan');

            $('#customServiceModalLabel').text('Edit Jasa Custom');
            $('#primary_id').val(id);
            $('#nama_jasa').val(name);
            $('#satuan').val(satuan);
            $('#harga_satuan').val(new Intl.NumberFormat('id-ID').format(harga));
            $('#keterangan_custom').val(keterangan === '-' ? '' : keterangan);
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#submitBtnCustom').prop('disabled', false);
            $('#submitBtnCustom').find('.spinner-border').addClass('d-none');
            $('#submitBtnCustom').find('.button-text').text('Simpan');
            $('#customServiceModal').modal('show');
        });

        $('#formCustomService').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $('#submitBtnCustom');
            let spinner = submitBtn.find('.spinner-border');
            let btnText = submitBtn.find('.button-text');

            spinner.removeClass('d-none');
            btnText.text('Menyimpan...');
            submitBtn.prop('disabled', true);

            let id = $('#primary_id').val();
            let url = id ? buildUrl(editCustomServiceUrl, { '__ID__': id }) : buildUrl(storeCustomServiceUrl, { '__SETTING__': currentSettingId });
            let method = id ? 'PUT' : 'POST';

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            let formData = new FormData(this);
            formData.append('_method', method);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    $('#customServiceModal').modal('hide');
                    audio.play();
                    let msg = id ? 'Jasa custom berhasil diperbarui.' : 'Jasa custom berhasil ditambahkan.';
                    toastr.success(response.message || msg, 'BERHASIL', {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: 'toast-bottom-right',
                    });
                    reloadServiceTable();
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
                        showAjaxError(xhr, 'Gagal menyimpan jasa custom.');
                    }

                    spinner.addClass('d-none');
                    btnText.text('Simpan');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        $('#customServiceModal').on('hidden.bs.modal', function() {
            $('#formCustomService')[0].reset();
            $('#primary_id').val('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
            $('#submitBtn').prop('disabled', false);
            $('#submitBtn').find('.spinner-border').addClass('d-none');
            $('#submitBtn').find('.button-text').text('Simpan');
        });

    </script>
@endpush
