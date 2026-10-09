<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use TCPDF;

class ExportService
{
    public function excel(string $filename, string $title, array $headers, array $rows, array $meta = []): \Symfony\Component\HttpFoundation\Response
    {
        Carbon::setLocale('id');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Style
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D3D3D3']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $cellStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]];

        // Headers
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
            $column++;
        }

        // Data
        $rowNum = 2;
        foreach ($rows as $row) {
            $column = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($column . $rowNum, $cell);
                $sheet->getStyle($column . $rowNum)->applyFromArray($cellStyle);
                $column++;
            }
            $rowNum++;
        }

        // Auto width
        foreach (range('A', $column) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename . '.xlsx')->deleteFileAfterSend(true);
    }

    public function pdf(string $filename, string $title, array $headers, array $rows, array $meta = []): \Illuminate\Http\Response
    {
        Carbon::setLocale('id');

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($meta['perusahaan'] ?? 'Dealaska');
        $pdf->SetTitle($title);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->AddPage();

        $logoPath = public_path('assets/logo-dealaska.jpg');
        $logo = file_exists($logoPath) ? '<img src="' . $logoPath . '" width="120">' : '<h2>' . strtoupper($meta['perusahaan'] ?? 'DEALASKA') . '</h2>';

        $html = '<div style="text-align:center; border-bottom: 3px double #000; padding-bottom: 10px;">' . $logo . '</div>';
        $html .= '<h3 style="text-align:center; margin-top: 20px;">' . $title . '</h3>';
        $html .= '<p style="text-align:center; font-size:10px;">Periode: ' . ($meta['periode'] ?? '') . '</p>';

        $html .= '<table border="1" cellpadding="3" style="width:100%; border-collapse:collapse;">
            <thead><tr style="background-color:#d3d3d3; font-weight:bold; text-align:center;">';
        foreach ($headers as $h) $html .= '<th>' . e((string)$h) . '</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) $html .= '<td>' . e((string)$cell) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename . '.pdf', 'I');
        exit;
    }

    private function buildHtmlTable(array $headers, array $rows): string
    {
        $html = '<table border="1" cellpadding="4" cellspacing="0" style="width:100%;border-collapse:collapse;">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th style="background-color:#f2f2f2;font-weight:bold;">' . e((string) $header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        if (empty($rows)) {
            $html .= '<tr><td colspan="' . count($headers) . '" style="text-align:center;">Tidak ada data.</td></tr>';
        } else {
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . e((string) $cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table>';

        return $html;
    }

    private function wrapHtml(string $title, array $meta, string $html, array $headers = []): string
    {
        $perusahaan = $meta['perusahaan'] ?? '';
        $alamat = $meta['alamat'] ?? '';
        $periode = $meta['periode'] ?? '';
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y H:i');

        $colspan = max(count($headers ?? []), 1);

        return "\xEF\xBB\xBF" .
            '<html xmlns:x="urn:schemas-microsoft-com:office:excel">' .
            '<head><meta charset="UTF-8">' .
            '<style>
                table { border-collapse: collapse; }
                td, th { border: 1px solid #000; padding: 4px; mso-number-format:"\\@"; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .title-row td { border: none; font-weight: bold; font-size: 16px; text-align: center; }
                .meta-row td { border: none; font-size: 11px; text-align: center; }
                .periode-row td { border: none; font-size: 11px; text-align: center; font-weight: bold; }
            </style></head><body>' .
            '<table>' .
            '<tr class="title-row"><td colspan="' . $colspan . '">' . e(strtoupper($perusahaan)) . '</td></tr>' .
            ($alamat ? '<tr class="meta-row"><td colspan="' . $colspan . '">' . e($alamat) . '</td></tr>' : '') .
            '<tr class="meta-row"><td colspan="' . $colspan . '">' . e($title) . '</td></tr>' .
            ($periode ? '<tr class="periode-row"><td colspan="' . $colspan . '">' . e('Periode : ' . $periode) . '</td></tr>' : '') .
            '<tr class="meta-row"><td colspan="' . $colspan . '">Dicetak : ' . e($tanggalCetak) . '</td></tr>' .
            '</table><br>' .
            $html .
            '</body></html>';
    }
}
