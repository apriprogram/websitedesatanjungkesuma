<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NavigationMenuController extends Controller
{
    public function index(Request $request)
    {
        $menus = NavigationMenu::with('children')->whereNull('parent_id')->orderBy('position')->get();
        $allMenus = NavigationMenu::orderBy('title')->get();
        $pages = Page::orderBy('title')->get();
        $editing = null;

        $stats = [
            'total' => NavigationMenu::count(),
            'active' => NavigationMenu::where('is_active', true)->count(),
            'submenu' => NavigationMenu::whereNotNull('parent_id')->count(),
            'custom' => NavigationMenu::where('type', 'custom')->count(),
        ];

        if ($request->filled('edit')) {
            $editing = NavigationMenu::find($request->integer('edit'));
        }

        return view('admin.navigation-menus.index', compact('menus', 'allMenus', 'editing', 'pages', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $this->validateMenu($request);
        NavigationMenu::create($data);

        return back()->with('status', 'Menu berhasil ditambahkan.')->with('status_variant', 'success');
    }

    public function update(Request $request, NavigationMenu $navigation_menu)
    {
        $data = $this->validateMenu($request, $navigation_menu->id);
        $navigation_menu->update($data);

        return redirect()->route('admin.navigation-menus.index')->with('status', 'Menu berhasil diperbarui.')->with('status_variant', 'success');
    }

    public function destroy(NavigationMenu $navigation_menu)
    {
        $navigation_menu->delete();

        return back()->with('status', 'Menu berhasil dihapus.')->with('status_variant', 'success');
    }

    protected function validateMenu(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(['page', 'custom'])],
            'page_slug' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'parent_id' => ['nullable', 'exists:navigation_menus,id'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'target_blank' => ['nullable', 'boolean'],
        ]);
    }
}
