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
                                <h3 class="font-weight-bold text-lg">{{ $reportTitle }}</h3>
                                <div class="d-flex">
                                    <a href="#" target="_blank" class="btn btn-sm mr-1 btn-export-excel" style="background-color: #28a745; border-color: #28a745; color: #ffffff;"><i class="fas fa-file-excel"></i><span class="ml-1">Excel</span></a>
                                    <a href="#" target="_blank" class="btn btn-sm btn-export-pdf" style="background-color: #dc3545; border-color: #dc3545; color: #ffffff;"><i class="fas fa-file-pdf"></i><span class="ml-1">PDF</span></a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="mb-1">Tanggal (Harian)</label>
                                    <input type="date" id="filter_tanggal" class="form-control" value="">
                                </div>
                                <div class="col-md-4">
                                    <label class="mb-1">Bulan</label>
                                    <select id="filter_bulan" class="form-control select-bulan">
                                        <option value="">Semua Bulan</option>
                                        @foreach ($monthOptions as $num => $name)
                                            <option value="{{ $num }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="mb-1">Tahun</label>
                                    <select id="filter_tahun" class="form-control select-tahun">
                                        <option value="">Semua Tahun</option>
                                        @foreach ($yearOptions as $year)
                                            <option value="{{ $year }}" @selected($year === (int) date('Y'))>{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @if($hasLokasiFilter)
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="mb-1">Lokasi</label>
                                    <select id="filter_lokasi" class="form-control select-lokasi">
                                        <option value="">Semua Lokasi</option>
                                        @foreach ($lokasiOptions as $lokasi)
                                            <option value="{{ $lokasi->id_lokasi }}">{{ $lokasi->nama_kavling }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="mb-1">Unit</label>
                                    <select id="filter_unit" class="form-control select-unit" disabled>
                                        <option value="">Semua Unit</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped small data-table w-100">
                                    <thead>
                                        <tr id="thead_laporan"></tr>
                                    </thead>
                                </table>
                            </div>

                            <div id="laporan_footer" class="d-none mt-2 text-right font-weight-bold"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
