<?php

namespace App\Http\Controllers;

use App\Models\NavigationMenu;
use App\Models\PublicInfoSetting;
use App\Models\SocialLink;
use App\Models\YoutubeVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class VideoFrontController extends Controller
{
    public function index(Request $request)
    {
        $videos = YoutubeVideo::published()
            ->ordered()
            ->paginate(6)
            ->withQueryString();

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

        return view('frontend.videos.index', compact(
            'videos',
            'navMenus',
            'socialLinks',
            'publicInfoSetting'
        ));
    }
}
