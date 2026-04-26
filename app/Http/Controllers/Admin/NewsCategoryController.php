<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class NewsCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.news.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validateCategory($request);
        $payload['slug'] = $payload['slug'] ?: Str::slug($payload['name']);

        Category::create($payload);

        return Redirect::route('admin.news.categories.index')->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $payload = $this->validateCategory($request);
        $payload['slug'] = $payload['slug'] ?: Str::slug($payload['name']);

        $category->update($payload);

        return Redirect::route('admin.news.categories.index')->with('status', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return Redirect::route('admin.news.categories.index')->with('status', 'Kategori berhasil dihapus.');
    }

    protected function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
