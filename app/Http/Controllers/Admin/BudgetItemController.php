<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BudgetItem;
use App\Models\PublicInfoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

class BudgetItemController extends Controller
{
    protected array $categories = [
        BudgetItem::CATEGORY_PELAKSANAAN => 'Pelaksanaan',
        BudgetItem::CATEGORY_PENDAPATAN => 'Pendapatan',
        BudgetItem::CATEGORY_PEMBELANJAAN => 'Pembelanjaan',
    ];

    public function index(Request $request): View
    {
        $selectedYear = $request->integer('year') ?: now()->year;
        $selectedCategory = $request->input('category');
        $search = trim((string) $request->input('search', ''));
        $perPage = max(5, min(200, $request->integer('per_page', 20)));

        $years = BudgetItem::select('year')->distinct()->orderByDesc('year')->pluck('year')->toArray();
        if (empty($years)) {
            $years[] = $selectedYear;
        }

        $query = BudgetItem::ordered();

        if ($selectedYear) {
            $query->forYear($selectedYear);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subcategory', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $statsQuery = BudgetItem::query();
        if ($selectedYear) {
            $statsQuery->where('year', $selectedYear);
        }
        if ($selectedCategory) {
            $statsQuery->where('category', $selectedCategory);
        }

        $totalAnggaran = (clone $statsQuery)->sum('anggaran');
        $totalRealisasi = (clone $statsQuery)->sum('realisasi');
        $progressPercent = $totalAnggaran > 0
            ? min(100, (int) round(($totalRealisasi / $totalAnggaran) * 100))
            : 0;

        $stats = [
            'total' => $statsQuery->count(),
            'published' => (clone $statsQuery)->where('is_published', true)->count(),
            'anggaran' => $totalAnggaran,
            'realisasi' => $totalRealisasi,
            'progress_percent' => $progressPercent,
        ];

        $items = $query->paginate($perPage)->withQueryString();

        $activeYear = Schema::hasColumn('budget_items', 'is_active_year')
            ? BudgetItem::where('is_active_year', true)->value('year')
            : null;

        $showBudgetSection = Schema::hasColumn('public_info_settings', 'show_budget_section')
            ? (PublicInfoSetting::value('show_budget_section') ?? true)
            : true;

        return view('admin.budget-items.index', [
            'items' => $items,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'activeYear' => $activeYear,
            'categories' => $this->categories,
            'selectedCategory' => $selectedCategory,
            'search' => $search,
            'perPage' => $perPage,
            'stats' => $stats,
            'showBudgetSection' => $showBudgetSection,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);

        BudgetItem::create($data);

        return back()->with('status', 'Data anggaran berhasil ditambahkan.');
    }

    public function update(Request $request, BudgetItem $budgetItem): RedirectResponse
    {
        $data = $this->validatePayload($request, $budgetItem);

        $budgetItem->update($data);

        return back()->with('status', 'Data anggaran berhasil diperbarui.');
    }

    public function destroy(BudgetItem $budgetItem): RedirectResponse
    {
        $budgetItem->delete();

        return back()->with('status', 'Data anggaran berhasil dihapus.');
    }

    public function toggle(BudgetItem $budgetItem): RedirectResponse
    {
        $budgetItem->update(['is_published' => !$budgetItem->is_published]);

        return back()->with('status', 'Status publikasi diubah.');
    }

    public function toggleSection(): RedirectResponse
    {
        $setting = PublicInfoSetting::first();
        if (!$setting) {
            return back()->with('error', 'Pengaturan tidak ditemukan.');
        }
        $setting->show_budget_section = !$setting->show_budget_section;
        $setting->save();

        $status = $setting->show_budget_section
            ? 'Seksi Transparansi Anggaran ditampilkan di website.'
            : 'Seksi Transparansi Anggaran disembunyikan dari website.';

        return back()->with('status', $status);
    }

    public function exportExcel(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $year = $request->integer('year') ?: now()->year;
        [$columns, $rows] = $this->exportPayload($request);
        $title = "Desa Tanjung Kesuma Tahun $year";
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, $title);

        $tempFile = tempnam(sys_get_temp_dir(), 'budget_excel_') . '.xlsx';

        while (ob_get_level() > 0)
            ob_end_clean();

        SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="transparansi_anggaran.xlsx"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFile));

