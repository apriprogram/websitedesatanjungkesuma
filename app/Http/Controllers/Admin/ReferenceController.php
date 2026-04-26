<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\References\RefAgama;
use App\Models\References\RefCacat;
use App\Models\References\RefCaraKb;
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
use App\Support\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReferenceController extends Controller
{
    /**
     * Master configuration for each reference table.
     *
     * @var array<string, array<string, mixed>>
     */
    public const CATEGORIES = [
        'ref_agama' => [
            'label' => 'Agama',
            'icon' => 'fas fa-mosque',
            'description' => 'Daftar agama resmi yang digunakan dalam pendataan penduduk.',
            'model' => RefAgama::class,
        ],
        'ref_cacat' => [
            'label' => 'Jenis Disabilitas',
            'icon' => 'fas fa-wheelchair',
            'description' => 'Kategori disabilitas untuk penduduk berkebutuhan khusus.',
            'model' => RefCacat::class,
        ],
        'ref_cara_kb' => [
            'label' => 'Metode KB',
            'icon' => 'fas fa-heartbeat',
            'description' => 'Metode keluarga berencana yang dilaporkan oleh warga.',
            'model' => RefCaraKb::class,
        ],
        'ref_golongan_darah' => [
            'label' => 'Golongan Darah',
            'icon' => 'fas fa-droplet',
            'description' => 'Referensi golongan darah yang dicatat pada profil penduduk.',
            'model' => RefGolonganDarah::class,
        ],
        'ref_hub_keluarga' => [
            'label' => 'Hubungan Keluarga',
            'icon' => 'fas fa-users',
            'description' => 'Status hubungan antar anggota dalam kartu keluarga.',
            'model' => RefHubKeluarga::class,
        ],
        'ref_jenis_kelamin' => [
            'label' => 'Jenis Kelamin',
            'icon' => 'fas fa-venus-mars',
            'description' => 'Pilihan jenis kelamin sesuai dokumen kependudukan.',
            'model' => RefJenisKelamin::class,
        ],
        'ref_pekerjaan' => [
            'label' => 'Pekerjaan',
            'icon' => 'fas fa-briefcase',
            'description' => 'Jenis pekerjaan yang tersedia pada data penduduk.',
            'model' => RefPekerjaan::class,
        ],
        'ref_pendidikan' => [
            'label' => 'Pendidikan',
            'icon' => 'fas fa-graduation-cap',
            'description' => 'Jenjang pendidikan yang digunakan dalam pendataan.',
            'model' => RefPendidikan::class,
        ],
        'ref_status_dasar' => [
            'label' => 'Status Dasar',
            'icon' => 'fas fa-id-card',
            'description' => 'Status dasar kependudukan (hidup, meninggal, pindah, dan lainnya).',
            'model' => RefStatusDasar::class,
        ],
        'ref_status_kawin' => [
            'label' => 'Status Perkawinan',
            'icon' => 'fas fa-ring',
            'description' => 'Status perkawinan resmi tiap penduduk.',
            'model' => RefStatusKawin::class,
        ],
        'ref_status_rekam' => [
            'label' => 'Status Rekam KTP-el',
            'icon' => 'fas fa-fingerprint',
            'description' => 'Status proses perekaman KTP elektronik.',
            'model' => RefStatusRekam::class,
        ],
        'ref_suku' => [
            'label' => 'Suku / Etnis',
            'icon' => 'fas fa-users',
            'description' => 'Daftar suku atau etnis yang digunakan pada profil penduduk.',
            'model' => RefSuku::class,
        ],
        'ref_warganegara' => [
            'label' => 'Kewarganegaraan',
            'icon' => 'fas fa-flag',
            'description' => 'Daftar status kewarganegaraan yang diakui.',
            'model' => RefWarganegara::class,
        ],
    ];

    private const ENTRIES_OPTIONS = [10, 25, 50, 100];

    private const SORT_LABELS = [
        'recent' => 'Terbaru diperbarui',
        'az' => 'Nama A-Z',
        'za' => 'Nama Z-A',
    ];

    public static function categorySlugs(): array
    {
        return array_keys(self::CATEGORIES);
    }

    public function index(Request $request): View
    {
        [$activeSlug, $activeCategory] = $this->safeCategory($request->string('category')->toString());

        $entries = (int) $request->input('entries', 10);
        if (! in_array($entries, self::ENTRIES_OPTIONS, true)) {
            $entries = 10;
        }

        $sort = $request->string('sort')->toString();
        if (! array_key_exists($sort, self::SORT_LABELS)) {
            $sort = 'recent';
        }

        $search = trim($request->string('search')->toString());

        $modelClass = $activeCategory['model'];
        $query = $modelClass::query();

        if ($search !== '') {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        switch ($sort) {
            case 'az':
                $query->orderBy('nama');
                break;
            case 'za':
                $query->orderByDesc('nama');
                break;
            default:
                $query->orderByDesc('updated_at');
        }

        $references = $query
            ->paginate($entries)
            ->withQueryString();

        $categories = $this->categoriesForView($activeSlug);
        $currentCategory = $categories[$activeSlug];

        $today = Carbon::today();
        $todayChanges = $modelClass::whereDate('updated_at', $today)->count();
        $latestRecord = $modelClass::orderByDesc('updated_at')->first();
        $latestCreated = $modelClass::orderByDesc('created_at')->first();
        $recentHighlights = $modelClass::orderByDesc('updated_at')->limit(3)->pluck('nama')->all();

        $metrics = [
            'total' => $currentCategory['count'],
            'today_changes' => $todayChanges,
            'recent_highlights' => $recentHighlights,
            'last_update_at' => $latestRecord?->updated_at,
            'last_created_at' => $latestCreated?->created_at,
        ];

        return view('admin.references.index', [
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'activeCategory' => $activeSlug,
            'references' => $references,
            'search' => $search,
            'sort' => $sort,
            'sortOptions' => self::SORT_LABELS,
            'entries' => $entries,
            'entriesOptions' => self::ENTRIES_OPTIONS,
            'metrics' => $metrics,
            'lastUpdateAt' => $latestRecord?->updated_at,
            'lastCreatedAt' => $latestCreated?->created_at,
            'recentNames' => $recentHighlights,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', Rule::in(self::categorySlugs())],
            'nama' => ['required', 'string', 'max:100', Rule::unique($request->input('category'), 'nama')],
            'form_context' => ['nullable', 'string'],
        ]);

        [$categorySlug, $category] = $this->strictCategory($data['category']);
        $modelClass = $category['model'];

        $reference = $modelClass::create([
            'nama' => trim($data['nama']),
        ]);

        ActivityLogger::log('references.created', $reference, "{$category['label']} ditambahkan", [
            'category' => $category['label'],
            'nama' => $reference->nama,
        ]);

        return redirect()
            ->route('admin.references.index', ['category' => $categorySlug])
            ->with('status', "{$category['label']} baru berhasil disimpan.")
            ->with('status_variant', 'success');
    }

    public function update(Request $request, string $category, int $reference): RedirectResponse
    {
        [$categorySlug, $categoryMeta] = $this->strictCategory($category);
        $modelClass = $categoryMeta['model'];
        $record = $modelClass::findOrFail($reference);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique($categorySlug, 'nama')->ignore($record->id)],
            'form_context' => ['nullable', 'string'],
        ]);

        $record->update([
            'nama' => trim($data['nama']),
        ]);

        ActivityLogger::log('references.updated', $record, "{$categoryMeta['label']} diperbarui", [
            'category' => $categoryMeta['label'],
            'nama' => $record->nama,
        ]);

        return redirect()
            ->route('admin.references.index', ['category' => $categorySlug])
            ->with('status', "{$categoryMeta['label']} berhasil diperbarui.")
            ->with('status_variant', 'info');
    }

    public function destroy(string $category, int $reference): RedirectResponse
    {
        [$categorySlug, $categoryMeta] = $this->strictCategory($category);
        $modelClass = $categoryMeta['model'];
        $record = $modelClass::findOrFail($reference);
        $recordName = $record->nama;

        $record->delete();

        ActivityLogger::log('references.deleted', $record, "{$categoryMeta['label']} dihapus", [
            'category' => $categoryMeta['label'],
            'nama' => $recordName,
        ]);

        return redirect()
            ->route('admin.references.index', ['category' => $categorySlug])
            ->with('status', "{$categoryMeta['label']} berhasil dihapus.")
            ->with('status_variant', 'danger');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'category' => ['required', 'string', Rule::in(self::categorySlugs())],
            'selected_ids' => ['required', 'array', 'min:1'],
            'selected_ids.*' => ['integer'],
            'search' => ['nullable', 'string'],
            'entries' => ['nullable', 'integer'],
            'sort' => ['nullable', 'string'],
        ], [
            'selected_ids.required' => 'Pilih minimal satu data untuk dihapus.',
            'selected_ids.array' => 'Format data terpilih tidak valid.',
        ]);

        [$categorySlug, $categoryMeta] = $this->strictCategory($payload['category']);
        $modelClass = $categoryMeta['model'];

        $ids = collect($payload['selected_ids'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()
                ->with('status', 'Tidak ada data yang dipilih untuk dihapus.')
                ->with('status_variant', 'info');
        }

        $records = $modelClass::whereIn('id', $ids)->get();
        $deletedCount = $records->count();

        if ($deletedCount === 0) {
            return back()
                ->with('status', 'Data yang dipilih sudah tidak tersedia.')
                ->with('status_variant', 'info');
        }

        $modelClass::whereIn('id', $ids)->delete();

        if ($subject = $records->first()) {
            ActivityLogger::log('references.bulk_deleted', null, "{$categoryMeta['label']} dihapus massal", [
                'category' => $categoryMeta['label'],
                'total' => $deletedCount,
                'ids' => $ids->all(),
            ]);
        }

        $query = array_filter([
            'category' => $categorySlug,
            'search' => $payload['search'] ?? null,
            'entries' => $payload['entries'] ?? null,
            'sort' => $payload['sort'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        return redirect()
            ->route('admin.references.index', $query)
            ->with('status', "{$deletedCount} data {$categoryMeta['label']} berhasil dihapus.")
            ->with('status_variant', 'danger');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function categoriesForView(string $activeSlug): array
    {
        $items = [];

        foreach (self::CATEGORIES as $slug => $category) {
            $modelClass = $category['model'];
            $items[$slug] = [
                'slug' => $slug,
                'label' => $category['label'],
                'icon' => $category['icon'],
                'description' => $category['description'],
                'count' => $modelClass::count(),
                'active' => $slug === $activeSlug,
            ];
        }

        return $items;
    }

    /**
     * Resolve category slug safely (fallback to first category).
     *
     * @return array{0:string,1:array<string,mixed>}
     */
    private function safeCategory(?string $slug): array
    {
        if ($slug && isset(self::CATEGORIES[$slug])) {
            return [$slug, self::CATEGORIES[$slug]];
        }

        $firstKey = array_key_first(self::CATEGORIES);

        return [$firstKey, self::CATEGORIES[$firstKey]];
    }

    /**
     * Resolve category slug or abort when invalid.
     *
     * @return array{0:string,1:array<string,mixed>}
     */
    private function strictCategory(string $slug): array
    {
        if (! isset(self::CATEGORIES[$slug])) {
            abort(404);
        }

        return [$slug, self::CATEGORIES[$slug]];
    }
}
