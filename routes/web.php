<?php

use App\Http\Controllers\Admin\AgendaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DusunController;
use App\Http\Controllers\Admin\KeluargaController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\NewsCategoryController;
use App\Http\Controllers\Admin\PendudukController;
use App\Http\Controllers\Admin\PendudukMeninggalController;
use App\Http\Controllers\Admin\PendudukPindahController;
use App\Http\Controllers\Admin\PegawaiController;
use App\Http\Controllers\Admin\ReferenceController;
use App\Http\Controllers\Admin\RtController;
use App\Http\Controllers\Admin\RwController;
use App\Http\Controllers\Admin\BudgetItemController;
use App\Http\Controllers\Admin\SekilasInfoController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\DocumentCategoryController;
use App\Http\Controllers\NewsFrontController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\HeroBannerController;
use App\Http\Controllers\Admin\HelpCenterController;
use App\Http\Controllers\Admin\NavigationMenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PublicInfoController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\YoutubeVideoController;
use App\Http\Controllers\AnnouncementFrontController;
use App\Http\Controllers\VideoFrontController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BudgetTransparencyController;
use App\Http\Controllers\Admin\AiAssistantController;
use App\Models\Announcement;
use App\Models\HeroSlide;
use App\Models\InfoMediaBanner;
use App\Models\InfographicBanner;
use App\Models\News;
use App\Models\YoutubeVideo;
use App\Models\BudgetItem;
use App\Models\Penduduk;
use App\Models\SocialLink;
use App\Models\SekilasInfo;
use App\Models\Pegawai;
use App\Models\PublicInfoSetting;
use App\Models\PublicInfoHour;
use App\Models\NavigationMenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/transparansi-anggaran', function (Request $request) {
    $budgetItems = collect();
    $budgetYear = now()->year;

    if (Schema::hasTable('budget_items')) {
        $requestedBudgetYear = $request->integer('year');
        $hasActiveFlag = Schema::hasColumn('budget_items', 'is_active_year');
        $activeBudgetYear = $hasActiveFlag ? BudgetItem::published()->activeYear()->max('year') : null;
        $budgetYear = $requestedBudgetYear ?: ($activeBudgetYear ?? (BudgetItem::published()->max('year') ?? now()->year));

        $budgetItems = BudgetItem::published()
            ->forYear($budgetYear)
            ->ordered()
            ->get()
            ->groupBy('category');
    }

    $navMenus = NavigationMenu::with([
        'children' => function ($q) {
            $q->orderBy('position');
        }
    ])->orderBy('position')->get();

    $socialLinks = Schema::hasTable('social_links')
        ? SocialLink::orderBy('sort_order')->get()
        : collect();

    $publicInfoSetting = Schema::hasTable('public_info_settings')
        ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => true]))
        : new PublicInfoSetting(['is_published' => true]);

    return view('frontend.transparansi', compact(
        'budgetItems',
        'budgetYear',
        'navMenus',
        'socialLinks',
        'publicInfoSetting'
    ));
})->name('budget.transparency.page');

Route::get('/statistik-desa', fn() => redirect()->route('statistics.population.page'))->name('statistics.page');

Route::get('/pegawai-desa', function () {
    $pegawaiList = Schema::hasTable('pegawais')
        ? Pegawai::whereNull('deleted_at')->orderBy('nama')->get()
        : collect();

    $statuses = $pegawaiList->map(function ($p) {
        return $p->status_kepegawaian ?? $p->status ?? 'Tidak diketahui';
    })->filter()->unique()->values();

    $navMenus = NavigationMenu::with([
        'children' => function ($q) {
            $q->orderBy('position');
        }
    ])->orderBy('position')->get();

    $socialLinks = Schema::hasTable('social_links')
        ? SocialLink::orderBy('sort_order')->get()
        : collect();

    $publicInfoSetting = Schema::hasTable('public_info_settings')
        ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => true]))
        : new PublicInfoSetting(['is_published' => true]);

    return view('frontend.pegawai', compact(
        'pegawaiList',
        'statuses',
        'navMenus',
        'socialLinks',
        'publicInfoSetting'
    ));
})->name('pegawai.page');

