<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RwRequest;
use App\Models\Dusun;
use App\Models\Rw;
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

class RwController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $dusunId = $request->integer('dusun_id');
        $status = $request->string('status')->toString();
        $entriesOptions = [10, 12, 25, 50, 100];
        $entries = (int) $request->input('entries', 12);

        if (! in_array($entries, $entriesOptions, true)) {
            $entries = 12;
        }

        $rws = Rw::query()
            ->with(['dusun:id,nama'])
            ->withCount(['rts', 'penduduks'])
            ->when($dusunId, fn ($query) => $query->where('dusun_id', $dusunId))
            ->when($search, fn ($query) => $query->where('nomor', 'like', '%' . $search . '%'))
            ->when($status === 'populated', fn ($query) => $query->having('penduduks_count', '>', 0))
            ->when($status === 'empty', fn ($query) => $query->having('penduduks_count', '=', 0))
            ->orderBy('nomor')
            ->paginate($entries)
            ->withQueryString();

        $dusuns = Dusun::orderBy('nama')->get();

        return view('admin.rws.index', compact(
            'rws',
            'dusuns',
            'search',
            'dusunId',
            'status',
            'entries',
            'entriesOptions'
        ));
    }

    public function create(): View
    {
        return view('admin.rws.create', [
            'dusuns' => Dusun::orderBy('nama')->get(),
        ]);
    }

    public function store(RwRequest $request): RedirectResponse
    {
        $rw = Rw::create($request->validated());

        ActivityLogger::log('rw.created', $rw, 'RW baru ditambahkan', [
            'nomor' => $rw->nomor,
            'dusun' => $rw->dusun?->nama,
        ]);

        return redirect($request->input('redirect', route('admin.rws.index')))
            ->with('status', 'RW berhasil ditambahkan.');
    }

    public function edit(Rw $rw): View
    {
        return view('admin.rws.edit', [
            'rw' => $rw,
            'dusuns' => Dusun::orderBy('nama')->get(),
        ]);
    }

    public function update(RwRequest $request, Rw $rw): RedirectResponse
    {
        $rw->update($request->validated());

        ActivityLogger::log('rw.updated', $rw, 'Data RW diperbarui', [
            'nomor' => $rw->nomor,
        ]);

        return redirect($request->input('redirect', route('admin.rws.index')))
            ->with('status', 'RW berhasil diperbarui.');
    }

    public function destroy(Request $request, Rw $rw): RedirectResponse
    {
        $payload = [
            'nomor' => $rw->nomor,
            'dusun' => $rw->dusun?->nama,
        ];

        $rw->delete();

        ActivityLogger::log('rw.deleted', $rw, 'Data RW dihapus', $payload);

        return redirect($request->input('redirect', route('admin.rws.index')))
            ->with('status', 'RW berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data RW');

        return response()->streamDownload(function () use ($spreadsheet) {
            SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'rw.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data RW');
        $tempFile = tempnam(sys_get_temp_dir(), 'rw_pdf_') . '.pdf';
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'rw.pdf', ['Content-Type' => 'application/pdf'])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $phpWord = $this->buildWordDocument($columns, $rows, 'Data RW');
        $tempFile = tempnam(sys_get_temp_dir(), 'rw_word_') . '.docx';
        WordIOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, 'rw.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    private function exportColumns(): array
    {
        return [
            'no' => 'No',
            'nomor' => 'Nomor RW',
            'dusun' => 'Dusun',
            'jumlah_rt' => 'Jumlah RT',
            'jumlah_penduduk' => 'Jumlah Penduduk',
        ];
    }

    private function exportRows(): array
    {
        $rws = Rw::query()
            ->with('dusun:id,nama')
            ->withCount(['rts', 'penduduks'])
            ->orderBy('nomor')
            ->get();

        return $rws->map(function ($rw, $index) {
            return [
                'no' => $index + 1,
                'nomor' => $rw->nomor,
                'dusun' => $rw->dusun?->nama ?? '-',
                'jumlah_rt' => $rw->rts_count ?? 0,
                'jumlah_penduduk' => $rw->penduduks_count ?? 0,
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
            ->setTop(0.25)->setBottom(0.25)->setLeft(0.25)->setRight(0.25);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $totalColumns = count($columns);
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

        $exportTitle = 'Data RW';
        $exportSubtitle = 'Desa Tanjung Kesuma';
        $exportDate = 'Diunduh: ' . now()->translatedFormat('d M Y');

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
        foreach ($columns as $label) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $label);
            $sheet->getColumnDimension($colLetter)->setWidth(20);
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

        return $spreadsheet;
    }

    private function buildWordDocument(array $columns, array $rows, string $title): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'pageSizeW' => WordConverter::cmToTwip(33),
            'pageSizeH' => WordConverter::cmToTwip(21),
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
        ]);

        $totalColumns = count($columns);
        $tableWidth = WordConverter::cmToTwip(33);
        $colWidth = (int) floor($tableWidth / max($totalColumns, 1));

        $section->addText('Data RW', ['bold' => true, 'size' => 18], ['spaceAfter' => 80]);
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
        $phpWord->addTableStyle('RwTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('RwTable');
        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $bodyFont = ['size' => 12];

        $table->addRow();
        foreach ($columns as $label) {
            $table->addCell($colWidth)->addText($label, $headerFont, ['spaceAfter' => 0, 'alignment' => 'center']);
        }

        foreach ($rows as $row) {
            $table->addRow();
            foreach ($columns as $key => $label) {
                $paraStyle = ['spaceAfter' => 0];
                if ($key === 'no') {
                    $paraStyle['alignment'] = 'center';
                }
                $table->addCell($colWidth)->addText((string) ($row[$key] ?? ''), $bodyFont, $paraStyle);
            }
        }

        return $phpWord;
    }
}
