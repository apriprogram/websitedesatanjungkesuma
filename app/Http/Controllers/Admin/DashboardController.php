<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Agenda;
use App\Models\DashboardStatistic;
use App\Models\Dusun;
use App\Models\HeroSlide;
use App\Models\InfoMediaBanner;
use App\Models\InfographicBanner;
use App\Models\BudgetItem;
use App\Models\Keluarga;
use App\Models\News;
use App\Models\Page;
use App\Models\Pegawai;
use App\Models\Penduduk;
use App\Models\References\RefStatusDasar;
use App\Models\Rt;
use App\Models\Rw;
use App\Models\VillageDocument;
use App\Models\YoutubeVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $agendas = Agenda::query()
            ->orderBy('is_completed')
            ->orderByRaw('COALESCE(due_date, CURRENT_DATE + INTERVAL 365 DAY)')
            ->limit(6)
            ->get();

        $activities = ActivityLog::with('user')
            ->latest()
            ->paginate(10);

        $statusLookup = $this->statusLookup(['HIDUP', 'PINDAH', 'MATI']);
        $summary = $this->buildSummaryMetrics($statusLookup, (int) $agendas->where('is_completed', false)->count());
        $statistics = $this->synchroniseStatistics($summary);

        return view('admin.dashboard', [
            'agendas' => $agendas,
            'activities' => $activities,
            'summary' => $summary,
            'statistics' => $statistics,
        ]);
    }

    /**
     * @param array<string,int|null> $statusLookup
     */
    private function buildSummaryMetrics(array $statusLookup, int $activeAgendaCount): array
    {
        // Kependudukan
        $matiId = $statusLookup['MATI'] ?? null;
        $pendudukCounts = Penduduk::query()
            ->when($matiId, fn($q) => $q->where('status_dasar_id', '!=', $matiId))
            ->selectRaw('status_dasar_id, jenis_kelamin_id, COUNT(*) as total')
            ->groupBy('status_dasar_id', 'jenis_kelamin_id')
            ->get();

        $hidupId = $statusLookup['HIDUP'] ?? null;
        $pindahId = $statusLookup['PINDAH'] ?? null;

        $hidup = $pendudukCounts->where('status_dasar_id', $hidupId)->sum('total');
        $pindah = $pendudukCounts->where('status_dasar_id', $pindahId)->sum('total');
        $mati = $matiId ? (int) Penduduk::where('status_dasar_id', $matiId)->count() : 0;

        $laki = $pendudukCounts->where('jenis_kelamin_id', 1)->sum('total'); // Assuming 1 is Male
        $perempuan = $pendudukCounts->where('jenis_kelamin_id', 2)->sum('total'); // Assuming 2 is Female

        $keluarga = (int) Keluarga::count();

        // Wilayah
        $dusun = (int) Dusun::count();
        $rw = (int) Rw::count();
        $rt = (int) Rt::count();

        // Publikasi
        $berita = (int) News::count();
        $beritaPublished = (int) News::where('status', 'published')->count();
        $beritaDraft = (int) News::where('status', 'draft')->count();
        $beritaArchived = (int) News::where('status', 'archived')->count();
        $beritaFeatured = (int) News::where('is_featured', true)->count();
        $beritaViews = (int) News::sum('views');

        $pages = (int) Page::count();
        $pagesPublished = (int) Page::where('status', 'published')->count();
        $pagesDraft = (int) Page::where('status', 'draft')->count();
        $pagesViews = Schema::hasColumn('pages', 'views') ? (int) Page::sum('views') : 0;

        $video = (int) YoutubeVideo::count();
        $dokumen = (int) VillageDocument::count();
        $galeri = (int) HeroSlide::count() + (int) InfoMediaBanner::count() + (int) InfographicBanner::count();
        $transparansi = (int) BudgetItem::published()->count();
        $transparansiYears = (int) BudgetItem::published()->distinct('year')->count('year');
        $transparansiByCategory = BudgetItem::published()
            ->select('category', DB::raw('COUNT(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->all();
        $transparansiTotalCount = (int) BudgetItem::count();
        $transparansiPublishedCount = (int) BudgetItem::published()->count();
        $transparansiTotalAnggaran = (float) BudgetItem::published()->sum('anggaran');
        $transparansiTotalRealisasi = (float) BudgetItem::published()->sum('realisasi');

        // Pemerintahan
        $pegawai = (int) Pegawai::count();
        $pegawaiStatuses = Pegawai::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->filter()
            ->all();
        $agendaSelesai = (int) Agenda::where('is_completed', true)->count();
        $agendaTotal = (int) Agenda::count();

        // Kependudukan detail
        $employmentQuery = DB::table('penduduks')
            ->when($matiId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $matiId))
            ->leftJoin('ref_pekerjaan', 'ref_pekerjaan.id', '=', 'penduduks.pekerjaan_id');

        $belumBekerja = (int) (clone $employmentQuery)
            ->where(function ($q) {
                $q->whereRaw('LOWER(ref_pekerjaan.nama) LIKE ?', ['%tidak bekerja%'])
                    ->orWhereRaw('LOWER(ref_pekerjaan.nama) LIKE ?', ['%belum%']);
            })
            ->count();

        $totalAktif = $hidup + $pindah;
        $sudahBekerja = max(0, $totalAktif - $belumBekerja);

        $topPendidikan = $this->topCategoryStat('pendidikan_sedang_id', 'ref_pendidikan', $matiId);
        $topPekerjaan = $this->topCategoryStat('pekerjaan_id', 'ref_pekerjaan', $matiId);
        $topPerkawinan = $this->topCategoryStat('status_kawin_id', 'ref_status_kawin', $matiId);
        $topAgama = $this->topCategoryStat('agama_id', 'ref_agama', $matiId);
        $topSuku = $this->topCategoryStat('suku_id', 'ref_suku', $matiId);
        $topGoldar = $this->topCategoryStat('golongan_darah_id', 'ref_golongan_darah', $matiId);
        $topStatusDasar = $this->topCategoryStat('status_dasar_id', 'ref_status_dasar', null);
        $usiaStats = collect($this->buildUsiaStatistic());
        $usiaTop = $usiaStats->sortByDesc('value')->first();
        $customAgeGroups = $this->buildAgeGroupStats($matiId);

        return [
            'kependudukan' => [
                'total_penduduk' => $hidup + $pindah, // Total active records
                'hidup' => $hidup,
                'pindah' => $pindah,
                'mati' => $mati,
                'kk' => $keluarga,
                'laki' => $laki,
                'perempuan' => $perempuan,
            ],
            'kependudukan_detail' => [
                'laki' => $laki,
                'perempuan' => $perempuan,
                'hidup' => $hidup,
                'pindah' => $pindah,
                'mati' => $mati,
                'belum_bekerja' => $belumBekerja,
                'sudah_bekerja' => $sudahBekerja,
                'pendidikan_top' => $topPendidikan,
                'pekerjaan_top' => $topPekerjaan,
                'status_kawin_top' => $topPerkawinan,
                'agama_top' => $topAgama,
                'suku_top' => $topSuku,
                'golongan_darah_top' => $topGoldar,
                'usia_top' => $usiaTop ? ['label' => $usiaTop['label'] ?? $usiaTop['label'], 'total' => $usiaTop['value'] ?? 0] : ['label' => '-', 'total' => 0],
                'status_top' => $topStatusDasar,
            ],
            'umur' => [
                'hidup' => $hidup,
                'mati' => $mati,
                'laki' => $laki,
                'perempuan' => $perempuan,
                'groups' => $customAgeGroups,
            ],
            'wilayah' => [
                'dusun' => $dusun,
                'rw' => $rw,
                'rt' => $rt,
            ],
            'publikasi' => [
                'berita' => $berita,
                'halaman' => $pages,
                'video' => $video,
                'dokumen' => $dokumen,
                'galeri' => $galeri,
                'transparansi' => $transparansi,
            ],
            'publikasi_detail' => [
                'berita' => [
                    'total' => $berita,
                    'published' => $beritaPublished,
                    'draft' => $beritaDraft,
                    'archived' => $beritaArchived,
                    'featured' => $beritaFeatured,
                    'views' => $beritaViews,
                ],
                'halaman' => [
                    'total' => $pages,
                    'published' => $pagesPublished,
                    'draft' => $pagesDraft,
                    'views' => $pagesViews,
                ],
                'transparansi' => [
                    'total' => $transparansi,
                    'years' => $transparansiYears,
                    'pendapatan' => $transparansiByCategory['pendapatan'] ?? 0,
                    'pelaksanaan' => $transparansiByCategory['pelaksanaan'] ?? 0,
                    'pembelanjaan' => $transparansiByCategory['pembelanjaan'] ?? 0,
                    'total_items' => $transparansiTotalCount,
                    'published_items' => $transparansiPublishedCount,
                    'total_anggaran' => $transparansiTotalAnggaran,
                    'total_realisasi' => $transparansiTotalRealisasi,
                ],
            ],
            'pemerintahan' => [
                'pegawai' => $pegawai,
                'agenda_aktif' => $activeAgendaCount,
                'agenda_selesai' => $agendaSelesai,
                'agenda_total' => $agendaTotal,
                'pegawai_status' => $pegawaiStatuses,
            ],
            'pegawai_detail' => [
                'total' => $pegawai,
                'aktif' => $this->statusCount($pegawaiStatuses, 'aktif'),
                'cuti' => $this->statusCount($pegawaiStatuses, 'cuti'),
                'pensiun' => $this->statusCount($pegawaiStatuses, 'pensiun'),
                'tidak_aktif' => $this->statusCount($pegawaiStatuses, 'tidak aktif'),
            ],
        ];
    }

    /**
     * Normalise status label lookup
     *
     * @param array<string,int> $statuses
     */
    private function statusCount(array $statuses, string $target): int
    {
        $normalised = collect($statuses)->mapWithKeys(function ($value, $key) {
            $label = strtolower(trim((string) $key));
            $label = str_replace(' ', '_', $label);
            return [$label => (int) $value];
        });

        $targetKey = str_replace(' ', '_', strtolower(trim($target)));

        return (int) $normalised->get($targetKey, 0);
    }

    /**
     * Kelompok usia kustom untuk dashboard.
     *
     * @return array<string,array{label:string,total:int}>
     */
    private function buildAgeGroupStats(?int $deadStatusId): array
    {
        $now = now();

        $rows = DB::table('penduduks')
            ->when($deadStatusId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $deadStatusId))
            ->selectRaw("
                CASE
                    WHEN tanggal_lahir IS NULL THEN 'tidak_diketahui'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) <= 1 THEN 'bayi'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 2 AND 5 THEN 'balita'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 6 AND 12 THEN 'anak'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 13 AND 21 THEN 'remaja'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 22 AND 34 THEN 'dewasa_muda'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 35 AND 50 THEN 'dewasa'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 51 AND 65 THEN 'paruh_baya'
                    ELSE 'lansia'
                END AS label,
                COUNT(*) AS total
            ", [$now, $now, $now, $now, $now, $now, $now])
            ->groupBy('label')
            ->pluck('total', 'label')
            ->map(fn($v) => (int) $v)
            ->all();

        $order = [
            'bayi' => 'Bayi (0-1)',
            'balita' => 'Balita (1-5)',
            'anak' => 'Anak-anak (5-12)',
            'remaja' => 'Remaja (12-21)',
            'dewasa_muda' => 'Dewasa Muda (20-34)',
            'dewasa' => 'Dewasa (35-50)',
            'paruh_baya' => 'Paruh Baya (51-65)',
            'lansia' => 'Lansia (65+)',
        ];

        $result = [];
        foreach ($order as $key => $label) {
            $result[$key] = [
                'label' => $label,
                'total' => $rows[$key] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Ambil kategori teratas untuk foreign key penduduk tertentu.
     */
    private function topCategoryStat(string $column, string $referenceTable, ?int $deadStatusId): array
    {
        if (!Schema::hasTable($referenceTable) || !Schema::hasColumn('penduduks', $column)) {
            return ['label' => '-', 'total' => 0];
        }

        $row = DB::table('penduduks')
            ->when($deadStatusId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $deadStatusId))
            ->leftJoin($referenceTable, "{$referenceTable}.id", '=', "penduduks.{$column}")
            ->selectRaw("COALESCE({$referenceTable}.nama, 'Belum diatur') as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(1)
            ->first();

        return [
            'label' => $row->label ?? '-',
            'total' => (int) ($row->total ?? 0),
        ];
    }

    private function synchroniseStatistics(array $summary): Collection
    {
        $datasets = [
            'penduduk' => [
                ['label' => 'Penduduk Hidup', 'value' => $summary['kependudukan']['hidup'] ?? 0, 'order' => 1],
                ['label' => 'Penduduk Pindah', 'value' => $summary['kependudukan']['pindah'] ?? 0, 'order' => 2],
                ['label' => 'Jumlah KK', 'value' => $summary['kependudukan']['kk'] ?? 0, 'order' => 3],
                ['label' => 'Total Penduduk', 'value' => $summary['kependudukan']['total_penduduk'] ?? 0, 'order' => 4],
            ],
            'wilayah' => [
                ['label' => 'Jumlah Dusun', 'value' => $summary['wilayah']['dusun'] ?? 0, 'order' => 1],
                ['label' => 'Jumlah RW', 'value' => $summary['wilayah']['rw'] ?? 0, 'order' => 2],
                ['label' => 'Jumlah RT', 'value' => $summary['wilayah']['rt'] ?? 0, 'order' => 3],
            ],
            'pekerjaan' => $this->buildReferenceStatistic('pekerjaan_id', 'ref_pekerjaan', limit: 6),
            'pendidikan' => $this->buildReferenceStatistic('pendidikan_sedang_id', 'ref_pendidikan', limit: 6),
            'agama' => $this->buildReferenceStatistic('agama_id', 'ref_agama'),
            'status_perkawinan' => $this->buildReferenceStatistic('status_kawin_id', 'ref_status_kawin'),
            'golongan_darah' => $this->buildReferenceStatistic('golongan_darah_id', 'ref_golongan_darah'),
            'usia' => $this->buildUsiaStatistic(),
        ];

        $updatedBy = Auth::id();

        foreach ($datasets as $type => $items) {
            $labels = collect($items)->pluck('label')->filter()->all();

            if (!empty($labels)) {
                DashboardStatistic::where('type', $type)->whereNotIn('label', $labels)->delete();
            }

            foreach ($items as $index => $item) {
                DashboardStatistic::updateOrCreate(
                    ['type' => $type, 'label' => $item['label']],
                    [
                        'value' => (int) $item['value'],
                        'order_column' => $item['order'] ?? ($index + 1),
                        'meta' => $item['meta'] ?? null,
                        'updated_by' => $updatedBy,
                    ]
                );
            }
        }

        return DashboardStatistic::query()
            ->orderBy('type')
            ->orderBy('order_column')
            ->orderBy('label')
            ->get()
            ->groupBy('type');
    }

    private function buildReferenceStatistic(string $column, string $table, int $limit = 10): array
    {
        $matiId = $this->statusLookup(['MATI'])['MATI'] ?? null;

        $query = DB::table('penduduks')
            ->when($matiId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $matiId))
            ->select("$table.nama as label", DB::raw('COUNT(penduduks.id) as total'))
            ->join($table, "penduduks.{$column}", '=', "$table.id")
            ->groupBy("$table.nama")
            ->orderByDesc('total');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get()
            ->map(fn($row, $index) => [
                'label' => $row->label,
                'value' => (int) $row->total,
                'order' => $index + 1,
            ])
            ->toArray();
    }

    private function buildUsiaStatistic(): array
    {
        $matiId = $this->statusLookup(['MATI'])['MATI'] ?? null;

        return DB::table('penduduks')
            ->when($matiId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $matiId))
            ->selectRaw("
                CASE
                    WHEN tanggal_lahir IS NULL THEN 'Tidak diketahui'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURRENT_DATE) < 13 THEN 'Anak (0-12)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURRENT_DATE) BETWEEN 13 AND 17 THEN 'Remaja (13-17)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURRENT_DATE) BETWEEN 18 AND 59 THEN 'Dewasa (18-59)'
                    ELSE 'Lansia (60+)'
                END AS label,
                COUNT(*) AS total
            ")
            ->groupBy('label')
            ->orderByRaw("
                CASE label
                    WHEN 'Anak (0-12)' THEN 1
                    WHEN 'Remaja (13-17)' THEN 2
                    WHEN 'Dewasa (18-59)' THEN 3
                    WHEN 'Lansia (60+)' THEN 4
                    ELSE 5
                END
            ")
            ->get()
            ->map(fn($row, $index) => [
                'label' => $row->label,
                'value' => (int) $row->total,
                'order' => $index + 1,
            ])
            ->toArray();
    }

    /**
     * @param array<int,string> $names
     * @return array<string,int|null>
     */
    private function statusLookup(array $names): array
    {
        $targets = collect($names)
            ->map(fn($name) => strtoupper(trim($name)))
            ->filter()
            ->values();

        if ($targets->isEmpty()) {
            return [];
        }

        return RefStatusDasar::query()
            ->select('id', 'nama')
            ->whereIn(DB::raw('UPPER(nama)'), $targets->all())
            ->get()
            ->mapWithKeys(fn($row) => [strtoupper($row->nama) => (int) $row->id])
            ->all();
    }
}
