<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Models\PembayaranJasa;
use App\Models\PembayaranTukang;
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

abstract class BaseLaporanController extends Controller
{
    protected const MONTH_NAMES = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    protected int $grandTotal = 0;

    abstract protected function reportTitle(): string;

    abstract protected function reportFolder(): string;

    abstract protected function reportKey(): string;

    abstract protected function reportFilename(): string;

    abstract protected function getHeaders(): array;

    abstract protected function buildRows(array $period): array;

    protected function footerLabel(): ?string
    {
        return null;
    }

    protected function moneyColumns(): array
    {
        return [];
    }

    protected function hasLokasiFilter(): bool
    {
        return false;
    }

    public function index(Request $request)
    {
        Carbon::setLocale('id');

        return view('admin.laporan.' . $this->reportFolder() . '.index', [
            'reportTitle' => $this->reportTitle(),
            'headers' => $this->getHeaders(),
            'yearOptions' => $this->getYearOptions(),
            'monthOptions' => self::MONTH_NAMES,
            'lokasiOptions' => LokasiKavling::orderBy('nama_kavling')->get(),
            'hasLokasiFilter' => $this->hasLokasiFilter(),
            'dataUrl' => route($this->reportKey() . '.data'),
            'exportUrlTemplate' => route($this->reportKey() . '.export', ':tab'),
        ]);
    }

    public function data(Request $request)
    {
        Carbon::setLocale('id');

        $period = $this->resolveFilters($request);
        $rows = $this->buildRows($period);

        $data = collect($rows)->map(function ($row) {
            $item = [];
            foreach ($this->getHeaders() as $i => $header) {
                $item['c' . $i] = $row[$i] ?? '';
            }

            return $item;
        });

        $table = DataTables::of($data);

        $moneyKeys = [];
        foreach ($this->moneyColumns() as $col) {
            $key = 'c' . $col;
            $moneyKeys[] = $key;
            $table->editColumn($key, function ($row) use ($key) {
                $nilai = (int) ($row[$key] ?? 0);

                return '
                    <div class="d-flex justify-content-between w-100">
                        <span>Rp.</span>
                        <span>' . number_format($nilai, 0, ',', '.') . '</span>
                    </div>';
            });
        }

        if ($moneyKeys) {
            $table->rawColumns($moneyKeys);
        }

        return $table->with(['footer' => $this->buildFooterPayload()])->make(true);
    }

    public function export(Request $request, string $tipe)
    {
        Carbon::setLocale('id');

        $period = $this->resolveFilters($request);
        $rows = $this->buildRows($period);
        $format = $request->get('format', 'excel');

        $money = $this->moneyColumns();
        foreach ($rows as &$row) {
            foreach ($money as $col) {
                $row[$col] = $this->rupiah((int) ($row[$col] ?? 0));
            }
        }
        unset($row);

        if ($this->footerLabel()) {
            $rows[] = $this->buildFooterRow();
        }

        $company = $this->getCompanyInfo();
        $meta = [
            'perusahaan' => $company['nama_perusahaan'],
            'alamat' => $company['alamat'],
            'periode' => $period['label'],
        ];

        $exportService = app(ExportService::class);

        if ($format === 'pdf') {
            return $exportService->pdf($this->reportFilename(), $this->reportTitle(), $this->getHeaders(), $rows, $meta);
        }

        return $exportService->excel($this->reportFilename(), $this->reportTitle(), $this->getHeaders(), $rows, $meta);
    }

    protected function buildFooterPayload(): ?array
    {
        if (!$this->footerLabel()) {
            return null;
        }

        return [
            'label' => $this->footerLabel(),
            'value' => $this->rupiah($this->grandTotal),
        ];
    }

    protected function buildFooterRow(): array
    {
        $headers = $this->getHeaders();
        $row = array_fill(0, count($headers), '');
        $row[count($headers) - 1] = $this->footerLabel() . ' : ' . $this->rupiah($this->grandTotal);

        return $row;
    }

    protected function resolveFilters(Request $request): array
    {
        Carbon::setLocale('id');
        $now = Carbon::now();
        $tanggal = $request->get('tanggal');
        $bulan = (int) $request->get('bulan');
        $tahun = (int) $request->get('tahun');
        $lokasi = $request->get('lokasi');
        $unit = $request->get('unit');

        if ($tanggal) {
            $dari = Carbon::parse($tanggal)->startOfDay();
            $sampai = $dari->copy()->endOfDay();
            $label = 'Tanggal ' . $dari->translatedFormat('d F Y');
        } elseif ($bulan >= 1 && $bulan <= 12) {
            $year = $tahun > 0 ? $tahun : (int) $now->format('Y');
            $dari = Carbon::create($year, $bulan, 1)->startOfMonth();
            $sampai = $dari->copy()->endOfMonth();
            $label = 'Bulan ' . $dari->translatedFormat('F Y');
        } elseif ($tahun > 0) {
            $dari = Carbon::create($tahun, 1, 1)->startOfYear();
            $sampai = Carbon::create($tahun, 12, 31)->endOfDay();
            $label = 'Tahun ' . $tahun;
        } else {
            $dari = Carbon::create(2000, 1, 1)->startOfDay();
            $sampai = $now->copy()->endOfDay();
            $label = 'Semua Data';
        }

        return [
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'label' => $label,
            'lokasi' => $lokasi ?: null,
            'unit' => $unit ?: null,
        ];
    }

    public function getUnitsByLokasi(Request $request): JsonResponse
    {
        $idLokasi = $request->get('id_lokasi');

        if (!$idLokasi) {
            return response()->json(['status' => 'success', 'data' => []]);
        }

        $units = KavlingPeta::where('id_lokasi', $idLokasi)
            ->orderBy('kode_kavling')
            ->get(['id_kavling', 'kode_kavling']);

        return response()->json(['status' => 'success', 'data' => $units]);
    }

    protected function getYearOptions(): array
    {
        $currentYear = (int) Carbon::now()->format('Y');
        $minYear = $currentYear;

        $minYear = min($minYear, (int) BarangMasuk::min(DB::raw('YEAR(tanggal)')));
        $minYear = min($minYear, (int) BarangKeluar::min(DB::raw('YEAR(tanggal)')));
        $minYear = min($minYear, (int) PembayaranTukang::min(DB::raw('YEAR(tanggal)')));
        $minYear = min($minYear, (int) PembayaranJasa::min(DB::raw('YEAR(tanggal_pembayaran)')));

        $minYear = max($minYear, 2000);

        $years = [];
        for ($y = $currentYear; $y >= $minYear; $y--) {
            $years[] = $y;
        }

        return $years;
    }

    protected function getCompanyInfo(): array
    {
        $konfigurasi = \App\Models\Konfigurasi::first();

        return [
            'nama_perusahaan' => $konfigurasi?->nama_perusahaan ?? 'Dealaska',
            'alamat' => $konfigurasi?->alamat ?? '',
        ];
    }

    protected function rupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    protected function qty($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }

    protected function persen(int $actual, int $target): float
    {
        if ($target <= 0) {
            return 0;
        }

        return round(((float) $actual / $target) * 100, 2);
    }

    protected function persenLabel(float $value): string
    {
        return number_format($value, 2, ',', '.') . '%';
    }

    protected function statusLabel(int $status): string
    {
        return match ($status) {
            1 => 'Sudah Disetting',
            2 => 'Proses',
            3 => 'Selesai',
            default => 'Belum di Setting',
        };
    }
}
