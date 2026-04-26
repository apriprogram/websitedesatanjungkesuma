@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $navTree = ($navMenus ?? collect())
        ->where('is_active', true)
        ->whereNull('parent_id')
        ->sortBy('position');

    // Recursive renderer untuk menu multi-level (hover-friendly)
    $renderMenu = function ($items) use (&$renderMenu) {
        if ($items->isEmpty()) {
            return '';
        }

        $html = '';

        foreach ($items as $item) {
            $children = $item->children->where('is_active', true)->sortBy('position');
            $url = '#';

            if ($item->type === 'custom' && $item->url) {
                $url = $item->url;
            } elseif ($item->page_slug) {
                $url = url($item->page_slug);
            }

            $html .= '<li>';
            $html .= '<a href="' . e($url) . '"' . ($item->target_blank ? ' target="_blank"' : '') . '>';

            if ($item->icon) {
                $html .= '<i class="' . e($item->icon) . '"></i> ';
            }

            $html .= e($item->title) . '</a>';

            if ($children->count()) {
                $html .= '<ul class="dropdown-menu">' . $renderMenu($children) . '</ul>';
            }

            $html .= '</li>';
        }

        return $html;
    };
@endphp
<header class="header">
    <div class="header-container">
        <a href="{{ url('/') }}" class="logo">
            <img src="{{ asset('img/Logo/logo_lampung_timur.png') }}" alt="Logo Desa Tanjung Kesuma" class="logo-image">
            <span class="logo-text">Desa Tanjung Kesuma</span>
        </a>

        @if ($navTree->count())
            <nav>
                <ul class="nav-menu">
                    {!! $renderMenu($navTree) !!}
                </ul>
            </nav>
        @endif

        <div class="search-container header-right">
            <button id="darkModeBtn" class="darkmode-icon" aria-label="Mode Gelap/Terang">
                <i class="fas fa-moon"></i>
            </button>
            <button class="search-icon" onclick="toggleSearch()">
                <i class="fas fa-search"></i>
                <span>Cari</span>
            </button>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Search Bar (Global - appears on all pages) -->
<div class="search-bar-wrapper" id="searchBar">
    <div class="home-search-bar">
        <i class="fas fa-search home-search-icon"></i>
        <input type="text" class="home-search-input" placeholder="Cari berita, pengumuman, atau data desa..."
            id="searchBarInput" aria-label="Pencaharian" onkeypress="if(event.key === 'Enter') performSearchBar()">
    </div>
</div>