Route::get('/statistik-penduduk', function (Request $request) {
    $selectedDusun = $request->integer('dusun');
    $populationStats = [
        'total' => 0,
        'male' => 0,
        'female' => 0,
        'alive' => 0,
        'male_percent' => 0,
        'female_percent' => 0,
    ];
    $statistikData = [
        'pekerjaan' => ['labels' => [], 'data' => []],
        'pendidikan' => ['labels' => [], 'data' => []],
        'agama' => ['labels' => [], 'data' => []],
        'perkawinan' => ['labels' => [], 'data' => []],
        'usia' => ['labels' => [], 'data' => []],
        'golongan_darah' => ['labels' => [], 'data' => []],
        'suku' => ['labels' => [], 'data' => []],
    ];
    $dusunOptions = collect();
    $dusunColumn = null;
    $wilayahData = collect();

    if (Schema::hasTable('penduduks')) {
        $hasDusunId = Schema::hasColumn('penduduks', 'dusun_id');
        $hasDusunName = Schema::hasColumn('penduduks', 'dusun');
        $dusunColumn = $hasDusunId ? 'dusun_id' : ($hasDusunName ? 'dusun' : null);

        if (Schema::hasTable('dusuns')) {
            $dusunOptions = DB::table('dusuns')
                ->select('id', DB::raw('COALESCE(nama, CONCAT("Dusun ", id)) as nama'))
                ->orderBy('nama')
                ->get();
        } elseif ($dusunColumn) {
            $dusunOptions = DB::table('penduduks')
                ->select($dusunColumn . ' as id', $dusunColumn . ' as nama')
                ->whereNotNull($dusunColumn)
                ->groupBy($dusunColumn)
                ->orderBy($dusunColumn)
                ->get();
        }

        // Data wilayah (per dusun)
        if ($dusunColumn) {
            $wilayahQuery = DB::table('penduduks');
            if ($hasDusunId && Schema::hasTable('dusuns')) {
                $wilayahQuery->leftJoin('dusuns', 'dusuns.id', '=', "penduduks.$dusunColumn")
                    ->selectRaw('COALESCE(dusuns.nama, CONCAT("Dusun ", penduduks.' . $dusunColumn . ')) as label, COUNT(*) as total')
                    ->groupBy('label')
                    ->orderBy('label');
            } else {
                $wilayahQuery->selectRaw("COALESCE(penduduks.$dusunColumn, 'Tidak diketahui') as label, COUNT(*) as total")
                    ->groupBy('label')
                    ->orderBy('label');
            }
            $wilayahData = $wilayahQuery->get();
        }

        $hasGenderTable = Schema::hasTable('ref_jenis_kelamin');
        $hasStatusTable = Schema::hasTable('ref_status_dasar');
        $deadStatusId = $hasStatusTable
            ? DB::table('ref_status_dasar')->whereRaw('UPPER(nama) = ?', ['MATI'])->value('id')
            : null;
        $aliveStatusId = $hasStatusTable
            ? DB::table('ref_status_dasar')->whereRaw('UPPER(nama) = ?', ['HIDUP'])->value('id')
            : null;

        $applyBaseFilter = function ($query) use ($deadStatusId, $selectedDusun, $dusunColumn) {
            if ($deadStatusId) {
                $query->where('penduduks.status_dasar_id', '!=', $deadStatusId);
            }
            if ($selectedDusun && $dusunColumn) {
                $query->where("penduduks.$dusunColumn", $selectedDusun);
            }
            return $query;
        };

        $basePendudukQuery = $applyBaseFilter(DB::table('penduduks'));
        $total = $basePendudukQuery->count();

        $genderQuery = $applyBaseFilter(DB::table('penduduks'));
        if ($hasGenderTable) {
            $genderQuery->leftJoin('ref_jenis_kelamin', 'penduduks.jenis_kelamin_id', '=', 'ref_jenis_kelamin.id')
                ->selectRaw('COALESCE(UPPER(ref_jenis_kelamin.nama), "TIDAK DIKETAHUI") as gender, COUNT(penduduks.id) as total')
                ->groupBy('gender');
        } else {
            $genderQuery->selectRaw('jenis_kelamin_id as gender, COUNT(*) as total')
                ->groupBy('jenis_kelamin_id');
        }

        $genderCounts = $genderQuery->pluck('total', 'gender');

        $male = 0;
        $female = 0;
        foreach ($genderCounts as $gender => $count) {
            $g = strtoupper((string) $gender);
            if (str_contains($g, 'LAKI') || $g === '1') {
                $male += (int) $count;
            } elseif (str_contains($g, 'PEREM') || $g === '2') {
                $female += (int) $count;
            }
        }

        $alive = 0;
        if ($aliveStatusId) {
            $alive = $applyBaseFilter(DB::table('penduduks'))
                ->where('penduduks.status_dasar_id', $aliveStatusId)
                ->count();
        }

        $malePercent = $total > 0 ? round(($male / $total) * 100) : 0;
        $femalePercent = $total > 0 ? round(($female / $total) * 100) : 0;

        $populationStats = [
            'total' => $total,
            'male' => $male,
            'female' => $female,
            'alive' => $alive,
            'dead' => 0,
            'male_percent' => $malePercent,
            'female_percent' => $femalePercent,
        ];

        $buildCategoryStats = function (string $foreignKey, string $referenceTable) use ($deadStatusId, $selectedDusun, $dusunColumn) {
            if (!Schema::hasTable($referenceTable) || !Schema::hasColumn('penduduks', $foreignKey)) {
                return ['labels' => [], 'data' => []];
            }

            $rows = DB::table('penduduks')
                ->when($deadStatusId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $deadStatusId))
                ->when($selectedDusun && $dusunColumn, fn($q) => $q->where("penduduks.$dusunColumn", $selectedDusun))
                ->leftJoin($referenceTable, "{$referenceTable}.id", '=', "penduduks.{$foreignKey}")
                ->selectRaw("COALESCE({$referenceTable}.nama, 'Belum diatur') as label, COUNT(*) as total")
                ->groupBy('label')
                ->orderByDesc('total')
                ->get();

            return [
                'labels' => $rows->pluck('label')->values()->all(),
                'data' => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
            ];
        };

        $statistikData['pekerjaan'] = $buildCategoryStats('pekerjaan_id', 'ref_pekerjaan');
        $statistikData['pendidikan'] = $buildCategoryStats('pendidikan_sedang_id', 'ref_pendidikan');
        $statistikData['agama'] = $buildCategoryStats('agama_id', 'ref_agama');
        $statistikData['perkawinan'] = $buildCategoryStats('status_kawin_id', 'ref_status_kawin');
        $statistikData['golongan_darah'] = $buildCategoryStats('golongan_darah_id', 'ref_golongan_darah');
        $statistikData['suku'] = $buildCategoryStats('suku_id', 'ref_suku');

        $now = now();
        $usiaRows = DB::table('penduduks')
            ->when($deadStatusId, fn($q) => $q->where('penduduks.status_dasar_id', '!=', $deadStatusId))
            ->when($selectedDusun && $dusunColumn, fn($q) => $q->where("penduduks.$dusunColumn", $selectedDusun))
            ->selectRaw("
                CASE
                    WHEN tanggal_lahir IS NULL THEN 'Tidak diketahui'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 0 AND 17 THEN '0-17'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 18 AND 30 THEN '18-30'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 31 AND 45 THEN '31-45'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 46 AND 60 THEN '46-60'
                    ELSE '61+'
                END as label,
                COUNT(*) as total
            ", [$now, $now, $now, $now])
            ->groupBy('label')
            ->get();

        $usiaOrder = ['0-17', '18-30', '31-45', '46-60', '61+', 'Tidak diketahui'];
        $usiaMap = collect($usiaRows)->mapWithKeys(fn($row) => [$row->label => (int) ($row->total ?? 0)]);

        foreach ($usiaOrder as $label) {
            if ($usiaMap->has($label)) {
                $statistikData['usia']['labels'][] = $label;
                $statistikData['usia']['data'][] = $usiaMap[$label];
            }
        }
    }

    $navMenus = NavigationMenu::with([
        'children' => function ($q) {
            $q->orderBy('position');
        }
    ])->orderBy('position')->get();

    $socialLinks = Schema::hasTable('social_links')
        ? SocialLink::orderBy('sort_order')->get()
        : collect();

    $publicInfoSetting = Schema::hasTable('public_info_settings')
        ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => true]))
        : new PublicInfoSetting(['is_published' => true]);

    return view('frontend.statistics-population', compact(
        'populationStats',
        'statistikData',
        'selectedDusun',
        'dusunOptions',
        'wilayahData',
        'navMenus',
        'socialLinks',
        'publicInfoSetting'
    ));
})->name('statistics.population.page');

