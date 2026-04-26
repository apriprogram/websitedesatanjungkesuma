<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendudukRequest;
use App\Models\Dusun;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\References\RefAgama;
use App\Models\References\RefCaraKb;
use App\Models\References\RefCacat;
use App\Models\References\RefGolonganDarah;
use App\Models\References\RefHubKeluarga;
use App\Models\References\RefJenisKelamin;
use App\Models\References\RefPekerjaan;
use App\Models\References\RefPendidikan;
use App\Models\References\RefStatusDasar;
use App\Models\References\RefStatusKawin;
use App\Models\References\RefStatusRekam;
use App\Models\References\RefSuku;
use App\Models\References\RefWarganegara;
use App\Models\Rw;
use App\Models\Rt;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf as PdfWriterMpdf;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter as WordConverter;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\Style\Table as WordTableStyle;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PendudukController extends Controller
{
    private const DEFAULT_ENTRIES = 10;

    private const ALLOWED_ENTRIES = [10, 25, 50, 100];

    /**
     * Normalised header => internal key mapping for Excel import.
     *
     * @var array<string,string>
     */
    private const IMPORT_COLUMN_MAP = [
        'id sistem' => 'id',
        'id sistem (jangan diubah)' => 'id',
        'id' => 'id',
        'no kk' => 'no_kk',
        'no_kk' => 'no_kk',
        'nik' => 'nik',
        'nama' => 'nama',
        'nama lengkap' => 'nama',
        'jenis kelamin id' => 'jenis_kelamin_id',
        'jenis kelamin' => 'jenis_kelamin',
        'jenis_kelamin' => 'jenis_kelamin',
        'tempat lahir' => 'tempat_lahir',
        'tanggal lahir' => 'tanggal_lahir',
        'agama id' => 'agama_id',
        'agama' => 'agama',
        'pendidikan kk id' => 'pendidikan_kk_id',
        'pendidikan kk' => 'pendidikan_kk',
        'pendidikan (kk)' => 'pendidikan_kk',
        'pendidikan sedang id' => 'pendidikan_sedang_id',
        'pendidikan sedang' => 'pendidikan_sedang',
        'pendidikan aktif' => 'pendidikan_sedang',
        'pekerjaan id' => 'pekerjaan_id',
        'pekerjaan' => 'pekerjaan',
        'status kawin id' => 'status_kawin_id',
        'status kawin' => 'status_kawin',
        'kk level id' => 'kk_level_id',
        'hubungan kk id' => 'kk_level_id',
        'hubungan kk' => 'kk_level',
        'hubungan' => 'kk_level',
        'warganegara id' => 'warganegara_id',
        'warganegara' => 'warganegara',
        'kewarganegaraan' => 'warganegara',
        'nama ayah' => 'nama_ayah',
        'ayah nik' => 'ayah_nik',
        'nik ayah' => 'ayah_nik',
        'nama ibu' => 'nama_ibu',
        'ibu nik' => 'ibu_nik',
        'nik ibu' => 'ibu_nik',
        'golongan darah id' => 'golongan_darah_id',
        'golongan darah' => 'golongan_darah',
        'akta lahir' => 'akta_lahir',
        'dokumen paspor' => 'dokumen_pasport',
        'tanggal akhir paspor' => 'tanggal_akhir_paspor',
        'dokumen kitas' => 'dokumen_kitas',
        'akta perkawinan' => 'akta_perkawinan',
        'tanggal perkawinan' => 'tanggal_perkawinan',
        'akta perceraian' => 'akta_perceraian',
        'tanggal perceraian' => 'tanggal_perceraian',
        'cacat id' => 'cacat_id',
        'cacat' => 'cacat',
        'jenis cacat' => 'cacat',
        'cara kb id' => 'cara_kb_id',
        'cara kb' => 'cara_kb',
        'hamil' => 'hamil',
        'sedang hamil' => 'hamil',
        'ktp el' => 'ktp_el',
        'memiliki ktp el' => 'ktp_el',
        'memiliki ktp-el' => 'ktp_el',
        'status rekam id' => 'status_rekam_id',
        'status rekam' => 'status_rekam',
        'alamat' => 'alamat',
        'alamat sekarang' => 'alamat_sekarang',
        'alamat saat ini' => 'alamat_sekarang',
        'dusun id' => 'dusun_id',
        'dusun' => 'dusun',
        'rw id' => 'rw_id',
        'rw' => 'rw',
        'rt id' => 'rt_id',
        'rt' => 'rt',
        'status dasar id' => 'status_dasar_id',
        'status dasar' => 'status_dasar',
        'suku id' => 'suku_id',
        'suku' => 'suku',
        'tag id card' => 'tag_id_card',
        'id asuransi' => 'id_asuransi',
        'no asuransi' => 'no_asuransi',
        'alamat domisili' => 'alamat',
        'dibuat pada' => 'created_at',
        'diperbarui pada' => 'updated_at',
    ];

    /**
     * Header kolom yang wajib tersedia pada templat impor.
     *
     * @var array<string,array<int,string>>
     */
    private const REQUIRED_IMPORT_HEADERS = [
        'No. KK' => ['no_kk'],
        'NIK' => ['nik'],
        'Nama Lengkap' => ['nama'],
        'Jenis Kelamin' => ['jenis_kelamin', 'jenis_kelamin_id'],
        'Status Kawin' => ['status_kawin', 'status_kawin_id'],
        'Hubungan KK' => ['kk_level', 'kk_level_id'],
        'Kewarganegaraan' => ['warganegara', 'warganegara_id'],
        'Dusun' => ['dusun', 'dusun_id'],
        'RW' => ['rw', 'rw_id'],
        'RT' => ['rt', 'rt_id'],
        'Status Dasar' => ['status_dasar', 'status_dasar_id'],
    ];

    /**
     * Export columns in desired order.
     *
     * @var array<string,string>
     */
    private const EXPORT_COLUMNS = [
        'id' => 'ID Sistem',
        'no_kk' => 'No. KK',
        'nik' => 'NIK',
        'nama' => 'Nama Lengkap',
        'nomor_hp' => 'No. HP',
        'email' => 'Email',
        'jenis_kelamin' => 'Jenis Kelamin',
        'tempat_lahir' => 'Tempat Lahir',
        'tanggal_lahir' => 'Tanggal Lahir',
        'agama' => 'Agama',
        'pendidikan_kk' => 'Pendidikan (KK)',
        'pendidikan_sedang' => 'Pendidikan Aktif',
        'pekerjaan' => 'Pekerjaan',
        'status_kawin' => 'Status Kawin',
        'kk_level' => 'Hubungan KK',
        'warganegara' => 'Kewarganegaraan',
        'alamat' => 'Alamat Domisili',
        'alamat_sekarang' => 'Alamat Saat Ini',
        'dusun' => 'Dusun',
        'rw' => 'RW',
        'rt' => 'RT',
        'status_dasar' => 'Status Dasar',
        'golongan_darah' => 'Golongan Darah',
        'status_rekam' => 'Status Rekam',
        'hamil' => 'Sedang Hamil',
        'ktp_el' => 'Memiliki KTP-el',
        'ayah_nik' => 'NIK Ayah',
        'nama_ayah' => 'Nama Ayah',
        'ibu_nik' => 'NIK Ibu',
        'nama_ibu' => 'Nama Ibu',
        'cacat' => 'Jenis Cacat',
        'cara_kb' => 'Cara KB',
        'suku' => 'Suku',
        'tag_id_card' => 'Tag ID Card',
        'id_asuransi' => 'ID Asuransi',
        'no_asuransi' => 'No. Asuransi',
        'akta_lahir' => 'Akta Lahir',
        'dokumen_pasport' => 'Dokumen Paspor',
        'tanggal_akhir_paspor' => 'Tanggal Akhir Paspor',
        'dokumen_kitas' => 'Dokumen KITAS',
        'akta_perkawinan' => 'Akta Perkawinan',
        'tanggal_perkawinan' => 'Tanggal Perkawinan',
        'akta_perceraian' => 'Akta Perceraian',
        'tanggal_perceraian' => 'Tanggal Perceraian',
    ];

    /** @var array<class-string<\Illuminate\Database\Eloquent\Model>,array<string,int>> */
    private array $referenceCache = [];

    /** @var array<string,int> */
    private array $dusunCache = [];

    /** @var array<string,int> */
    private array $rwCache = [];

    /** @var array<string,int> */
    private array $rtCache = [];

    /** @var array<string,int|null> */
    private array $defaultReferenceCache = [];

    /** @var array<string,int|null>|null */
    private ?array $defaultTerritory = null;

    public function index(Request $request): View
    {
        $filters = $this->buildFilters($request);

        $penduduks = $this->pendudukQuery($filters)
            ->paginate($filters['entries'])
            ->withQueryString();

        $options = $this->sharedFormData();
        $statusSummary = $this->buildStatusSummary();
        $statusOptions = collect($options['statusDasarOptions'] ?? []);
        $metrics = $this->buildMetrics($statusSummary, $statusOptions);
        $lastUpdated = Penduduk::query()->latest('updated_at')->value('updated_at');

        return view('admin.penduduks.index', [
            'penduduks' => $penduduks,
            'filters' => $filters,
            'entriesOptions' => self::ALLOWED_ENTRIES,
            'metrics' => $metrics,
            'statusSummary' => $statusSummary,
            'lastUpdated' => $lastUpdated ? Carbon::parse($lastUpdated) : null,
        ] + $options);
    }

    public function create(): View
    {
        return view('admin.penduduks.create', $this->sharedFormData());
    }

    public function store(PendudukRequest $request): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated());
        $uploadPayload = $this->handleUploadedPhotos($request);

        $penduduk = Penduduk::create($payload + $uploadPayload);

        ActivityLogger::log('penduduk.created', $penduduk, 'Penduduk baru ditambahkan', [
            'nik' => $penduduk->nik,
            'nama' => $penduduk->nama,
        ]);

        return redirect()
            ->route('admin.penduduks.index')
            ->with('status', 'Data penduduk berhasil ditambahkan.')
            ->with('status_variant', 'success')
            ->with('status_description', 'Penduduk baru telah ditambahkan ke daftar.');
    }

    public function edit(Penduduk $penduduk): View
    {
        $penduduk->load([
            'keluarga:id,no_kk',
            'jenisKelamin:id,nama',
            'agama:id,nama',
            'pendidikanKk:id,nama',
            'pendidikanSedang:id,nama',
            'pekerjaan:id,nama',
            'statusKawin:id,nama',
            'kkLevel:id,nama',
            'warganegara:id,nama',
            'golonganDarah:id,nama',
            'statusRekam:id,nama',
            'cacat:id,nama',
            'caraKb:id,nama',
            'statusDasar:id,nama',
            'dusun:id,nama',
            'rw:id,nomor',
            'rt:id,nomor',
            'suku:id,nama',
        ]);

        return view('admin.penduduks.edit', [
            'penduduk' => $penduduk,
        ] + $this->sharedFormData());
    }

    public function update(PendudukRequest $request, Penduduk $penduduk): RedirectResponse
    {
        $payload = $this->preparePayload($request->validated());
        $uploadPayload = $this->handleUploadedPhotos($request, $penduduk);

        $penduduk->update($payload + $uploadPayload);

        ActivityLogger::log('penduduk.updated', $penduduk, 'Data penduduk diperbarui', [
            'nik' => $penduduk->nik,
        ]);

        return redirect()
            ->route('admin.penduduks.index')
            ->with('status', 'Data penduduk berhasil diperbarui.')
            ->with('status_variant', 'success')
            ->with('status_description', 'Perubahan data tersimpan dan dapat dilihat pada daftar.');
    }

    public function destroy(Penduduk $penduduk): RedirectResponse
    {
        $payload = [
            'nik' => $penduduk->nik,
            'nama' => $penduduk->nama,
        ];

        $this->deletePhotoFiles($penduduk);
        $penduduk->delete();

        ActivityLogger::log('penduduk.deleted', $penduduk, 'Data penduduk dihapus', $payload);

        return redirect()
            ->route('admin.penduduks.index')
            ->with('status', 'Data penduduk berhasil dihapus.')
            ->with('status_variant', 'warning')
            ->with('status_description', 'Data dipindahkan dari daftar aktif.');
    }

    public function import(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        try {
            $validated = $request->validate([
                'file' => ['required', 'file', 'mimes:xlsx,xls'],
            ]);
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Berkas impor tidak valid.',
                    'errors' => Arr::flatten($exception->errors()),
                ], 422);
            }

            throw $exception;
        }

        $file = $validated['file'];

        try {
            $spreadsheet = SpreadsheetIOFactory::load($file->getPathname());
        } catch (\Throwable $exception) {
            return $this->importFailureResponse($request, [
                'Berkas Excel tidak dapat dibaca. Pastikan menggunakan file .xls atau .xlsx yang valid dan tidak rusak.',
            ]);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows)) {
            return $this->importFailureResponse($request, ['Berkas impor tidak memiliki data.']);
        }

        // Smart Header Detection: Scan first 10 rows
        $headerMap = [];
        $headerRowIndex = 0;

        // Rows are 1-indexed in PhpSpreadsheet toArray result if keys preserved? 
        // toArray(null, true, true, true) returns [1 => [...], 2 => [...]] presumably.
        // Let's iterate carefully.
        foreach ($rows as $index => $row) {
            if ($index > 10)
                break; // Limit scan depth

            $map = $this->buildHeaderMap($row);
            $mappedColumns = array_values($map);

            // Check for critical columns to confirm this is the header row
            if (in_array('nik', $mappedColumns) && (in_array('nama', $mappedColumns) || in_array('no_kk', $mappedColumns))) {
                $headerMap = $map;
                $headerRowIndex = $index;
                break;
            }
        }

        if (empty($headerMap)) {
            return $this->importFailureResponse($request, [
                'Header Excel tidak dikenali. Sistem mencari kolom "NIK" dan "Nama Lengkap" pada 10 baris pertama.',
                'Pastikan Anda menggunakan templat ekspor terbaru.'
            ]);
        }

        $missingHeaders = $this->missingImportHeaders($headerMap);
        if (!empty($missingHeaders)) {
            $errors = array_map(
                static fn(string $label) => "Kolom '{$label}' wajib ada pada baris header. Gunakan templat terbaru.",
                $missingHeaders
            );

            return $this->importFailureResponse($request, $errors);
        }

        $this->warmTerritoryCaches();

        $result = [
            'created' => 0,
            'updated' => 0,
            'deleted' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $importedIds = [];

        // Process rows AFTER the header row
        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex <= $headerRowIndex) {
                continue;
            }

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            try {
                $data = $this->normaliseImportRow($headerMap, $row);

                // Skip if critical data is missing even after normalization
                if (empty($data['nik']) || empty($data['nama'])) {
                    continue;
                }

                $payload = $this->transformImportRow($data);

                DB::transaction(function () use (&$result, &$importedIds, $payload) {
                    $existing = null;
                    if (!empty($payload['id'])) {
                        $existing = Penduduk::find($payload['id']);
                    }
                    if (!$existing && !empty($payload['nik'])) {
                        $existing = Penduduk::query()->where('nik', $payload['nik'])->first();
                    }

                    $saveData = $payload;
                    unset($saveData['id']);

                    if ($existing) {
                        $existing->update($saveData);
                        $importedIds[] = $existing->id;
                        $result['updated']++;
                    } else {
                        $newRecord = Penduduk::create($saveData);
                        $importedIds[] = $newRecord->id;
                        $result['created']++;
                    }
                });
            } catch (RuntimeException $exception) {
                $result['skipped']++;
                $result['errors'][] = sprintf('Baris %d: %s', $rowIndex, $exception->getMessage());
            } catch (\Exception $exception) {
                $result['skipped']++;
                $result['errors'][] = sprintf('Baris %d: Gagal memproses data. %s', $rowIndex, $exception->getMessage());
            }
        }

        // Hapus data yang tidak ada di file excel yang diimpor
        if (!empty($importedIds)) {
            $toDelete = Penduduk::whereNotIn('id', $importedIds)->get();
            $deletedCount = 0;

            foreach ($toDelete as $penduduk) {
                // Hapus file foto dari storage sebelum menghapus record database
                $this->deletePhotoFiles($penduduk);
                $penduduk->delete();
                $deletedCount++;
            }

            $result['deleted'] = $deletedCount;
        }

        if ($result['created'] || $result['updated'] || $result['deleted']) {
            ActivityLogger::log('penduduk.imported', $request->user(), 'Impor data penduduk', [
                'ditambahkan' => $result['created'],
                'diperbarui' => $result['updated'],
                'dihapus' => $result['deleted'],
                'dilewati' => $result['skipped'],
            ]);
        }

        $message = sprintf(
            'Impor selesai. Ditambahkan: %d, diperbarui: %d, dihapus: %d, dilewati: %d.',
            $result['created'],
            $result['updated'],
            $result['deleted'],
            $result['skipped']
        );

        if ($request->expectsJson()) {
            $statusCode = ($result['created'] === 0 && $result['updated'] === 0 && $result['deleted'] === 0 && !empty($result['errors'])) ? 422 : 200;

            return response()->json([
                'message' => $message,
                'summary' => [
                    'created' => $result['created'],
                    'updated' => $result['updated'],
                    'deleted' => $result['deleted'],
                    'skipped' => $result['skipped'],
                ],
                'errors' => $result['errors'],
            ], $statusCode);
        }

        $redirect = back()
            ->with('status', $message)
            ->with('status_variant', empty($result['errors']) ? 'success' : 'warning')
            ->with('status_description', 'Ringkasan impor dapat dilihat pada pesan ini.');

        if (!empty($result['errors'])) {
            $redirect = $redirect->withErrors($result['errors']);
        }

        return $redirect;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    private function importFailureResponse(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Impor gagal diproses.',
                'errors' => $errors,
            ], 422);
        }

        return back()->withErrors($errors);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $records = $this->fetchExportRecords();

        if ($records->isEmpty()) {
            $spreadsheet = $this->buildExportSpreadsheet($this->buildTemplateRows(), 'Templat Impor Penduduk');
        } else {
            $spreadsheet = $this->buildExportSpreadsheet($records, 'Data Penduduk');
        }

        return $this->streamExcel($spreadsheet, 'penduduk.xlsx');
    }

    public function exportTemplate(Request $request): StreamedResponse
    {
        abort_unless($request->user()?->is_admin, 403);

        $references = $this->sharedFormData();
        $spreadsheet = $this->buildExportSpreadsheet(
            $this->buildTemplateRows(),
            'Templat Impor Penduduk',
            $references
        );

        return $this->streamExcel($spreadsheet, 'template-import-penduduk.xlsx');
    }

    public function exportPdf(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

        $spreadsheet = $this->buildExportSpreadsheet();

        SpreadsheetIOFactory::registerWriter('Pdf', PdfWriterMpdf::class);
        $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Pdf');

        $tempFile = tempnam(sys_get_temp_dir(), 'penduduk_pdf_');
        $writer->save($tempFile);

        return response()->download($tempFile, 'penduduk.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend();
    }

    public function exportWord(Request $request)
    {
        abort_unless($request->user()?->is_admin, 403);

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

        $totalColumns = count(self::EXPORT_COLUMNS);
        $tableWidth = WordConverter::cmToTwip(33);
        $columnWidthMap = [
            'id' => WordConverter::cmToTwip(3.0),
            'no_kk' => WordConverter::cmToTwip(5.8),
            'nik' => WordConverter::cmToTwip(5.8),
            'nama' => WordConverter::cmToTwip(10),
            'jenis_kelamin' => WordConverter::cmToTwip(4.2),
            'tempat_lahir' => WordConverter::cmToTwip(5.5),
            'tanggal_lahir' => WordConverter::cmToTwip(4.8),
            'agama' => WordConverter::cmToTwip(4.6),
            'pendidikan_kk' => WordConverter::cmToTwip(5.8),
            'pendidikan_sedang' => WordConverter::cmToTwip(6),
            'pekerjaan' => WordConverter::cmToTwip(5.8),
            'status_kawin' => WordConverter::cmToTwip(5.2),
            'kk_level' => WordConverter::cmToTwip(4.8),
            'warganegara' => WordConverter::cmToTwip(8.2),
            'alamat' => WordConverter::cmToTwip(13.5),
            'alamat_sekarang' => WordConverter::cmToTwip(13.5),
            'dusun' => WordConverter::cmToTwip(4.5),
            'rw' => WordConverter::cmToTwip(3.6),
            'rt' => WordConverter::cmToTwip(3.6),
            'status_dasar' => WordConverter::cmToTwip(4.6),
            'golongan_darah' => WordConverter::cmToTwip(4.6),
            'status_rekam' => WordConverter::cmToTwip(4.6),
            'hamil' => WordConverter::cmToTwip(3.6),
            'ktp_el' => WordConverter::cmToTwip(4.2),
            'ayah_nik' => WordConverter::cmToTwip(5.2),
            'nama_ayah' => WordConverter::cmToTwip(7),
            'ibu_nik' => WordConverter::cmToTwip(5.2),
            'nama_ibu' => WordConverter::cmToTwip(7),
            'cacat' => WordConverter::cmToTwip(4.8),
            'cara_kb' => WordConverter::cmToTwip(4.8),
            'suku' => WordConverter::cmToTwip(4.8),
            'tag_id_card' => WordConverter::cmToTwip(4.8),
            'id_asuransi' => WordConverter::cmToTwip(4.8),
            'no_asuransi' => WordConverter::cmToTwip(5.4),
            'akta_lahir' => WordConverter::cmToTwip(5.2),
            'dokumen_pasport' => WordConverter::cmToTwip(5.2),
            'tanggal_akhir_paspor' => WordConverter::cmToTwip(5.2),
            'dokumen_kitas' => WordConverter::cmToTwip(5.2),
            'akta_perkawinan' => WordConverter::cmToTwip(5.2),
            'tanggal_perkawinan' => WordConverter::cmToTwip(5.2),
            'akta_perceraian' => WordConverter::cmToTwip(5.2),
            'tanggal_perceraian' => WordConverter::cmToTwip(5.2),
            'created_at' => WordConverter::cmToTwip(5.6),
            'updated_at' => WordConverter::cmToTwip(5.6),
        ];
        $colWidth = (int) floor($tableWidth / max($totalColumns, 1));

        $section->addText('Data Penduduk', ['bold' => true, 'size' => 18], ['spaceAfter' => 80]);
        $headerTable = $section->addTable([
            'width' => $tableWidth,
            'unit' => TblWidth::TWIP,
            'layout' => WordTableStyle::LAYOUT_FIXED,
            'borderSize' => 0,
        ]);
        $headerTable->addRow();
        $headerTable->addCell((int) ($tableWidth * 0.6))->addText('Desa Tanjung Kesuma', ['size' => 12], ['spaceAfter' => 0]);
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
        $phpWord->addTableStyle('PendudukTable', $tableStyle, $firstRowStyle);

        $table = $section->addTable('PendudukTable');
        $headerFont = ['bold' => true, 'color' => 'FFFFFF', 'size' => 12];
        $bodyFont = ['size' => 12];

        // header
        $table->addRow();
        foreach (self::EXPORT_COLUMNS as $key => $label) {
            $cellWidth = $columnWidthMap[$key] ?? $colWidth;
            $table->addCell($cellWidth)->addText($label, $headerFont, ['spaceAfter' => 0, 'alignment' => 'center']);
        }

        $records = $this->fetchExportRecords();
        foreach ($records as $record) {
            $row = $this->exportRow($record);
            $table->addRow();
            foreach (self::EXPORT_COLUMNS as $key => $label) {
                $paraStyle = ['spaceAfter' => 0];
                if (in_array($key, ['no', 'no_kk', 'nik', 'tanggal_lahir', 'ktp_el'], true)) {
                    $paraStyle['alignment'] = 'center';
                }
                $cellWidth = $columnWidthMap[$key] ?? $colWidth;
                $table->addCell($cellWidth)->addText((string) ($row[$key] ?? ''), $bodyFont, $paraStyle);
            }
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'penduduk_word_') . '.docx';
        $writer = WordIOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile, 'penduduk.docx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend();
    }

    /**
     * @return array<string,mixed>
     */
    private function buildFilters(Request $request): array
    {
        $entries = (int) $request->input('entries', self::DEFAULT_ENTRIES);
        if (!in_array($entries, self::ALLOWED_ENTRIES, true)) {
            $entries = self::DEFAULT_ENTRIES;
        }

        return [
            'entries' => $entries,
            'search' => trim((string) $request->input('search', '')),
            'status_dasar_id' => $request->input('status_dasar_id'),
            'dusun_id' => $request->input('dusun_id'),
        ];
    }

    /**
     * @param array<string,mixed> $filters
     */
    private function pendudukQuery(array $filters)
    {
        return Penduduk::query()
            ->with([
                'keluarga:id,no_kk',
                'jenisKelamin:id,nama',
                'agama:id,nama',
                'pendidikanKk:id,nama',
                'pendidikanSedang:id,nama',
                'pekerjaan:id,nama',
                'statusKawin:id,nama',
                'kkLevel:id,nama',
                'warganegara:id,nama',
                'golonganDarah:id,nama',
                'statusRekam:id,nama',
                'cacat:id,nama',
                'caraKb:id,nama',
                'statusDasar:id,nama',
                'dusun:id,nama',
                'rw:id,nomor',
                'rt:id,nomor',
                'suku:id,nama',
            ])
            ->when($filters['search'], function ($query) use ($filters) {
                $search = trim($filters['search']);
                $year = ctype_digit($search) && strlen($search) === 4 ? (int) $search : null;
                $query->where(function ($inner) use ($search, $year) {
                    $inner->where('nama', 'like', '%' . $search . '%')
                        ->orWhere('nik', 'like', '%' . $search . '%')
                        ->orWhere('no_kk', 'like', '%' . $search . '%')
                        ->orWhere('alamat', 'like', '%' . $search . '%')
                        ->orWhere('alamat_sekarang', 'like', '%' . $search . '%')
                        ->orWhere('tempat_lahir', 'like', '%' . $search . '%')
                        ->orWhereHas('jenisKelamin', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('agama', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('pendidikanKk', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('pendidikanSedang', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('pekerjaan', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('statusKawin', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('warganegara', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('golonganDarah', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('suku', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('dusun', fn($q) => $q->where('nama', 'like', '%' . $search . '%'))
                        ->orWhereHas('rw', fn($q) => $q->where('nomor', 'like', '%' . $search . '%'))
                        ->orWhereHas('rt', fn($q) => $q->where('nomor', 'like', '%' . $search . '%'))
                        ->orWhereHas('statusDasar', fn($q) => $q->where('nama', 'like', '%' . $search . '%'));

                    if ($year) {
                        $inner->orWhereYear('tanggal_lahir', $year);
                    }
                });
            })
            ->when($filters['status_dasar_id'], fn($query, $statusId) => $query->where('status_dasar_id', $statusId))
            ->when($filters['dusun_id'], fn($query, $dusunId) => $query->where('dusun_id', $dusunId))
            ->orderBy('nama');
    }

    /**
     * @return array<string,mixed>
     */
    private function sharedFormData(): array
    {
        return [
            'keluargaOptions' => Keluarga::query()->orderBy('no_kk')->get(['id', 'no_kk']),
            'jenisKelaminOptions' => RefJenisKelamin::query()
                ->orderBy('nama')
                ->get(['id', 'nama'])
                ->filter(fn($item) => in_array(strtoupper($item->nama), ['LAKI-LAKI', 'PEREMPUAN'], true))
                ->values(),
            'agamaOptions' => RefAgama::query()->orderBy('nama')->get(['id', 'nama']),
            'pendidikanOptions' => RefPendidikan::query()->orderBy('nama')->get(['id', 'nama']),
            'pekerjaanOptions' => RefPekerjaan::query()->orderBy('nama')->get(['id', 'nama']),
            'statusKawinOptions' => RefStatusKawin::query()->orderBy('nama')->get(['id', 'nama']),
            'hubunganKkOptions' => RefHubKeluarga::query()->orderBy('nama')->get(['id', 'nama']),
            'warganegaraOptions' => RefWarganegara::query()->orderBy('nama')->get(['id', 'nama']),
            'golonganDarahOptions' => RefGolonganDarah::query()->orderBy('nama')->get(['id', 'nama']),
            'statusRekamOptions' => RefStatusRekam::query()->orderBy('nama')->get(['id', 'nama']),
            'caraKbOptions' => RefCaraKb::query()->orderBy('nama')->get(['id', 'nama']),
            'cacatOptions' => RefCacat::query()->orderBy('nama')->get(['id', 'nama']),
            'sukuOptions' => RefSuku::query()->orderBy('nama')->get(['id', 'nama']),
            'statusDasarOptions' => RefStatusDasar::query()->orderBy('nama')->get(['id', 'nama']),
            'dusunOptions' => Dusun::query()->orderBy('nama')->get(['id', 'nama']),
            'rwOptions' => Rw::query()->with('dusun:id,nama')->orderBy('nomor')->get(['id', 'dusun_id', 'nomor']),
            'rtOptions' => Rt::query()->with('rw:id,nomor,dusun_id')->orderBy('nomor')->get(['id', 'rw_id', 'nomor']),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildStatusSummary(): array
    {
        $deadId = $this->resolveStatusId('MATI');

        $counts = Penduduk::query()
            ->select('status_dasar_id', DB::raw('COUNT(*) as total'))
            ->groupBy('status_dasar_id')
            ->pluck('total', 'status_dasar_id');

        $hidupId = $this->resolveStatusId('HIDUP');
        $meninggalId = $this->resolveStatusId('MATI');
        $pindahId = $this->resolveStatusId('PINDAH');

        return [
            'total' => (int) $counts->sum(),
            'hidup' => $hidupId ? (int) ($counts[$hidupId] ?? 0) : 0,
            'meninggal' => $meninggalId ? (int) ($counts[$meninggalId] ?? 0) : 0,
            'pindah' => $pindahId ? (int) ($counts[$pindahId] ?? 0) : 0,
            'per_status' => $counts
                ->filter(fn($total, $statusId) => $statusId !== null)
                ->map(fn($total) => (int) $total)
                ->toArray(),
        ];
    }

    /**
     * @param array<string,mixed> $summary
     * @return array<int,array<string,string>>
     */
    /**
     * @param array<string,mixed> $summary
     * @param \Illuminate\Support\Collection<int,\App\Models\References\RefStatusDasar>|array<int,\App\Models\References\RefStatusDasar> $statusOptions
     * @return array<string,mixed>
     */
    private function buildMetrics(array $summary, $statusOptions = []): array
    {
        $deadId = $this->resolveStatusId('MATI');
        $statusLookup = collect($statusOptions)->mapWithKeys(function ($item) {
            $id = $item->id ?? ($item['id'] ?? null);
            $name = $item->nama ?? ($item['nama'] ?? null);

            if ($id === null) {
                return [];
            }

            $label = $name ? (string) $name : 'Status lainnya';

            return [
                (int) $id => [
                    'name' => $label,
                    'slug' => Str::slug($label),
                ],
            ];
        });

        $statusColorMap = [
            'hidup' => 'emerald',
            'aktif' => 'emerald',
            'mati' => 'rose',
            'meninggal' => 'rose',
            'pindah' => 'amber',
            'hilang' => 'amber',
            'lahir' => 'indigo',
            'datang' => 'indigo',
            'pendatang' => 'indigo',
            'lainnya' => 'slate',
        ];

        $baseQuery = Penduduk::query()
            ->when($deadId, fn($q) => $q->where('status_dasar_id', '!=', $deadId));

        $recent = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $genderCounts = (clone $baseQuery)
            ->select('jenis_kelamin_id', DB::raw('COUNT(*) as total'))
            ->groupBy('jenis_kelamin_id')
            ->pluck('total', 'jenis_kelamin_id');

        $maleId = $this->resolveGenderId('LAKI-LAKI');
        $femaleId = $this->resolveGenderId('PEREMPUAN');

        $totalCount = (int) ($summary['total'] ?? 0);
        $maleCount = $maleId ? (int) ($genderCounts[$maleId] ?? 0) : 0;
        $femaleCount = $femaleId ? (int) ($genderCounts[$femaleId] ?? 0) : 0;

        $activeCount = (int) ($summary['hidup'] ?? 0);
        $movedCount = (int) ($summary['pindah'] ?? 0);

        $segments = [
            [
                'label' => 'Aktif / Hidup',
                'value' => $activeCount,
                'percent' => $totalCount > 0 ? ($activeCount / $totalCount) * 100 : 0,
                'color' => 'emerald',
            ],
            [
                'label' => 'Penduduk Pindah',
                'value' => $movedCount,
                'percent' => $totalCount > 0 ? ($movedCount / $totalCount) * 100 : 0,
                'color' => 'amber',
            ],
            [
                'label' => 'Tidak Aktif / Mati',
                'value' => (int) ($summary['meninggal'] ?? 0),
                'percent' => $totalCount > 0 ? (($summary['meninggal'] ?? 0) / $totalCount) * 100 : 0,
                'color' => 'rose',
            ],
        ];

        $statusItems = collect($summary['per_status'] ?? [])
            ->map(function ($value, $statusId) use ($totalCount, $statusLookup, $statusColorMap) {
                $meta = $statusLookup->get((int) $statusId, ['name' => 'Status lainnya', 'slug' => 'lainnya']);
                $slug = $meta['slug'] ?? 'lainnya';
                $slugKey = $slug;
                if (!array_key_exists($slugKey, $statusColorMap)) {
                    $parts = explode('-', $slug);
                    $slugKey = $parts[0] ?? $slug;
                }
                $color = $statusColorMap[$slugKey] ?? 'slate';

                return [
                    'status_id' => (int) $statusId,
                    'label' => $meta['name'] ?? 'Status lainnya',
                    'value' => (int) $value,
                    'percent' => $totalCount > 0 ? ($value / $totalCount) * 100 : 0,
                    'color' => $color,
                ];
            })
            ->sortByDesc('value')
            ->values()
            ->take(5)
            ->all();

        return [
            'overview' => [
                'title' => 'Total Penduduk',
                'subtitle' => 'Seluruh penduduk yang terdaftar dalam sistem.',
                'total' => $totalCount,
                'trend' => [
                    'direction' => $recent > 0 ? 'up' : 'flat',
                    'value' => $recent,
                    'label' => 'Penduduk baru 30 hari terakhir',
                ],
                'segments' => $segments,
            ],
            'status' => [
                'title' => 'Status Dasar',
                'subtitle' => 'Sebaran status kependudukan.',
                'items' => $statusItems,
            ],
            'categories' => $this->buildCategoryMetrics($totalCount, $deadId),
            'gender' => [
                'title' => 'Komposisi Gender',
                'subtitle' => 'Perbandingan penduduk berdasarkan gender.',
                'items' => [
                    [
                        'label' => 'Penduduk Laki-Laki',
                        'value' => $maleCount,
                        'percent' => $totalCount > 0 ? ($maleCount / $totalCount) * 100 : 0,
                        'icon' => 'fas fa-mars',
                        'color' => 'blue',
                    ],
                    [
                        'label' => 'Penduduk Perempuan',
                        'value' => $femaleCount,
                        'percent' => $totalCount > 0 ? ($femaleCount / $totalCount) * 100 : 0,
                        'icon' => 'fas fa-venus',
                        'color' => 'pink',
                    ],
                ],
            ],
        ];
    }

    private function resolveStatusId(string $status): ?int
    {
        return RefStatusDasar::query()
            ->whereRaw('UPPER(nama) = ?', [strtoupper($status)])
            ->value('id');
    }

    private function resolveGenderId(string $gender): ?int
    {
        return RefJenisKelamin::query()
            ->whereRaw('UPPER(nama) = ?', [strtoupper($gender)])
            ->value('id');
    }

    private function defaultReferenceId(string $model, ?string $orderBy = 'nama'): ?int
    {
        $cacheKey = $model . '|' . ($orderBy ?? '');

        if (!array_key_exists($cacheKey, $this->defaultReferenceCache)) {
            $query = $model::query();
            if ($orderBy) {
                $query->orderBy($orderBy);
            }
            $this->defaultReferenceCache[$cacheKey] = $query->value('id');
        }

        return $this->defaultReferenceCache[$cacheKey];
    }

    private function defaultStatusDasarId(): ?int
    {
        if (!array_key_exists('status_dasar', $this->defaultReferenceCache)) {
            $preferred = $this->resolveStatusId('HIDUP');
            if (!$preferred) {
                $preferred = RefStatusDasar::query()->orderBy('nama')->value('id');
            }
            $this->defaultReferenceCache['status_dasar'] = $preferred;
        }

        return $this->defaultReferenceCache['status_dasar'];
    }

    /**
     * @return array{dusun_id:int|null,rw_id:int|null,rt_id:int|null}
     */
    private function defaultTerritory(): array
    {
        if ($this->defaultTerritory === null) {
            $dusun = Dusun::query()->orderBy('nama')->first();
            $rw = Rw::query()
                ->when($dusun, fn($query) => $query->where('dusun_id', $dusun->id))
                ->orderBy('nomor')
                ->first()
                ?? Rw::query()->orderBy('nomor')->first();

            $rt = Rt::query()
                ->when($rw, fn($query) => $query->where('rw_id', $rw->id))
                ->orderBy('nomor')
                ->first()
                ?? Rt::query()->orderBy('nomor')->first();

            $this->defaultTerritory = [
                'dusun_id' => $dusun?->id,
                'rw_id' => $rw?->id,
                'rt_id' => $rt?->id,
            ];
        }

        return $this->defaultTerritory;
    }

    /**
     * @param array<string,mixed> $validated
     * @return array<string,mixed>
     */
    private function preparePayload(array $validated): array
    {
        $payload = Arr::map($validated, function ($value) {
            if ($value === '') {
                return null;
            }

            return $value;
        });

        $payload['ktp_el'] = $this->booleanValue($validated['ktp_el'] ?? false, default: false);
        $payload['hamil'] = array_key_exists('hamil', $validated)
            ? $this->booleanValue($validated['hamil'], allowNull: true)
            : null;

        if (!empty($validated['pekerjaan_custom'])) {
            $customJob = Str::title(trim((string) $validated['pekerjaan_custom']));
            if ($customJob !== '') {
                $payload['pekerjaan_id'] = RefPekerjaan::query()->firstOrCreate(['nama' => $customJob])->id;
            }
        }
        unset($payload['pekerjaan_custom']);
        unset(
            $payload['foto_profil'],
            $payload['foto_ktp'],
            $payload['foto_kk'],
            $payload['remove_foto_profil'],
            $payload['remove_foto_ktp'],
            $payload['remove_foto_kk']
        );

        $payload['agama_id'] = $payload['agama_id'] ?? $this->defaultReferenceId(RefAgama::class);
        $payload['pendidikan_kk_id'] = $payload['pendidikan_kk_id'] ?? $this->defaultReferenceId(RefPendidikan::class);
        $payload['pendidikan_sedang_id'] = $payload['pendidikan_sedang_id'] ?? $this->defaultReferenceId(RefPendidikan::class);
        $payload['pekerjaan_id'] = $payload['pekerjaan_id'] ?? $this->defaultReferenceId(RefPekerjaan::class);
        $payload['status_kawin_id'] = $payload['status_kawin_id'] ?? $this->defaultReferenceId(RefStatusKawin::class);
        $payload['kk_level_id'] = $payload['kk_level_id'] ?? $this->defaultReferenceId(RefHubKeluarga::class);
        $payload['warganegara_id'] = $payload['warganegara_id'] ?? $this->defaultReferenceId(RefWarganegara::class);
        $payload['status_dasar_id'] = $payload['status_dasar_id'] ?? $this->defaultStatusDasarId();

        $territory = $this->defaultTerritory();
        $payload['dusun_id'] = $payload['dusun_id'] ?? $territory['dusun_id'];
        $payload['rw_id'] = $payload['rw_id'] ?? $territory['rw_id'];
        $payload['rt_id'] = $payload['rt_id'] ?? $territory['rt_id'];

        return $payload;
    }

    private function handleUploadedPhotos(Request $request, ?Penduduk $penduduk = null): array
    {
        $result = [];

        if ($request->boolean('remove_foto_profil') && $penduduk?->foto_profil) {
            $this->deleteFile($penduduk->foto_profil);
            $result['foto_profil'] = null;
        }

        if ($request->boolean('remove_foto_ktp') && $penduduk?->foto_ktp) {
            $this->deleteFile($penduduk->foto_ktp);
            $result['foto_ktp'] = null;
        }

        if ($request->boolean('remove_foto_kk') && $penduduk?->foto_kk) {
            $this->deleteFile($penduduk->foto_kk);
            $result['foto_kk'] = null;
        }

        if ($request->hasFile('foto_profil')) {
            if ($penduduk?->foto_profil) {
                $this->deleteFile($penduduk->foto_profil);
            }
            $result['foto_profil'] = $request->file('foto_profil')->store('penduduk/foto-profil', 'public');
        }

        if ($request->hasFile('foto_ktp')) {
            if ($penduduk?->foto_ktp) {
                $this->deleteFile($penduduk->foto_ktp);
            }
            $result['foto_ktp'] = $request->file('foto_ktp')->store('penduduk/foto-ktp', 'public');
        }

        if ($request->hasFile('foto_kk')) {
            if ($penduduk?->foto_kk) {
                $this->deleteFile($penduduk->foto_kk);
            }
            $result['foto_kk'] = $request->file('foto_kk')->store('penduduk/foto-kk', 'public');
        }

        return $result;
    }

    private function deletePhotoFiles(?Penduduk $penduduk): void
    {
        if (!$penduduk) {
            return;
        }

        $this->deleteFile($penduduk->foto_profil);
        $this->deleteFile($penduduk->foto_ktp);
        $this->deleteFile($penduduk->foto_kk);
    }

    private function deleteFile(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            if (file_exists(public_path('storage/' . $path))) {
                Storage::disk('public')->delete($path);
            }
        } catch (\Exception $e) {
            // Abaikan error
        }
    }

    /**
     * @param array<string,string|null> $headerRow
     * @return array<string,string>
     */
    private function buildHeaderMap(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $column => $label) {
            $normalised = $this->normaliseHeader($label);
            if ($normalised && isset(self::IMPORT_COLUMN_MAP[$normalised])) {
                $map[$column] = self::IMPORT_COLUMN_MAP[$normalised];
            }
        }

        return $map;
    }

    private function normaliseHeader(?string $label): ?string
    {
        if ($label === null) {
            return null;
        }

        $value = Str::of($label)
            ->lower()
            ->ascii()
            ->replace(['.', '_'], ' ')
            ->squish()
            ->toString();

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string,string> $headerMap
     * @return array<int,string>
     */
    private function missingImportHeaders(array $headerMap): array
    {
        $presentKeys = array_values($headerMap);
        $missing = [];

        foreach (self::REQUIRED_IMPORT_HEADERS as $label => $aliases) {
            $aliases = (array) $aliases;
            $found = false;

            foreach ($aliases as $candidate) {
                if (in_array($candidate, $presentKeys, true)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    /**
     * @param array<string,mixed> $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (!empty(trim((string) $value))) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string,string> $headerMap
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function normaliseImportRow(array $headerMap, array $row): array
    {
        $data = [];

        foreach ($headerMap as $column => $key) {
            $value = $row[$column] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $data[$key] = ($value === '-' || $value === '') ? null : $value;
        }

        return $data;
    }

    private function booleanValue(mixed $value, bool $allowNull = false, bool $default = false): ?bool
    {
        if ($value === null || $value === '') {
            return $allowNull ? null : $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $string = Str::lower(trim((string) $value));

        if (in_array($string, ['1', 'true', 'ya', 'y', 'yes'], true)) {
            return true;
        }

        if (in_array($string, ['0', 'false', 'tidak', 't', 'no', 'n'], true)) {
            return false;
        }

        return $allowNull ? null : $default;
    }

    private function warmTerritoryCaches(): void
    {
        if (empty($this->dusunCache)) {
            $this->dusunCache = Dusun::query()
                ->get(['id', 'nama'])
                ->mapWithKeys(function ($dusun) {
                    $key = $this->normaliseCode($dusun->nama);
                    return [$key => $dusun->id];
                })
                ->all();
        }

        if (empty($this->rwCache)) {
            $cache = [];
            Rw::query()
                ->get(['id', 'dusun_id', 'nomor'])
                ->each(function ($rw) use (&$cache) {
                    $primary = $this->normaliseCode($rw->nomor);
                    $alternate = ltrim($primary, '0');
                    $key = $rw->dusun_id . '|' . $primary;
                    $cache[$key] = $rw->id;
                    if ($alternate !== $primary) {
                        $cache[$rw->dusun_id . '|' . $alternate] = $rw->id;
                    }
                });
            $this->rwCache = $cache;
        }

        if (empty($this->rtCache)) {
            $cache = [];
            Rt::query()
                ->get(['id', 'rw_id', 'nomor'])
                ->each(function ($rt) use (&$cache) {
                    $primary = $this->normaliseCode($rt->nomor);
                    $alternate = ltrim($primary, '0');
                    $key = $rt->rw_id . '|' . $primary;
                    $cache[$key] = $rt->id;
                    if ($alternate !== $primary) {
                        $cache[$rt->rw_id . '|' . $alternate] = $rt->id;
                    }
                });
            $this->rtCache = $cache;
        }
    }

    private function normaliseCode(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = Str::of($value)
            ->lower()
            ->replace(['rt', 'rw'], '')
            ->replace(['-', '_', '.', ','], ' ')
            ->squish()
            ->replace(' ', '')
            ->toString();

        return $value;
    }

    /**
     * @param array<string,mixed> $row
     * @return array<string,mixed>
     */
    private function transformImportRow(array $row): array
    {
        $required = ['no_kk', 'nik', 'nama'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                throw new RuntimeException("Kolom {$field} wajib diisi.");
            }
        }

        $payload = [
            'id' => trim((string) ($row['id'] ?? '')),
            'no_kk' => trim((string) $row['no_kk']),
            'nik' => trim((string) $row['nik']),
            'nama' => trim((string) $row['nama']),
            'tempat_lahir' => $this->nullableString($row['tempat_lahir'] ?? null),
            'tanggal_lahir' => $this->nullableDate($row['tanggal_lahir'] ?? null),
            'ayah_nik' => $this->nullableString($row['ayah_nik'] ?? null),
            'nama_ayah' => $this->nullableString($row['nama_ayah'] ?? null),
            'ibu_nik' => $this->nullableString($row['ibu_nik'] ?? null),
            'nama_ibu' => $this->nullableString($row['nama_ibu'] ?? null),
            'akta_lahir' => $this->nullableString($row['akta_lahir'] ?? null),
            'dokumen_pasport' => $this->nullableString($row['dokumen_pasport'] ?? null),
            'tanggal_akhir_paspor' => $this->nullableDate($row['tanggal_akhir_paspor'] ?? null),
            'dokumen_kitas' => $this->nullableString($row['dokumen_kitas'] ?? null),
            'akta_perkawinan' => $this->nullableString($row['akta_perkawinan'] ?? null),
            'tanggal_perkawinan' => $this->nullableDate($row['tanggal_perkawinan'] ?? null),
            'akta_perceraian' => $this->nullableString($row['akta_perceraian'] ?? null),
            'tanggal_perceraian' => $this->nullableDate($row['tanggal_perceraian'] ?? null),
            'alamat' => $this->nullableString($row['alamat'] ?? null),
            'alamat_sekarang' => $this->nullableString($row['alamat_sekarang'] ?? null),
            'tag_id_card' => $this->nullableString($row['tag_id_card'] ?? null),
            'id_asuransi' => $this->nullableString($row['id_asuransi'] ?? null),
            'no_asuransi' => $this->nullableString($row['no_asuransi'] ?? null),
            'hamil' => $this->booleanValue($row['hamil'] ?? null, allowNull: true),
            'ktp_el' => $this->booleanValue($row['ktp_el'] ?? false, default: false),
        ];

        // Helper to resolve or get default
        $resolveOrDefault = function ($model, $idKey, $nameKey) use ($row) {
            $id = $this->resolveReferenceId($model, $row, $idKey, $nameKey, true);
            return $id ?? $this->defaultReferenceId($model);
        };

        // Try to get gender, but fallback to first available if missing
        $payload['jenis_kelamin_id'] = $this->resolveReferenceId(RefJenisKelamin::class, $row, 'jenis_kelamin_id', 'jenis_kelamin', true);
        if (!$payload['jenis_kelamin_id']) {
            $payload['jenis_kelamin_id'] = $this->defaultReferenceId(RefJenisKelamin::class);
        }

        $payload['agama_id'] = $resolveOrDefault(RefAgama::class, 'agama_id', 'agama');
        $payload['pendidikan_kk_id'] = $resolveOrDefault(RefPendidikan::class, 'pendidikan_kk_id', 'pendidikan_kk');
        $payload['pendidikan_sedang_id'] = $resolveOrDefault(RefPendidikan::class, 'pendidikan_sedang_id', 'pendidikan_sedang');
        $payload['pekerjaan_id'] = $resolveOrDefault(RefPekerjaan::class, 'pekerjaan_id', 'pekerjaan');
        $payload['status_kawin_id'] = $resolveOrDefault(RefStatusKawin::class, 'status_kawin_id', 'status_kawin');
        $payload['kk_level_id'] = $resolveOrDefault(RefHubKeluarga::class, 'kk_level_id', 'kk_level');
        $payload['warganegara_id'] = $resolveOrDefault(RefWarganegara::class, 'warganegara_id', 'warganegara');

        $payload['status_dasar_id'] = $this->resolveReferenceId(RefStatusDasar::class, $row, 'status_dasar_id', 'status_dasar', true)
            ?? $this->defaultStatusDasarId();

        $payload['golongan_darah_id'] = $resolveOrDefault(RefGolonganDarah::class, 'golongan_darah_id', 'golongan_darah');
        $payload['status_rekam_id'] = $resolveOrDefault(RefStatusRekam::class, 'status_rekam_id', 'status_rekam');
        $payload['cacat_id'] = $resolveOrDefault(RefCacat::class, 'cacat_id', 'cacat');
        $payload['cara_kb_id'] = $resolveOrDefault(RefCaraKb::class, 'cara_kb_id', 'cara_kb');
        $payload['suku_id'] = $resolveOrDefault(RefSuku::class, 'suku_id', 'suku');

        $payload['dusun_id'] = $this->resolveNullableDusun($row);
        $payload['rw_id'] = $this->resolveNullableRw($row, $payload['dusun_id']);
        $payload['rt_id'] = $this->resolveNullableRt($row, $payload['rw_id']);

        // Default territory if missing
        $defaultTerritory = $this->defaultTerritory();
        if (!$payload['dusun_id'])
            $payload['dusun_id'] = $defaultTerritory['dusun_id'];
        if (!$payload['rw_id'])
            $payload['rw_id'] = $defaultTerritory['rw_id'];
        if (!$payload['rt_id'])
            $payload['rt_id'] = $defaultTerritory['rt_id'];

        $this->ensureKeluargaExists($payload['no_kk']);

        return $payload;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function nullableDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        // Try standard format first
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            // Try Indonesian format (d/m/Y)
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->toDateString();
            } catch (\Throwable) {
                // Try d-m-Y just in case
                try {
                    return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
                } catch (\Throwable) {
                    throw new RuntimeException(sprintf("Format tanggal '%s' tidak valid.", $value));
                }
            }
        }
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @param array<string,mixed> $row
     */
    private function resolveReferenceId(string $model, array $row, string $idKey, string $nameKey, bool $nullable = false): ?int
    {
        $idValue = $row[$idKey] ?? null;
        if ($idValue !== null && $idValue !== '') {
            return (int) $idValue;
        }

        $nameValue = $row[$nameKey] ?? null;
        if ($nameValue === null || trim((string) $nameValue) === '') {
            if ($nullable) {
                return null;
            }

            throw new RuntimeException("Kolom {$nameKey} wajib diisi.");
        }

        $lookup = $this->referenceLookup($model);
        $key = Str::lower(trim((string) $nameValue));

        if (!isset($lookup[$key])) {
            if ($nullable) {
                return null;
            }

            throw new RuntimeException("Referensi {$nameKey} '{$nameValue}' tidak ditemukan.");
        }

        return $lookup[$key];
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @return array<string,int>
     */
    private function referenceLookup(string $model): array
    {
        if (!isset($this->referenceCache[$model])) {
            $this->referenceCache[$model] = $model::query()
                ->get(['id', 'nama'])
                ->mapWithKeys(function ($item) {
                    return [Str::lower($item->nama) => $item->id];
                })
                ->all();
        }

        return $this->referenceCache[$model];
    }

    /**
     * @param array<string,mixed> $row
     */
    private function resolveDusunId(array $row): int
    {
        $id = $row['dusun_id'] ?? null;
        if ($id !== null && $id !== '') {
            return (int) $id;
        }

        $name = $row['dusun'] ?? null;
        if ($name === null || trim((string) $name) === '') {
            throw new RuntimeException('Kolom dusun wajib diisi.');
        }

        $key = $this->normaliseCode((string) $name);
        $id = $this->dusunCache[$key] ?? null;

        if (!$id) {
            throw new RuntimeException("Dusun '{$name}' tidak ditemukan.");
        }

        return $id;
    }

    private function resolveNullableDusun(array $row): ?int
    {
        try {
            return $this->resolveDusunId($row);
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * @param array<string,mixed> $row
     */
    private function resolveRwId(array $row, int $dusunId): int
    {
        $id = $row['rw_id'] ?? null;
        if ($id !== null && $id !== '') {
            return (int) $id;
        }

        $value = $row['rw'] ?? null;
        if ($value === null || trim((string) $value) === '') {
            throw new RuntimeException('Kolom RW wajib diisi.');
        }

        $key = $dusunId . '|' . $this->normaliseCode((string) $value);
        $alternate = $dusunId . '|' . ltrim($this->normaliseCode((string) $value), '0');

        $id = $this->rwCache[$key] ?? $this->rwCache[$alternate] ?? null;

        if (!$id) {
            throw new RuntimeException("RW '{$value}' tidak ditemukan untuk dusun tersebut.");
        }

        return $id;
    }

    private function resolveNullableRw(array $row, ?int $dusunId): ?int
    {
        if (!$dusunId) {
            return null;
        }

        try {
            return $this->resolveRwId($row, $dusunId);
        } catch (RuntimeException) {
            return null;
        }
    }

    /**
     * @param array<string,mixed> $row
     */
    private function resolveRtId(array $row, int $rwId): int
    {
        $id = $row['rt_id'] ?? null;
        if ($id !== null && $id !== '') {
            return (int) $id;
        }

        $value = $row['rt'] ?? null;
        if ($value === null || trim((string) $value) === '') {
            throw new RuntimeException('Kolom RT wajib diisi.');
        }

        $key = $rwId . '|' . $this->normaliseCode((string) $value);
        $alternate = $rwId . '|' . ltrim($this->normaliseCode((string) $value), '0');

        $id = $this->rtCache[$key] ?? $this->rtCache[$alternate] ?? null;

        if (!$id) {
            throw new RuntimeException("RT '{$value}' tidak ditemukan untuk RW tersebut.");
        }

        return $id;
    }

    private function resolveNullableRt(array $row, ?int $rwId): ?int
    {
        if (!$rwId) {
            return null;
        }

        try {
            return $this->resolveRtId($row, $rwId);
        } catch (RuntimeException) {
            return null;
        }
    }

    private function ensureKeluargaExists(string $noKk): void
    {
        $exists = Keluarga::query()->where('no_kk', $noKk)->exists();

        if (!$exists) {
            throw new RuntimeException("No. KK '{$noKk}' belum terdaftar di sistem.");
        }
    }

    private function streamExcel(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            try {
                if (ob_get_level() > 0) {
                    ob_end_clean();
                }

                $writer = SpreadsheetIOFactory::createWriter($spreadsheet, 'Xlsx');
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
            } finally {
                $spreadsheet->disconnectWorksheets();
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildExportSpreadsheet(?iterable $rows = null, string $sheetTitle = 'Data Penduduk', array $referenceData = []): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(12);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit($sheetTitle, 31, ''));
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
            ->setTop(0.25)
            ->setBottom(0.25)
            ->setLeft(0.25)
            ->setRight(0.25);
        $sheet->getPageSetup()->setHorizontalCentered(true);

        $totalColumns = count(self::EXPORT_COLUMNS);
        $lastColumnLetter = Coordinate::stringFromColumnIndex($totalColumns);

        $title = 'Data Penduduk';
        $subtitle = 'Desa Tanjung Kesuma';
        $dateLine = 'diunduh: ' . now()->locale('id')->translatedFormat('l, d F Y');

        $sheet->mergeCells("A1:{$lastColumnLetter}1");
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);

        $midIndex = max(1, (int) floor($totalColumns / 2));
        if ($midIndex >= $totalColumns) {
            $midIndex = max(1, $totalColumns - 1);
        }
        $leftEnd = Coordinate::stringFromColumnIndex($midIndex);
        $rightStart = Coordinate::stringFromColumnIndex($midIndex + 1);

        $sheet->mergeCells("A2:{$leftEnd}2");
        $sheet->setCellValue('A2', $subtitle);
        $sheet->mergeCells("{$rightStart}2:{$lastColumnLetter}2");
        $sheet->setCellValue("{$rightStart}2", $dateLine);
        $sheet->getStyle("A2:{$leftEnd}2")->getFont()->setSize(12);
        $sheet->getStyle("{$rightStart}2:{$lastColumnLetter}2")->getFont()->setSize(11);
        $sheet->getStyle("A1:{$lastColumnLetter}1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("A2:{$leftEnd}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("{$rightStart}2:{$lastColumnLetter}2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $columnIndex = 1;
        $headerRow = 3;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);
        $columnWidths = [
            'no' => 8,
            'no_kk' => 20,
            'nik' => 20,
            'nama' => 35, // nama lebih panjang agar terbaca
            'warganegara' => 30,
            'alamat' => 50,
            'alamat_sekarang' => 50,
        ];

        foreach (self::EXPORT_COLUMNS as $key => $label) {
            $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
            $sheet->setCellValue("{$columnLetter}{$headerRow}", $label);
            $width = $columnWidths[$key] ?? 20;
            $sheet->getColumnDimension($columnLetter)->setWidth($width);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(false);
            $columnIndex++;
        }

        $records = $rows ?? $this->fetchExportRecords();
        $rowIndex = $headerRow + 1;

        foreach ($records as $record) {
            $values = $record instanceof Penduduk ? $this->exportRow($record) : (array) $record;
            $columnIndex = 1;

            foreach (self::EXPORT_COLUMNS as $key => $label) {
                $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                $cellRef = "{$columnLetter}{$rowIndex}";
                $sheet->setCellValueExplicit(
                    $cellRef,
                    isset($values[$key]) ? (string) $values[$key] : '',
                    DataType::TYPE_STRING
                );
                $columnIndex++;
            }

            $sheet->getRowDimension($rowIndex)->setRowHeight(22);
            $rowIndex++;
        }

        $headerRange = "A{$headerRow}:{$lastColumnLetter}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => '1D4ED8'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '1E3A8A'],
                ],
            ],
        ]);

        $dataEndRow = max($rowIndex - 1, $headerRow);
        $dataRange = "A" . ($headerRow + 1) . ":{$lastColumnLetter}{$dataEndRow}";

        if ($dataEndRow >= 2) {
            $sheet->getStyle($dataRange)->applyFromArray([
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color' => ['rgb' => 'CBD5F5'],
                    ],
                ],
            ]);

            // zebra striping: putih dan abu-abu #F2F2F2 selang-seling
            for ($row = $headerRow + 1; $row <= $dataEndRow; $row++) {
                $range = "A{$row}:{$lastColumnLetter}{$row}";
                $isEven = ($row - ($headerRow + 1)) % 2 === 0;
                $fillColor = $isEven ? 'F2F2F2' : 'FFFFFF';
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID);
                $sheet->getStyle($range)->getFill()->getStartColor()->setRGB($fillColor);
            }
        }

        $sheet->freezePane('A' . ($headerRow + 1));
        // Filter removed as per user request
        $sheet->getStyle("A1:{$lastColumnLetter}{$dataEndRow}")->getAlignment()->setWrapText(true);
        $sheet->setSelectedCell("A{$headerRow}");

        // Fix: Force NIK and KK columns to be Text to prevent scientific notation on edit
        // Column A (No. KK) and B (NIK)
        $sheet->getStyle("A{$headerRow}:A{$dataEndRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->getStyle("B{$headerRow}:B{$dataEndRow}")
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        if (!empty($referenceData)) {
            $this->addValidationDropdowns($spreadsheet, $headerRow, $dataEndRow, $referenceData);
        }

        return $spreadsheet;
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param int $startRow
     * @param int $endRow
     * @param array<string,mixed> $references
     */
    private function addValidationDropdowns(Spreadsheet $spreadsheet, int $startRow, int $endRow, array $references): void
    {
        // 1. Buat sheet referensi tersembunyi
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('Referensi');
        $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $rowIdx = 1;
        $ranges = [];

        // Daftar kunci referensi yang perlu validasi
        $keys = [
            'jenisKelaminOptions' => 'jenis_kelamin',
            'agamaOptions' => 'agama',
            'pendidikanOptions' => 'pendidikan_kk',
            'pendidikanSedangOptions' => 'pendidikan_sedang',
            'pekerjaanOptions' => 'pekerjaan',
            'statusKawinOptions' => 'status_kawin',
            'hubunganKkOptions' => 'kk_level',
            'warganegaraOptions' => 'warganegara',
            'golonganDarahOptions' => 'golongan_darah',
            'statusRekamOptions' => 'status_rekam',
            'cacatOptions' => 'cacat',
            'caraKbOptions' => 'cara_kb',
            'statusDasarOptions' => 'status_dasar',
            'sukuOptions' => 'suku',
        ];

        foreach ($keys as $optionKey => $colName) {
            $options = $references[$optionKey] ?? [];
            if (empty($options))
                continue;

            $colLetter = Coordinate::stringFromColumnIndex($rowIdx); // Gunakan kolom sebagai pemisah list
            $startDataRow = 1;

            foreach ($options as $opt) {
                // Handle collection or array result
                $val = is_object($opt) ? $opt->nama : ($opt['nama'] ?? '');
                $refSheet->setCellValue("{$colLetter}{$startDataRow}", $val);
                $startDataRow++;
            }

            $endDataRow = $startDataRow - 1;
            $rangeName = "REF_" . strtoupper($colName);
            $fullRange = "Referensi!\${$colLetter}\$1:\${$colLetter}\${$endDataRow}";

            // Simpan range formula untuk validasi
            $ranges[$colName] = $fullRange;
            $rowIdx++;
        }

        // 2. Terapkan validasi ke kolom utama
        $mainSheet = $spreadsheet->getSheet(0);
        $totalRows = max(100, $endRow + 50); // Validasi sampai 50 baris di bawah data

        foreach (self::EXPORT_COLUMNS as $colKey => $label) {
            // Mapping nama kolom internal ke nama referensi
            $refKey = $colKey; // Default match
            if ($colKey === 'dusun' || $colKey === 'rw' || $colKey === 'rt')
                continue; // Skip wilayah dulu karena hierarki kompleks

            if (isset($ranges[$refKey])) {
                // Cari index kolom di sheet utama
                $colIndex = array_search($colKey, array_keys(self::EXPORT_COLUMNS)) + 1;
                $colStr = Coordinate::stringFromColumnIndex($colIndex);

                // Terapkan ke range data
                $validationRange = "{$colStr}" . ($startRow + 1) . ":{$colStr}{$totalRows}";
                $validation = $mainSheet->getCell("{$colStr}" . ($startRow + 1))->getDataValidation();

                $validation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(true)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Input tidak valid')
                    ->setError('Silakan pilih nilai dari daftar yang tersedia.')
                    ->setPromptTitle('Pilih ' . $label)
                    ->setPrompt('Pilih item dari dropdown list.')
                    ->setFormula1($ranges[$refKey]);

                // Clone validation ke seluruh range (PhpSpreadsheet optimization)
                $mainSheet->setDataValidation($validationRange, $validation);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int,\App\Models\Penduduk>
     */
    private function fetchExportRecords()
    {
        return Penduduk::query()
            ->with([
                'keluarga:id,no_kk',
                'jenisKelamin:id,nama',
                'agama:id,nama',
                'pendidikanKk:id,nama',
                'pendidikanSedang:id,nama',
                'pekerjaan:id,nama',
                'statusKawin:id,nama',
                'kkLevel:id,nama',
                'warganegara:id,nama',
                'golonganDarah:id,nama',
                'statusRekam:id,nama',
                'cacat:id,nama',
                'caraKb:id,nama',
                'statusDasar:id,nama',
                'dusun:id,nama',
                'rw:id,nomor',
                'rt:id,nomor',
                'suku:id,nama',
            ])
            ->orderBy('nama')
            ->get();
    }

    /**
     * @return array<int,array<string,string>>
     */
    private function buildTemplateRows(): array
    {
        return [
            [
                'id' => '',
                'no_kk' => '1671080101010001',
                'nik' => '1671086512870001',
                'nama' => 'Siti Aminah',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Tanjung Kesuma',
                'tanggal_lahir' => '12/02/1987',
                'agama' => 'Islam',
                'pendidikan_kk' => 'SMA/SMK',
                'pendidikan_sedang' => 'Tidak Sekolah',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'status_kawin' => 'Kawin',
                'kk_level' => 'Istri',
                'warganegara' => 'WNI',
                'alamat' => 'Jl. Melati No. 12',
                'alamat_sekarang' => 'Jl. Melati No. 12',
                'dusun' => 'Dusun 1',
                'rw' => 'RW 01',
                'rt' => 'RT 02',
                'status_dasar' => 'HIDUP',
                'golongan_darah' => 'O',
                'status_rekam' => 'Rekam',
                'hamil' => 'Tidak',
                'ktp_el' => 'Ya',
                'ayah_nik' => '1671083006600001',
                'nama_ayah' => 'Muhammad Yusuf',
                'ibu_nik' => '1671084506650002',
                'nama_ibu' => 'Siti Maryam',
                'cacat' => 'Tidak Ada',
                'cara_kb' => 'Suntik',
                'suku' => 'Lampung',
                'tag_id_card' => 'RFID-0001',
                'id_asuransi' => 'BPJS',
                'no_asuransi' => '0001234567890',
                'akta_lahir' => '3170-LHR-1987-001',
                'dokumen_pasport' => '',
                'tanggal_akhir_paspor' => '',
                'dokumen_kitas' => '',
                'akta_perkawinan' => '3170-PWK-2008-015',
                'tanggal_perkawinan' => '18/08/2008',
                'akta_perceraian' => '',
                'tanggal_perceraian' => '',
            ],
            [
                'id' => '',
                'no_kk' => '1671080202020004',
                'nik' => '1671081203900002',
                'nama' => 'Adi Pratama',
                'jenis_kelamin' => 'Laki-Laki',
                'tempat_lahir' => 'Bandar Lampung',
                'tanggal_lahir' => '20/03/1990',
                'agama' => 'Islam',
                'pendidikan_kk' => 'S1',
                'pendidikan_sedang' => 'S2',
                'pekerjaan' => 'Guru',
                'status_kawin' => 'Kawin',
                'kk_level' => 'Kepala Keluarga',
                'warganegara' => 'WNI',
                'alamat' => 'Jl. Cendana No. 5',
                'alamat_sekarang' => 'Jl. Cendana No. 5',
                'dusun' => 'Dusun 2',
                'rw' => 'RW 02',
                'rt' => 'RT 04',
                'status_dasar' => 'HIDUP',
                'golongan_darah' => 'A',
                'status_rekam' => 'Rekam',
                'hamil' => 'Tidak',
                'ktp_el' => 'Ya',
                'ayah_nik' => '1671081201600003',
                'nama_ayah' => 'Slamet Riyadi',
                'ibu_nik' => '1671085401650004',
                'nama_ibu' => 'Sri Wahyuni',
                'cacat' => 'Tidak Ada',
                'cara_kb' => 'Kondom',
                'suku' => 'Jawa',
                'tag_id_card' => 'RFID-0002',
                'id_asuransi' => 'BPJS',
                'no_asuransi' => '0009876543210',
                'akta_lahir' => '3170-LHR-1990-045',
                'dokumen_pasport' => 'C1234567',
                'tanggal_akhir_paspor' => '31/07/2028',
                'dokumen_kitas' => '',
                'akta_perkawinan' => '3170-PWK-2015-022',
                'tanggal_perkawinan' => '21/11/2015',
                'akta_perceraian' => '',
                'tanggal_perceraian' => '',
            ],

        ];
    }

    /**
     * @return array<string,string>
     */
    private function exportRow(Penduduk $penduduk): array
    {
        return [
            'id' => $penduduk->id,
            'no_kk' => $penduduk->no_kk,
            'nik' => $penduduk->nik,
            'nama' => $penduduk->nama,
            'jenis_kelamin' => $penduduk->jenisKelamin?->nama ?? '-',
            'tempat_lahir' => $penduduk->tempat_lahir ?? '-',
            'tanggal_lahir' => $penduduk->tanggal_lahir?->format('d/m/Y') ?? '-',
            'agama' => $penduduk->agama?->nama ?? '-',
            'pendidikan_kk' => $penduduk->pendidikanKk?->nama ?? '-',
            'pendidikan_sedang' => $penduduk->pendidikanSedang?->nama ?? '-',
            'pekerjaan' => $penduduk->pekerjaan?->nama ?? '-',
            'status_kawin' => $penduduk->statusKawin?->nama ?? '-',
            'kk_level' => $penduduk->kkLevel?->nama ?? '-',
            'warganegara' => $penduduk->warganegara?->nama ?? '-',
            'alamat' => $penduduk->alamat ?? '-',
            'alamat_sekarang' => $penduduk->alamat_sekarang ?? '-',
            'dusun' => $penduduk->dusun?->nama ?? '-',
            'rw' => $penduduk->rw ? ('RW ' . $penduduk->rw->nomor) : '-',
            'rt' => $penduduk->rt ? ('RT ' . $penduduk->rt->nomor) : '-',
            'status_dasar' => $penduduk->statusDasar?->nama ?? '-',
            'golongan_darah' => $penduduk->golonganDarah?->nama ?? '-',
            'status_rekam' => $penduduk->statusRekam?->nama ?? '-',
            'hamil' => $penduduk->hamil === null ? 'Tidak diketahui' : ($penduduk->hamil ? 'Ya' : 'Tidak'),
            'ktp_el' => $penduduk->ktp_el ? 'Ya' : 'Tidak',
            'ayah_nik' => $penduduk->ayah_nik ?? '-',
            'nama_ayah' => $penduduk->nama_ayah ?? '-',
            'ibu_nik' => $penduduk->ibu_nik ?? '-',
            'nama_ibu' => $penduduk->nama_ibu ?? '-',
            'cacat' => $penduduk->cacat?->nama ?? '-',
            'cara_kb' => $penduduk->caraKb?->nama ?? '-',
            'suku' => $penduduk->suku?->nama ?? '-',
            'tag_id_card' => $penduduk->tag_id_card ?? '-',
            'id_asuransi' => $penduduk->id_asuransi ?? '-',
            'no_asuransi' => $penduduk->no_asuransi ?? '-',
            'akta_lahir' => $penduduk->akta_lahir ?? '-',
            'dokumen_pasport' => $penduduk->dokumen_pasport ?? '-',
            'tanggal_akhir_paspor' => $penduduk->tanggal_akhir_paspor?->format('d/m/Y') ?? '-',
            'dokumen_kitas' => $penduduk->dokumen_kitas ?? '-',
            'akta_perkawinan' => $penduduk->akta_perkawinan ?? '-',
            'tanggal_perkawinan' => $penduduk->tanggal_perkawinan?->format('d/m/Y') ?? '-',
            'akta_perceraian' => $penduduk->akta_perceraian ?? '-',
            'tanggal_perceraian' => $penduduk->tanggal_perceraian?->format('d/m/Y') ?? '-',
        ];
    }

    private function buildCategoryMetrics(int $totalCount, ?int $excludeStatusId = null): array
    {
        $configs = [
            'pendidikan' => [
                'title' => 'Pendidikan Aktif',
                'foreign_key' => 'pendidikan_sedang_id',
                'table' => 'ref_pendidikan',
                'icon' => 'fa-user-graduate',
                'color' => 'indigo',
            ],
            'pekerjaan' => [
                'title' => 'Pekerjaan',
                'foreign_key' => 'pekerjaan_id',
                'table' => 'ref_pekerjaan',
                'icon' => 'fa-briefcase',
                'color' => 'amber',
            ],
            'status_kawin' => [
                'title' => 'Status Perkawinan',
                'foreign_key' => 'status_kawin_id',
                'table' => 'ref_status_kawin',
                'icon' => 'fa-ring',
                'color' => 'rose',
            ],
            'agama' => [
                'title' => 'Agama',
                'foreign_key' => 'agama_id',
                'table' => 'ref_agama',
                'icon' => 'fa-hands-praying',
                'color' => 'primary',
            ],
            'suku' => [
                'title' => 'Suku',
                'foreign_key' => 'suku_id',
                'table' => 'ref_suku',
                'icon' => 'fa-users',
                'color' => 'cyan',
            ],
            'golongan_darah' => [
                'title' => 'Golongan Darah',
                'foreign_key' => 'golongan_darah_id',
                'table' => 'ref_golongan_darah',
                'icon' => 'fa-droplet',
                'color' => 'pink',
            ],
        ];

        $results = [];

        foreach ($configs as $key => $config) {
            $metric = $this->buildCategoryMetric(
                $totalCount,
                $config['title'],
                $config['foreign_key'],
                $config['table'],
                $config['icon'],
                $config['color'],
                $excludeStatusId
            );

            if (!empty($metric['items'])) {
                $results[$key] = $metric;
            }
        }

        $ageMetric = $this->buildAgeGroupMetric($totalCount, $excludeStatusId);
        if (!empty($ageMetric['items'])) {
            $results['age_groups'] = $ageMetric;
        }

        return $results;
    }

    /**
     * @return array<string,mixed>
     */
    private function buildCategoryMetric(
        int $totalCount,
        string $title,
        string $foreignKey,
        string $referenceTable,
        string $icon,
        string $color,
        ?int $excludeStatusId = null,
        int $limit = 4
    ): array {
        $rows = DB::table('penduduks')
            ->when($excludeStatusId, fn($query) => $query->where('penduduks.status_dasar_id', '!=', $excludeStatusId))
            ->leftJoin($referenceTable, "{$referenceTable}.id", '=', "penduduks.{$foreignKey}")
            ->selectRaw("COALESCE({$referenceTable}.nama, 'Belum diatur') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'title' => $title,
                'icon' => $icon,
                'color' => $color,
                'items' => [],
            ];
        }

        return [
            'title' => $title,
            'icon' => $icon,
            'color' => $color,
            'items' => $rows->map(function ($row) use ($totalCount, $color) {
                $value = (int) ($row->total ?? 0);

                return [
                    'label' => $row->label ?? 'Belum diatur',
                    'value' => $value,
                    'percent' => $totalCount > 0 ? ($value / $totalCount) * 100 : 0,
                    'color' => $color,
                ];
            })->all(),
        ];
    }

    private function buildAgeGroupMetric(int $totalCount, ?int $excludeStatusId = null): array
    {
        $ageExpression = "TIMESTAMPDIFF(YEAR, penduduks.tanggal_lahir, CURDATE())";

        $groups = [
            'Bayi (0-1 tahun)' => "{$ageExpression} <= 1",
            'Balita (1-5 tahun)' => "{$ageExpression} > 1 AND {$ageExpression} <= 5",
            'Anak-Anak (5-12 tahun)' => "{$ageExpression} > 5 AND {$ageExpression} <= 12",
            'Remaja (12-21 tahun)' => "{$ageExpression} > 12 AND {$ageExpression} <= 21",
            'Dewasa Muda Awal (21-30 tahun)' => "{$ageExpression} > 21 AND {$ageExpression} <= 30",
            'Dewasa (31-50 tahun)' => "{$ageExpression} > 30 AND {$ageExpression} <= 50",
            'Lansia (60+ tahun)' => "{$ageExpression} >= 60",
        ];

        $caseClauses = [];
        foreach ($groups as $label => $condition) {
            $escapedLabel = str_replace("'", "''", $label);
            $caseClauses[] = "WHEN {$condition} THEN '{$escapedLabel}'";
        }
        $caseSql = 'CASE ' . implode(' ', $caseClauses) . " ELSE 'Usia lainnya' END";

        $rows = DB::table('penduduks')
            ->when(
                $excludeStatusId,
                fn($query) => $query->where('penduduks.status_dasar_id', '!=', $excludeStatusId)
            )
            ->selectRaw("{$caseSql} as label, COUNT(*) as total")
            ->groupBy('label')
            ->get();

        $items = [];
        foreach (array_keys($groups) as $label) {
            $value = (int) optional($rows->firstWhere('label', $label))->total;
            $items[] = [
                'label' => $label,
                'value' => $value,
                'percent' => $totalCount > 0 ? ($value / $totalCount) * 100 : 0,
                'color' => 'primary',
            ];
        }

        $other = $rows->firstWhere('label', 'Usia lainnya');
        if ($other && (int) $other->total > 0) {
            $items[] = [
                'label' => 'Usia lainnya',
                'value' => (int) $other->total,
                'percent' => $totalCount > 0 ? ((int) $other->total / $totalCount) * 100 : 0,
                'color' => 'slate',
            ];
        }

        return [
            'title' => 'Kelompok Usia',
            'icon' => 'fa-child',
            'color' => 'primary',
            'items' => $items,
        ];
    }
}
