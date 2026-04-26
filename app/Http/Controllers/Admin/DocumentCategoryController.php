<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
{
    public function index()
    {
        $categories = DocumentCategory::orderBy('name')->get();

        return view('admin.documents.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:document_categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        DocumentCategory::create($data);

        return back()->with('status', 'Kategori ditambahkan.')->with('status_variant', 'success');
    }

    public function update(Request $request, DocumentCategory $document_category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_categories', 'name')->ignore($document_category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $data['slug'] = Str::slug($data['name']);

        $document_category->update($data);

        return back()->with('status', 'Kategori diperbarui.')->with('status_variant', 'success');
    }

    public function destroy(DocumentCategory $document_category)
    {
        $document_category->delete();

        return back()->with('status', 'Kategori dihapus.')->with('status_variant', 'success');
    }
}
