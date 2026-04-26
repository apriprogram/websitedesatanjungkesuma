<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\NavigationMenu;
use App\Models\PublicInfoSetting;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnnouncementFrontController extends Controller
{
    public function index(Request $request)
    {
        $categories = Announcement::categories();
        $category = $request->get('category');
        $search = trim((string) $request->get('q', ''));

        $query = Announcement::published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if ($category && array_key_exists($category, $categories)) {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }

        $announcements = $query->paginate($perPage)->withQueryString();

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

        return view('frontend.announcements.index', compact(
            'announcements',
            'categories',
            'category',
            'search',
            'navMenus',
            'socialLinks',
            'publicInfoSetting',
            'perPage'
        ));
    }

    public function show(Announcement $announcement)
    {
        if ($announcement->status !== Announcement::STATUS_PUBLISHED) {
            abort(404);
        }

        $announcement->increment('views');

        $announcement->load('attachments');

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

        return view('frontend.announcements.show', compact(
            'announcement',
            'navMenus',
            'socialLinks',
            'publicInfoSetting'
        ));
    }
}
