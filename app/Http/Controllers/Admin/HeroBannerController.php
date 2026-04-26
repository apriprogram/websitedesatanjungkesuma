<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\InfoMediaBanner;
use App\Models\InfographicBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HeroBannerController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->get();
        $infoBanners = InfoMediaBanner::orderBy('sort_order')->get();
        $infographicBanners = InfographicBanner::orderBy('sort_order')->get();
        return view('admin.hero-banners', compact('slides', 'infoBanners', 'infographicBanners'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSlide($request);
        $data['background_url'] = $this->storeImage($request);
        unset($data['image']);

        HeroSlide::create($data);

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('status', 'Banner berhasil ditambahkan.')
            ->with('status_variant', 'success');
    }

    public function update(Request $request, HeroSlide $hero_banner)
    {
        $data = $this->validateSlide($request, $hero_banner);

        if ($request->hasFile('image')) {
            $data['background_url'] = $this->storeImage($request, $hero_banner);
        }

        unset($data['image']);
        $hero_banner->update($data);

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('status', 'Banner berhasil diperbarui.')
            ->with('status_variant', 'success');
    }

    public function destroy(HeroSlide $hero_banner)
    {
        if ($hero_banner->background_url) {
            Storage::disk('public')->delete($hero_banner->background_url);
        }

        $hero_banner->delete();

        return redirect()
            ->route('admin.hero-banners.index')
            ->with('status', 'Banner berhasil dihapus.')
            ->with('status_variant', 'success');
    }

    protected function validateSlide(Request $request, HeroSlide $hero_banner = null): array
    {
        $imageRules = $hero_banner ? ['nullable', 'image', 'max:4096'] : ['required', 'image', 'max:4096'];

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_label' => 'nullable|string|max:255',
            'button_url' => 'nullable|url',
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'sort_order' => 'nullable|integer|min:0',
            'image' => $imageRules,
        ]);

        $data['is_profile_slide'] = $request->boolean('is_profile_slide');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }

    protected function storeImage(Request $request, HeroSlide $hero_banner = null): ?string
    {
        if (!$request->hasFile('image')) {
            return $hero_banner ? $hero_banner->background_url : null;
        }

        if ($hero_banner && $hero_banner->background_url) {
            Storage::disk('public')->delete($hero_banner->background_url);
        }

        return $request->file('image')->store('hero-banners', 'public');
    }

    public function storeInfo(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active'])],
            'sort_order' => 'nullable|integer|min:0',
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $data['image_url'] = $request->file('image')->store('banner-info', 'public');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        InfoMediaBanner::create($data);

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Banner info berhasil ditambahkan.')->with('status_variant', 'success');
    }

    public function updateInfo(Request $request, InfoMediaBanner $infoBanner)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active'])],
            'sort_order' => 'nullable|integer|min:0',
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($infoBanner->image_url);
            $data['image_url'] = $request->file('image')->store('banner-info', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? $infoBanner->sort_order ?? 0;

        $infoBanner->update($data);

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Banner info berhasil diperbarui.')->with('status_variant', 'success');
    }

    public function storeInfographic(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active'])],
            'sort_order' => 'nullable|integer|min:0',
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $data['image_url'] = $request->file('image')->store('infographics', 'public');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        InfographicBanner::create($data);

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Infografis berhasil ditambahkan.')->with('status_variant', 'success');
    }

    public function destroyInfo(InfoMediaBanner $infoBanner)
    {
        Storage::disk('public')->delete($infoBanner->image_url);
        $infoBanner->delete();

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Banner info berhasil dihapus.')->with('status_variant', 'success');
    }

    public function destroyInfographic(InfographicBanner $banner)
    {
        Storage::disk('public')->delete($banner->image_url);
        $banner->delete();

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Infografis berhasil dihapus.')->with('status_variant', 'success');
    }

    public function updateInfographic(Request $request, InfographicBanner $banner)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['draft', 'active'])],
            'sort_order' => 'nullable|integer|min:0',
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_url);
            $data['image_url'] = $request->file('image')->store('infographics', 'public');
        }

        $data['sort_order'] = $data['sort_order'] ?? $banner->sort_order ?? 0;

        $banner->update($data);

        return redirect()->route('admin.hero-banners.index')
            ->with('status', 'Infografis berhasil diperbarui.')->with('status_variant', 'success');
    }
}
