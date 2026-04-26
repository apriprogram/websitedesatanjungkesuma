<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KeluargaRequest;
use App\Models\Dusun;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rw;
use App\Models\Rt;
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

class KeluargaController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $dusunId = $request->integer('dusun_id');
        $entriesOptions = [10, 12, 25, 50, 100];
        $entries = (int) $request->input('entries', 12);

        if (! in_array($entries, $entriesOptions, true)) {
            $entries = 12;
        }

        $keluargas = Keluarga::query()
            ->with([
                'dusun:id,nama', 
                'rw:id,nomor', 
                'rt:id,nomor',
                'penduduks' => function ($query) {
                    $query->with([
                        'kkLevel:id,nama',
                        'jenisKelamin:id,nama',
                        'agama:id,nama',
                        'pendidikanKk:id,nama',
                        'pendidikanSedang:id,nama',
                        'pekerjaan:id,nama',
                        'statusKawin:id,nama',
                        'warganegara:id,nama',
                        'golonganDarah:id,nama',
                        'suku:id,nama',
                        'cacat:id,nama',
                        'caraKb:id,nama',
                        'statusDasar:id,nama',
                        'statusRekam:id,nama'
                    ])
                    ->orderBy('kk_level_id');
                }
            ])
            ->withCount('penduduks')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('no_kk', 'like', '%' . $search . '%')
                        ->orWhere('kepala_nik', 'like', '%' . $search . '%');
                });
            })
            ->when($dusunId, fn ($query) => $query->where('dusun_id', $dusunId))
            ->orderBy('no_kk')
            ->paginate($entries)
            ->withQueryString();

        $dusuns = Dusun::orderBy('nama')->get();
        $rws = Rw::with('dusun:id,nama')->orderBy('nomor')->get();
        $rts = Rt::with('rw:id,nomor')->orderBy('nomor')->get();

        return view('admin.keluargas.index', compact(
            'keluargas',
            'dusuns',
            'rws',
            'rts',
            'search',
            'dusunId',
            'entries',
            'entriesOptions'
        ));
    }

    public function create(): View
    {
        return view('admin.keluargas.create', [
            'dusuns' => Dusun::orderBy('nama')->get(),
            'rws' => Rw::with('dusun:id,nama')->orderBy('nomor')->get(),
            'rts' => Rt::with('rw:id,nomor')->orderBy('nomor')->get(),
        ]);
    }

    public function store(KeluargaRequest $request): RedirectResponse
    {
        $keluarga = Keluarga::create($request->validated());

        ActivityLogger::log('keluarga.created', $keluarga, 'Keluarga baru ditambahkan', [
            'no_kk' => $keluarga->no_kk,
        ]);

        return redirect($request->input('redirect', route('admin.keluargas.index')))
            ->with('status', 'Keluarga berhasil ditambahkan.');
    }

    public function edit(Keluarga $keluarga): View
    {
        return view('admin.keluargas.edit', [
            'keluarga' => $keluarga,
            'dusuns' => Dusun::orderBy('nama')->get(),
            'rws' => Rw::with('dusun:id,nama')->orderBy('nomor')->get(),
            'rts' => Rt::with('rw:id,nomor')->orderBy('nomor')->get(),
        ]);
    }

    public function update(KeluargaRequest $request, Keluarga $keluarga): RedirectResponse
    {
        $keluarga->update($request->validated());

        ActivityLogger::log('keluarga.updated', $keluarga, 'Data keluarga diperbarui', [
            'no_kk' => $keluarga->no_kk,
        ]);

        return redirect($request->input('redirect', route('admin.keluargas.index')))
            ->with('status', 'Keluarga berhasil diperbarui.');
    }

    public function destroy(Request $request, Keluarga $keluarga): RedirectResponse
    {
        $payload = ['no_kk' => $keluarga->no_kk];

        $keluarga->delete();

        ActivityLogger::log('keluarga.deleted', $keluarga, 'Data keluarga dihapus', $payload);

        return redirect($request->input('redirect', route('admin.keluargas.index')))
            ->with('status', 'Keluarga berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data Keluarga');
        $filename = 'keluarga.xlsx';

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
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Data Keluarga');
        $tempFile = tempnam(sys_get_temp_dir(), 'keluarga_pdf_') . '.pdf';
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'keluarga.pdf', ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $phpWord = $this->buildWordDocument($columns, $rows, 'Data Keluarga');
        $tempFile = tempnam(sys_get_temp_dir(), 'keluarga_word_') . '.docx';
        WordIOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, 'keluarga.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    private function exportColumns(): array
    {
        return [
            'no' => 'No',
            'no_kk' => 'No KK',
            'kepala_nik' => 'Kepala Keluarga (NIK)',
            'kepala_nama' => 'Nama Kepala Keluarga',
            'alamat' => 'Alamat',
            'dusun' => 'Dusun',
            'rw' => 'RW',
            'rt' => 'RT',
            'jumlah_anggota' => 'Jumlah Anggota',
        ];
    }

    private function exportRows(): array
    {
        $keluargas = Keluarga::query()
            ->with(['dusun:id,nama', 'rw:id,nomor', 'rt:id,nomor', 'penduduks:id,no_kk,nik,nama'])
            ->withCount('penduduks')
            ->orderBy('no_kk')
            ->get();

        $rows = [];
        foreach ($keluargas as $index => $keluarga) {
            $kepala = $keluarga->penduduks->firstWhere('nik', $keluarga->kepala_nik);
            $rows[] = [
                'no' => $index + 1,
                'no_kk' => $keluarga->no_kk,
                'kepala_nik' => $keluarga->kepala_nik ?? '-',
                'kepala_nama' => $kepala?->nama ?? 'Belum tercatat',
                'alamat' => $keluarga->alamat ?? '-',
                'dusun' => $keluarga->dusun?->nama ?? '-',
                'rw' => $keluarga->rw?->nomor ?? '-',
                'rt' => $keluarga->rt?->nomor ?? '-',
                'jumlah_anggota' => $keluarga->penduduks_count,
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
        $lastColumnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

        $colIndex = 1;
        foreach ($columns as $label) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}1", $label);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            $colIndex++;
        }

        $rowIndex = 2;
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
        $phpWord->addTableStyle('KeluargaTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('KeluargaTable');
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
    public function print(Keluarga $keluarga)
    {
        $keluarga->load([
            'dusun', 
            'rw', 
            'rt',
            'penduduks' => function ($query) {
                $query->with([
                    'kkLevel', 'jenisKelamin', 'agama', 'pendidikanKk', 
                    'pendidikanSedang', 'pekerjaan', 'statusKawin', 
                    'warganegara', 'golonganDarah', 'suku', 'cacat', 
                    'caraKb', 'statusDasar', 'statusRekam'
                ])->orderBy('kk_level_id');
            }
        ]);

        $kepala = $keluarga->penduduks->firstWhere('kk_level_id', 1) ?: $keluarga->penduduks->first();

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $html = view('admin.keluargas.print', compact('keluarga', 'kepala'))->render();
        
        $pdf->SetTitle('Detail Kartu Keluarga - ' . $keluarga->no_kk);
        $pdf->WriteHTML($html);
        
        return response($pdf->Output('Detail_KK_' . $keluarga->no_kk . '.pdf', 'I'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
