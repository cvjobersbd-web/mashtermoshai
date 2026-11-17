<?php
/**
 * ছোট Excel (.xlsx) লেখক — কোনো লাইব্রেরি লাগে না (শুধু PHP এর ZipArchive)।
 * xlsx_send('file-name', [
 *     ['name' => 'Sheet নাম', 'rows' => [[...], [...]], 'bold' => [0, 3], 'widths' => [24, 18, ...]],
 * ]);
 * সংখ্যা থাকলে সংখ্যা হিসেবে, বাকিসব লেখা হিসেবে বসে (মোবাইল নম্বরের শুরুর 0 হারায় না)।
 * ZipArchive না থাকলে প্রথম শিটটা CSV হিসেবে নামে (Excel এও খোলে)।
 */

function xlsx_esc(string $s): string
{
    $s = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $s) ?? '';
    return htmlspecialchars($s, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function xlsx_col(int $i): string
{
    $s = '';
    for ($i++; $i > 0; $i = intdiv($i - 1, 26)) $s = chr(65 + ($i - 1) % 26) . $s;
    return $s;
}

function xlsx_sheet_xml(array $sh): string
{
    $bold = array_flip($sh['bold'] ?? []);
    $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
    if (!empty($sh['freeze'])) $x .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . (int)$sh['freeze'] . '" topLeftCell="A' . ((int)$sh['freeze'] + 1) . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
    if (!empty($sh['widths'])) {
        $x .= '<cols>';
        foreach (array_values($sh['widths']) as $i => $w) $x .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float)$w . '" customWidth="1"/>';
        $x .= '</cols>';
    }
    $x .= '<sheetData>';
    foreach (array_values($sh['rows']) as $r => $row) {
        $x .= '<row r="' . ($r + 1) . '">';
        $st = isset($bold[$r]) ? ' s="1"' : '';
        foreach (array_values($row) as $c => $v) {
            $ref = xlsx_col($c) . ($r + 1);
            if (is_int($v) || is_float($v)) $x .= '<c r="' . $ref . '"' . $st . '><v>' . $v . '</v></c>';
            elseif ($v === null || $v === '') $x .= '<c r="' . $ref . '"' . $st . '/>';
            else $x .= '<c r="' . $ref . '" t="inlineStr"' . $st . '><is><t xml:space="preserve">' . xlsx_esc((string)$v) . '</t></is></c>';
        }
        $x .= '</row>';
    }
    return $x . '</sheetData></worksheet>';
}

function xlsx_send(string $fname, array $sheets): void
{
    $fname = preg_replace('/[^A-Za-z0-9._-]+/', '-', $fname) ?: 'export';
    if (!class_exists('ZipArchive')) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fname . '.csv"');
        $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
        foreach ($sheets[0]['rows'] ?? [] as $row) fputcsv($o, array_map('strval', $row));
        fclose($o); exit;
    }
    $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
    $z = new ZipArchive();
    $z->open($tmp, ZipArchive::OVERWRITE);
    $n = count($sheets);
    $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
    $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $used = [];
    foreach (array_values($sheets) as $i => $sh) {
        $k = $i + 1;
        $name = mb_substr(preg_replace('#[\\\\/?*\[\]:]#u', ' ', (string)($sh['name'] ?? "Sheet$k")), 0, 31) ?: "Sheet$k";
        while (isset($used[mb_strtolower($name)])) $name = mb_substr($name, 0, 28) . " $k";
        $used[mb_strtolower($name)] = 1;
        $ct .= '<Override PartName="/xl/worksheets/sheet' . $k . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $wb .= '<sheet name="' . xlsx_esc($name) . '" sheetId="' . $k . '" r:id="rId' . $k . '"/>';
        $rels .= '<Relationship Id="rId' . $k . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $k . '.xml"/>';
        $z->addFromString("xl/worksheets/sheet$k.xml", xlsx_sheet_xml($sh));
    }
    $rels .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    $z->addFromString('[Content_Types].xml', $ct . '</Types>');
    $z->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $z->addFromString('xl/workbook.xml', $wb . '</sheets></workbook>');
    $z->addFromString('xl/_rels/workbook.xml.rels', $rels);
    $z->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
        . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCEFFF"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>');
    $z->close();
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fname . '.xlsx"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: no-store');
    readfile($tmp);
    @unlink($tmp);
    exit;
}
