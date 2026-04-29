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

        // Auto-fill meta title & description
        $data['meta_title'] = $data['title'];
        $data['meta_description'] = \Illuminate\Support\Str::limit(strip_tags($data['content'] ?? ''), 150);

        if ($request->hasFile('feature_image')) {
            $data['feature_image'] = safe_store($request->file('feature_image'), 'page-images');
        } elseif ($request->input('remove_feature_image') === '1') {
            $data['feature_image'] = null;
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

    public function update(Request $request, $id)
    {
        // 1. Manually resolve the page to prevent Route Model Binding 404s
        $page = Page::find($id);
        
        if (!$page) {
            \Illuminate\Support\Facades\Log::error("UPDATE FAILED: Page with ID {$id} not found.");
            return redirect()->route('admin.pages.index')
                ->with('status', 'Gagal: Halaman tidak ditemukan atau mungkin telah dihapus.')
                ->with('status_variant', 'danger');
        }

        // 2. Critical Post Size Check
        if ($request->isMethod('put') && empty($request->all()) && empty($request->file())) {
            return back()->with('status', 'Gagal: Ukuran file terlalu besar untuk server Anda. Mohon unggah secara bertahap.')->with('status_variant', 'danger');
        }

        // 3. Validation
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,' . $id],
            'status' => ['required', 'in:draft,published,archived'],
            'content' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
        ]);

        // 4. Automation
        $data['meta_title'] = $data['title'];
        $data['meta_description'] = \Illuminate\Support\Str::limit(strip_tags($data['content'] ?? ''), 150);

        if ($request->input('published_at') === '') {
            $data['published_at'] = null;
        }

        $removeCover = $request->input('remove_feature_image') === '1';
        $oldImagePath = null;

        if ($request->hasFile('feature_image')) {
            if ($page->feature_image) {
                $oldImagePath = public_path('storage/' . $page->feature_image);
            }
            $data['feature_image'] = safe_store($request->file('feature_image'), 'page-images');
        } elseif ($removeCover) {
            if ($page->feature_image) {
                $oldImagePath = public_path('storage/' . $page->feature_image);
            }
            $data['feature_image'] = null;
        }

        // 5. Transactional Execution
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();
            
            // Re-fetch to lock for update if possible
            $activePage = Page::lockForUpdate()->find($id);
            if (!$activePage) throw new \Exception('Halaman hilang saat proses simpan.');

            $activePage->update($data);
            $this->storeAttachments($request, $activePage);

            if ($request->has('attachment_order')) {
                $orderRaw = $request->input('attachment_order');
                if (!empty($orderRaw)) {
                    $orders = json_decode($orderRaw, true);
                    if (is_array($orders)) {
                        foreach ($orders as $index => $attId) {
                            \App\Models\PageAttachment::where('id', $attId)
                                ->where('page_id', $activePage->id)
                                ->update(['sort_order' => $index]);
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();


            if ($oldImagePath && file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
            try {
                ActivityLogger::log('page.updated', $activePage, 'Halaman diperbarui', [
                    'title' => $activePage->title,
                    'status' => $activePage->status,
                ]);
            } catch (\Throwable $e) {}

            return redirect()->route('admin.pages.index')
                ->with('status', 'Halaman berhasil diperbarui.')
                ->with('status_variant', 'success');

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error("CRITICAL UPDATE ERROR ID {$id}: " . $e->getMessage());
            return back()->withInput()->with('status', 'Gagal menyimpan: ' . $e->getMessage())->with('status_variant', 'danger');
        }
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

        $unifiedFiles = $request->file('unified_attachments', []);
        foreach ($unifiedFiles as $file) {
            if (! $file) continue;
            $mime = $file->getClientMimeType();
            $type = str_starts_with($mime, 'image/') ? 'image' : 'file';
            
            $size = $file->getSize();
            $originalName = $file->getClientOriginalName();
            $path = safe_store($file, 'page-attachments');
            
            PageAttachment::create([
                'page_id' => $page->id,
                'type' => $type,
                'path' => $path,
                'original_name' => $originalName,
                'mime' => $mime,
                'size' => $size,
            ]);
        }

        $files = $request->file('attachments_files', []);
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }
            $size = $file->getSize();
            $mime = $file->getClientMimeType();
            $originalName = $file->getClientOriginalName();
            $path = safe_store($file, 'page-attachments');
            
            PageAttachment::create([
                'page_id' => $page->id,
                'type' => 'file',
                'path' => $path,
                'original_name' => $originalName,
                'mime' => $mime,
                'size' => $size,
            ]);
        }

        $images = $request->file('attachments_images', []);
        foreach ($images as $image) {
            if (! $image) {
                continue;
            }
            $size = $image->getSize();
            $mime = $image->getClientMimeType();
            $originalName = $image->getClientOriginalName();
            $path = safe_store($image, 'page-attachments');
            
            PageAttachment::create([
                'page_id' => $page->id,
                'type' => 'image',
                'path' => $path,
                'original_name' => $originalName,
                'mime' => $mime,
                'size' => $size,
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
