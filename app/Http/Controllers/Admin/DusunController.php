<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DusunRequest;
use App\Models\Dusun;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf as PdfWriterMpdf;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter as WordConverter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Table as WordTableStyle;

class DusunController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $entriesOptions = [10, 12, 25, 50, 100];
        $entries = (int) $request->input('entries', 12);

        if (! in_array($entries, $entriesOptions, true)) {
            $entries = 12;
        }

        $dusuns = Dusun::query()
            ->withCount(['rws', 'penduduks'])
            ->when($search, fn ($query) => $query->where('nama', 'like', '%' . $search . '%'))
            ->when($status === 'populated', fn ($query) => $query->having('penduduks_count', '>', 0))
            ->when($status === 'empty', fn ($query) => $query->having('penduduks_count', '=', 0))
            ->orderBy('nama')
            ->paginate($entries)
            ->withQueryString();

        return view('admin.dusuns.index', compact(
            'dusuns',
            'search',
            'status',
            'entries',
            'entriesOptions'
        ));
    }

    public function create(): View
    {
        return view('admin.dusuns.create');
    }

    public function store(DusunRequest $request): RedirectResponse
    {
        $dusun = Dusun::create($request->validated());

        ActivityLogger::log('dusun.created', $dusun, 'Dusun baru ditambahkan', [
            'nama' => $dusun->nama,
        ]);

        return redirect($request->input('redirect', route('admin.dusuns.index')))
            ->with('status', 'Dusun berhasil ditambahkan.');
    }

    public function edit(Dusun $dusun): View
    {
        return view('admin.dusuns.edit', compact('dusun'));
    }

    public function update(DusunRequest $request, Dusun $dusun): RedirectResponse
    {
        $dusun->update($request->validated());

        ActivityLogger::log('dusun.updated', $dusun, 'Dusun diperbarui', [
            'nama' => $dusun->nama,
        ]);

        return redirect($request->input('redirect', route('admin.dusuns.index')))
            ->with('status', 'Dusun berhasil diperbarui.');
    }

    public function destroy(Request $request, Dusun $dusun): RedirectResponse
    {
        $dusunName = $dusun->nama;
        $dusun->delete();

        ActivityLogger::log('dusun.deleted', $dusun, 'Dusun diarsipkan', [
            'nama' => $dusunName,
        ]);

        return redirect($request->input('redirect', route('admin.dusuns.index')))
            ->with('status', 'Dusun berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data Dusun');

        return response()->streamDownload(function () use ($spreadsheet) {
            SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'dusun.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data Dusun');
        $tempFile = tempnam(sys_get_temp_dir(), 'dusun_pdf_') . '.pdf';
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'dusun.pdf', ['Content-Type' => 'application/pdf'])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $phpWord = $this->buildWordDocument($columns, $rows);
        $tempFile = tempnam(sys_get_temp_dir(), 'dusun_word_') . '.docx';
        WordIOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, 'dusun.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    private function exportColumns(): array
    {
        return [
            'no' => 'No',
            'nama' => 'Nama Dusun',
            'kode' => 'Kode',
            'jumlah_rw' => 'Jumlah RW',
            'jumlah_penduduk' => 'Jumlah Penduduk',
        ];
    }

    private function exportRows(): array
    {
        $dusuns = Dusun::query()
            ->withCount(['rws', 'penduduks'])
            ->orderBy('nama')
            ->get();

        return $dusuns->map(function ($dusun, $index) {
            return [
                'no' => $index + 1,
                'nama' => $dusun->nama,
                'kode' => $dusun->kode ?? '-',
                'jumlah_rw' => $dusun->rws_count ?? 0,
                'jumlah_penduduk' => $dusun->penduduks_count ?? 0,
            ];
        })->all();
    }

    private function buildExportSpreadsheet(array $columns, array $rows, string $title): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($title, 0, 31));
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_FOLIO)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setScale(100);
        $sheet->getPageMargins()
            ->setTop(0.2)->setBottom(0.2)->setLeft(0.15)->setRight(0.15);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $totalColumns = count($columns);
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

        $exportTitle = 'Data Dusun';
        $exportSubtitle = 'Desa Tanjung Kesuma';
        $exportDate = 'diunduh: ' . now()->translatedFormat('d M Y');

        $sheet->mergeCells("A1:{$lastColumnLetter}1");
        $sheet->setCellValue('A1', $exportTitle);
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);

        $midIndex = max(1, (int) floor($totalColumns / 2));
        if ($midIndex >= $totalColumns) {
            $midIndex = max(1, $totalColumns - 1);
        }
        $leftEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($midIndex);
        $rightStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($midIndex + 1);

        $sheet->mergeCells("A2:{$leftEnd}2");
        $sheet->setCellValue('A2', $exportSubtitle);
        $sheet->mergeCells("{$rightStart}2:{$lastColumnLetter}2");
        $sheet->setCellValue("{$rightStart}2", $exportDate);
        $sheet->getStyle("A2:{$leftEnd}2")->getFont()->setSize(12);
        $sheet->getStyle("{$rightStart}2:{$lastColumnLetter}2")->getFont()->setSize(11);
        $sheet->getStyle("A1:{$lastColumnLetter}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A2:{$leftEnd}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("{$rightStart}2:{$lastColumnLetter}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $colIndex = 1;
        $headerRow = 3;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        $columnWidths = [
            'no' => 8,
            'nama' => 40,
            'kode' => 15,
            'jumlah_rw' => 15,
            'jumlah_penduduk' => 15,
        ];
        foreach ($columns as $label) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $label);
            $width = 18;
            $key = array_keys($columns)[$colIndex - 1] ?? null;
            if ($key && isset($columnWidths[$key])) {
                $width = $columnWidths[$key];
            }
            $sheet->getColumnDimension($colLetter)->setWidth($width);
            $sheet->getColumnDimension($colLetter)->setAutoSize(false);
            $colIndex++;
        }

        $rowIndex = $headerRow + 1;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($columns as $key => $label) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue("{$colLetter}{$rowIndex}", $row[$key] ?? '');
                $colIndex++;
            }
            $sheet->getRowDimension($rowIndex)->setRowHeight(22);
            $rowIndex++;
        }

        $headerRange = "A{$headerRow}:{$lastColumnLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1D4ED8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A8A']]],
        ]);

        $dataEndRow = max($rowIndex - 1, $headerRow);
        if ($dataEndRow >= $headerRow + 1) {
            $dataRange = "A" . ($headerRow + 1) . ":{$lastColumnLetter}{$dataEndRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'CBD5F5']]],
            ]);
        }

        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setAutoFilter("A{$headerRow}:{$lastColumnLetter}{$dataEndRow}");
        $sheet->getStyle("A1:{$lastColumnLetter}{$dataEndRow}")->getAlignment()->setWrapText(true);
        $sheet->setSelectedCell("A{$headerRow}");

        // Kolom nomor rata tengah
        $sheet->getStyle("A{$headerRow}:A{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C{$headerRow}:C{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D{$headerRow}:E{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return $spreadsheet;
    }

    private function buildWordDocument(array $columns, array $rows): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'pageSizeW' => WordConverter::cmToTwip(33),
            'pageSizeH' => WordConverter::cmToTwip(21),
            'marginLeft' => 450,
            'marginRight' => 450,
            'marginTop' => 450,
            'marginBottom' => 450,
        ]);

        $totalColumns = count($columns);
        $tableWidth = WordConverter::cmToTwip(33);
        $columnWidths = [
            'no' => WordConverter::cmToTwip(3),
            'nama' => WordConverter::cmToTwip(16),
            'kode' => WordConverter::cmToTwip(6),
            'jumlah_rw' => WordConverter::cmToTwip(4.5),
            'jumlah_penduduk' => WordConverter::cmToTwip(5.5),
        ];

        $section->addText('Data Dusun', ['bold' => true, 'size' => 18], ['spaceAfter' => 80]);
        $headerTable = $section->addTable([
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
            'layout' => WordTableStyle::LAYOUT_FIXED,
            'borderSize' => 0,
        ]);
        $headerTable->addRow();
        $headerTable->addCell((int) ($tableWidth * 0.6))->addText('Desa Tanjung Kesuma', ['size' => 12], ['spaceAfter' => 0]);
        $headerTable->addCell((int) ($tableWidth * 0.4))->addText(
            'Diunduh: ' . now()->translatedFormat('d M Y'),
            ['size' => 11],
            ['spaceAfter' => 200, 'alignment' => 'right']
        );

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => 'DDDDDD',
            'cellMargin' => 80,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'layout' => WordTableStyle::LAYOUT_FIXED,
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
        ];
        $firstRowStyle = ['bgColor' => '1D4ED8'];
        $phpWord->addTableStyle('DusunTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('DusunTable');
        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $bodyFont = ['size' => 12];

        $table->addRow();
        foreach ($columns as $key => $label) {
            $cellWidth = $columnWidths[$key] ?? (int) floor($tableWidth / max($totalColumns, 1));
            $table->addCell($cellWidth)->addText($label, $headerFont, ['spaceAfter' => 0, 'alignment' => 'center']);
        }

        foreach ($rows as $row) {
            $table->addRow();
            foreach ($columns as $key => $label) {
                $paraStyle = ['spaceAfter' => 0];
                if (in_array($key, ['no', 'kode', 'jumlah_rw', 'jumlah_penduduk'], true)) {
                    $paraStyle['alignment'] = 'center';
                }
                $cellWidth = $columnWidths[$key] ?? (int) floor($tableWidth / max($totalColumns, 1));
                $table->addCell($cellWidth)->addText((string) ($row[$key] ?? ''), $bodyFont, $paraStyle);
            }
        }

        return $phpWord;
    }
}
