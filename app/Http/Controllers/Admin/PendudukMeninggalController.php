<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendudukMeninggalRequest;
use App\Models\Penduduk;
use App\Models\PendudukMeninggal;
use App\Models\References\RefStatusDasar;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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

class PendudukMeninggalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $period = $request->string('period')->toString() ?: 'all';
        $entriesOptions = [10, 25, 50, 100];
        $entries = (int) $request->input('entries', 10);

        if (! in_array($entries, $entriesOptions, true)) {
            $entries = 10;
        }

        $records = PendudukMeninggal::query()
            ->with('penduduk:id,nik,nama')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('penduduk', function ($inner) use ($search) {
                    $inner->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%');
                });
            })
            ->when($period !== 'all', function ($query) use ($period) {
                $date = match ($period) {
                    '7' => now()->subDays(7)->startOfDay(),
                    '30' => now()->subDays(30)->startOfDay(),
                    '90' => now()->subDays(90)->startOfDay(),
                    '365' => now()->subDays(365)->startOfDay(),
                    default => null,
                };

                if ($date) {
                    $query->whereDate('tanggal_meninggal', '>=', $date);
                }
            })
            ->orderByDesc('tanggal_meninggal')
            ->paginate($entries)
            ->withQueryString();

        return view('admin.penduduk-meninggal.index', [
            'records' => $records,
            'search' => $search,
            'period' => $period,
            'entries' => $entries,
            'entriesOptions' => $entriesOptions,
            'periodOptions' => [
                'all' => 'Semua Waktu',
                '7' => '7 Hari Terakhir',
                '30' => '30 Hari Terakhir',
                '90' => '3 Bulan Terakhir',
                '365' => '1 Tahun Terakhir',
            ],
            'pendudukOptions' => $this->eligiblePenduduk()->get(['id', 'nama', 'nik']),
        ]);
    }

    public function create(): View
    {
        return view('admin.penduduk-meninggal.create', [
            'pendudukOptions' => $this->eligiblePenduduk()->get(['id', 'nama', 'nik']),
        ]);
    }

    public function store(PendudukMeninggalRequest $request): RedirectResponse
    {
        $data = Arr::map($request->validated(), fn ($value) => $value === '' ? null : $value);

        $record = PendudukMeninggal::create($data);

        $this->markPendudukStatus($record->penduduk, 'MATI');

        ActivityLogger::log('penduduk.meninggal.created', $record, 'Penduduk tercatat meninggal', [
            'penduduk' => $record->penduduk?->nama,
            'tanggal' => $record->tanggal_meninggal?->toDateString(),
        ]);

        return redirect($request->input('redirect', route('admin.penduduk-meninggal.index')))
            ->with('status', 'Data kematian berhasil ditambahkan.');
    }

    public function edit(PendudukMeninggal $pendudukMeninggal): View
    {
        return view('admin.penduduk-meninggal.edit', [
            'record' => $pendudukMeninggal->load('penduduk'),
            'pendudukOptions' => $this->eligiblePenduduk($pendudukMeninggal->penduduk_id)->get(['id', 'nama', 'nik']),
        ]);
    }

    public function update(PendudukMeninggalRequest $request, PendudukMeninggal $pendudukMeninggal): RedirectResponse
    {
        $previousPendudukId = $pendudukMeninggal->penduduk_id;

        $data = Arr::map($request->validated(), fn ($value) => $value === '' ? null : $value);

        $pendudukMeninggal->update($data);
        $pendudukMeninggal->refresh();

        if ($previousPendudukId !== $pendudukMeninggal->penduduk_id) {
            $this->maybeResetStatus($previousPendudukId);
        }

        $this->markPendudukStatus($pendudukMeninggal->penduduk, 'MATI');

        ActivityLogger::log('penduduk.meninggal.updated', $pendudukMeninggal, 'Data kematian diperbarui', [
            'penduduk' => $pendudukMeninggal->penduduk?->nama,
        ]);

        return redirect($request->input('redirect', route('admin.penduduk-meninggal.index')))
            ->with('status', 'Data kematian berhasil diperbarui.');
    }

    public function destroy(Request $request, PendudukMeninggal $pendudukMeninggal): RedirectResponse
    {
        $penduduk = $pendudukMeninggal->penduduk;
        $payload = [
            'penduduk' => $penduduk?->nama,
            'tanggal' => $pendudukMeninggal->tanggal_meninggal?->toDateString(),
        ];

        $pendudukMeninggal->delete();

        $this->maybeResetStatus($penduduk?->id);

        ActivityLogger::log('penduduk.meninggal.deleted', $pendudukMeninggal, 'Data kematian dihapus', $payload);

        return redirect($request->input('redirect', route('admin.penduduk-meninggal.index')))
            ->with('status', 'Data kematian berhasil dihapus.');
    }

    private function eligiblePenduduk(?int $includeId = null)
    {
        $excludedStatuses = array_filter([
            $this->resolveStatusId('PINDAH'),
            $this->resolveStatusId('MATI'),
        ]);

        return Penduduk::query()
            ->orderBy('nama')
            ->when(! empty($excludedStatuses), fn ($query) => $query->whereNotIn('status_dasar_id', $excludedStatuses))
            ->when($includeId, fn ($query) => $query->orWhere('id', $includeId));
    }

    private function markPendudukStatus(?Penduduk $penduduk, string $status): void
    {
        if (! $penduduk) {
            return;
        }

        $statusId = $this->resolveStatusId($status);
        if (! $statusId) {
            return;
        }

        $penduduk->forceFill(['status_dasar_id' => $statusId])->save();
    }

    private function maybeResetStatus(?int $pendudukId): void
    {
        if (! $pendudukId) {
            return;
        }

        $penduduk = Penduduk::withCount(['pendudukPindahs', 'pendudukMeninggals'])->find($pendudukId);

        if (! $penduduk) {
            return;
        }

        if ($penduduk->pendudukMeninggals_count > 0) {
            $this->markPendudukStatus($penduduk, 'MATI');
            return;
        }

        if ($penduduk->pendudukPindahs_count > 0) {
            $this->markPendudukStatus($penduduk, 'PINDAH');
            return;
        }

        $this->markPendudukStatus($penduduk, 'HIDUP');
    }

    private function resolveStatusId(string $status): ?int
    {
        $status = strtoupper(trim($status));

        return RefStatusDasar::query()
            ->whereRaw('UPPER(nama) = ?', [$status])
            ->value('id');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Penduduk Meninggal');
        $filename = 'penduduk_meninggal.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Penduduk Meninggal');
        $tempFile = tempnam(sys_get_temp_dir(), 'meninggal_pdf_') . '.pdf';
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'penduduk_meninggal.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $phpWord = $this->buildWordDocument($columns, $rows, 'Penduduk Meninggal');
        $tempFile = tempnam(sys_get_temp_dir(), 'meninggal_word_') . '.docx';
        WordIOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, 'penduduk_meninggal.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    private function exportColumns(): array
    {
        return [
            'no' => 'No',
            'nik' => 'NIK',
            'nama' => 'Nama',
            'no_kk' => 'No KK',
            'tanggal_meninggal' => 'Tanggal Meninggal',
            'penyebab' => 'Penyebab',
            'tempat_meninggal' => 'Tempat Meninggal',
            'akta_meninggal_no' => 'No Akta Meninggal',
            'keterangan' => 'Keterangan',
        ];
    }

    private function exportRows(): array
    {
        $records = PendudukMeninggal::query()
            ->with('penduduk:id,nik,nama,no_kk')
            ->orderByDesc('tanggal_meninggal')
            ->get();

        $rows = [];
        foreach ($records as $index => $record) {
            $rows[] = [
                'no' => $index + 1,
                'nik' => $record->penduduk?->nik ?? '-',
                'nama' => $record->penduduk?->nama ?? '-',
                'no_kk' => $record->penduduk?->no_kk ?? '-',
                'tanggal_meninggal' => $record->tanggal_meninggal?->format('d-m-Y') ?? '-',
                'penyebab' => $record->penyebab ?? '-',
                'tempat_meninggal' => $record->tempat_meninggal ?? '-',
                'akta_meninggal_no' => $record->akta_meninggal_no ?? '-',
                'keterangan' => $record->keterangan ?? '-',
            ];
        }

        return $rows;
    }

    private function buildExportSpreadsheet(array $columns, array $rows, string $sheetTitle): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_FOLIO)
            ->setFitToWidth(0)
            ->setFitToHeight(0)
            ->setScale(100);
        $sheet->getPageMargins()
            ->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $totalColumns = count($columns);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);

        $colIndex = 1;
        foreach ($columns as $label) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}1", $label);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $colIndex++;
        }

        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($columns as $key => $label) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue("{$colLetter}{$rowIndex}", $row[$key] ?? '');
                $colIndex++;
            }
            $sheet->getRowDimension($rowIndex)->setRowHeight(22);
            $rowIndex++;
        }

        $headerRange = "A1:{$lastColumnLetter}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1D4ED8']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3A8A']]],
        ]);

        $dataEndRow = max($rowIndex - 1, 1);
        if ($dataEndRow >= 2) {
            $dataRange = "A2:{$lastColumnLetter}{$dataEndRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'CBD5F5']]],
            ]);
        }

        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumnLetter}{$dataEndRow}");
        $sheet->getStyle("A1:{$lastColumnLetter}{$dataEndRow}")->getAlignment()->setWrapText(true);
        $sheet->setSelectedCell('A1');

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
        $phpWord->addTableStyle('MeninggalTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('MeninggalTable');
        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $bodyFont = ['size' => 12];

        $table->addRow();
        foreach ($columns as $label) {
            $table->addCell($colWidth)->addText($label, $headerFont, ['spaceAfter' => 0]);
        }

        foreach ($rows as $row) {
            $table->addRow();
            foreach ($columns as $key => $label) {
                $table->addCell($colWidth)->addText((string) ($row[$key] ?? ''), $bodyFont, ['spaceAfter' => 0]);
            }
        }

        return $phpWord;
    }
}
