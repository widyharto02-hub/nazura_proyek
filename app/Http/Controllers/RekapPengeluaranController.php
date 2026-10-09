<?php

namespace App\Http\Controllers;

use App\Models\BarangMasukPembayaran;
use App\Models\PembayaranJasa;
use App\Models\PembayaranTukang;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $week = $this->resolveWeek($request->get('minggu'));
        $jenis = $request->get('jenis');
        $entries = $this->buildEntries($week, $jenis);

        return view('admin.rekap_pengeluaran.index', [
            'week' => $week,
            'jenis' => $jenis,
            'entries' => $entries,
            'summary' => $this->buildSummary($entries),
        ]);
    }

    public function data(Request $request)
    {
        Carbon::setLocale('id');

        $week = $this->resolveWeek($request->get('minggu'));
        $jenis = $request->get('jenis');
        $entries = $this->buildEntries($week, $jenis);

        return response()->json([
            'label' => $week['label'],
            'html' => view('admin.rekap_pengeluaran._table', [
                'entries' => $entries,
                'summary' => $this->buildSummary($entries),
            ])->render(),
        ]);
    }

    public function export(Request $request)
    {
        Carbon::setLocale('id');

        $week = $this->resolveWeek($request->get('minggu'));
        $jenis = $request->get('jenis');
        $entries = $this->buildEntries($week, $jenis);
        $format = $request->get('format', 'excel');

        $headers = ['No', 'Tanggal', 'Hari', 'Jenis', 'Keterangan', 'Nominal'];
        $rows = [];
        $no = 1;
        $dailyTotal = 0;
        $lastDate = null;

        foreach ($entries as $entry) {
            if ($lastDate && $lastDate !== $entry['tanggal']) {
                $rows[] = ['', '', '', '', 'Sub Total ' . Carbon::parse($lastDate)->translatedFormat('d F Y'), $this->rupiah($dailyTotal)];
                $dailyTotal = 0;
            }

            $dailyTotal += (int) $entry['nominal'];
            $lastDate = $entry['tanggal'];

            $rows[] = [
                $no++,
                Carbon::parse($entry['tanggal'])->translatedFormat('d F Y'),
                $entry['hari'],
                $entry['jenis'],
                $entry['keterangan'],
                $this->rupiah((int) $entry['nominal']),
            ];
        }

        if ($lastDate && $dailyTotal > 0) {
            $rows[] = ['', '', '', '', 'Sub Total ' . Carbon::parse($lastDate)->translatedFormat('d F Y'), $this->rupiah($dailyTotal)];
        }

        $rows[] = ['', '', '', '', 'TOTAL PENGELUARAN MINGGU INI', $this->rupiah($this->buildSummary($entries)['total'])];

        $company = $this->getCompanyInfo();
        $meta = [
            'perusahaan' => $company['nama_perusahaan'],
            'alamat' => $company['alamat'],
            'periode' => 'Minggu ' . $week['label'],
        ];

        $title = 'Rekap Pengeluaran Mingguan';
        $filename = 'rekap_pengeluaran_' . $week['minggu_key'];

        $exportService = app(ExportService::class);

        if ($format === 'pdf') {
            return $exportService->pdf($filename, $title, $headers, $rows, $meta);
        }

        return $exportService->excel($filename, $title, $headers, $rows, $meta);
    }

    private function buildEntries(array $week, ?string $jenis = null): array
    {
        $entries = [];

        if (!$jenis || $jenis === 'Barang Masuk') {
            $barangMasuk = BarangMasukPembayaran::with('barangMasuk.supplier')
                ->whereBetween('tanggal_pembayaran', [$week['dari']->toDateString(), $week['sampai']->toDateString()])
                ->get();

            foreach ($barangMasuk as $payment) {
                $entries[] = [
                    'tanggal' => $payment->tanggal_pembayaran,
                    'hari' => Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('l'),
                    'jenis' => 'Barang Masuk',
                    'keterangan' => 'Nota #' . $payment->id_barang_masuk . ' - ' . ($payment->barangMasuk?->supplier?->nama ?? 'Supplier'),
                    'nominal' => (int) $payment->jumlah_bayar,
                ];
            }
        }

        if (!$jenis || $jenis === 'Pembayaran Jasa') {
            $jasa = PembayaranJasa::with(['tukang', 'jasaDetail', 'settingProyek.kavling'])
                ->whereBetween('tanggal_pembayaran', [$week['dari']->toDateString(), $week['sampai']->toDateString()])
                ->get();

            foreach ($jasa as $payment) {
                $namaJasa = $payment->jasaDetail?->nama_item ?? '-';
                $unit = $payment->settingProyek?->kavling?->kode_kavling ?? '-';

                $entries[] = [
                    'tanggal' => $payment->tanggal_pembayaran,
                    'hari' => Carbon::parse($payment->tanggal_pembayaran)->translatedFormat('l'),
                    'jenis' => 'Pembayaran Jasa',
                    'keterangan' => ($unit ? 'Unit ' . $unit . ' - ' : '') . $namaJasa . ($payment->tukang ? ' (' . $payment->tukang->nama . ')' : ''),
                    'nominal' => (int) $payment->jumlah_bayar,
                ];
            }
        }

        if (!$jenis || $jenis === 'Jasa Harian') {
            $tukang = PembayaranTukang::with('tukang')
                ->whereBetween('tanggal', [$week['dari']->toDateString(), $week['sampai']->toDateString()])
                ->get();

            foreach ($tukang as $payment) {
                $entries[] = [
                    'tanggal' => $payment->tanggal,
                    'hari' => Carbon::parse($payment->tanggal)->translatedFormat('l'),
                    'jenis' => 'Jasa Harian',
                    'keterangan' => ($payment->tukang?->nama ?? '-') . ' - ' . ($payment->nama_pekerjaan ?? '-'),
                    'nominal' => (int) $payment->nominal,
                ];
            }
        }

        usort($entries, function ($a, $b) {
            return strcmp($a['tanggal'] . '|' . $a['jenis'], $b['tanggal'] . '|' . $b['jenis']);
        });

        return $entries;
    }

    private function buildSummary(array $entries): array
    {
        $total = 0;
        $perJenis = [];

        foreach ($entries as $entry) {
            $total += (int) $entry['nominal'];
            $perJenis[$entry['jenis']] = ($perJenis[$entry['jenis']] ?? 0) + (int) $entry['nominal'];
        }

        return [
            'total' => $total,
            'per_jenis' => $perJenis,
        ];
    }

    private function resolveWeek($minggu): array
    {
        Carbon::setLocale('id');

        if (is_string($minggu) && preg_match('/^(\d{4})-W?(\d{1,2})$/i', $minggu, $m)) {
            $year = (int) $m[1];
            $week = (int) $m[2];
        } else {
            $now = Carbon::now();
            $year = (int) $now->format('o');
            $week = (int) $now->format('W');
        }

        $week = max(1, min($week, 53));

        $start = Carbon::now()->setISODate($year, $week)->startOfDay();
        $end = $start->copy()->addDays(6)->endOfDay();

        return [
            'year' => $year,
            'week' => $week,
            'minggu_key' => sprintf('%04d-W%02d', $year, $week),
            'dari' => $start,
            'sampai' => $end,
            'label' => $start->translatedFormat('d F Y') . ' s/d ' . $end->translatedFormat('d F Y'),
            'dates' => collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i))->toArray(),
        ];
    }

    private function getCompanyInfo(): array
    {
        $konfigurasi = \App\Models\Konfigurasi::first();

        return [
            'nama_perusahaan' => $konfigurasi?->nama_perusahaan ?? 'Dealaska',
            'alamat' => $konfigurasi?->alamat ?? '',
        ];
    }

    private function rupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
