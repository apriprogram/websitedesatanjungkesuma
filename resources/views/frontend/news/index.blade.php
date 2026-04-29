@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    /**
     * Resolver URL media yang aman dari ketergantungan ekstensi 'fileinfo'.
     * Menggunakan file_exists dan asset() sebagai fallback.
     */
    $mediaUrl = function ($path, $default = null) {
        if (empty($path)) {
            return $default;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $normalized = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        $fullPath = public_path('storage/' . $normalized);

        if (file_exists($fullPath)) {
            return asset('storage/' . $normalized);
        }

        return $default;
    };
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Desa</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
</head>

<body>
    @include('frontend.partials.nav', ['navMenus' => $navMenus ?? collect()])

    <!-- Mobile Menu -->
    @include('frontend.partials.mobile-menu', [
        'navMenus' => $navMenus ?? collect(),
        'extraLinks' => [['title' => 'Transparansi Anggaran', 'url' => route('budget.transparency.page')]]
    ])

    <div class="news-header-bg-v2">
        <div class="news-list-page">
            <div class="news-hero-v2">
                <div class="news-breadcrumb-v2">
                    <a href="{{ route('home') }}">Beranda</a>
                    <span class="breadcrumb-sep-v2">&gt;</span>
                    <span>Berita Desa</span>
                </div>
                <h1 class="news-title-hero-v2">Berita Desa</h1>
                <p class="news-desc-hero-v2">
                    Kumpulan berita, agenda, dan pengumuman terkini dari Desa Tanjung Kesuma. Temukan informasi terbaru seputar perkembangan desa di sini.
                </p>

                <form action="{{ route('news.index') }}" method="GET" class="news-search-hero-v2">
                    @foreach($selectedCategories as $selectedCat)
                        <input type="hidden" name="categories[]" value="{{ $selectedCat }}">
                    @endforeach
                    @if($selectedYear)
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                    @endif
                    <i class="fas fa-search search-icon-v2"></i>
                    <input type="text" name="q" placeholder="Cari berita desa..." value="{{ request('q') }}">
                </form>
            </div>
        </div>
    </div>

    <div class="news-list-page">

        <div class="news-container-v2">
            <div class="news-list-v2">
                <div class="news-list-header-v2">
                    {{ $news->total() }} Berita Desa
                </div>

                @forelse($news as $item)
                    @php
                        $thumb = $mediaUrl($item->thumbnail);
                        $publishedAt = $item->published_at ?? $item->created_at;
                    @endphp
                    <a href="{{ route('news.show', $item) }}" class="news-card-v2">
                        <div class="news-date-v2">
                            {{ optional($publishedAt)->locale('id')->translatedFormat('d F Y') }}
                        </div>
                        <div class="news-body-v2">
                            <h2 class="news-title-v2">{{ $item->title }}</h2>
                            <p class="news-excerpt-v2">{{ $item->summary ?? Str::limit(strip_tags($item->content), 120) }}</p>
                            <div class="news-meta-v2">
                                @if($item->category)
                                    <span class="news-tag-v2">{{ $item->category->name }}</span>
                                @endif
                                <span class="news-views-v2">
                                    <i class="fas fa-eye"></i> {{ number_format($item->views ?? 0) }} Dilihat
                                </span>
                            </div>
                        </div>
                        <div class="news-thumb-v2 {{ $thumb ? '' : 'news-thumb-v2--placeholder' }}">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $item->title }}" loading="lazy">
                            @else
                                <i class="fas fa-image"></i>
                            @endif
                        </div>
                    </a>
                @empty
                    <div style="text-align: center; padding: 64px 20px; background: #f8fafc; border-radius: 12px; border: 1px dashed #e2e8f0;">
                        <i class="fas fa-newspaper" style="font-size: 48px; color: #e2e8f0; margin-bottom: 20px; display: block;"></i>
                        <p style="color: #64748b; font-weight: 500; font-size: 1.1rem;">Tidak ada berita yang ditemukan.</p>
                        <a href="{{ route('news.index') }}" style="color: #2563eb; font-weight: 600; text-decoration: none; margin-top: 12px; display: inline-block;">Reset Semua Filter</a>
                    </div>
                @endforelse

                <div class="news-list-pagination">
                    {{ $news->onEachSide(1)->links('frontend.partials.pagination') }}
                </div>
            </div>

            <aside class="news-sidebar-v2">
                <form id="filter-form" action="{{ route('news.index') }}" method="GET">
                    @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    
                    <div class="filter-group-v2">
                        <h3 class="filter-title-v2">Filters</h3>
                    </div>

                    <div class="filter-group-v2">
                        <h3 class="filter-title-v2">Category</h3>
                        <div class="filter-list-v2">
                            @foreach($categories as $cat)
                                <label class="filter-item-v2">
                                    <input type="checkbox" name="categories[]" value="{{ $cat->id }}" 
                                        {{ in_array($cat->id, $selectedCategories) ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    {{ $cat->name }}
                                    <span class="filter-count-v2">({{ $cat->news_count }})</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="filter-group-v2">
                        <h3 class="filter-title-v2">Year</h3>
                        <select name="year" class="filter-select-v2" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(!empty($selectedCategories) || $selectedYear || request('q'))
                        <a href="{{ route('news.index') }}" style="display: block; text-align: center; padding: 12px; background: #f1f5f9; color: #475569; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.95rem; margin-top: 10px;">
                            <i class="fas fa-times" style="margin-right: 8px;"></i> Reset Semua Filter
                        </a>
                    @endif
                </form>
            </aside>
        </div>
    </div>

    @include('frontend.partials.footer', [
        'socialLinks' => $socialLinks ?? collect(),
        'publicInfoSetting' => $publicInfoSetting ?? null,
    ])
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>
</html>