Route::get('/statistik-anggaran', function (Request $request) {
    $budgetItems = collect();
    $budgetYear = now()->year;
    $budgetYears = collect();
    $selectedCategory = $request->input('category');
    $allowedCategories = [
        BudgetItem::CATEGORY_PELAKSANAAN,
        BudgetItem::CATEGORY_PENDAPATAN,
        BudgetItem::CATEGORY_PEMBELANJAAN,
    ];

    if (!in_array($selectedCategory, $allowedCategories, true)) {
        $selectedCategory = null;
    }

    if (Schema::hasTable('budget_items')) {
        $budgetYears = BudgetItem::published()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $requestedBudgetYear = $request->integer('year');
        $hasActiveFlag = Schema::hasColumn('budget_items', 'is_active_year');
        $activeBudgetYear = $hasActiveFlag ? BudgetItem::published()->activeYear()->max('year') : null;
        $budgetYear = $requestedBudgetYear ?: ($activeBudgetYear ?? (BudgetItem::published()->max('year') ?? now()->year));

        $budgetQuery = BudgetItem::published()
            ->forYear($budgetYear)
            ->ordered();

        if ($selectedCategory) {
            $budgetQuery->where('category', $selectedCategory);
        }

        $budgetItems = $budgetQuery->get()->groupBy('category');
    }

    $budgetSummary = $budgetItems->map(function ($items) {
        $anggaran = (float) $items->sum('anggaran');
        $realisasi = (float) $items->sum('realisasi');
        $percent = $anggaran > 0 ? round(($realisasi / $anggaran) * 100) : 0;
        return [
            'anggaran' => $anggaran,
            'realisasi' => $realisasi,
            'percent' => $percent,
        ];
    });

    $budgetTotals = [
        'anggaran' => (float) $budgetItems->flatten(1)->sum('anggaran'),
        'realisasi' => (float) $budgetItems->flatten(1)->sum('realisasi'),
    ];
    $budgetTotals['percent'] = $budgetTotals['anggaran'] > 0
        ? round(($budgetTotals['realisasi'] / $budgetTotals['anggaran']) * 100)
        : 0;

    $navMenus = NavigationMenu::with([
        'children' => function ($q) {
            $q->orderBy('position');
        }
    ])->orderBy('position')->get();

    $socialLinks = Schema::hasTable('social_links')
        ? SocialLink::orderBy('sort_order')->get()
        : collect();

    $publicInfoSetting = Schema::hasTable('public_info_settings')
        ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => true]))
        : new PublicInfoSetting(['is_published' => true]);

    $categoryOptions = [
        BudgetItem::CATEGORY_PELAKSANAAN => 'Pelaksanaan',
        BudgetItem::CATEGORY_PENDAPATAN => 'Pendapatan',
        BudgetItem::CATEGORY_PEMBELANJAAN => 'Pembelanjaan',
    ];

    return view('frontend.statistics-budget', compact(
        'budgetItems',
        'budgetSummary',
        'budgetTotals',
        'budgetYear',
        'budgetYears',
        'selectedCategory',
        'categoryOptions',
        'navMenus',
        'socialLinks',
        'publicInfoSetting'
    ));
})->name('statistics.budget.page');


