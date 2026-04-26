<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\Page;
use App\Models\PageAttachment;
use App\Models\PublicInfoSetting;
use App\Models\SocialLink;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 20);
        $pagesQuery = Page::query()->orderByDesc('updated_at');

        if (Schema::hasTable('page_attachments')) {
            $pagesQuery->with(['attachments' => function ($q) {
                $q->orderBy('sort_order')->orderBy('id');
            }]);
        }

        if ($search = request('search')) {
            $pagesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $pagesQuery->paginate($perPage)->appends(request()->only('per_page', 'search'));

        $stats = [
            'total' => Page::count(),
            'published' => Page::where('status', 'published')->count(),
            'draft' => Page::where('status', 'draft')->count(),
            'with_image' => Page::whereNotNull('feature_image')->count(),
            'views' => Schema::hasColumn('pages', 'views') ? Page::sum('views') : 0,
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function create()
    {
        $page = new Page();
        if (! Schema::hasTable('page_attachments')) {
            $page->setRelation('attachments', collect());
        } else {
            $page->loadMissing('attachments');
        }
        return view('admin.pages.form', compact('page'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePage($request);

        if ($request->hasFile('feature_image')) {
            $data['feature_image'] = safe_store($request->file('feature_image'), 'page-images');
        }

        $page = Page::create($data);
        $this->storeAttachments($request, $page);

        ActivityLogger::log('page.created', $page, 'Halaman ditambahkan', [
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status,
        ]);

        return redirect()->route('admin.pages.index')
            ->with('status', 'Halaman berhasil ditambahkan.')
            ->with('status_variant', 'success');
    }

    public function edit(Page $page)
    {
        if (Schema::hasTable('page_attachments')) {
            $page->loadMissing('attachments');
        } else {
            $page->setRelation('attachments', collect());
        }
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validatePage($request, $page->id);

        if ($request->hasFile('feature_image')) {
            if ($page->feature_image) {
                try {
                    if (file_exists(public_path('storage/' . $page->feature_image))) {
                        @unlink(public_path('storage/' . $page->feature_image));
                    }
                } catch (\Exception $e) {
                    // Abaikan error
                }
            }
            $data['feature_image'] = safe_store($request->file('feature_image'), 'page-images');
        }

        $page->update($data);

        $this->storeAttachments($request, $page);

        ActivityLogger::log('page.updated', $page, 'Halaman diperbarui', [
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status,
        ]);

        return redirect()->route('admin.pages.index')
            ->with('status', 'Halaman berhasil diperbarui.')
            ->with('status_variant', 'success');
    }

    public function destroy(Page $page)
    {
        if ($page->feature_image) {
            try {
                if (file_exists(public_path('storage/' . $page->feature_image))) {
                    @unlink(public_path('storage/' . $page->feature_image));
                }
            } catch (\Exception $e) {
                // Abaikan error
            }
        }

        ActivityLogger::log('page.deleted', $page, 'Halaman dihapus', [
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status,
        ]);

        $page->delete();

        return back()->with('status', 'Halaman dihapus.')->with('status_variant', 'success');
    }

    public function destroyAttachment(Page $page, PageAttachment $attachment)
    {
        abort_if($attachment->page_id !== $page->id, 404);

        if ($attachment->path) {
            try {
                if (file_exists(public_path('storage/' . $attachment->path))) {
                    @unlink(public_path('storage/' . $attachment->path));
                }
            } catch (\Exception $e) {
                // Abaikan error
            }
        }
        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.')->with('status_variant', 'success');
    }

    private function storeAttachments(Request $request, Page $page): void
    {
        if (! Schema::hasTable('page_attachments')) {
            return;
        }
        $files = $request->file('attachments_files', []);
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }
            $path = safe_store($file, 'page-attachments');
            PageAttachment::create([
                'page_id' => $page->id,
                'type' => 'file',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => class_exists('finfo') ? $file->getMimeType() : $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $images = $request->file('attachments_images', []);
        foreach ($images as $image) {
            if (! $image) {
                continue;
            }
            $path = safe_store($image, 'page-attachments');
            PageAttachment::create([
                'page_id' => $page->id,
                'type' => 'image',
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime' => class_exists('finfo') ? $image->getMimeType() : $image->getClientMimeType(),
                'size' => $image->getSize(),
            ]);
        }
    }

    protected function validatePage(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($ignoreId)],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'feature_image' => ['nullable', 'image', 'max:4096'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    // Frontend preview
    public function show(Page $page)
    {
        if ($page->status !== 'published') {
            abort(404);
        }

        if (Schema::hasTable('page_attachments')) {
            $page->loadMissing('attachments');
        } else {
            $page->setRelation('attachments', collect());
        }

        // Hitung view setiap kali halaman diakses
        if (Schema::hasColumn('pages', 'views')) {
            $page->increment('views');
        }

        $navMenus = NavigationMenu::with(['children' => function ($q) {
            $q->orderBy('position');
        }])->orderBy('position')->get();

        $socialLinks = Schema::hasTable('social_links')
            ? SocialLink::orderBy('sort_order')->get()
            : collect();

        $publicInfoSetting = Schema::hasTable('public_info_settings')
            ? (PublicInfoSetting::with('hours')->first() ?? new PublicInfoSetting(['is_published' => false]))
            : new PublicInfoSetting(['is_published' => false]);

        return view('frontend.page', compact('page', 'navMenus', 'socialLinks', 'publicInfoSetting'));
    }
}