        readfile($tempFile);
        unlink($tempFile);
        exit;
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $year = $request->integer('year') ?: now()->year;
        [$columns, $rows] = $this->exportPayload($request);
        $title = "Desa Tanjung Kesuma Tahun $year";
        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, $title);
        $tempFile = tempnam(sys_get_temp_dir(), 'budget_pdf_') . '.pdf';

        while (ob_get_level() > 0)
            ob_end_clean();

        SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf')->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, 'transparansi_anggaran.pdf', [
            'Content-Type' => 'application/pdf',
            'Pragma' => 'public',
        ])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        try {
            $year = $request->integer('year') ?: now()->year;
            [$columns, $rows] = $this->exportPayload($request);
            $title = "Desa Tanjung Kesuma Tahun $year";

            // Create spreadsheet first (more reliable)
            $spreadsheet = $this->buildExportSpreadsheet($columns, $rows, $title);

            // Save as DOCX using Word2007 writer
            $tempFile = tempnam(sys_get_temp_dir(), 'budget_word_') . '.docx';

            // Use PhpWord to create a simple document
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(10);

            $section = $phpWord->addSection([
                'orientation' => 'landscape',
            ]);

            // Add title
            $section->addText(
                'TRANSPARANSI ANGGARAN',
                ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                ['alignment' => 'center', 'spaceAfter' => 100]
            );

            $section->addText(
                strtoupper($title),
                ['size' => 12, 'name' => 'Arial'],
                ['alignment' => 'center', 'spaceAfter' => 200]
            );

            // Create simple table
            $tableStyle = ['borderSize' => 6, 'borderColor' => '000000'];
            $phpWord->addTableStyle('DataTable', $tableStyle);
            $table = $section->addTable('DataTable');

            // Add header
            $table->addRow();
            foreach ($columns as $label) {
                $table->addCell(1500, ['bgColor' => '4472C4'])
                    ->addText($label, ['bold' => true, 'color' => 'FFFFFF', 'size' => 9]);
            }

            // Add data
            foreach ($rows as $row) {
                $table->addRow();
                foreach ($columns as $key => $label) {
                    $value = $row[$key] ?? '';
                    if (in_array($key, ['anggaran', 'realisasi']) && is_numeric($value)) {
                        $value = 'Rp ' . number_format($value, 0, ',', '.');
                    }
                    $table->addCell(1500)->addText((string) $value, ['size' => 9]);
                }
            }

            // Save with proper error handling
            $objWriter = WordIOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempFile);

            // Verify file was created
            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('File Word gagal dibuat atau kosong');
            }

            return response()->download($tempFile, 'transparansi_anggaran.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])->deleteFileAfterSend();

        } catch (\Exception $e) {
            \Log::error('Word export failed: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return back()->withErrors(['error' => 'Gagal membuat file Word. Silakan coba export PDF atau Excel.']);
        }
    }

    public function activateYear(Request $request): RedirectResponse
    {
        $year = $request->integer('year');
        if (!$year) {
            return back()->with('status', 'Tahun tidak valid.');
        }

        if (!Schema::hasColumn('budget_items', 'is_active_year')) {
            return back()->with('status', 'Kolom penanda tahun aktif belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        // Matikan penanda aktif pada tahun lain
        BudgetItem::where('is_active_year', true)->update(['is_active_year' => false]);

        // Pastikan data tahun yang dipilih dipublikasikan dan ditandai aktif
        BudgetItem::where('year', $year)->update(['is_published' => true, 'is_active_year' => true]);

        return back()->with('status', "Tahun {$year} diaktifkan untuk ditampilkan di beranda.");
    }

    protected function validatePayload(Request $request, ?BudgetItem $current = null): array
    {
        // Normalisasi angka sebelum validasi agar tidak gagal karena titik/koma
        $input = $request->all();
        $input['anggaran'] = $this->cleanNumber($input['anggaran'] ?? null);
        $input['realisasi'] = $this->cleanNumber($input['realisasi'] ?? null);

        $rules = [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'category' => ['required', 'in:' . implode(',', array_keys($this->categories))],
            'subcategory' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'anggaran' => ['required', 'numeric', 'min:0'],
            'realisasi' => ['required', 'numeric', 'min:0'],
            'order_no' => ['nullable', 'integer', 'min:0'],
            'icon' => ['nullable', 'string', 'max:120'],
            'is_published' => ['sometimes', 'boolean'],
            'is_active_year' => ['sometimes', 'boolean'],
        ];

        $data = validator($input, $rules)->validate();

        $data['order_no'] = $data['order_no'] ?? 0;
        $data['is_published'] = $request->has('is_published') ? (bool) $request->input('is_published') : false;
        $data['is_active_year'] = $request->boolean('is_active_year', $current?->is_active_year ?? false);

        // Jika kolom belum ada (migrasi belum dijalankan), jangan kirim field-nya
        if (!Schema::hasColumn('budget_items', 'is_active_year')) {
            unset($data['is_active_year']);
        }

        return $data;
    }

    private function cleanNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        return (float) $digits;
    }

    private function exportColumns(): array
    {
        return [
            'no' => 'No',
            'year' => 'Tahun',
            'category' => 'Kategori',
            'subcategory' => 'Subkategori',
            'description' => 'Deskripsi',
            'anggaran' => 'Anggaran',
            'realisasi' => 'Realisasi',
            'progress' => 'Progress',
        ];
    }

    private function exportRows(Request $request): array
    {
        $selectedYear = $request->integer('year');
        $selectedCategory = $request->input('category');
        $search = trim((string) $request->input('search', ''));

        $query = BudgetItem::ordered();

        if ($selectedYear) {
            $query->forYear($selectedYear);
        }

        if ($selectedCategory) {
            $query->where('category', $selectedCategory);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subcategory', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        $items = $query->get();

        return $items->map(function (BudgetItem $item, int $index) {
            $progress = $item->anggaran > 0
                ? round(($item->realisasi / $item->anggaran) * 100)
                : 0;

            return [
                'no' => $index + 1,
                'year' => $item->year,
                'category' => $this->categories[$item->category] ?? $item->category,
                'subcategory' => $item->subcategory,
                'description' => $item->description ?? '-',
                'anggaran' => $item->anggaran ?? 0,
                'realisasi' => $item->realisasi ?? 0,
                'progress' => $progress . '%',
            ];
        })->all();
    }

    private function exportPayload(Request $request): array
    {
        $columns = $this->exportColumns();
        $rows = $this->exportRows($request);

        return [$columns, $rows];
    }

    private function buildExportSpreadsheet(array $columns, array $rows, string $sheetTitle): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Anggaran');
        $sheet->getDefaultRowDimension()->setRowHeight(20);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_FOLIO)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.5)->setBottom(0.5)->setLeft(0.5)->setRight(0.5);

        $totalColumns = count($columns);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);

        // Judul & deskripsi ekspor
        $indonesianDate = $this->getIndonesianDate();

        // Line 1: Judul Utama
        $sheet->mergeCells("A1:{$lastColumnLetter}1");
        $sheet->setCellValue('A1', "TRANSPARANSI ANGGARAN");
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
        $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Line 2: Sub Judul dengan Tahun
        $sheet->mergeCells("A2:{$lastColumnLetter}2");
        $sheet->setCellValue('A2', strtoupper($sheetTitle));
        $sheet->getStyle("A2")->getFont()->setSize(13)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));
        $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(25);

        // Line 3: Tanggal
        $sheet->mergeCells("A3:{$lastColumnLetter}3");
        $sheet->setCellValue('A3', $indonesianDate);
        $sheet->getStyle("A3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A3")->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));
        $sheet->getRowDimension(3)->setRowHeight(20);

        $colWidths = [
            'no' => 5,
            'year' => 8,
            'category' => 16,
            'subcategory' => 32,
            'description' => 42,
            'anggaran' => 20,
            'realisasi' => 20,
            'progress' => 10,
        ];

        $colIndex = 1;
        $headerRow = 5;
        foreach ($columns as $key => $label) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $label);
            $sheet->getColumnDimension($colLetter)->setWidth($colWidths[$key] ?? 20);
            $colIndex++;
        }

        // Modern header styling
        $sheet->getStyle("A{$headerRow}:{$lastColumnLetter}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '3B82F6']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '2563EB']
                ]
            ]
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(35);

        $rowIndex = $headerRow + 1;
        foreach ($rows as $index => $row) {
            $colIndex = 1;
            foreach ($columns as $key => $label) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $value = $row[$key] ?? '';
                $sheet->setCellValue("{$colLetter}{$rowIndex}", $value);

                // Alignment khusus
                if (in_array($key, ['no', 'year', 'progress'])) {
                    $sheet->getStyle("{$colLetter}{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } elseif (in_array($key, ['anggaran', 'realisasi'])) {
                    $sheet->getStyle("{$colLetter}{$rowIndex}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("{$colLetter}{$rowIndex}")->getNumberFormat()->setFormatCode('#,##0');
                }

                $colIndex++;
            }

            // Alternating row colors for better readability
            $rowColor = ($index % 2 === 0) ? 'F8FAFC' : 'FFFFFF';
            $sheet->getStyle("A{$rowIndex}:{$lastColumnLetter}{$rowIndex}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $rowColor]],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E2E8F0']
                    ]
                ]
            ]);
            $sheet->getRowDimension($rowIndex)->setRowHeight(22);
            $rowIndex++;
        }

        return $spreadsheet;
    }

    private function buildWordDocument(array $columns, array $rows, string $title): PhpWord
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        // F4 Landscape (33cm x 21cm)
        $section = $phpWord->addSection([
            'orientation' => 'landscape',
            'pageSizeW' => WordConverter::cmToTwip(33), // F4 width
            'pageSizeH' => WordConverter::cmToTwip(21), // F4 height
            'marginLeft' => WordConverter::cmToTwip(1.5),
            'marginRight' => WordConverter::cmToTwip(1.5),
            'marginTop' => WordConverter::cmToTwip(1.5),
            'marginBottom' => WordConverter::cmToTwip(1.5),
        ]);

        $indonesianDate = $this->getIndonesianDate();

        $section->addText("TRANSPARANSI ANGGARAN", ['size' => 14, 'bold' => true, 'name' => 'Arial'], ['alignment' => 'center', 'spaceAfter' => 100]);
        $section->addText(strtoupper($title), ['size' => 12, 'name' => 'Arial'], ['alignment' => 'center', 'spaceAfter' => 100]);
        $section->addText("Tanggal : " . $indonesianDate, ['italic' => true, 'size' => 10, 'name' => 'Arial'], ['alignment' => 'right', 'spaceAfter' => 200]);

        // Adjusted column widths for F4 landscape (Total available ~18000 twips)
        $colWidths = [
            'no' => 600,
            'year' => 1000,
            'category' => 2000,
            'subcategory' => 4000,
            'description' => 5000,
            'anggaran' => 2200,
            'realisasi' => 2200,
            'progress' => 1000,
        ];

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
        ];
        $phpWord->addTableStyle('BudgetTable', $tableStyle);
        $table = $section->addTable('BudgetTable');

        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 10, 'name' => 'Arial'];
        $headerPara = ['alignment' => 'center', 'spaceAfter' => 0];

        $table->addRow(400);
        foreach ($columns as $key => $label) {
            $cell = $table->addCell($colWidths[$key] ?? 1400, ['bgColor' => '1D4ED8', 'valign' => 'center']);
            $cell->addText(strtoupper($label), $headerFont, $headerPara);
        }

        $bodyFont = ['size' => 9, 'name' => 'Arial'];
        foreach ($rows as $row) {
            $table->addRow(300);
            foreach ($columns as $key => $label) {
                $cell = $table->addCell($colWidths[$key] ?? 1400, ['valign' => 'center']);
                $cellText = (string) ($row[$key] ?? '');

                if (in_array($key, ['anggaran', 'realisasi']) && is_numeric($cellText)) {
                    $cellText = 'Rp ' . number_format($cellText, 0, ',', '.');
                }

                $paraStyle = ['spaceAfter' => 0];
                if (in_array($key, ['no', 'year', 'progress'])) {
                    $paraStyle['alignment'] = 'center';
                } elseif (in_array($key, ['anggaran', 'realisasi'])) {
                    $paraStyle['alignment'] = 'right';
                }

                $cell->addText($cellText, $bodyFont, $paraStyle);
            }
        }

        return $phpWord;
    }

    private function getIndonesianDate(): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $months = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember'
        ];

        $now = now();
        $dayNum = $now->format('l');
        $monthNum = $now->format('F');

        return ($days[$dayNum] ?? $dayNum) . ', ' . $now->format('d') . ' ' . ($months[$monthNum] ?? $monthNum) . ' ' . $now->format('Y');
    }
}
