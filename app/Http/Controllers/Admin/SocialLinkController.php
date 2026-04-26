<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Models\SocialLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SocialLinkController extends Controller
{
    private function defaultLinks(): array
    {
        return [
            ['name' => 'Facebook', 'slug' => 'facebook', 'icon' => 'fab fa-facebook-f', 'type' => 'icon'],
            ['name' => 'LinkedIn', 'slug' => 'linkedin', 'icon' => 'fab fa-linkedin-in', 'type' => 'icon'],
            ['name' => 'Instagram', 'slug' => 'instagram', 'icon' => 'fab fa-instagram', 'type' => 'icon'],
            ['name' => 'Twitter', 'slug' => 'twitter', 'icon' => 'fab fa-twitter', 'type' => 'icon'],
            ['name' => 'YouTube', 'slug' => 'youtube', 'icon' => 'fab fa-youtube', 'type' => 'icon'],
            ['name' => 'TikTok', 'slug' => 'tiktok', 'icon' => 'fab fa-tiktok', 'type' => 'icon'],
        ];
    }

    private function defaultTextLinks(): array
    {
        return [
            ['name' => 'Contact', 'slug' => 'contact', 'icon' => 'fa-link', 'type' => 'text'],
            ['name' => 'Feedback', 'slug' => 'feedback', 'icon' => 'fa-link', 'type' => 'text'],
        ];
    }

    public function index()
    {
        if (! Schema::hasTable('social_links')) {
            $links = collect($this->defaultLinks())->map(fn ($item) => (object) $item);
        } else {
            $hasType = Schema::hasColumn('social_links', 'type');

            if ($hasType) {
                SocialLink::whereNull('type')->update(['type' => 'icon']);
            }

            $defaults = $this->defaultLinks();
            foreach ($defaults as $index => $item) {
                $payload = array_merge($item, ['sort_order' => $index]);
                if (! $hasType) {
                    unset($payload['type']);
                }
                SocialLink::firstOrCreate(
                    ['slug' => $item['slug']],
                    $payload
                );
            }

            if ($hasType) {
                foreach ($this->defaultTextLinks() as $index => $item) {
                    SocialLink::firstOrCreate(
                        ['slug' => $item['slug']],
                        array_merge($item, ['sort_order' => 100 + $index])
                    );
                }
            }

            $links = SocialLink::orderBy('sort_order')->get();
        }
        $allowedIconSlugs = collect($this->defaultLinks())->pluck('slug')->all();
        $iconLinks = $links->filter(function ($l) use ($allowedIconSlugs) {
            $slug = $l->slug ?? '';
            $type = $l->type ?? 'icon';
            return $type === 'icon' && in_array($slug, $allowedIconSlugs, true);
        });
        $textLinks = FooterLink::orderBy('sort_order')->get();
        return view('admin.social-links.index', compact('iconLinks', 'textLinks'));
    }

    public function update(Request $request)
    {
        if (! Schema::hasTable('social_links')) {
            return back()->with('error', 'Tabel media sosial belum tersedia.');
        }

        $hasType = Schema::hasColumn('social_links', 'type');

        $validated = $request->validate([
            'links' => ['array'],
            'links.*.url' => ['nullable', 'url'],
            'text_links' => ['array'],
            'text_links.*.name' => ['nullable', 'string', 'max:120'],
            'text_links.*.url' => ['nullable', 'url'],
        ]);

        foreach ($validated['links'] ?? [] as $slug => $link) {
            $record = SocialLink::where('slug', $slug)->first();
            if ($record) {
                $record->update([
                    'url' => $link['url'] ?? null,
                ]);
            }
        }

        if ($request->has('text_links') && Schema::hasTable('footer_links')) {
            $textPayload = collect($validated['text_links'] ?? [])
                ->filter(function ($item) {
                    return !empty(trim($item['name'] ?? '')) || !empty(trim($item['url'] ?? ''));
                })
                ->values();

            // replace footer_links with new ordered set
            FooterLink::truncate();
            foreach ($textPayload as $idx => $item) {
                FooterLink::create([
                    'name' => trim($item['name'] ?? 'Link'),
                    'url' => trim($item['url'] ?? ''),
                    'sort_order' => $idx,
                ]);
            }
        }

        return back()->with('status', 'Data media sosial berhasil diperbarui.');
    }
}
