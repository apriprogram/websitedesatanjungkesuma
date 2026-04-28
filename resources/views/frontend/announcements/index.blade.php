@php
    use Illuminate\Support\Str;
    $categories = $categories ?? [];
    $defaultAnnouncementImage = asset('img/Logo/speaker.png');

    $resolveAnnouncementImage = function ($announcement) use ($defaultAnnouncementImage) {
        $img = null;
        if ($announcement->relationLoaded('imageAttachments')) {
            $img = optional($announcement->imageAttachments->first())->url;
        } else {
            $img = optional($announcement->imageAttachments()->first())->url;
        }
        if (!$img && $announcement->relationLoaded('attachments')) {
            $img = optional($announcement->attachments->where('type', 'image')->first())->url;
        } elseif (!$img && method_exists($announcement, 'attachments')) {
            $img = optional($announcement->attachments()->where('type', 'image')->first())->url;
        }
        return image_url($img, $defaultAnnouncementImage);
    };
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengumuman Desa</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <style>
        body {
            background: #f6f7fb;
        }
    </style>
</head>

<body>
    @include('frontend.partials.nav', ['navMenus' => $navMenus ?? collect()])

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            @php
                $navTree = ($navMenus ?? collect())->where('is_active', true)->whereNull('parent_id')->sortBy('position');
                $resolveUrl = function ($item) {
                    if ($item->type === 'custom' && $item->url) return $item->url;
                    if ($item->page_slug) return url($item->page_slug);
                    return '#';
                };
            @endphp
            @foreach ($navTree as $item)
                @php
                    $children = $item->children->where('is_active', true)->sortBy('position');
                    $menuId = 'nav-' . $loop->index;
                @endphp
                <li>
                    <a href="{{ $resolveUrl($item) }}"
                        @if($children->count()) onclick="toggleMobileSubmenu(event, '{{ $menuId }}')" @endif
                        @if($item->target_blank) target="_blank" rel="noopener" @endif>
                        {{ $item->title }}
                    </a>
                    @if($children->count())
                        <ul class="mobile-submenu" id="{{ $menuId }}-submenu">
                            @foreach ($children as $child)
                                <li>
                                    <a href="{{ $resolveUrl($child) }}" @if($child->target_blank) target="_blank" rel="noopener" @endif>
                                        {{ $child->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
            <li><a href="{{ route('budget.transparency.page') }}">Transparansi Anggaran</a></li>
        </ul>
    </div>

    <section class="announcement-page-section">
        <div class="announcement-container">
            <div class="announcement-content">
                <!-- Header -->
                <div class="announcement-page-header">
                    <div class="announcement-page-title-group">
                        <div class="section-title-icon">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div>
                            <h2 class="announcement-page-title">Pengumuman</h2>
                            <p class="announcement-page-desc">Daftar pengumuman terbaru.</p>
                        </div>
                    </div>
                    <a href="{{ route('home') }}#pengumuman" class="announcement-page-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <!-- Filters -->
                <div class="announcement-page-filters">
                    <a class="announcement-tab {{ $category === 'all' || !$category ? 'active' : '' }}"
                        href="{{ route('announcements.index') }}">Semua</a>
                    @foreach($categories as $key => $label)
                        <a class="announcement-tab {{ $category === $key ? 'active' : '' }}"
                            href="{{ route('announcements.index', ['category' => $key]) }}">{{ $label }}</a>
                    @endforeach
                </div>

                <!-- Toolbar Search & Per Page -->
                <div class="announcement-page-toolbar">
                    <div class="announcement-search-wrapper">
                        <i class="fas fa-search search-icon-inside"></i>
                        <input type="search" id="announcementSearchInput" class="announcement-page-search-input"
                            placeholder="Cari pengumuman..." value="{{ $search }}">
                    </div>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <select id="announcementPerPageInput" onchange="submitAnnouncementSearch()"
                            class="announcement-page-select">
                            <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / Hal</option>
                            <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 / Hal</option>
                            <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / Hal</option>
                        </select>

                    </div>
                </div>

                <script>
                    function submitAnnouncementSearch() {
                        const q = document.getElementById('announcementSearchInput').value;
                        const perPage = document.getElementById('announcementPerPageInput').value;
                        const category = '{{ $category ?? "all" }}';

                        const url = new URL("{{ route('announcements.index') }}");
                        if (q) url.searchParams.set('q', q);
                        if (perPage) url.searchParams.set('per_page', perPage);
                        if (category && category !== 'all') url.searchParams.set('category', category);

                        window.location.href = url.toString();
                    }

                    document.getElementById('announcementSearchInput').addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            submitAnnouncementSearch();
                        }
                    });
                </script>

                <!-- List -->
                <div class="announcement-page-list">
                    @forelse($announcements as $item)
                        @php
                            $date = optional($item->published_at ?? $item->created_at);
                            $dateStr = $date?->locale('id')->translatedFormat('l, d M Y') ?? '-';
                            $img = $resolveAnnouncementImage($item);
                        @endphp
                        <a class="announcement-item" href="{{ route('announcements.show', $item) }}">
                            <div class="announcement-media">
                                <img src="{{ $img }}" alt="{{ $item->title }}" loading="lazy">
                            </div>
                            <div class="announcement-body">
                                <h3 class="announcement-title">{{ $item->title }}</h3>
                                <div class="announcement-desc">
                                    {{ Str::limit(strip_tags($item->excerpt ?? $item->body), 100) }}
                                </div>
                            </div>
                            <div class="announcement-date-col">
                                <div class="announcement-date-text">
                                    <span>{{ $dateStr }}</span>
                                    <i class="fas fa-chevron-right announcement-chevron"></i>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="pegawai-empty">
                            <i class="fas fa-clipboard-list" style="font-size:32px; color:#cbd5e1; margin-bottom:10px;"></i>
                            <div style="font-weight:600; color:#94a3b8;">Belum ada pengumuman ditemukan</div>
                            <p style="font-size:0.9rem; margin-top:4px;">Silakan coba kata kunci lain atau kategori berbeda.
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                <div style="margin-top:30px;">
                    {{ $announcements->onEachSide(1)->links('frontend.partials.pagination') }}
                </div>
            </div>
        </div>
    </section>

    @include('frontend.partials.footer', [
        'socialLinks' => $socialLinks ?? collect(),
        'publicInfoSetting' => $publicInfoSetting ?? null,
    ])


    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>
</html>
