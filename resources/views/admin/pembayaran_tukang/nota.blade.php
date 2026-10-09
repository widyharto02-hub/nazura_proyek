<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran Tukang - {{ $nota['no_nota'] }}</title>
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
            display: none;
        }

        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('assets/logo-dealaska.jpg') }}" alt="Logo Perusahaan" style="max-width: 120px; height: auto;">
    </div>

    <div class="title">KWITANSI PEMBAYARAN TUKANG</div>

    <table class="info">
        <tr>
            <td width="140">No. Nota</td>
            <td width="10">:</td>
            <td>{{ $nota['no_nota'] }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td>{{ $nota['tanggal'] }}</td>
        </tr>
        <tr>
            <td>Nama Tukang</td>
            <td>:</td>
            <td>{{ $nota['tukang'] }}</td>
        </tr>
        <tr>
            <td>Nama Pekerjaan</td>
            <td>:</td>
            <td>{{ $nota['nama_pekerjaan'] }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td>{{ $nota['keterangan'] }}</td>
        </tr>
    </table>

    <div class="amount-box">
        <div class="label">Telah diterima sejumlah :</div>
        <div class="value">Rp {{ number_format($nota['nominal'], 0, ',', '.') }}</div>
    </div>

    <div class="terbilang">( {{ ucfirst($nota['terbilang']) }} rupiah )</div>

    <table width="100%" style="margin-top: 50px;">
        <tr>
            <td width="60%"></td>
            <td width="40%" style="text-align: center;">
                <div>{{ $company['kota'] }}, {{ $nota['tanggal'] }}</div>
                <div style="height: 60px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $company['nama_penandatangan'] }}</div>
                <div>{{ $company['jabatan_penandatangan'] }}</div>
            </td>
        </tr>
    </table>
</body>

</html>
