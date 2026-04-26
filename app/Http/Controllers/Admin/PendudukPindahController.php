<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendudukPindahRequest;
use App\Models\Dusun;
use App\Models\Penduduk;
use App\Models\PendudukPindah;
use App\Models\References\RefJenisKelamin;
use App\Models\References\RefStatusDasar;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

class PendudukPindahController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $period = $request->string('period')->toString() ?: 'all';
        $entriesOptions = [10, 25, 50, 100];
        $entries = (int) $request->input('entries', 10);
        $dusunId = $request->input('dusun_id');

        if (! in_array($entries, $entriesOptions, true)) {
            $entries = 10;
        }

        $records = PendudukPindah::query()
            ->with([
                'penduduk' => function ($query) {
                    $query->select('id', 'nik', 'nama', 'foto_profil', 'jenis_kelamin_id', 'status_dasar_id', 'dusun_id', 'rw_id', 'rt_id', 'tempat_lahir', 'tanggal_lahir')
                        ->with(['jenisKelamin:id,nama', 'statusDasar:id,nama', 'dusun:id,nama', 'rw:id,nomor', 'rt:id,nomor']);
                }
            ])
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
                    $query->whereDate('tanggal_pindah', '>=', $date);
                }
            })
            ->when($dusunId, function ($query) use ($dusunId) {
                $query->whereHas('penduduk', function ($inner) use ($dusunId) {
                    $inner->where('dusun_id', $dusunId);
                });
            })
            ->orderByDesc('tanggal_pindah')
            ->paginate($entries)
            ->withQueryString();

        $metrics = $this->buildMetrics();

        return view('admin.penduduk-pindah.index', [
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
            'dusunOptions' => Dusun::orderBy('nama')->get(['id', 'nama']),
            'filters' => [
                'search' => $search,
                'period' => $period,
                'entries' => $entries,
                'dusun_id' => $dusunId,
            ],
            'metrics' => $metrics,
            'resetSearchUrl' => route('admin.penduduk-pindah.index'),
        ]);
    }

    private function buildMetrics(): array
    {
        $totalCount = PendudukPindah::count();
        $thisMonth = PendudukPindah::whereMonth('tanggal_pindah', now()->month)
            ->whereYear('tanggal_pindah', now()->year)
            ->count();
        $thisYear = PendudukPindah::whereYear('tanggal_pindah', now()->year)->count();

        // Previous month for comparison
        $lastMonth = PendudukPindah::whereMonth('tanggal_pindah', now()->subMonth()->month)
            ->whereYear('tanggal_pindah', now()->subMonth()->year)
            ->count();

        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (($thisMonth - $lastMonth) / $lastMonth) * 100;
        } elseif ($thisMonth > 0) {
            $growth = 100;
        }

        return [
            'overview' => [
                'total' => $totalCount,
                'this_month' => $thisMonth,
                'this_year' => $thisYear,
                'growth' => round($growth, 1),
            ],
            'gender' => $this->buildGenderMetrics($totalCount),
            'reasons' => $this->buildReasonMetrics($totalCount),
            'destinations' => $this->buildDestinationMetrics($totalCount),
        ];
    }

    private function buildGenderMetrics(int $totalCount): array
    {
        $rows = DB::table('penduduk_pindahs')
            ->join('penduduks', 'penduduks.id', '=', 'penduduk_pindahs.penduduk_id')
            ->join('ref_jenis_kelamin', 'ref_jenis_kelamin.id', '=', 'penduduks.jenis_kelamin_id')
            ->selectRaw('ref_jenis_kelamin.nama as label, COUNT(*) as total')
            ->groupBy('label')
            ->get();

        return [
            'title' => 'Demografi Jenis Kelamin',
            'icon' => 'fa-venus-mars',
            'color' => 'primary',
            'items' => $rows->map(fn($row) => [
                'label' => $row->label,
                'value' => $row->total,
                'percent' => $totalCount > 0 ? ($row->total / $totalCount) * 100 : 0,
                'color' => str_contains(strtolower($row->label), 'laki') ? 'blue' : 'rose',
            ])->all()
        ];
    }

    private function buildReasonMetrics(int $totalCount): array
    {
        $rows = DB::table('penduduk_pindahs')
            ->selectRaw('COALESCE(alasan_pindah, "Lainnya") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'title' => 'Alasan Kepindahan',
            'icon' => 'fa-door-open',
            'color' => 'indigo',
            'items' => $rows->map(fn($row) => [
                'label' => $row->label,
                'value' => $row->total,
                'percent' => $totalCount > 0 ? ($row->total / $totalCount) * 100 : 0,
                'color' => 'indigo',
            ])->all()
        ];
    }

    private function buildDestinationMetrics(int $totalCount): array
    {
        $rows = DB::table('penduduk_pindahs')
            ->selectRaw('COALESCE(kabupaten_tujuan, "Lainnya") as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(4)
            ->get();

        return [
            'title' => 'Wilayah Tujuan Populer',
            'icon' => 'fa-map-location-dot',
            'color' => 'amber',
            'items' => $rows->map(fn($row) => [
                'label' => $row->label,
                'value' => $row->total,
                'percent' => $totalCount > 0 ? ($row->total / $totalCount) * 100 : 0,
                'color' => 'amber',
            ])->all()
        ];
    }

    public function create(): View
    {
        return view('admin.penduduk-pindah.create', [
            'pendudukOptions' => $this->eligiblePenduduk()->get(['id', 'nama', 'nik']),
        ]);
    }

    public function store(PendudukPindahRequest $request): RedirectResponse
    {
        $data = Arr::map($request->validated(), fn ($value) => $value === '' ? null : $value);

        $record = PendudukPindah::create($data);

        $this->markPendudukStatus($record->penduduk, 'PINDAH');

        ActivityLogger::log('penduduk.pindah.created', $record, 'Penduduk tercatat pindah', [
            'penduduk' => $record->penduduk?->nama,
            'tanggal' => optional($record->tanggal_pindah)->format('Y-m-d'),
        ]);

        return redirect($request->input('redirect', route('admin.penduduk-pindah.index')))
            ->with('status', 'Data perpindahan berhasil ditambahkan.');
    }

    public function edit(PendudukPindah $pendudukPindah): View
    {
        return view('admin.penduduk-pindah.edit', [
            'record' => $pendudukPindah->load('penduduk'),
            'pendudukOptions' => $this->eligiblePenduduk($pendudukPindah->penduduk_id)->get(['id', 'nama', 'nik']),
        ]);
    }

    public function update(PendudukPindahRequest $request, PendudukPindah $pendudukPindah): RedirectResponse
    {
        $previousPendudukId = $pendudukPindah->penduduk_id;

        $data = Arr::map($request->validated(), fn ($value) => $value === '' ? null : $value);

        $pendudukPindah->update($data);
        $pendudukPindah->refresh();

        if ($previousPendudukId !== $pendudukPindah->penduduk_id) {
            $this->maybeResetStatus($previousPendudukId);
        }

        $this->markPendudukStatus($pendudukPindah->penduduk, 'PINDAH');

        ActivityLogger::log('penduduk.pindah.updated', $pendudukPindah, 'Data perpindahan diperbarui', [
            'penduduk' => $pendudukPindah->penduduk?->nama,
        ]);

        return redirect($request->input('redirect', route('admin.penduduk-pindah.index')))
            ->with('status', 'Data perpindahan berhasil diperbarui.');
    }

    public function destroy(Request $request, PendudukPindah $pendudukPindah): RedirectResponse
    {
        $penduduk = $pendudukPindah->penduduk;
        $payload = [
            'penduduk' => $penduduk?->nama,
            'tanggal' => optional($pendudukPindah->tanggal_pindah)->format('Y-m-d'),
        ];

        $pendudukPindah->delete();

        $this->maybeResetStatus($penduduk?->id);

        ActivityLogger::log('penduduk.pindah.deleted', $pendudukPindah, 'Data perpindahan dihapus', $payload);

        return redirect($request->input('redirect', route('admin.penduduk-pindah.index')))
            ->with('status', 'Data perpindahan berhasil dihapus.');
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

        if ($penduduk->pendudukPindahs_count === 0) {
            $this->markPendudukStatus($penduduk, 'HIDUP');
        }
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

        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Penduduk Pindah');
        $filename = 'penduduk_pindah.xlsx';

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
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, 'Penduduk Pindah');
        $tempFile = tempnam(sys_get_temp_dir(), 'pindah_pdf_') . '.pdf';
        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'penduduk_pindah.pdf', ['Content-Type' => 'application/pdf'])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);
        $columns = $this->exportColumns();
        $rows = $this->exportRows();

        $phpWord = $this->buildWordDocument($columns, $rows, 'Penduduk Pindah');
        $tempFile = tempnam(sys_get_temp_dir(), 'pindah_word_') . '.docx';
        WordIOFactory::createWriter($phpWord, 'Word2007')->save($tempFile);

        return response()->download($tempFile, 'penduduk_pindah.docx', [
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
            'tanggal_pindah' => 'Tanggal Pindah',
            'alasan_pindah' => 'Alasan Pindah',
            'alamat_tujuan' => 'Alamat Tujuan',
            'desa_tujuan' => 'Desa Tujuan',
            'kecamatan_tujuan' => 'Kecamatan Tujuan',
            'kabupaten_tujuan' => 'Kabupaten Tujuan',
            'provinsi_tujuan' => 'Provinsi Tujuan',
            'keterangan' => 'Keterangan',
        ];
    }

    private function exportRows(): array
    {
        $records = PendudukPindah::query()
            ->with('penduduk:id,nik,nama,no_kk')
            ->orderByDesc('tanggal_pindah')
            ->get();

        $rows = [];
        foreach ($records as $index => $record) {
            $rows[] = [
                'no' => $index + 1,
                'nik' => $record->penduduk?->nik ?? '-',
                'nama' => $record->penduduk?->nama ?? '-',
                'no_kk' => $record->penduduk?->no_kk ?? '-',
                'tanggal_pindah' => $record->tanggal_pindah?->format('d-m-Y') ?? '-',
                'alasan_pindah' => $record->alasan_pindah ?? '-',
                'alamat_tujuan' => $record->alamat_tujuan ?? '-',
                'desa_tujuan' => $record->desa_tujuan ?? '-',
                'kecamatan_tujuan' => $record->kecamatan_tujuan ?? '-',
                'kabupaten_tujuan' => $record->kabupaten_tujuan ?? '-',
                'provinsi_tujuan' => $record->provinsi_tujuan ?? '-',
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

        $title = 'Data Penduduk Pindah';
        $subtitle = 'Desa Tanjung Kesuma';
        $dateLine = 'diunduh: ' . now()->translatedFormat('d M Y');

        $sheet->mergeCells("A1:{$lastColumnLetter}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);

        $midIndex = max(1, (int) floor($totalColumns / 2));
        if ($midIndex >= $totalColumns) {
            $midIndex = max(1, $totalColumns - 1);
        }
        $leftEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($midIndex);
        $rightStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($midIndex + 1);

        $sheet->mergeCells("A2:{$leftEnd}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells("{$rightStart}2:{$lastColumnLetter}2");
        $sheet->setCellValue("{$rightStart}2", $dateLine);
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
            'nik' => 20,
            'nama' => 22,
            'no_kk' => 18,
            'tanggal_pindah' => 14,
            'alasan_pindah' => 50,
            'alamat_tujuan' => 50,
            'desa_tujuan' => 14,
            'kecamatan_tujuan' => 16,
            'kabupaten_tujuan' => 20,
            'provinsi_tujuan' => 20,
            'keterangan' => 45,
        ];
        foreach ($columns as $key => $label) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $label);
            $width = $columnWidths[$key] ?? 20;
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
        $sheet->getStyle("A1:A{$dataEndRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setSelectedCell("A{$headerRow}");

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
            'marginLeft' => 450,
            'marginRight' => 450,
            'marginTop' => 450,
            'marginBottom' => 450,
        ]);

        $totalColumns = count($columns);
        $tableWidth = WordConverter::cmToTwip(33);
        $colWidth = (int) floor($tableWidth / max($totalColumns, 1));

        $columnWidths = [
            'no' => WordConverter::cmToTwip(2.2),
            'nik' => WordConverter::cmToTwip(6),
            'nama' => WordConverter::cmToTwip(7),
            'no_kk' => WordConverter::cmToTwip(5.2),
            'tanggal_pindah' => WordConverter::cmToTwip(4.2),
            'alasan_pindah' => WordConverter::cmToTwip(9),
            'alamat_tujuan' => WordConverter::cmToTwip(10),
            'desa_tujuan' => WordConverter::cmToTwip(4.5),
            'kecamatan_tujuan' => WordConverter::cmToTwip(5.5),
            'kabupaten_tujuan' => WordConverter::cmToTwip(5.5),
            'provinsi_tujuan' => WordConverter::cmToTwip(5.5),
            'keterangan' => WordConverter::cmToTwip(8.5),
        ];

        $section->addText('Data Penduduk Pindah', ['bold' => true, 'size' => 18], ['spaceAfter' => 80, 'alignment' => 'left']);
        $headerTable = $section->addTable([
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
            'layout' => WordTableStyle::LAYOUT_FIXED,
            'borderSize' => 0,
        ]);
        $headerTable->addRow();
        $headerTable->addCell((int) ($tableWidth * 0.6))->addText('Desa Tanjung Kesuma', ['size' => 12], ['spaceAfter' => 0, 'alignment' => 'left']);
        $headerTable->addCell((int) ($tableWidth * 0.4))->addText(
            'diunduh: ' . now()->translatedFormat('d M Y'),
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
        $phpWord->addTableStyle('PindahTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('PindahTable');
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
                if (in_array($key, ['no'], true)) {
                    $paraStyle['alignment'] = 'center';
                }
                $table->addCell($colWidth)->addText((string) ($row[$key] ?? ''), $bodyFont, $paraStyle);
            }
        }

        return $phpWord;
    }
}
