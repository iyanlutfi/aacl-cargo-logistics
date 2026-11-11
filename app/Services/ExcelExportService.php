<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class ExcelExportService
{
    public function buildXlsx(Collection $shipments, array $filterMeta = []): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive tidak aktif.');
        }

        $companyName = mb_strtoupper(CompanySetting::query()->value('company_name') ?: 'PT. AACL CARGO & LOGISTICS');
        $printedAt = now('Asia/Jakarta')->format('d M Y, H:i').' WIB';
        $filterNote = trim((string) ($filterMeta['summary'] ?? 'Semua Data'));

        $colWidths = [
            18, // A: No. CN
            13, // B: Tanggal
            26, // C: Nama Cust
            32, // D: Alamat Pengirim
            34, // E: Alamat Penerima
            9,  // F: Colly
            10, // G: Kg
            10, // H: Vol (m3)
            9,  // I: m2
            24, // J: Armada
            13, // K: Pickup
            24, // L: Status 1
            24, // M: Status 2
            24, // N: Status 3
            24, // O: Status 4
            24, // P: Status 5
            28, // Q: POD
            18, // R: Harga Kirim
            18, // S: Status Bayar
            26, // T: Catatan
        ];

        $rowsXml = [];

        // Row 1: Kop Banner (height 30)
        $rowsXml[] = $this->buildRowXml(1, 30, [
            'A1' => ['t' => 's', 'v' => "{$companyName} — LAPORAN OPERASIONAL PENGIRIMAN & TRACKING CARGO", 's' => 1],
        ] + $this->fillRangeStyle('B', 'T', 1, 1));

        // Row 2: Subtitle (height 20)
        $subtitle = "Dicetak: {$printedAt}  |  Total Data: {$shipments->count()} CN  |  Filter: {$filterNote}";
        $rowsXml[] = $this->buildRowXml(2, 20, [
            'A2' => ['t' => 's', 'v' => $subtitle, 's' => 2],
        ] + $this->fillRangeStyle('B', 'T', 2, 2));

        // Row 3: Spacer (height 8)
        $rowsXml[] = '<row r="3" ht="8" customHeight="1"/>';

        // Row 4: Group Headers (height 24)
        $row4Cells = [
            'A4' => ['t' => 's', 'v' => 'No. CN', 's' => 3],
            'B4' => ['t' => 's', 'v' => 'Tanggal', 's' => 3],
            'C4' => ['t' => 's', 'v' => 'Nama Cust', 's' => 3],
            'D4' => ['t' => 's', 'v' => 'Alamat Pengirim', 's' => 3],
            'E4' => ['t' => 's', 'v' => 'Alamat Penerima', 's' => 3],
            'F4' => ['t' => 's', 'v' => 'Jumlah', 's' => 4],
            'G4' => ['t' => 's', 'v' => '', 's' => 4],
            'H4' => ['t' => 's', 'v' => '', 's' => 4],
            'I4' => ['t' => 's', 'v' => '', 's' => 4],
            'J4' => ['t' => 's', 'v' => 'Armada', 's' => 3],
            'K4' => ['t' => 's', 'v' => 'Pickup', 's' => 3],
            'L4' => ['t' => 's', 'v' => 'Status Tracking (1 - 5)', 's' => 4],
            'M4' => ['t' => 's', 'v' => '', 's' => 4],
            'N4' => ['t' => 's', 'v' => '', 's' => 4],
            'O4' => ['t' => 's', 'v' => '', 's' => 4],
            'P4' => ['t' => 's', 'v' => '', 's' => 4],
            'Q4' => ['t' => 's', 'v' => 'Bukti Diterima (POD)', 's' => 3],
            'R4' => ['t' => 's', 'v' => 'Harga Kirim (Rp)', 's' => 3],
            'S4' => ['t' => 's', 'v' => 'Status Bayar', 's' => 3],
            'T4' => ['t' => 's', 'v' => 'Catatan', 's' => 3],
        ];
        $rowsXml[] = $this->buildRowXml(4, 24, $row4Cells);

        // Row 5: Sub Headers (height 22)
        $row5Cells = [
            'A5' => ['t' => 's', 'v' => '', 's' => 3],
            'B5' => ['t' => 's', 'v' => '', 's' => 3],
            'C5' => ['t' => 's', 'v' => '', 's' => 3],
            'D5' => ['t' => 's', 'v' => '', 's' => 3],
            'E5' => ['t' => 's', 'v' => '', 's' => 3],
            'F5' => ['t' => 's', 'v' => 'Colly', 's' => 5],
            'G5' => ['t' => 's', 'v' => 'Kg', 's' => 5],
            'H5' => ['t' => 's', 'v' => 'Vol (m3)', 's' => 5],
            'I5' => ['t' => 's', 'v' => 'm2', 's' => 5],
            'J5' => ['t' => 's', 'v' => '', 's' => 3],
            'K5' => ['t' => 's', 'v' => '', 's' => 3],
            'L5' => ['t' => 's', 'v' => '1', 's' => 5],
            'M5' => ['t' => 's', 'v' => '2', 's' => 5],
            'N5' => ['t' => 's', 'v' => '3', 's' => 5],
            'O5' => ['t' => 's', 'v' => '4', 's' => 5],
            'P5' => ['t' => 's', 'v' => '5', 's' => 5],
            'Q5' => ['t' => 's', 'v' => '', 's' => 3],
            'R5' => ['t' => 's', 'v' => '', 's' => 3],
            'S5' => ['t' => 's', 'v' => '', 's' => 3],
            'T5' => ['t' => 's', 'v' => '', 's' => 3],
        ];
        $rowsXml[] = $this->buildRowXml(5, 22, $row5Cells);

        $firstDataRow = 6;
        $rowNum = $firstDataRow;

        foreach ($shipments as $idx => $s) {
            $zebra = ($idx % 2 === 1);
            $textStyle = $zebra ? 7 : 6;
            $centerStyle = $zebra ? 9 : 8;
            $numStyle = $zebra ? 11 : 10;
            $currStyle = $zebra ? 13 : 12;
            $boldCenterStyle = $zebra ? 15 : 14;

            $podText = '-';
            if ($s->pod_receiver_name || $s->pod_received_at || $s->pod_notes) {
                $parts = array_filter([
                    $s->pod_receiver_name ? 'Penerima: '.$s->pod_receiver_name : null,
                    $s->pod_received_at ? 'Waktu: '.$s->pod_received_at : null,
                    $s->pod_notes ?: null,
                    $s->pod_photo_url ? '[Foto POD]' : null,
                    $s->pod_signature_data ? '[TTD Digital]' : null,
                ]);
                $podText = implode(' | ', $parts);
            }

            $cells = [
                "A{$rowNum}" => ['t' => 's', 'v' => (string) $s->cn_number, 's' => $boldCenterStyle],
                "B{$rowNum}" => ['t' => 's', 'v' => (string) $s->shipment_date, 's' => $centerStyle],
                "C{$rowNum}" => ['t' => 's', 'v' => (string) $s->customer_name, 's' => $textStyle],
                "D{$rowNum}" => ['t' => 's', 'v' => (string) ($s->shipper_address ?? ''), 's' => $textStyle],
                "E{$rowNum}" => ['t' => 's', 'v' => (string) ($s->receiver_address ?? ''), 's' => $textStyle],
                "F{$rowNum}" => ['t' => 'n', 'v' => (int) $s->colly, 's' => $numStyle],
                "G{$rowNum}" => ['t' => 'n', 'v' => (float) $s->weight_kg, 's' => $numStyle],
                "H{$rowNum}" => ['t' => 'n', 'v' => (float) $s->volume_m3, 's' => $numStyle],
                "I{$rowNum}" => ['t' => 'n', 'v' => (float) $s->area_m2, 's' => $numStyle],
                "J{$rowNum}" => ['t' => 's', 'v' => (string) ($s->fleet_name ?? '-'), 's' => $textStyle],
                "K{$rowNum}" => ['t' => 's', 'v' => (string) ($s->pickup_time ?? '-'), 's' => $centerStyle],
                "L{$rowNum}" => ['t' => 's', 'v' => (string) ($s->status_1_at ?? ''), 's' => $textStyle],
                "M{$rowNum}" => ['t' => 's', 'v' => (string) ($s->status_2_at ?? ''), 's' => $textStyle],
                "N{$rowNum}" => ['t' => 's', 'v' => (string) ($s->status_3_at ?? ''), 's' => $textStyle],
                "O{$rowNum}" => ['t' => 's', 'v' => (string) ($s->status_4_at ?? ''), 's' => $textStyle],
                "P{$rowNum}" => ['t' => 's', 'v' => (string) ($s->status_5_at ?? ''), 's' => $textStyle],
                "Q{$rowNum}" => ['t' => 's', 'v' => $podText, 's' => $textStyle],
                "R{$rowNum}" => ['t' => 'n', 'v' => (float) $s->service_cost, 's' => $currStyle],
                "S{$rowNum}" => ['t' => 's', 'v' => (string) ($s->payment_status ?: 'Belum Lunas'), 's' => $centerStyle],
                "T{$rowNum}" => ['t' => 's', 'v' => (string) ($s->notes ?? ''), 's' => $textStyle],
            ];

            $height = $this->estimateRowHeight($cells, $colWidths);
            $rowsXml[] = $this->buildRowXml($rowNum, $height, $cells);
            $rowNum++;
        }

        // Totals Row
        $lastDataRow = max($firstDataRow, $rowNum - 1);
        $totalRow = $rowNum;
        $hasData = $shipments->count() > 0;

        $totalCells = [
            "A{$totalRow}" => ['t' => 's', 'v' => 'TOTAL KESELURUHAN ('.$shipments->count().' CN)', 's' => 16],
            "B{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "C{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "D{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "E{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "F{$totalRow}" => $hasData ? ['f' => "SUM(F{$firstDataRow}:F{$lastDataRow})", 's' => 17] : ['t' => 'n', 'v' => 0, 's' => 17],
            "G{$totalRow}" => $hasData ? ['f' => "SUM(G{$firstDataRow}:G{$lastDataRow})", 's' => 17] : ['t' => 'n', 'v' => 0, 's' => 17],
            "H{$totalRow}" => $hasData ? ['f' => "SUM(H{$firstDataRow}:H{$lastDataRow})", 's' => 17] : ['t' => 'n', 'v' => 0, 's' => 17],
            "I{$totalRow}" => $hasData ? ['f' => "SUM(I{$firstDataRow}:I{$lastDataRow})", 's' => 17] : ['t' => 'n', 'v' => 0, 's' => 17],
            "J{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "K{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "L{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "M{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "N{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "O{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "P{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "Q{$totalRow}" => ['t' => 's', 'v' => 'TOTAL TAGIHAN KIRIM', 's' => 16],
            "R{$totalRow}" => $hasData ? ['f' => "SUM(R{$firstDataRow}:R{$lastDataRow})", 's' => 18] : ['t' => 'n', 'v' => 0, 's' => 18],
            "S{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
            "T{$totalRow}" => ['t' => 's', 'v' => '', 's' => 16],
        ];
        $rowsXml[] = $this->buildRowXml($totalRow, 24, $totalCells);

        $colsXml = '';
        foreach ($colWidths as $idx => $width) {
            $colIdx = $idx + 1;
            $colsXml .= "<col min=\"{$colIdx}\" max=\"{$colIdx}\" width=\"{$width}\" customWidth=\"1\"/>";
        }

        $merges = [
            'A1:T1', 'A2:T2',
            'A4:A5', 'B4:B5', 'C4:C5', 'D4:D5', 'E4:E5',
            'F4:I4',
            'J4:J5', 'K4:K5',
            'L4:P4',
            'Q4:Q5', 'R4:R5', 'S4:S5', 'T4:T5',
            "A{$totalRow}:E{$totalRow}",
        ];
        $mergeXml = '<mergeCells count="'.count($merges).'">';
        foreach ($merges as $ref) {
            $mergeXml .= "<mergeCell ref=\"{$ref}\"/>";
        }
        $mergeXml .= '</mergeCells>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .'<dimension ref="A1:T'.$totalRow.'"/>'
            .'<sheetViews><sheetView tabSelected="1" workbookViewId="0">'
            .'<pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="18"/>'
            .'<cols>'.$colsXml.'</cols>'
            .'<sheetData>'.implode('', $rowsXml).'</sheetData>'
            .$mergeXml
            .'<pageMargins left="0.3" right="0.3" top="0.4" bottom="0.4" header="0.2" footer="0.2"/>'
            .'<pageSetup paperSize="9" orientation="landscape" fitToWidth="1" fitToHeight="0"/>'
            .'</worksheet>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'aacl_xlsx_');
        $zip = new ZipArchive;
        if ($zip->open($tmpFile, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Gagal membuat arsip XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $binary = file_get_contents($tmpFile);
        @unlink($tmpFile);

        return $binary ?: '';
    }

    private function fillRangeStyle(string $startCol, string $endCol, int $row, int $styleIdx): array
    {
        $cells = [];
        for ($c = ord($startCol); $c <= ord($endCol); $c++) {
            $col = chr($c);
            $cells["{$col}{$row}"] = ['t' => 's', 'v' => '', 's' => $styleIdx];
        }

        return $cells;
    }

    private function estimateRowHeight(array $cells, array $colWidths): int
    {
        $maxLines = 1;
        $idx = 0;
        foreach ($cells as $cell) {
            $width = max(8, ($colWidths[$idx] ?? 18) - 2);
            $val = (string) ($cell['v'] ?? '');
            if ($val !== '') {
                $parts = preg_split('/\r\n|\r|\n/', $val) ?: [$val];
                $lines = 0;
                foreach ($parts as $part) {
                    $lines += max(1, (int) ceil(mb_strlen($part) / $width));
                }
                if ($lines > $maxLines) {
                    $maxLines = $lines;
                }
            }
            $idx++;
        }

        return max(20, min(140, $maxLines * 15 + 6));
    }

    private function buildRowXml(int $rowNum, int $height, array $cells): string
    {
        $xml = "<row r=\"{$rowNum}\" ht=\"{$height}\" customHeight=\"1\">";
        foreach ($cells as $ref => $cell) {
            $s = $cell['s'] ?? 0;
            if (isset($cell['f'])) {
                $xml .= "<c r=\"{$ref}\" s=\"{$s}\"><f>{$this->esc($cell['f'])}</f></c>";
            } elseif (($cell['t'] ?? 's') === 'n') {
                $xml .= "<c r=\"{$ref}\" s=\"{$s}\"><v>{$cell['v']}</v></c>";
            } else {
                $val = $this->esc((string) ($cell['v'] ?? ''));
                $xml .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"{$s}\"><is><t xml:space=\"preserve\">{$val}</t></is></c>";
            }
        }
        $xml .= '</row>';

        return $xml;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="AACL Operasional" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function stylesXml(): string
    {
        // Styles index:
        // 0: default
        // 1: Kop banner (navy fill, white bold 14pt, center/left)
        // 2: Subtitle (slate-100 fill, dark gray italic 9pt)
        // 3: Main Header (navy fill #0F2744, white bold 10pt, border)
        // 4: Group Header (blue fill #1E40AF, white bold 10pt, border)
        // 5: Sub Header (sky fill #DBEAFE, navy bold 9pt, border)
        // 6: Data text left (white, border, wrap)
        // 7: Data text left zebra (#F8FAFC, border, wrap)
        // 8: Data center (white, border, center, wrap)
        // 9: Data center zebra (#F8FAFC, border, center, wrap)
        // 10: Data numeric (white, border, right, #,##0.##)
        // 11: Data numeric zebra
        // 12: Data currency (white, border, right, "Rp "#,##0)
        // 13: Data currency zebra
        // 14: Bold CN center (white, border, bold)
        // 15: Bold CN center zebra
        // 16: Total row label (#E2E8F0, bold, border)
        // 17: Total row numeric (#E2E8F0, bold, #,##0.##, border)
        // 18: Total row currency (#E2E8F0, bold, "Rp "#,##0, border)
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="2">'
            .'<numFmt numFmtId="164" formatCode="#,##0.##"/>'
            .'<numFmt numFmtId="165" formatCode="&quot;Rp &quot;#,##0"/>'
            .'</numFmts>'
            .'<fonts count="7">'
            .'<font><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="13"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><i/><sz val="9"/><color rgb="FF334155"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="9"/><color rgb="FF0F2744"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="10"/><color rgb="FF0F2744"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="7">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0F2744"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1E40AF"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFDBEAFE"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border/>'
            .'<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="19">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="6" borderId="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="6" borderId="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="top"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="6" borderId="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="top"/></xf>'
            .'<xf numFmtId="165" fontId="5" fillId="0" borderId="1" applyFont="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="top"/></xf>'
            .'<xf numFmtId="165" fontId="5" fillId="6" borderId="1" applyFont="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="6" fillId="0" borderId="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="6" fillId="6" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="5" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="5" fillId="3" borderId="1" applyFont="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="165" fontId="5" fillId="3" borderId="1" applyFont="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }
}
