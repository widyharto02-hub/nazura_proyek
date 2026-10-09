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
                                    <h3 class="font-weight-bold text-xl">Data Pembayaran Tukang</h3>
                                    <div class="d-flex align-items-center">
                                        @if (isset($permissions['tambah']) && $permissions['tambah'] == 1)
                                            <button type="button" class="btn btn-sm btn-primary" id="addButton">
                                                <i class="fas fa-plus"></i> Tambah Pembayaran
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-striped data-table">
                                    <thead>
                                        <tr>
                                            <th width="50px">No</th>
                                            <th width="100px">Tanggal</th>
                                            <th>Nama Tukang</th>
                                            <th>Nama Pekerjaan</th>
                                            <th>Keterangan</th>
                                            <th width="140px">Nominal</th>
                                            <th width="90px">Bukti Bayar</th>
                                            <th width="130px">Action</th>
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

        <div class="modal fade" id="modalForm" tabindex="-1" role="dialog" data-focus="false"
            aria-labelledby="modalFormLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white font-weight-bold" id="modalFormLabel"></h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="formData" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="primary_id" name="primary_id">
                        <div class="modal-body">
                            <div class="form-group row">
                                <label for="tanggal" class="col-sm-4 col-form-label">Tanggal</label>
                                <div class="col-sm-8">
                                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                                        value="{{ $today }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="id_tukang" class="col-sm-4 col-form-label">Nama Tukang</label>
                                <div class="col-sm-8">
                                    <select name="id_tukang" id="id_tukang" class="form-control select-tukang"
                                        style="width: 100%;">
                                        <option value="">Pilih tukang</option>
                                        @foreach ($tukangList as $tukang)
                                            <option value="{{ $tukang->id }}">{{ $tukang->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nama_pekerjaan" class="col-sm-4 col-form-label">Nama Pekerjaan</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nama_pekerjaan" id="nama_pekerjaan"
                                        class="form-control" maxlength="255">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                                <div class="col-sm-8">
                                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" maxlength="2000"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="nominal" class="col-sm-4 col-form-label">Nominal</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rp</span>
                                        </div>
                                        <input type="text" name="nominal" id="nominal" class="form-control"
                                            inputmode="numeric" autocomplete="off">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <label for="bukti_bayar" class="col-sm-4 col-form-label">Upload Bukti Bayar</label>
                                <div class="col-sm-8">
                                    <input type="file" name="bukti_bayar" id="bukti_bayar" class="form-control"
                                        accept=".jpg,.jpeg,.png,.pdf">
                                    <small class="form-text text-muted">Opsional. Format JPG, JPEG, PNG, atau PDF, maksimal 2MB.</small>
                                    <div id="existingProof" class="mt-2 d-none">
                                        <span class="text-muted">Bukti saat ini:</span>
                                        <a href="#" target="_blank" rel="noopener" id="existingProofLink"></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary ms-1" id="submitBtn">
                                <span class="spinner-border spinner-border-sm mx-1 d-none" role="status"
                                    aria-hidden="true"></span>
                                <span class="button-text">Simpan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const permissions = @json($permissions);
        const defaultDate = @json($today);
        const showActionColumn = (permissions.edit == 1 || permissions.hapus == 1);

        function resetForm() {
            $('#formData')[0].reset();
            $('#primary_id').val('');
            $('#tanggal').val(defaultDate);
            $('#id_tukang').val('').trigger('change');
            $('#existingProof').addClass('d-none');
            $('#existingProofLink').attr('href', '#').text('');
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            const submitBtn = $('#submitBtn');
            submitBtn.find('.spinner-border').addClass('d-none');
            submitBtn.find('.button-text').text('Simpan');
            submitBtn.prop('disabled', false);
        }

        function setSubmitting(isSubmitting) {
            const submitBtn = $('#submitBtn');
            submitBtn.find('.spinner-border').toggleClass('d-none', !isSubmitting);
            submitBtn.find('.button-text').text(isSubmitting ? 'Menyimpan...' : 'Simpan');
            submitBtn.prop('disabled', isSubmitting);
        }

        function showValidationErrors(errors) {
            $.each(errors, function(key, messages) {
                const input = $('#' + key);
                input.addClass('is-invalid');

                const container = input.closest('.input-group').length ? input.closest('.input-group').parent() : input.parent();
                container.find('.invalid-feedback').remove();
                container.append('<span class="invalid-feedback d-block" role="alert"><strong>' +
                    $('<div>').text(messages[0]).html() + '</strong></span>');
            });
        }

        $(function() {
            $('.select-tukang').select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih tukang',
                allowClear: true,
                dropdownParent: $('#modalForm')
            });

            $('.data-table').DataTable({
                processing: false,
                serverSide: true,
                ordering: false,
                responsive: true,
                ajax: "{{ route('pembayaran-tukang.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal'
                    },
                    {
                        data: 'nama_tukang',
                        name: 'tukang.nama'
                    },
                    {
                        data: 'nama_pekerjaan',
                        name: 'nama_pekerjaan'
                    },
                    {
                        data: 'keterangan',
                        name: 'keterangan'
                    },
                    {
                        data: 'nominal',
                        name: 'nominal',
                        className: 'text-right'
                    },
                    {
                        data: 'bukti_bayar_view',
                        name: 'bukti_bayar',
                        orderable: false,
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

        $('#addButton').on('click', function() {
            resetForm();
            $('#modalFormLabel').text('Tambah Pembayaran Tukang');
            $('#modalForm').modal('show');
        });

        $(document).on('click', '.edit-button', function() {
            const url = $(this).data('url');
            resetForm();

            $.get(url, function(response) {
                if (response.status !== 'success') {
                    return;
                }

                const data = response.data;
                $('#modalFormLabel').text('Edit Pembayaran Tukang');
                $('#primary_id').val(data.id);
                $('#tanggal').val(data.tanggal);
                $('#id_tukang').val(String(data.id_tukang)).trigger('change');
                $('#nama_pekerjaan').val(data.nama_pekerjaan);
                $('#keterangan').val(data.keterangan);
                $('#nominal').val(data.nominal);

                if (data.bukti_bayar_url) {
                    $('#existingProofLink').attr('href', data.bukti_bayar_url)
                        .text(data.bukti_bayar_name || 'Lihat bukti bayar');
                    $('#existingProof').removeClass('d-none');
                }

                $('#modalForm').modal('show');
            }).fail(function() {
                toastr.error('Gagal mengambil data pembayaran.', 'GAGAL!', {
                    positionClass: 'toast-bottom-right'
                });
            });
        });

        $('#modalForm').on('hidden.bs.modal', resetForm);

        $('#nominal').on('input', function() {
            const digits = this.value.replace(/\D/g, '');
            this.value = digits ? new Intl.NumberFormat('id-ID').format(digits) : '';
        });

        $('#formData').on('submit', function(e) {
            e.preventDefault();
            setSubmitting(true);
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            const id = $('#primary_id').val();
            const url = id ? '{{ route('pembayaran-tukang.update', ['pembayaran_tukang' => ':id']) }}'.replace(':id', id) :
                '{{ route('pembayaran-tukang.store') }}';
            const formData = new FormData(this);
            formData.append('_method', id ? 'PUT' : 'POST');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function() {
                    $('#modalForm').modal('hide');
                    audio.play();
                    toastr.success(id ? 'Data berhasil diupdate!' : 'Data berhasil ditambahkan!', 'BERHASIL', {
                        progressBar: true,
                        timeOut: 3500,
                        positionClass: 'toast-bottom-right'
                    });
                    $('.data-table').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    audio.play();

                    if (xhr.status === 422) {
                        toastr.error('Ada inputan yang salah!', 'GAGAL!', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right'
                        });
                        showValidationErrors(xhr.responseJSON.errors || {});
                    } else {
                        toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan data.', 'GAGAL!', {
                            progressBar: true,
                            timeOut: 3500,
                            positionClass: 'toast-bottom-right'
                        });
                    }

                    setSubmitting(false);
                }
            });
        });

        $(document).on('click', '.delete-button', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<span class="swal-btn-text">Ya, Hapus</span>',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: false,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger mx-2',
                    cancelButton: 'btn btn-secondary'
                },
                preConfirm: () => new Promise((resolve) => {
                    const confirmBtn = Swal.getConfirmButton();
                    const btnText = confirmBtn.querySelector('.swal-btn-text');
                    btnText.innerHTML = '<span class="spinner-border spinner-border-sm mx-2" role="status"></span> Menghapus...';
                    confirmBtn.disabled = true;

                    $.ajax({
                        url: form.attr('action'),
                        method: 'POST',
                        data: form.serialize(),
                        success: function() {
                            audio.play();
                            toastr.success('Data telah dihapus!', 'BERHASIL', {
                                progressBar: true,
                                timeOut: 3500,
                                positionClass: 'toast-bottom-right'
                            });
                            $('.data-table').DataTable().ajax.reload(null, false);
                            Swal.close();
                            resolve();
                        },
                        error: function(xhr) {
                            audio.play();
                            toastr.error(xhr.responseJSON?.message || 'Gagal menghapus data.', 'GAGAL!', {
                                progressBar: true,
                                timeOut: 3500,
                                positionClass: 'toast-bottom-right'
                            });
                            btnText.innerHTML = 'Ya, Hapus';
                            confirmBtn.disabled = false;
                        }
                    });
                })
            });
        });
    </script>
@endpush
