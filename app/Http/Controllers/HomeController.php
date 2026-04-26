<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\HeroSlide;
use App\Models\InfoMediaBanner;
use App\Models\InfographicBanner;
use App\Models\News;
use App\Models\YoutubeVideo;
use App\Models\BudgetItem;
use App\Models\SekilasInfo;
use App\Models\Pegawai;
use App\Models\PublicInfoSetting;
use App\Models\NavigationMenu;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $news = News::with('category')
            ->where('status', 'published')
            ->orderByDesc(DB::raw('COALESCE(published_at, created_at)'))
            ->orderByDesc('created_at')
            ->get();

        $announcementsByCategory = Announcement::published()
            ->with('imageAttachments')
            ->orderByDesc('published_at')
            ->get()
            ->groupBy('category');

        $announcementSections = Announcement::categories();

        $heroSlides = HeroSlide::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $infoBanners = InfoMediaBanner::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $infographicBanners = InfographicBanner::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $budgetItems = collect();
        $budgetYear = now()->year;
        if (Schema::hasTable('budget_items')) {
            $requestedBudgetYear = request()->integer('year');
            $hasActiveFlag = Schema::hasColumn('budget_items', 'is_active_year');
            $activeBudgetYear = $hasActiveFlag ? BudgetItem::published()->activeYear()->max('year') : null;
            $budgetYear = $requestedBudgetYear ?: ($activeBudgetYear ?? (BudgetItem::published()->max('year') ?? now()->year));
            $budgetItems = BudgetItem::published()
                ->forYear($budgetYear)
                ->ordered()
                ->get()
                ->groupBy('category');
        }

        $populationStats = $this->getPopulationStats();
        $statistikData = $this->getStatistikData();
        $wilayahData = $this->getWilayahData();

        $socialLinks = Schema::hasTable('social_links') ? SocialLink::orderBy('sort_order')->get() : collect();
        $youtubeVideos = Schema::hasTable('youtube_videos') ? YoutubeVideo::published()->ordered()->take(8)->get() : collect();
        $sekilasInfos = Schema::hasTable('sekilas_infos') ? SekilasInfo::published()->ordered()->get() : collect();
        $pegawaiList = Schema::hasTable('pegawais') ? Pegawai::whereNull('deleted_at')->orderBy('nama')->get() : collect();

        $publicInfoSetting = Schema::hasTable('public_info_settings')
            ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => true]))
            : new PublicInfoSetting(['is_published' => true]);

        $publicInfoHours = $this->getPublicInfoHours($publicInfoSetting);

        $heroSlides = $this->processHeroSlides($heroSlides);

        $navMenus = NavigationMenu::with(['children' => fn($q) => $q->orderBy('position')])
            ->orderBy('position')
            ->get();

        return view('frontend.home', compact(
            'news', 'announcementSections', 'announcementsByCategory', 'heroSlides',
            'infoBanners', 'infographicBanners', 'socialLinks', 'youtubeVideos',
            'budgetItems', 'budgetYear', 'populationStats', 'sekilasInfos',
            'pegawaiList', 'publicInfoSetting', 'publicInfoHours', 'navMenus',
            'statistikData', 'wilayahData'
        ));
    }

    private function getPopulationStats()
    {
        if (!Schema::hasTable('penduduks')) return ['total' => 0, 'male' => 0, 'female' => 0, 'alive' => 0, 'male_percent' => 0, 'female_percent' => 0];

        $hasStatusTable = Schema::hasTable('ref_status_dasar');
        $deadStatusId = $hasStatusTable ? DB::table('ref_status_dasar')->whereRaw('UPPER(nama) = ?', ['MATI'])->value('id') : null;
        $aliveStatusId = $hasStatusTable ? DB::table('ref_status_dasar')->whereRaw('UPPER(nama) = ?', ['HIDUP'])->value('id') : null;

        $total = DB::table('penduduks')->when($deadStatusId, fn($q) => $q->where('status_dasar_id', '!=', $deadStatusId))->count();
        
        $genderCounts = DB::table('penduduks')
            ->when($deadStatusId, fn($q) => $q->where('status_dasar_id', '!=', $deadStatusId))
            ->selectRaw('jenis_kelamin_id as gender, COUNT(*) as total')
            ->groupBy('jenis_kelamin_id')
            ->pluck('total', 'gender');

        $male = (int)($genderCounts['1'] ?? $genderCounts['LAKI-LAKI'] ?? 0);
        $female = (int)($genderCounts['2'] ?? $genderCounts['PEREMPUAN'] ?? 0);

        return [
            'total' => $total,
            'male' => $male,
            'female' => $female,
            'alive' => $aliveStatusId ? DB::table('penduduks')->where('status_dasar_id', $aliveStatusId)->count() : $total,
            'male_percent' => $total > 0 ? round(($male / $total) * 100) : 0,
            'female_percent' => $total > 0 ? round(($female / $total) * 100) : 0,
        ];
    }

    private function getStatistikData()
    {
        if (!Schema::hasTable('penduduks')) return [];

        $matiId = Schema::hasTable('ref_status_dasar') 
            ? DB::table('ref_status_dasar')->whereRaw('UPPER(nama) = ?', ['MATI'])->value('id') 
            : null;

        $buildCategoryStats = function ($column, $table) use ($matiId) {
            if (!Schema::hasTable($table) || !Schema::hasColumn('penduduks', $column)) {
                return ['labels' => [], 'data' => []];
            }

            $rows = DB::table('penduduks')
                ->when($matiId, fn($q) => $q->where('status_dasar_id', '!=', $matiId))
                ->join($table, "{$table}.id", '=', "penduduks.{$column}")
                ->selectRaw("{$table}.nama as label, COUNT(*) as total")
                ->groupBy('label')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            return [
                'labels' => $rows->pluck('label')->all(),
                'data' => $rows->pluck('total')->map(fn($v) => (int)$v)->all(),
            ];
        };

        $now = now();
        $usiaRows = DB::table('penduduks')
            ->when($matiId, fn($q) => $q->where('status_dasar_id', '!=', $matiId))
            ->selectRaw("
                CASE
                    WHEN tanggal_lahir IS NULL THEN 'Lainnya'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) < 13 THEN 'Anak-anak'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 13 AND 17 THEN 'Remaja'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, ?) BETWEEN 18 AND 59 THEN 'Dewasa'
                    ELSE 'Lansia'
                END AS label,
                COUNT(*) AS total
            ", [$now, $now, $now])
            ->groupBy('label')
            ->get();

        return [
            'pekerjaan' => $buildCategoryStats('pekerjaan_id', 'ref_pekerjaan'),
            'pendidikan' => $buildCategoryStats('pendidikan_sedang_id', 'ref_pendidikan'),
            'agama' => $buildCategoryStats('agama_id', 'ref_agama'),
            'perkawinan' => $buildCategoryStats('status_kawin_id', 'ref_status_kawin'),
            'golongan_darah' => $buildCategoryStats('golongan_darah_id', 'ref_golongan_darah'),
            'usia' => [
                'labels' => $usiaRows->pluck('label')->all(),
                'data' => $usiaRows->pluck('total')->map(fn($v) => (int)$v)->all(),
            ],
        ];
    }

    private function getWilayahData()
    {
        if (!Schema::hasTable('penduduks')) return collect();
        $hasDusunId = Schema::hasColumn('penduduks', 'dusun_id');
        $dusunColumn = $hasDusunId ? 'dusun_id' : (Schema::hasColumn('penduduks', 'dusun') ? 'dusun' : null);
        
        if (!$dusunColumn) return collect();

        return DB::table('penduduks')
            ->selectRaw("COALESCE($dusunColumn, 'Tidak diketahui') as label, COUNT(*) as total")
            ->groupBy('label')
            ->get();
    }

    private function getPublicInfoHours($setting)
    {
        if (!$setting->exists || !Schema::hasTable('public_info_hours')) return collect();
        $labels = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return $setting->hours->map(function ($hour) use ($labels) {
            $hour->day_label = $labels[$hour->day_of_week] ?? $hour->day_of_week;
            return $hour;
        });
    }

    private function processHeroSlides($slides)
    {
        if ($slides->isEmpty()) {
            return collect([(object)[
                'title' => 'Selamat Datang di Desa Tanjung Kesuma',
                'subtitle' => 'Desa berbudaya dan ramah digital',
                'description' => 'Informasi resmi desa, layanan publik, dan agenda dikemas satu halaman.',
                'image_url' => asset('img/Banner/gambar_desa_1.png'),
                'button_label' => 'Layanan Publik',
                'button_url' => '#layanan',
            ]]);
        }

        return $slides->map(function ($slide) {
            $path = $slide->background_url;
            if (Str::startsWith($path, ['http://', 'https://'])) {
                $slide->image_url = $path;
            } else {
                $storagePath = 'storage/' . ltrim($path, '/');
                $slide->image_url = file_exists(public_path($storagePath)) ? asset($storagePath) : asset('img/Banner/gambar_desa_1.png');
            }
            return $slide;
        });
    }
}
