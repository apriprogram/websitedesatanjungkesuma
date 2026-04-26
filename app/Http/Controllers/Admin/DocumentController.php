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
        $data = $this->validateData($request);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
            $data['file_size'] = (int) round($file->getSize() / 1024); // KB
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
        $data = $this->validateData($request, $document->id);

        if ($request->hasFile('file')) {
            if ($document->file_path) {
                try {
                    if (file_exists(public_path('storage/' . $document->file_path))) {
                        Storage::disk('public')->delete($document->file_path);
                    }
                } catch (\Exception $e) {
                    // Abaikan error
                }
            }
            $file = $request->file('file');
            $path = $file->store('documents', 'public');
            $data['file_path'] = $path;
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
            $data['file_size'] = (int) round($file->getSize() / 1024); // KB
        }

        $document->update($data);

        return redirect()->route('admin.documents.index')->with('status', 'Dokumen diperbarui.')->with('status_variant', 'success');
    }

    public function destroy(VillageDocument $document)
    {
        if ($document->file_path) {
            try {
                if (file_exists(public_path('storage/' . $document->file_path))) {
                    Storage::disk('public')->delete($document->file_path);
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
        if (! Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->title . '.' . ($document->file_type ?: 'file'));
    }

    protected function validateData(Request $request, $id = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_category_id' => ['required', 'exists:document_categories,id'],
            'description' => ['nullable', 'string'],
            'file' => [$id ? 'nullable' : 'required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (now()->year + 1)],
            'is_public' => ['boolean'],
        ]);
    }
}
