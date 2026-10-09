<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\KavlingPeta;
use App\Models\LokasiKavling;
use App\Models\MasterProjectDetail;
use App\Models\SettingProyek;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use TCPDF;

class SiteplanController extends Controller
{
    public function index()
    {
        $lokasiKavling = LokasiKavling::with([
            'masterSvg',
            'kavlingPeta.settingProyek.masterProject',
            'kavlingPeta.settingProyek.tukang',
        ])->orderBy('urutan', 'asc')->get();

        $legend = [
            ['label' => 'Belum di Setting', 'color' => '#ffffff'],
            ['label' => 'Sudah Disetting', 'color' => '#ffd54f'],
            ['label' => 'Proses', 'color' => '#ff9800'],
            ['label' => 'Selesai', 'color' => '#28a745'],
        ];

        return view('admin.siteplan.index', compact('lokasiKavling', 'legend'));
    }

    public function show($id)
    {
        $data = KavlingPeta::with(['lokasi', 'settingProyek.masterProject', 'settingProyek.tukang'])->findOrFail($id);
        $monitoringItems = [];

        if ($data->settingProyek) {
            $monitoringItems = $this->buildMonitoringItems($data->settingProyek);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $data,
            'monitoring_items' => $monitoringItems,
        ]);
    }

    public function cetakJPG($id_lokasi)
    {
        $svgContent = $this->generateSVG($id_lokasi);

        $svgFilename = "siteplan_{$id_lokasi}.svg";

        $svgPath = public_path("svg/{$svgFilename}");
        if (! file_exists(dirname($svgPath))) {
            mkdir(dirname($svgPath), 0755, true);
        }
        file_put_contents($svgPath, $svgContent);

        $endpoint   = 'https://ycikotabatu.org/convert/proses.php';
        $clientName = 'gesya_ready';

        $response = Http::attach(
            'svg_file',
            file_get_contents($svgPath),
            $svgFilename
        )->post($endpoint, [
            'client' => $clientName,
        ]);

        if (! $response->successful()) {
            abort(500, "Gagal upload ke server convert: " . $response->body());
        }

        $data = $response->json();
        if (! isset($data['jpg_url'])) {
            abort(500, "Gagal convert ke JPG: " . json_encode($data));
        }

        $jpgUrl = $data['jpg_url'];

        $jpgContent  = Http::get($jpgUrl)->body();
        $jpgFilename = basename(parse_url($jpgUrl, PHP_URL_PATH));

        $jpgPath = public_path("hasil/{$jpgFilename}");
        if (! file_exists(dirname($jpgPath))) {
            mkdir(dirname($jpgPath), 0755, true);
        }
        file_put_contents($jpgPath, $jpgContent);

        return response()->download($jpgPath);
    }

    private function generateSVG($id_lokasi, $width = 500, $height = 200)
    {
        $svg = DB::table('master_svg')->where('id_lokasi', $id_lokasi)->first();
        if (! $svg) {
            abort(404, "Data master_svg tidak ditemukan");
        }

        ob_start();

        echo $svg->header_xml;
        $header = str_replace(['[[lebar]]', '[[tinggi]]'], [$width, $height], $svg->header_svg);
        echo $header;

        $kavlings = DB::table('kavling_peta as k')
            ->leftJoin('setting_proyek as sp', 'k.id_kavling', '=', 'sp.id_kavling')
            ->where('k.id_lokasi', $id_lokasi)
            ->select('k.*', 'sp.status as setting_status')
            ->get();

        foreach ($kavlings as $pt) {
            $warna = $this->getStatusColor($pt->setting_status ?? null);

            $replacements = [$pt->map, $warna, $pt->matrik, $pt->kode_kavling];
            if ($pt->jenis_map === 'polygon') {
                echo str_replace(['[[1]]', '[[2]]', '[[3]]', '[[4]]'], $replacements, $svg->polygon_svg);
            } elseif ($pt->jenis_map === 'path') {
                echo str_replace(['[[1]]', '[[2]]', '[[3]]', '[[4]]'], $replacements, $svg->path_svg);
            }
        }

        echo $svg->footer_svg;

        return ob_get_clean();
    }

    public function cetakPDF($id_lokasi)
    {
        $namaPerusahaan = DB::table('konfigurasi')->value('nama_perusahaan');
        $lokasi         = DB::table('lokasi_kavling')->where('id_lokasi', $id_lokasi)->first();
        $namaKavling    = $lokasi->nama_kavling ?? '-';
        $periodeCetak   = now()->translatedFormat('d F Y');

        $svgContent  = $this->generateSVG($id_lokasi);
        $svgFilename = "siteplan_{$id_lokasi}.svg";
        $svgPath     = public_path("svg/{$svgFilename}");
        if (! file_exists(dirname($svgPath))) {
            mkdir(dirname($svgPath), 0755, true);
        }

        file_put_contents($svgPath, $svgContent);

        $endpoint   = 'https://ycikotabatu.org/convert/proses.php';
        $clientName = 'gesya_ready';
        $response   = Http::attach('svg_file', file_get_contents($svgPath), $svgFilename)
            ->post($endpoint, ['client' => $clientName]);
        if (! $response->successful()) {
            abort(500, "Gagal upload ke server convert: " . $response->body());
        }

        $data = $response->json();
        if (! isset($data['jpg_url'])) {
            abort(500, "Gagal convert ke JPG: " . json_encode($data));
        }

        $jpgUrl      = $data['jpg_url'];
        $jpgContent  = Http::get($jpgUrl)->body();
        $jpgFilename = basename(parse_url($jpgUrl, PHP_URL_PATH));
        $jpgPath     = public_path("hasil/{$jpgFilename}");
        if (! file_exists(dirname($jpgPath))) {
            mkdir(dirname($jpgPath), 0755, true);
        }

        file_put_contents($jpgPath, $jpgContent);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($namaPerusahaan);
        $pdf->SetTitle("Site Plan - {$namaKavling}");
        $pdf->SetMargins(10, 10, 10);
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 7, strtoupper($namaPerusahaan), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 6, 'SITE PLAN ' . strtoupper($namaKavling), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Periode Cetak : ' . $periodeCetak, 0, 1, 'C');

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.7);
        $pdf->Line(10, $pdf->GetY() + 2, 200, $pdf->GetY() + 2);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(10, $pdf->GetY() + 3, 200, $pdf->GetY() + 3);

        $pdf->Ln(10);

        $pdf->Image($jpgPath, 25, $pdf->GetY(), 160, 0, 'JPG');

        $pdf->Output("siteplan_{$namaKavling}.pdf", 'I');
    }

    private function getStatusColor(?int $status): string
    {
        return match ((int) $status) {
            1 => '#ffd54f',
            2 => '#ff9800',
            3 => '#28a745',
            default => '#ffffff',
        };
    }

    private function buildMonitoringItems(SettingProyek $setting): array
    {
        $rabItems = MasterProjectDetail::where('id_master_project', $setting->id_master_project)
            ->where('tipe', 'material')
            ->orderBy('id')
            ->get();

        $realisasiItems = BarangKeluar::query()
            ->join('barang_keluar_detail as d', 'barang_keluar.id', '=', 'd.id_barang_keluar')
            ->leftJoin('barang as b', 'd.id_barang', '=', 'b.id')
            ->where('barang_keluar.id_kavling', $setting->id_kavling)
            ->selectRaw("d.id_barang, COALESCE(b.nama, CONCAT('Barang #', d.id_barang)) as nama_barang, SUM(d.jumlah) as total_jumlah")
            ->groupBy('d.id_barang', 'b.nama')
            ->get()
            ->keyBy('id_barang');

        $rows = [];
        $usedBarang = [];

        foreach ($rabItems as $item) {
            $realisasi = $item->id_barang ? $realisasiItems->get($item->id_barang) : null;
            $rows[] = [
                'nama_barang' => $item->nama_item ?? '-',
                'rab' => $this->formatQty($item->qty),
                'realisasi' => $this->formatQty($realisasi->total_jumlah ?? 0),
            ];

            if ($item->id_barang) {
                $usedBarang[] = (string) $item->id_barang;
            }
        }

        foreach ($realisasiItems as $idBarang => $item) {
            if (in_array((string) $idBarang, $usedBarang, true)) {
                continue;
            }

            $rows[] = [
                'nama_barang' => $item->nama_barang ?: '-',
                'rab' => '0',
                'realisasi' => $this->formatQty($item->total_jumlah),
            ];
        }

        return $rows;
    }

    private function formatQty($value): string
    {
        $formatted = number_format((float) $value, 2, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    }
}


