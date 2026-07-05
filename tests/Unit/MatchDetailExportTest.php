<?php

namespace Tests\Unit;

use App\Exports\MatchDetailExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ReflectionMethod;
use Tests\TestCase;
use ZipArchive;

class MatchDetailExportTest extends TestCase
{
    public function test_match_detail_download_is_not_queued(): void
    {
        $export = new MatchDetailExport(match: 6);

        $this->assertNotInstanceOf(ShouldQueue::class, $export);
    }

    public function test_all_dropdowns_use_inline_lists_including_positions(): void
    {
        $export = new MatchDetailExport(match: 6);
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $addInlineDropdown = new ReflectionMethod($export, 'addInlineDropdown');
        $addInlineDropdown->invoke($export, $sheet, 'C', ['Sí', 'No'], 30);
        $positions = array_values(config('variables.KEY_POSITIONS', []));
        $addInlineDropdown->invoke($export, $sheet, 'F', $positions, 30);
        $addInlineDropdown->invoke($export, $sheet, 'L', range(1, 5), 30);

        $this->assertCount(1, $spreadsheet->getAllSheets());
        $this->assertSame('"Sí,No"', $sheet->getDataValidation('C2:C30')->getFormula1());
        $this->assertSame('"'.implode(',', $positions).'"', $sheet->getDataValidation('F2:F30')->getFormula1());
        $this->assertSame('"1,2,3,4,5"', $sheet->getDataValidation('L2:L30')->getFormula1());
        $this->assertGreaterThan(255, strlen($sheet->getDataValidation('F2:F30')->getFormula1()));

        $path = tempnam(sys_get_temp_dir(), 'match-export-');
        (new Xlsx($spreadsheet))->save($path);

        $archive = new ZipArchive;
        $this->assertTrue($archive->open($path));
        $sheetXml = $archive->getFromName('xl/worksheets/sheet1.xml');
        $archive->close();
        unlink($path);

        $this->assertStringContainsString('sqref="C2:C30"', $sheetXml);
        $this->assertStringContainsString('<formula1>&quot;Sí,No&quot;</formula1>', $sheetXml);
        $this->assertStringContainsString('sqref="F2:F30"', $sheetXml);
        $this->assertStringContainsString('Portero,Defensa (Central)', $sheetXml);
    }
}
