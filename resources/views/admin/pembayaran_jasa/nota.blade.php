<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran Jasa - {{ $data['no_nota'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 13px;
            color: #000;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h2 {
            font-size: 22px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header p {
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }

        .info {
            width: 100%;
            margin-bottom: 20px;
        }

        .info td {
            padding: 3px 8px 3px 0;
            vertical-align: top;
        }

        .amount-box {
            border: 1px solid #000;
            padding: 10px 15px;
            margin-bottom: 10px;
        }

        .amount-box .label {
            font-size: 12px;
        }

        .amount-box .value {
            font-size: 18px;
            font-weight: bold;
        }

        .terbilang {
            font-style: italic;
            margin-bottom: 20px;
        }

        .signature {
            display: flex;
            justify-content: flex-end;
            margin-top: 50px;
        }

        .signature div {
            text-align: center;
            width: 250px;
        }

        .signature .name {
            margin-top: 70px;
            font-weight: bold;
            text-decoration: underline;
        }

        .btn-print {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 16px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        @media print {
            .btn-print {
                display: none;
            }

            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <button type="button" class="btn-print" onclick="window.print()">Cetak</button>

    <div class="header">
        <h2>{{ $company['nama_perusahaan'] }}</h2>
        <p>{{ $company['alamat'] }}</p>
        <p>{{ $company['telp'] }}</p>
    </div>

    <div class="title">KWITANSI PEMBAYARAN JASA</div>

    <table class="info">
        <tr>
            <td width="140">No. Nota</td>
            <td width="10">:</td>
            <td>{{ $data['no_nota'] }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $data['tanggal'] }}</td>
        </tr>
        <tr>
            <td>Unit</td>
            <td>:</td>
            <td>{{ $data['unit'] }}</td>
        </tr>
        <tr>
            <td>Lokasi</td>
            <td>:</td>
            <td>{{ $data['lokasi'] }}</td>
        </tr>
        <tr>
            <td>Type Rumah</td>
            <td>:</td>
            <td>{{ $data['type'] }}</td>
        </tr>
        <tr>
            <td>Nama Jasa</td>
            <td>:</td>
            <td>{{ $data['nama_jasa'] }}</td>
        </tr>
        <tr>
            <td>Nama Tukang</td>
            <td>:</td>
            <td>{{ $data['tukang'] }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td>{{ $data['keterangan'] }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Telah diterima sejumlah :</div>
        <div class="value">Rp {{ number_format($data['jumlah_bayar'], 0, ',', '.') }}</div>
    </div>

    <div class="terbilang">( {{ ucfirst($data['terbilang']) }} rupiah )</div>

    <div class="signature">
        <div>
            <div>{{ $company['kota'] }}, {{ $data['tanggal'] }}</div>
            <div class="name">{{ $company['nama_penandatangan'] }}</div>
            <div>{{ $company['jabatan_penandatangan'] }}</div>
        </div>
    </div>
</body>

</html>
