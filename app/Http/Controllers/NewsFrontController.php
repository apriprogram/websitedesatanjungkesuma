<?php

namespace App\Http\Controllers;

use App\Models\NavigationMenu;
use App\Models\News;
use App\Models\PublicInfoSetting;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NewsFrontController extends Controller
{
    public function index(Request $request)
    {
        $newsQuery = News::with(['category', 'author'])
            ->where('status', 'published');

        // Search Filter
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $newsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Category Filter (Array of IDs)
        $selectedCategories = (array) $request->input('categories', []);
        if (!empty($selectedCategories)) {
            $newsQuery->whereIn('category_id', $selectedCategories);
        }

        // Year Filter
        $selectedYear = $request->input('year');
        if ($selectedYear && is_numeric($selectedYear)) {
            $newsQuery->whereYear(DB::raw('COALESCE(published_at, created_at)'), $selectedYear);
        }

        $newsQuery->orderByDesc(DB::raw('COALESCE(published_at, created_at)'))
            ->orderByDesc('created_at');

        $news = $newsQuery->paginate(10)->withQueryString();

        // Data for Sidebar Filters
        $categories = \App\Models\Category::whereHas('news', function ($q) {
            $q->where('status', 'published');
        })
            ->withCount([
                    'news' => function ($q) {
                        $q->where('status', 'published');
                    }
                ])
            ->get();

        $availableYears = News::where('status', 'published')
            ->selectRaw('YEAR(COALESCE(published_at, created_at)) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $navMenus = NavigationMenu::with([
            'children' => function ($q) {
                $q->orderBy('position');
            }
        ])->orderBy('position')->get();

        $socialLinks = Schema::hasTable('social_links')
            ? SocialLink::orderBy('sort_order')->get()
            : collect();

        $publicInfoSetting = Schema::hasTable('public_info_settings')
            ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => false]))
            : new PublicInfoSetting(['is_published' => false]);

        return view('frontend.news.index', compact(
            'news',
            'categories',
            'availableYears',
            'selectedCategories',
            'selectedYear',
            'navMenus',
            'socialLinks',
            'publicInfoSetting'
        ));
    }

    public function show(News $news)
    {
        if ($news->status !== 'published') {
            abort(404);
        }

        // Tambah jumlah views setiap kali halaman berita dibuka
        $news->increment('views');
        $news->refresh();

        $navMenus = NavigationMenu::with([
            'children' => function ($q) {
                $q->orderBy('position');
            }
        ])->orderBy('position')->get();

        $socialLinks = Schema::hasTable('social_links')
            ? SocialLink::orderBy('sort_order')->get()
            : collect();

        $publicInfoSetting = Schema::hasTable('public_info_settings')
            ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => false]))
            : new PublicInfoSetting(['is_published' => false]);

        return view('frontend.news.show', compact('news', 'navMenus', 'socialLinks', 'publicInfoSetting'));
    }
}
