<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SekilasInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SekilasInfoController extends Controller
{
    public function index()
    {
        $items = Schema::hasTable('sekilas_infos')
            ? SekilasInfo::ordered()->get()
            : collect();

        $stats = [
            'total' => $items->count(),
            'active' => $items->where('is_active', true)->count(),
            'inactive' => $items->where('is_active', false)->count(),
            'scheduled' => $items->whereNotNull('published_at')->count(),
            'lastUpdated' => optional($items->sortByDesc('updated_at')->first())->updated_at,
        ];

        return view('admin.sekilas-info.index', compact('items', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('sekilas_infos')) {
            return back()->with('error', 'Tabel sekilas info belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $data = $this->validateData($request);

        if ($data['is_active'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        SekilasInfo::create($data);

        return back()->with('status', 'Sekilas info berhasil ditambahkan.');
    }

    public function update(Request $request, SekilasInfo $sekilasInfo): RedirectResponse
    {
        $data = $this->validateData($request);

        if ($data['is_active'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $sekilasInfo->update($data);

        return back()->with('status', 'Sekilas info berhasil diperbarui.');
    }

    public function destroy(SekilasInfo $sekilasInfo): RedirectResponse
    {
        $sekilasInfo->delete();

        return back()->with('status', 'Sekilas info dihapus.');
    }

    public function toggle(SekilasInfo $sekilasInfo): RedirectResponse
    {
        $sekilasInfo->update([
            'is_active' => ! $sekilasInfo->is_active,
            'published_at' => $sekilasInfo->is_active ? $sekilasInfo->published_at : ($sekilasInfo->published_at ?? now()),
        ]);

        return back()->with('status', 'Status sekilas info diperbarui.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'content.required' => 'Isi sekilas info wajib diisi.',
        ]) + [
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->input('sort_order', 0),
        ];
    }
}
