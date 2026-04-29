<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\VillageDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $categories = DocumentCategory::orderBy('name')->get();
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $documents = VillageDocument::with('category', 'uploader')
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('document_category_id', $request->integer('category'));
            })
            ->when($request->filled('public'), function ($q) use ($request) {
                $q->where('is_public', $request->boolean('public'));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->input('search') . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('uploaded_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => VillageDocument::count(),
            'public' => VillageDocument::where('is_public', true)->count(),
            'private' => VillageDocument::where('is_public', false)->count(),
            'categories' => $categories->count(),
        ];

        return view('admin.documents.index', compact('categories', 'documents', 'stats'));
    }

    public function store(Request $request)
    {
        // Cek error upload PHP-level SEBELUM validasi (mencegah RuntimeException)
        if ($request->hasFile('file') && !$request->file('file')->isValid()) {
            return back()->withInput()
                ->with('status', 'File gagal diunggah. Pastikan ukuran file tidak melebihi 10 MB dan coba lagi.')
                ->with('status_variant', 'error');
        }

        try {
            $data = $this->validateData($request);
        } catch (\RuntimeException $e) {
            return back()->withInput()
                ->with('status', 'Terjadi kesalahan saat memproses file. Pastikan ukuran file tidak melebihi 10 MB.')
                ->with('status_variant', 'error');
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            try {
                $file = $request->file('file');
                $path = safe_store($file, 'documents');
                $data['file_path'] = $path;
                $data['file_type'] = strtolower($file->getClientOriginalExtension());
                $data['file_size'] = (int) round($file->getSize() / 1024); // KB
            } catch (\RuntimeException $e) {
                return back()->withInput()
                    ->with('status', 'File tidak dapat diproses. Pastikan format dan ukuran file sesuai (maks 10 MB).')
                    ->with('status_variant', 'error');
            }
        }

        $data['uploaded_by'] = Auth::id();
        $data['uploaded_at'] = now();

        VillageDocument::create($data);

        return back()->with('status', 'Dokumen berhasil diunggah.')->with('status_variant', 'success');
    }

    public function edit(VillageDocument $document)
    {
        $categories = DocumentCategory::orderBy('name')->get();
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = request()->integer('per_page', 10);
        if (! in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $documents = VillageDocument::with('category', 'uploader')
            ->when(request()->filled('search'), function ($q) {
                $term = '%' . request('search') . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('title', 'like', $term)->orWhere('description', 'like', $term);
                });
            })
            ->orderByDesc('uploaded_at')
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => VillageDocument::count(),
            'public' => VillageDocument::where('is_public', true)->count(),
            'private' => VillageDocument::where('is_public', false)->count(),
            'categories' => $categories->count(),
        ];

        return view('admin.documents.index', compact('categories', 'documents', 'document', 'stats'));
    }

    public function update(Request $request, VillageDocument $document)
    {
        // Cek error upload PHP-level SEBELUM validasi (mencegah RuntimeException)
        if ($request->hasFile('file') && !$request->file('file')->isValid()) {
            return back()->withInput()
                ->with('status', 'File gagal diunggah. Pastikan ukuran file tidak melebihi 10 MB dan coba lagi.')
                ->with('status_variant', 'error');
        }

        try {
            $data = $this->validateData($request, $document->id);
        } catch (\RuntimeException $e) {
            return back()->withInput()
                ->with('status', 'Terjadi kesalahan saat memproses file. Pastikan ukuran file tidak melebihi 10 MB.')
                ->with('status_variant', 'error');
        }

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            if ($document->file_path) {
                try {
                    if (file_exists(public_path('storage/' . $document->file_path))) {
                        @unlink(public_path('storage/' . $document->file_path));
                    }
                } catch (\Exception $e) {
                    // Abaikan error
                }
            }
            try {
                $file = $request->file('file');
                $path = safe_store($file, 'documents');
                $data['file_path'] = $path;
                $data['file_type'] = strtolower($file->getClientOriginalExtension());
                $data['file_size'] = (int) round($file->getSize() / 1024); // KB
            } catch (\RuntimeException $e) {
                return back()->withInput()
                    ->with('status', 'File tidak dapat diproses. Pastikan format dan ukuran file sesuai (maks 10 MB).')
                    ->with('status_variant', 'error');
            }
        }

        $document->update($data);

        return redirect()->route('admin.documents.index')->with('status', 'Dokumen diperbarui.')->with('status_variant', 'success');
    }

    public function destroy(VillageDocument $document)
    {
        if ($document->file_path) {
            try {
                if (file_exists(public_path('storage/' . $document->file_path))) {
                    @unlink(public_path('storage/' . $document->file_path));
                }
            } catch (\Exception $e) {
                // Abaikan error
            }
        }

        $document->delete();

        return back()->with('status', 'Dokumen dihapus.')->with('status_variant', 'success');
    }

    public function download(VillageDocument $document)
    {
        $fullPath = public_path('storage/' . $document->file_path);
        
        if (! file_exists($fullPath)) {
            abort(404);
        }

        return response()->download($fullPath, $document->title . '.' . ($document->file_type ?: 'file'));
    }

    protected function validateData(Request $request, $id = null): array
    {
        return $request->validate([
            'title'                => ['required', 'string', 'max:255'],
            'document_category_id' => ['required', 'exists:document_categories,id'],
            'description'          => ['nullable', 'string'],
            // Catatan: 'mimes' dihapus karena membutuhkan ekstensi 'finfo' yang tidak tersedia di server.
            // Validasi ekstensi dilakukan secara manual via getClientOriginalExtension() di controller.
            'file'                 => [$id ? 'nullable' : 'required', 'file', 'max:10240'],
            'year'                 => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'is_public'            => ['boolean'],
        ]);
    }
}
