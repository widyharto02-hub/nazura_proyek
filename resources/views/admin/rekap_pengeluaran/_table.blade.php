<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <div class="info-box-content">
                <span class="info-box-text">Total Pengeluaran</span>
                <span class="info-box-number">Rp {{ number_format($summary['total'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    @foreach ($summary['per_jenis'] as $jenis => $total)
        <div class="col-md-3">
            <div class="info-box">
                <div class="info-box-content">
                    <span class="info-box-text">{{ $jenis }}</span>
                    <span class="info-box-number">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th width="30px">No</th>
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Jenis</th>
                <th>Keterangan</th>
                <th width="140px">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry['tanggal'])->translatedFormat('d F Y') }}</td>
                    <td>{{ $entry['hari'] }}</td>
                    <td>{{ $entry['jenis'] }}</td>
                    <td>{{ $entry['keterangan'] }}</td>
                    <td>
                        <div class="d-flex justify-content-between w-100">
                            <span>Rp.</span>
                            <span>{{ number_format((int) $entry['nominal'], 0, ',', '.') }}</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data pada minggu ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="font-weight-bold">
                <td colspan="5" class="text-right">TOTAL PENGELUARAN</td>
                <td class="text-right">Rp {{ number_format($summary['total'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