Route::get('/news', [NewsFrontController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [NewsFrontController::class, 'show'])->name('news.show');
Route::get('/videos', [VideoFrontController::class, 'index'])->name('videos.index');
Route::get('/announcements', [AnnouncementFrontController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{announcement:slug}', [AnnouncementFrontController::class, 'show'])->name('announcements.show');
Route::get('/budget/transparency/{year?}', BudgetTransparencyController::class)->name('budget.transparency');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        // Settings & media
        Route::get('settings', [UserController::class, 'index'])->name('users.index');
        Route::post('settings/users', [UserController::class, 'store'])->name('users.store');
        Route::put('settings/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('settings/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('settings/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::put('settings/pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('settings/pegawai/{pegawai}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

        Route::get('media/users/{user}', [MediaController::class, 'userAvatar'])->name('media.users.avatar');
        Route::get('media/pegawai/{pegawai}', [MediaController::class, 'pegawaiAvatar'])->name('media.pegawai.avatar');

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::view('/help-center', 'admin.help-center')->name('help-center');
        Route::post('/help-center', [HelpCenterController::class, 'send'])->name('help-center.send');

        // Dokumen Desa
        Route::resource('documents', DocumentController::class);
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::resource('document-categories', DocumentCategoryController::class)->except(['show']);

        // Export endpoints (used by admin-users.js)
        Route::post('users/export', [\App\Http\Controllers\Admin\ExportController::class, 'exportUsers'])->name('users.export');
        Route::post('pegawai/export', [\App\Http\Controllers\Admin\ExportController::class, 'exportPegawai'])->name('pegawai.export');

        // Agenda Desa (to-do)
        Route::resource('agendas', AgendaController::class)
            ->except(['create', 'edit', 'show']);
        Route::patch('agendas/{agenda}/toggle', [AgendaController::class, 'toggle'])->name('agendas.toggle');
        Route::delete('agendas/bulk', [AgendaController::class, 'bulkDestroy'])->name('agendas.bulk-destroy');

        Route::prefix('announcements')->name('announcements.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'index'])->name('index');
            Route::get('create', [AnnouncementController::class, 'create'])->name('create');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::get('{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
            Route::put('{announcement}', [AnnouncementController::class, 'update'])->name('update');
            Route::delete('{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
            Route::delete('{announcement}/attachments/{attachment}', [AnnouncementController::class, 'destroyAttachment'])->name('attachments.destroy');
        });

        Route::patch('sekilas-info/{sekilasInfo}/toggle', [SekilasInfoController::class, 'toggle'])->name('sekilas-info.toggle');
        Route::resource('sekilas-info', SekilasInfoController::class)->except('show');

        Route::get('public-info', [PublicInfoController::class, 'index'])->name('public-info.index');
        Route::put('public-info', [PublicInfoController::class, 'update'])->name('public-info.update');

        Route::post('budget-items/activate-year', [BudgetItemController::class, 'activateYear'])->name('budget-items.activate-year');
        Route::get('budget-items/export/excel', [BudgetItemController::class, 'exportExcel'])->name('budget-items.export.excel');
        Route::get('budget-items/export/pdf', [BudgetItemController::class, 'exportPdf'])->name('budget-items.export.pdf');
        Route::get('budget-items/export/word', [BudgetItemController::class, 'exportWord'])->name('budget-items.export.word');
        Route::patch('budget-items/{budget_item}/toggle', [BudgetItemController::class, 'toggle'])->name('budget-items.toggle');
        Route::resource('budget-items', BudgetItemController::class)->except('show');

        // Berita
        Route::resource('news', NewsController::class)->except('show');
        Route::prefix('news/categories')->name('news.categories.')->group(function () {
            Route::get('/', [NewsCategoryController::class, 'index'])->name('index');
            Route::post('/', [NewsCategoryController::class, 'store'])->name('store');
            Route::put('{category}', [NewsCategoryController::class, 'update'])->name('update');
            Route::delete('{category}', [NewsCategoryController::class, 'destroy'])->name('destroy');
        });
        Route::get('youtube-videos', [YoutubeVideoController::class, 'index'])->name('youtube-videos.index');
        Route::post('youtube-videos', [YoutubeVideoController::class, 'store'])->name('youtube-videos.store');
        Route::put('youtube-videos/{youtubeVideo}', [YoutubeVideoController::class, 'update'])->name('youtube-videos.update');
        Route::patch('youtube-videos/{youtubeVideo}/toggle', [YoutubeVideoController::class, 'toggle'])->name('youtube-videos.toggle');
        Route::delete('youtube-videos/{youtubeVideo}', [YoutubeVideoController::class, 'destroy'])->name('youtube-videos.destroy');

        // AI Assistant
        Route::post('ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');

        // Master data
        Route::resource('dusuns', DusunController::class)->except('show');
        Route::get('dusuns/export/excel', [DusunController::class, 'exportExcel'])->name('dusuns.export.excel');
        Route::get('dusuns/export/pdf', [DusunController::class, 'exportPdf'])->name('dusuns.export.pdf');
        Route::get('dusuns/export/word', [DusunController::class, 'exportWord'])->name('dusuns.export.word');
        Route::resource('rws', RwController::class)->except('show');
        Route::get('rws/export/excel', [RwController::class, 'exportExcel'])->name('rws.export.excel');
        Route::get('rws/export/pdf', [RwController::class, 'exportPdf'])->name('rws.export.pdf');
        Route::get('rws/export/word', [RwController::class, 'exportWord'])->name('rws.export.word');
        Route::resource('rts', RtController::class)->except('show');
        Route::get('rts/export/excel', [RtController::class, 'exportExcel'])->name('rts.export.excel');
        Route::get('rts/export/pdf', [RtController::class, 'exportPdf'])->name('rts.export.pdf');
        Route::get('rts/export/word', [RtController::class, 'exportWord'])->name('rts.export.word');
        Route::resource('keluargas', KeluargaController::class)->except('show');
        Route::get('keluargas/export/excel', [KeluargaController::class, 'exportExcel'])->name('keluargas.export.excel');
        Route::get('keluargas/export/pdf', [KeluargaController::class, 'exportPdf'])->name('keluargas.export.pdf');
        Route::get('keluargas/export/word', [KeluargaController::class, 'exportWord'])->name('keluargas.export.word');
        Route::get('keluargas/{keluarga}/print', [KeluargaController::class, 'print'])->name('keluargas.print');
        Route::post('penduduks/import', [PendudukController::class, 'import'])->name('penduduks.import');
        Route::get('penduduks/export/template', [PendudukController::class, 'exportTemplate'])->name('penduduks.export.template');
        Route::get('penduduks/export/excel', [PendudukController::class, 'exportExcel'])->name('penduduks.export.excel');
        Route::get('penduduks/export/pdf', [PendudukController::class, 'exportPdf'])->name('penduduks.export.pdf');
        Route::get('penduduks/export/word', [PendudukController::class, 'exportWord'])->name('penduduks.export.word');
        Route::resource('penduduks', PendudukController::class)->except('show');
        Route::get('penduduk-pindah/export/excel', [PendudukPindahController::class, 'exportExcel'])->name('penduduk-pindah.export.excel');
        Route::get('penduduk-pindah/export/pdf', [PendudukPindahController::class, 'exportPdf'])->name('penduduk-pindah.export.pdf');
        Route::get('penduduk-pindah/export/word', [PendudukPindahController::class, 'exportWord'])->name('penduduk-pindah.export.word');
        Route::resource('penduduk-pindah', PendudukPindahController::class)->except('show');
        Route::get('penduduk-meninggal/export/excel', [PendudukMeninggalController::class, 'exportExcel'])->name('penduduk-meninggal.export.excel');
        Route::get('penduduk-meninggal/export/pdf', [PendudukMeninggalController::class, 'exportPdf'])->name('penduduk-meninggal.export.pdf');
        Route::get('penduduk-meninggal/export/word', [PendudukMeninggalController::class, 'exportWord'])->name('penduduk-meninggal.export.word');
        Route::resource('penduduk-meninggal', PendudukMeninggalController::class)->except('show');

        Route::prefix('references')->name('references.')->group(function () {
            Route::get('/', [ReferenceController::class, 'index'])->name('index');
            Route::post('/', [ReferenceController::class, 'store'])->name('store');
            Route::delete('bulk', [ReferenceController::class, 'bulkDestroy'])->name('bulk-destroy');
            Route::put('/{category}/{reference}', [ReferenceController::class, 'update'])
                ->name('update')
                ->whereIn('category', ReferenceController::categorySlugs());
            Route::delete('/{category}/{reference}', [ReferenceController::class, 'destroy'])
                ->name('destroy')
                ->whereIn('category', ReferenceController::categorySlugs());
        });

        Route::get('hero-slides', function () {
            $slides = [
                ['title' => 'Selamat Datang di Tanjung Kesuma', 'subtitle' => 'Pilar transformasi desa', 'status' => 'Aktif', 'background' => 'hero-1.jpg'],
                ['title' => 'Program Pelayanan Publik Digital', 'subtitle' => 'Layanan prima untuk seluruh warga', 'status' => 'Aktif', 'background' => 'hero-2.jpg'],
                ['title' => 'Agenda Desa dan Informasi Penting', 'subtitle' => 'Update rutin setiap pekan', 'status' => 'Draft', 'background' => 'hero-3.jpg'],
            ];
            $bannerMenus = [
                ['name' => 'Publikasi', 'label' => 'Tampilkan Banner', 'state' => true],
                ['name' => 'Profil Desa', 'label' => 'Aktifkan Slide Profil', 'state' => false],
            ];
            return view('admin.hero-slides', compact('slides', 'bannerMenus'));
        })->name('hero-slides.index');

        Route::get('hero-banners', [HeroBannerController::class, 'index'])->name('hero-banners.index');
        Route::post('hero-banners', [HeroBannerController::class, 'store'])->name('hero-banners.store');
        Route::put('hero-banners/{hero_banner}', [HeroBannerController::class, 'update'])->name('hero-banners.update');
        Route::delete('hero-banners/{hero_banner}', [HeroBannerController::class, 'destroy'])->name('hero-banners.destroy');
        Route::post('hero-banners/info', [HeroBannerController::class, 'storeInfo'])->name('hero-banners.info.store');
        Route::put('hero-banners/info/{infoBanner}', [HeroBannerController::class, 'updateInfo'])->name('hero-banners.info.update');
        Route::delete('hero-banners/info/{infoBanner}', [HeroBannerController::class, 'destroyInfo'])->name('hero-banners.info.destroy');
        Route::post('hero-banners/infografis', [HeroBannerController::class, 'storeInfographic'])->name('hero-banners.infografis.store');
        Route::put('hero-banners/infografis/{banner}', [HeroBannerController::class, 'updateInfographic'])->name('hero-banners.infografis.update');
        Route::delete('hero-banners/infografis/{banner}', [HeroBannerController::class, 'destroyInfographic'])->name('hero-banners.infografis.destroy');
        Route::get('navigation-menus', [NavigationMenuController::class, 'index'])->name('navigation-menus.index');
        Route::post('navigation-menus', [NavigationMenuController::class, 'store'])->name('navigation-menus.store');
        Route::put('navigation-menus/{navigation_menu}', [NavigationMenuController::class, 'update'])->name('navigation-menus.update');
        Route::delete('navigation-menus/{navigation_menu}', [NavigationMenuController::class, 'destroy'])->name('navigation-menus.destroy');
        Route::resource('pages', PageController::class)->names('pages')->except(['show']);
        Route::delete('pages/{page}/attachments/{attachment}', [PageController::class, 'destroyAttachment'])->name('pages.attachments.destroy');
        Route::get('social-links', [SocialLinkController::class, 'index'])->name('social-links.index');
        Route::put('social-links', [SocialLinkController::class, 'update'])->name('social-links.update');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// Route catch-all untuk halaman dinamis (pindahkan ke paling bawah agar tidak bentrok dengan route lain)
Route::get('/{page:slug}', [\App\Http\Controllers\Admin\PageController::class, 'show'])->name('pages.show');
