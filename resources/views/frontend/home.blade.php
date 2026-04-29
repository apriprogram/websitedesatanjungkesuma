<!DOCTYPE html>
<html lang="id">

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Route;
    $mediaUrl = function ($path, $default = null) {
        if (empty($path)) {
            return $default;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        
        // Cek file di folder storage/ secara langsung (lebih aman untuk hosting)
        $storagePath = 'storage/' . ltrim($path, '/');
        if (file_exists(public_path($storagePath))) {
            return asset($storagePath);
        }
        
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        return $default;
    };
@endphp

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desa Tanjung Kesuma - Website Resmi</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <meta name="description"
        content="Website resmi Desa Tanjung Kesuma: informasi desa, layanan publik, berita, statistik, dan kontak.">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/transparency.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/widget-animations.css') }}?v={{ time() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <!-- Library JS dimuat di akhir body untuk performa -->
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="#" class="logo">
                <img src="img/Logo/logo_lampung_timur.png" alt="Logo Desa Tanjung Kesuma" class="logo-image">
                <span class="logo-text">Desa Tanjung Kesuma</span>
            </a>

            @php
                $navTree = ($navMenus ?? collect())->where('is_active', true)->whereNull('parent_id')->sortBy('position');
                $renderMenu = function ($items) use (&$renderMenu) {
                    if ($items->isEmpty())
                        return '';
                    $html = '';
                    foreach ($items as $item) {
                        $children = $item->children->where('is_active', true)->sortBy('position');
                        $url = '#';
                        if ($item->type === 'custom' && $item->url) {
                            $url = $item->url;
                        } elseif ($item->page_slug) {
                            $url = url($item->page_slug);
                        }

                        $title = $item->title;
                        $displayUrl = $url;
                        $isAuth = auth()->check();

                        if ($isAuth && (Str::lower($title) === 'login' || Str::lower($title) === 'masuk')) {
                            $title = 'Dashboard';
                            $displayUrl = route('admin.dashboard');
                        }

                        $html .= '<li>';
                        $html .= '<a href="' . e($displayUrl) . '"' . ($item->target_blank ? ' target="_blank"' : '') . '>';
                        if ($item->icon) {
                            $html .= '<i class="' . e($item->icon) . '"></i> ';
                        }
                        $html .= e($title) . '</a>';
                        if ($children->count()) {
                            $html .= '<ul class="dropdown-menu">' . $renderMenu($children) . '</ul>';
                        }
                        $html .= '</li>';
                    }
                    return $html;
                };
            @endphp

            @if($navTree->count())
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

    <!-- Search Bar (Global - appears when search button is clicked) -->
    <div class="search-bar-wrapper" id="searchBar">
        <div class="home-search-bar">
            <i class="fas fa-search home-search-icon"></i>
            <input type="text" class="home-search-input" placeholder="Cari berita, pengumuman, atau data desa..."
                id="searchBarInput" aria-label="Pencaharian" onkeypress="if(event.key === 'Enter') performSearchBar()">
        </div>
    </div>

    <!-- Top-left Clock & Weather (fixed) -->
    <div id="topLeftInfo" class="top-left-info" role="region" aria-label="Jam dan Cuaca">
        <button class="tli-clock" id="tliClock" title="Jam" aria-label="Jam"
            style="background:none;border:0;color:inherit;padding:0;cursor:pointer">--:--:--</button>
        <div class="tli-date" id="tliDate">Tanggal</div>
        <div class="tli-weather" id="tliWeather" data-latitude="-5.257" data-longitude="105.465"
            data-timezone="Asia/Jakarta">
            <i class="tli-weather-icon fas fa-cloud"></i>
            <span class="tli-weather-text">Memuat cuacaÎ“Ã‡Âª</span>
        </div>
    </div>

    <!-- Admin Login Modal (hidden) -->
    <div id="adminLoginModal" class="admin-modal" aria-hidden="true" role="dialog" aria-label="Login Admin">
        <div class="admin-modal-overlay"></div>
        <div class="admin-card">
            <button class="admin-close" id="adminClose" aria-label="Tutup">&times;</button>
            <div class="admin-card-pane admin-card-form">
                <div class="admin-brand">
                    <img src="img/Logo/logo_lampung_timur.png" alt="Logo Desa Tanjung Kesuma" class="admin-brand-logo"
                        loading="lazy">
                    <div class="admin-brand-copy">
                        <span class="admin-brand-eyebrow">Panel Admin</span>
                        <span class="admin-brand-title">Desa Tanjung Kesuma</span>
                    </div>
                    <span class="admin-brand-meta"><i class="fas fa-circle"></i> Aktif</span>
                </div>
                <div class="admin-form-header">
                    <h2 class="admin-form-title">Kelola Website Desa Lebih Cepat</h2>
                    <p class="admin-form-sub">Masuk untuk memperbarui konten, memantau layanan publik, dan menyajikan
                        informasi terbaru bagi warga.</p>
                </div>
                <form id="adminLoginForm" class="admin-form-fields" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    @if ($errors->has('email') || $errors->has('password'))
                        <div class="admin-form-alert" role="alert">
                            {{ $errors->first('email') ?? $errors->first('password') }}
                        </div>
                    @endif
                    <div class="admin-input-block">
                        <label class="admin-label" for="adminUsername">Email</label>
                        <div class="admin-input-group">
                            <span class="admin-input-icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                            <input class="admin-input" type="email" id="adminUsername" name="email"
                                value="{{ old('email') }}" placeholder="Masukkan email" autocomplete="email" required>
                        </div>
                    </div>
                    <div class="admin-input-block">
                        <label class="admin-label" for="adminPass">Password</label>
                        <div class="admin-input-group">
                            <span class="admin-input-icon" aria-hidden="true"><i class="fas fa-lock"></i></span>
                            <input class="admin-input" type="password" id="adminPass" name="password"
                                placeholder="Masukkan password" required aria-describedby="adminPassChecklist"
                                autocomplete="current-password">
                            <button type="button" class="admin-input-toggle" id="adminPassToggle"
                                aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
                        </div>
                        <ul class="admin-pass-checklist" id="adminPassChecklist" aria-live="polite">
                            <li data-rule="length"><i class="fas fa-circle" aria-hidden="true"></i><span>Minimal 8
                                    karakter</span></li>
                            <li data-rule="upper-lower"><i class="fas fa-circle" aria-hidden="true"></i><span>Huruf
                                    besar & kecil</span></li>
                            <li data-rule="number"><i class="fas fa-circle" aria-hidden="true"></i><span>Memuat
                                    angka</span></li>
                            <li data-rule="symbol"><i class="fas fa-circle" aria-hidden="true"></i><span>Memuat simbol
                                    khusus</span></li>
                        </ul>
                    </div>
                    <div class="admin-actions">
                        <label class="admin-check"><input type="checkbox" id="adminRemember" name="remember" value="1"
                                {{ old('remember') ? 'checked' : '' }}> <span>Ingat saya</span></label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="admin-link">Lupa password?</a>
                        @else
                            <span class="admin-link admin-link--disabled" aria-disabled="true">Lupa password?</span>
                        @endif
                    </div>
                    <button type="submit" class="admin-submit">Login</button>
                </form>
            </div>
            <div class="admin-card-pane admin-card-visual">
                <img class="admin-visual-img" src="img/Banner/HERO3.jpg" alt="Aparatur Desa Tanjung Kesuma"
                    loading="lazy">
                <div class="admin-visual-overlay">
                    <div class="admin-visual-badge"><i class="fas fa-map-marker-alt"></i> <span
                            id="adminLocationLabel">Lampung Timur</span></div>
                    <div class="admin-visual-body">
                        <h3>Portal Admin Desa</h3>
                        <p>Pusat kendali informasi dan pelayanan digital bagi warga Tanjung Kesuma.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <!-- Mobile Menu -->
    @include('frontend.partials.mobile-menu', ['navMenus' => $navMenus])

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            @php $slides = $heroSlides ?? collect(); @endphp
            @forelse($slides as $slide)
                @php
                    $bg = $mediaUrl(data_get($slide, 'image_url') ?? data_get($slide, 'background_url'), asset('img/Banner/gambar_desa_1.png'));
                    $title = data_get($slide, 'title') ?: 'Selamat Datang di Website Resmi Desa Tanjung Kesuma';
                    $subtitle = data_get($slide, 'subtitle') ?: data_get($slide, 'description');
                    $buttonLabel = data_get($slide, 'button_label') ?: 'Layanan Publik';
                    $buttonUrl = data_get($slide, 'button_url') ?: '#layanan';
                @endphp
                <div class="hero-slide {{ $loop->first ? 'active' : '' }}"
                    style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ $bg }}');">
                    <div class="hero-content">
                        <h1>{{ $title }}</h1>
                        @if(!empty($subtitle))
                            <p>{{ $subtitle }}</p>
                        @endif
                        <a href="{{ $buttonUrl }}" class="cta-button">{{ $buttonLabel }}</a>
                    </div>
                </div>
            @empty
                <div class="hero-slide active"
                    style="background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('{{ asset('img/Banner/gambar_desa_1.png') }}');">
                    <div class="hero-content">
                        <h1>Selamat Datang di Website Resmi <br>Desa Tanjung Kesuma</h1>
                        <p>Media informasi dan layanan digital untuk masyarakat Desa Tanjung Kesuma</p>
                        <a href="#layanan" class="cta-button">Layanan Publik</a>
                    </div>
                </div>
            @endforelse
        </div>

        @if(($heroSlides ?? collect())->count() > 1)
            <button class="hero-nav prev" onclick="changeSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="hero-nav next" onclick="changeSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="hero-dots">
                @foreach($heroSlides as $slide)
                    <span class="hero-dot {{ $loop->first ? 'active' : '' }}"
                        onclick="currentSlide({{ $loop->iteration }})"></span>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Modal untuk Infografis -->
    <div class="modal" id="infografisModal" aria-hidden="true" role="dialog" aria-label="Tampilan gambar infografis">
        <div class="modal-content">
            <button class="modal-close" id="infografisModalClose" aria-label="Tutup">&times;</button>
            <img id="infografisModalImg" alt="Infografis" />
        </div>
    </div>

    <!-- Modal untuk Video Galeri -->
    <div class="modal" id="videoModal" aria-hidden="true" role="dialog" aria-label="Tampilan video">
        <div class="modal-content video">
            <button class="modal-close" id="videoModalClose" aria-label="Tutup">&times;</button>
            <div class="video-modal-wrapper">
                <iframe id="videoModalFrame" src="" title="Pemutar Video" frameborder="0"
                    allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <!-- Back to top button -->
    <button id="backToTop" class="back-to-top" aria-label="Kembali ke atas">
        <i class="fas fa-arrow-up" aria-hidden="true"></i>
    </button>

    <!-- Accessibility (Ramah Disabilitas) Floating Widget moved to partials.floating-widgets -->

    <!-- Informasi Publik Section moved later -->

    <!-- Statistics Section -->
    <section class="stats-section">
        @php
            $pop = $populationStats ?? [];
            $popTotal = (int) data_get($pop, 'total', 0);
            $popMale = (int) data_get($pop, 'male', 0);
            $popFemale = (int) data_get($pop, 'female', 0);
        @endphp
        <div class="stats-card">
            <div class="stat-item">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <div class="stat-info">
                    <div class="stat-number">Data</div>
                    <div class="stat-label">Data Penduduk</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-info">
                    <div class="stat-number" data-target="{{ $popTotal }}">{{ number_format($popTotal, 0, ',', '.') }}
                    </div>
                    <div class="stat-label">Jumlah Penduduk</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-male stat-icon"></i>
                <div class="stat-info">
                    <div class="stat-number" data-target="{{ $popMale }}">{{ number_format($popMale, 0, ',', '.') }}
                    </div>
                    <div class="stat-label">Laki-Laki @if($popTotal > 0)<span style="font-size: 0.75em; opacity: 0.8;">({{ round(($popMale / $popTotal) * 100) }}%)</span>@endif</div>
                </div>
            </div>
            <div class="stat-item">
                <i class="fas fa-female stat-icon"></i>
                <div class="stat-info">
                    <div class="stat-number" data-target="{{ $popFemale }}">{{ number_format($popFemale, 0, ',', '.') }}
                    </div>
                    <div class="stat-label">Perempuan @if($popTotal > 0)<span style="font-size: 0.75em; opacity: 0.8;">({{ round(($popFemale / $popTotal) * 100) }}%)</span>@endif</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Info Banner (Running Text) -->
    <div class="info-banner">
        <div class="info-tag">Sekilas Info</div>
        <div class="running-text">
            <div class="running-text-content">
                @if(($sekilasInfos ?? collect())->isNotEmpty())
                    @foreach($sekilasInfos as $info)
                        @php
                            $title = $info->title ?: 'Sekilas Info';
                            $body = $info->content ? Str::limit(strip_tags($info->content), 140) : '';
                        @endphp
                        <span class="sekilas-item">
                            <strong>{{ $title }}</strong>@if($body) &mdash; {{ $body }}@endif
                        </span>
                        @unless($loop->last)
                            <span class="separator">&bull;</span>
                        @endunless
                    @endforeach
                @else
                    Desa Tanjung Kesuma merupakan salah satu desa di Kecamatan Purbolinggo, Kabupaten Lampung Timur yang
                    memiliki potensi besar dalam bidang pertanian, perkebunan, dan pariwisata alam yang menjanjikan untuk
                    pengembangan ekonomi masyarakat.
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Dashboard Section -->
    <section class="statistics-dashboard">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Statistik Desa</h2>
            <a href="{{ route('statistics.population.page') }}" class="dashboard-link">Lihat Lebih Detail ></a>
        </div>

        <div class="dashboard-container">
            <div class="dashboard-scroll">
                <!-- Statistik Penduduk -->
                @php
                    $pop = $populationStats ?? [];
                    $popTotal = (int) data_get($pop, 'total', 0);
                    $popMale = (int) data_get($pop, 'male', 0);
                    $popFemale = (int) data_get($pop, 'female', 0);
                    $popMalePct = (int) data_get($pop, 'male_percent', 0);
                    $popFemalePct = (int) data_get($pop, 'female_percent', 0);
                @endphp
                <div class="stat-card" id="cardPenduduk">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Penduduk</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('donutPenduduk', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('donutPenduduk', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card-content">
                        <div class="chart-container">
                            <div class="donut-css" id="donutPenduduk" data-p1="{{ $popMalePct }}"
                                data-p2="{{ $popFemalePct }}">
                                <div class="donut-center">
                                    <i class="fas fa-user"></i>
                                    <div class="total-number">{{ number_format($popTotal, 0, ',', '.') }}</div>
                                    <div class="total-label">Total Penduduk</div>
                                </div>
                            </div>
                        </div>
                        <div class="stat-details">
                            <div class="stat-detail-item" data-seg="primary">
                                <i class="fas fa-male"></i>
                                <span>Laki-Laki</span>
                                <strong>{{ number_format($popMale, 0, ',', '.') }} ({{ $popMalePct }}%)</strong>
                            </div>
                            <div class="stat-detail-item" data-seg="secondary">
                                <i class="fas fa-female"></i>
                                <span>Perempuan</span>
                                <strong>{{ number_format($popFemale, 0, ',', '.') }} ({{ $popFemalePct }}%)</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Wilayah -->
                @php
                    $mapEmbed = $publicInfoSetting->map_embed_url ?? null;
                    $mapSrc = null;
                    if (!empty($mapEmbed) && preg_match('/src="([^"]+)"/', $mapEmbed, $match)) {
                        $mapSrc = $match[1];
                    }
                    $defaultMapSrc = 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15899.602539798221!2d105.51701121149443!3d-4.956181492759378!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e409c7586b5d587%3A0xfa2e376879bfa2dd!2sTanjung%20Kesuma%2C%20Purbolinggo%2C%20East%20Lampung%20Regency%2C%20Lampung!5e0!3m2!1sen!2sid!4v1755350967514!5m2!1sen!2sid';
                    $openMapUrl = $mapSrc ?: 'https://www.google.com/maps/place/Tanjung+Kesuma,+Purbolinggo,+East+Lampung+Regency,+Lampung';
                @endphp
                <style>
                    .map-embed iframe {
                        width: 100% !important;
                        height: 230px !important;
                        border: 0;
                        border-radius: 12px;
                    }

                    .map-buttons {
                        display: flex;
                        gap: 0.5rem;
                        margin-top: 0.75rem;
                    }

                    .map-btn {
                        flex: 1;
                        border: 1px solid #d4d4d8;
                        border-radius: 12px;
                        padding: 0.65rem 0.75rem;
                        font-weight: 700;
                        cursor: pointer;
                        transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
                    }

                    .map-btn-blue {
                        background: #2563eb;
                        color: #fff;
                        border-color: #2563eb;
                    }

                    .map-btn-blue:hover {
                        background: #1d4ed8;
                    }

                    .map-btn-gray {
                        background: #f3f4f6;
                        color: #111827;
                    }

                    .map-btn-gray:hover {
                        background: #e5e7eb;
                        color: #0f172a;
                    }

                    .map-modal {
                        position: fixed;
                        inset: 0;
                        display: none;
                        align-items: center;
                        justify-content: center;
                        z-index: 9999;
                    }

                    .map-modal.is-open {
                        display: flex;
                    }

                    .map-modal__overlay {
                        position: absolute;
                        inset: 0;
                        background: rgba(15, 23, 42, 0.45);
                    }

                    .map-modal__dialog {
                        position: relative;
                        background: #fff;
                        border-radius: 16px;
                        max-width: 860px;
                        width: 94vw;
                        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
                        overflow: hidden;
                        padding: 1rem;
                        display: grid;
                        gap: 0.75rem;
                        z-index: 1;
                    }

                    .map-modal__header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 0.5rem;
                    }

                    .map-modal__close {
                        background: #f3f4f6;
                        border: 1px solid #e5e7eb;
                        border-radius: 10px;
                        width: 36px;
                        height: 36px;
                        display: grid;
                        place-items: center;
                        cursor: pointer;
                    }

                    .map-modal__body iframe {
                        width: 100% !important;
                        height: 360px !important;
                        border: 0;
                        border-radius: 12px;
                    }

                    .map-modal__desc {
                        margin: 0;
                        color: #374151;
                        line-height: 1.5;
                    }
                </style>
                <!-- Statistik Data Wilayah -->
                @if($publicInfoSetting->is_published)
                    <div class="stat-card" id="cardDataWilayah">
                        <!-- Force Hide Menu for this card specifically -->
                        <style>
                            #cardDataWilayah .stat-menu-container,
                            #cardDataWilayah .card-menu-btn,
                            #cardDataWilayah .card-menu-dropdown {
                                display: none !important;
                            }
                        </style>
                        <h3 class="stat-card-title">Data Wilayah</h3>
                        <div class="stat-card-content">
                            <div class="map-container">
                                <!-- Google Maps Embed -->
                                @if(!empty($mapEmbed))
                                    <div class="map-embed">{!! $mapEmbed !!}</div>
                                @else
                                    <iframe src="{{ $defaultMapSrc }}" width="100%" height="250"
                                        style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade">
                                    </iframe>
                                @endif

                                <!-- Tombol Aksi: Hanya Buka Peta -->
                                <div class="map-buttons">
                                    <button type="button" id="mapDetailBtn" class="map-btn map-btn-blue"
                                        style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                        <i class="fas fa-map-marked-alt"></i> Buka Peta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- End Data Wilayah -->

                <!-- Inline Style to Force Menu Visibility (Bypasses Cache) -->
                <!-- Inline Style to Force Menu Visibility (Bypasses Cache) -->
                <style>
                    .stat-card-header {
                        display: flex !important;
                        justify-content: space-between !important;
                        align-items: center !important;
                        margin-bottom: 15px !important;
                        width: 100% !important;
                        position: relative !important;
                    }

                    .stat-card-title {
                        margin: 0 !important;
                        font-size: 1.1rem !important;
                        font-weight: 600 !important;
                        flex-grow: 1 !important;
                    }

                    /* Container for button and dropdown to keep them together */
                    .stat-menu-container {
                        position: relative !important;
                        display: inline-block !important;
                        z-index: 99 !important;
                    }

                    .card-menu-btn {
                        background: transparent !important;
                        border: none !important;
                        cursor: pointer !important;
                        padding: 0 !important;
                        font-size: 24px !important;
                        line-height: 1 !important;
                        color: #6B7280 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 32px !important;
                        height: 32px !important;
                        border-radius: 4px !important;
                        transition: background-color 0.2s !important;
                    }

                    .card-menu-btn:hover {
                        background-color: rgba(0, 0, 0, 0.05) !important;
                        color: #374151 !important;
                    }

                    .card-menu-dropdown {
                        position: absolute !important;
                        right: 0 !important;
                        top: 100% !important;
                        background: white !important;
                        border: 1px solid #e5e7eb !important;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
                        border-radius: 6px !important;
                        width: 160px !important;
                        display: none;
                        z-index: 100 !important;
                        overflow: hidden !important;
                    }

                    .card-menu-dropdown.show {
                        display: block !important;
                    }

                    .card-menu-dropdown button {
                        display: block !important;
                        width: 100% !important;
                        text-align: left !important;
                        padding: 8px 12px !important;
                        font-size: 13px !important;
                        color: #374151 !important;
                        background: none !important;
                        border: none !important;
                        cursor: pointer !important;
                    }

                    .card-menu-dropdown button:hover {
                        background-color: #f3f4f6 !important;
                    }
                </style>

                <!-- Statistik Pekerjaan -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Pekerjaan</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartPekerjaan', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartPekerjaan', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartPekerjaan"></canvas>
                    </div>
                </div>

                <!-- Statistik Pendidikan -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Pendidikan</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartPendidikan', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartPendidikan', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartPendidikan"></canvas>
                    </div>
                </div>
                <!-- Statistik Agama -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Agama</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartAgama', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartAgama', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartAgama"></canvas>
                    </div>
                </div>

                <!-- Statistik Status Perkawinan -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Status Perkawinan</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartPerkawinan', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartPerkawinan', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartPerkawinan"></canvas>
                    </div>
                </div>

                <!-- Statistik Usia -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Usia</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartUsia', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartUsia', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartUsia"></canvas>
                    </div>
                </div>

                <!-- Statistik Golongan Darah -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Golongan Darah</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartDarah', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartDarah', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartDarah"></canvas>
                    </div>
                </div>

                <!-- Statistik Suku -->
                <div class="stat-card">
                    <div class="stat-card-header">
                        <h3 class="stat-card-title">Suku</h3>
                        <div class="stat-menu-container">
                            <button class="card-menu-btn" onclick="toggleCardMenu(event)" aria-label="Opsi Chart">
                                &#8943;
                            </button>
                            <div class="card-menu-dropdown">
                                <button onclick="exportHomeChart('chartSuku', 'jpg')">Download JPG</button>
                                <button onclick="exportHomeChart('chartSuku', 'png')">Download PNG</button>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 280px;">
                        <canvas id="chartSuku"></canvas>
                    </div>
                </div>

            </div>
        </div>


    </section>

    <!-- News Section -->
    <section class="news-section" id="berita">
        <div class="news-header">
            <div>
                <h2 class="news-title">Berita Desa</h2>
                <p class="news-subtitle">Berita dan pengumuman terkini dari pemerintah desa</p>
            </div>
            <a href="{{ route('news.index') }}" class="news-link">Lihat Lebih Detail ></a>
        </div>

        <div class="news-container">
            <div class="news-grid" id="newsGrid">
                <!-- News cards will be populated by JavaScript -->
            </div>
        </div>

        <div class="news-pagination" id="newsPagination">
            <!-- Pagination buttons will be populated by JavaScript -->
        </div>
        </div>
    </section>



    <!-- Informasi Publik Section (relocated below News) -->
    <section class="info-publik-section" id="informasi-publik">
        <div class="news-header">
            <div>
                <h2 class="news-title">{{ $publicInfoSetting->section_title ?? 'Informasi Publik' }}</h2>
                <p class="news-subtitle">
                    {{ $publicInfoSetting->section_subtitle ?? 'Akses informasi, jam layanan, pengumuman banner, dan lokasi kantor desa' }}
                </p>
            </div>
        </div>

        <div class="info-grid">
            <!-- Card 1: Permohonan Informasi Publik -->
            <article class="info-card info-card--tall">
                <div class="info-card-body">
                    <h3 class="info-title">{{ $publicInfoSetting->request_title ?? 'Permohonan Informasi Publik' }}</h3>
                    <p class="info-desc">
                        {{ $publicInfoSetting->request_description ?? 'Ajukan permohonan informasi publik desa secara mudah dan transparan. Ikuti prosedur resmi sesuai peraturan yang berlaku.' }}
                    </p>
                </div>
                <div class="info-media info-media--with-btn">
                    @php
                        $reqImg = $publicInfoSetting->request_image;
                        $reqImgUrl = $reqImg
                            ? (Str::startsWith($reqImg, ['http://', 'https://']) ? $reqImg : asset('storage/' . ltrim($reqImg, '/')))
                            : asset('img/Pelayanan/Pelayanan1.jpg');
                    @endphp
                    <img src="{{ $reqImgUrl }}" alt="Permohonan Informasi Publik" loading="lazy">
                    @if(!empty($publicInfoSetting->request_button_url))
                        <a class="info-media-btn" href="{{ $publicInfoSetting->request_button_url }}" target="_blank"
                            rel="noopener">{{ $publicInfoSetting->request_button_label ?? 'Ajukan Permohonan' }}</a>
                    @else
                        <a class="info-media-btn" href="#"
                            aria-disabled="true">{{ $publicInfoSetting->request_button_label ?? 'Ajukan Permohonan' }}</a>
                    @endif
                </div>
            </article>

            <!-- Card 2: Jam Kerja -->
            <article class="info-card info-card--short">
                <div class="info-card-body">
                    <h3 class="info-title">{{ $publicInfoSetting->hours_title ?? 'Jam Kerja' }}</h3>
                    <p class="info-desc">
                        {{ $publicInfoSetting->hours_description ?? 'Jam pelayanan kantor Desa Tanjung Kesuma.' }}
                    </p>
                </div>
                <div class="info-media">
                    <div class="jam-kerja jam-kerja--inline">
                        <ul class="jam-list" aria-label="Jadwal Layanan Kantor Desa">
                            @forelse($publicInfoHours as $hour)
                                @php
                                    $isOff = $hour->is_closed;
                                    $isToday = $hour->day_of_week === now()->dayOfWeekIso - 1;
                                    $timeLabel = $isOff ? 'Libur' : trim(($hour->open_time ?: '-') . ' - ' . ($hour->close_time ?: '-'));
                                @endphp
                                <li class="jam-item {{ $isToday ? 'is-today' : '' }} {{ $isOff ? 'is-off' : '' }}">
                                    <span class="day">{{ $hour->day_label ?? $hour->day_of_week }}</span>
                                    <span class="time">{{ $timeLabel }}</span>
                                </li>
                            @empty
                                <li class="jam-item is-off"><span class="day">Data</span><span class="time">Belum
                                        diisi</span></li>
                            @endforelse
                        </ul>
                        @if($publicInfoSetting->hours_note)
                            <div class="jam-note"><span class="legend live"></span>{{ $publicInfoSetting->hours_note }}
                            </div>
                        @endif
                    </div>
                </div>
            </article>

            <!-- Card 3: Maps Lokasi Kantor Desa -->
            <article class="info-card info-card--tall">
                <div class="info-card-body">
                    <h3 class="info-title">{{ $publicInfoSetting->map_title ?? 'Maps Lokasi Kantor Desa' }}</h3>
                    <p class="info-desc">
                        {{ $publicInfoSetting->map_description ?? 'Temukan lokasi kantor desa pada peta berikut.' }}
                    </p>
                </div>
                <div class="info-media info-media--map">
                    @if(!empty($publicInfoSetting->map_embed_url))
                        {!! $publicInfoSetting->map_embed_url !!}
                    @else
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7314.608734059403!2d105.5164522364145!3d-4.958129316991919!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e409bf56c965ac7%3A0xb421528668ad55ea!2sBalai%20Desa%20Tanjung%20Kesuma!5e0!3m2!1sid!2sid!4v1757531013080!5m2!1sid!2sid"
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    @endif
                </div>
            </article>

            <!-- Row 2: Banner slider full width -->
            <article class="info-card info-card--full">
                <div class="info-media">
                    <div class="ip-slider" id="ipSlider">
                        <button class="ip-arrow prev" aria-label="Sebelumnya" onclick="changeIpSlide(-1)"><i
                                class="fas fa-chevron-left" aria-hidden="true"></i></button>
                        <div class="ip-track" id="ipSlides">
                            @php $infoBanners = $infoBanners ?? collect(); @endphp
                            @forelse($infoBanners as $banner)
                                @php $img = $mediaUrl($banner->image_url ?? null, asset('img/Banner/gambar_desa_1.png')); @endphp
                                <div class="ip-item">
                                    <img loading="lazy" src="{{ $img }}" alt="{{ $banner->title ?? 'Banner Desa' }}">
                                </div>
                            @empty
                                <div class="ip-item"><img loading="lazy" src="img/Banner/gambar_desa_1.png" alt="Banner 1">
                                </div>
                                <div class="ip-item"><img loading="lazy" src="img/Banner/gambar_desa_4.jpg" alt="Banner 2">
                                </div>
                                <div class="ip-item"><img loading="lazy" src="img/Banner/gambar_desa_3.jpg" alt="Banner 3">
                                </div>
                            @endforelse
                        </div>
                        <button class="ip-arrow next" aria-label="Berikutnya" onclick="changeIpSlide(1)"><i
                                class="fas fa-chevron-right" aria-hidden="true"></i></button>
                    </div>
                </div>
            </article>
        </div>
    </section>
    <!-- Announcement Section -->
    <!-- Announcement Section -->
    <section class="announcement-page-section" id="pengumuman">
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
                    <a href="{{ route('announcements.index') }}" class="announcement-page-back"
                        style="flex-direction: row-reverse;">
                        Lihat Lebih Detail <i class="fas fa-arrow-right" style="margin-right:0; margin-left:8px;"></i>
                    </a>
                </div>

                <!-- Filters -->
                <div class="announcement-page-filters">
                    <button type="button" class="announcement-tab active" data-type="all"
                        onclick="showAnnouncement(event, 'all')">Semua</button>
                    <button type="button" class="announcement-tab" data-type="desa"
                        onclick="showAnnouncement(event, 'desa')">Pengumuman Desa</button>
                    <button type="button" class="announcement-tab" data-type="daerah"
                        onclick="showAnnouncement(event, 'daerah')">Pengumuman Pemerintah Daerah</button>
                    <button type="button" class="announcement-tab" data-type="pusat"
                        onclick="showAnnouncement(event, 'pusat')">Pengumuman Pemerintah Pusat</button>
                </div>

                <!-- Toolbar -->
                <div class="announcement-page-toolbar">
                    <div class="announcement-search-wrapper">
                        <i class="fas fa-search search-icon-inside"></i>
                        <input id="announcementSearch" type="text" class="announcement-page-search-input"
                            placeholder="Cari pengumuman..." aria-label="Cari pengumuman">
                    </div>

                    <div style="display:flex; gap:10px; align-items:center;">
                        <select id="announcementPerPage" class="announcement-page-select"
                            onchange="renderAnnouncements()">
                            <option value="10">10 / Hal</option>
                            <option value="20">20 / Hal</option>
                            <option value="50">50 / Hal</option>
                        </select>
                    </div>
                </div>

                <div class="announcement-page-list" id="announcementList">
                    <div style="padding:20px; text-align:center; color:#64748b;">Memuat data pengumuman...</div>
                </div>

                <div class="announcement-pagination" id="announcementPagination" style="margin-top:30px;">
                    <!-- Pagination buttons will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </section>

    <!-- Transparansi Anggaran Desa -->
    <section class="transparansi-section">
        <div class="transparansi-header">
            <div class="transparansi-title">
                <h2>Transparansi Anggaran Desa</h2>
                <p class="transparansi-desc">Informasi ringkas realisasi dan anggaran desa secara transparan.</p>
            </div>
            <a href="{{ route('budget.transparency.page') }}" class="transparansi-link">Lihat Lebih Detail &gt;</a>
        </div>
        @php
            $pelaksanaan = $budgetItems['pelaksanaan'] ?? collect();
            $pendapatan = $budgetItems['pendapatan'] ?? collect();
            $pembelanjaan = $budgetItems['pembelanjaan'] ?? collect();
        @endphp
        <div class="transparansi-grid">
            <div class="transparansi-row">
                <!-- Pelaksanaan -->
                <div class="transparansi-card pelaksanaan-card">
                    <div class="card-header">
                        <i class="fa-regular fa-money-bill-1"></i>
                        <span class="card-title">Pelaksanaan</span>
                    </div>
                    <div class="card-content-3col">
                        @php $pelCols = $pelaksanaan; @endphp
                        @forelse($pelCols as $item)
                            @php
                                $title = $item->subcategory ?: $item->description;
                                $percent = $item->progress_percent;
                            @endphp
                            <div class="card-col">
                                <div class="card-subtitle">{{ $title }}</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">Rp.
                                    {{ number_format((float) $item->realisasi, 0, ',', '.') }}
                                </div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                <div class="card-percent">{{ $percent }}%</div>
                            </div>
                            @if(!$loop->last)
                                <div class="card-divider"></div>
                            @endif
                        @empty
                            <div class="card-col">
                                <div class="card-subtitle">Data pelaksanaan belum tersedia</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">-</div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">-</div>
                                <div class="card-bar"><span style="width:0%"></span></div>
                                <div class="card-percent">0%</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="transparansi-row">
                <!-- Pendapatan -->
                <div class="transparansi-card pendapatan-card">
                    <div class="card-header">
                        <i class="fa-regular fa-money-bill-1"></i>
                        <span class="card-title">Pendapatan</span>
                    </div>
                    <div class="card-content-3col pendapatan-grid">
                        @php $pendList = $pendapatan; @endphp
                        @forelse($pendList as $item)
                            @php
                                $title = $item->subcategory ?: $item->description;
                                $percent = $item->progress_percent;
                            @endphp
                            <div class="card-col">
                                <div class="card-subtitle">{{ $title }}</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">Rp.
                                    {{ number_format((float) $item->realisasi, 0, ',', '.') }}
                                </div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                <div class="card-percent">{{ $percent }}%</div>
                            </div>
                        @empty
                            <div class="card-col">
                                <div class="card-subtitle">Data pendapatan belum tersedia</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">-</div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">-</div>
                                <div class="card-bar"><span style="width:0%"></span></div>
                                <div class="card-percent">0%</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="transparansi-row">
                <!-- Pembelanjaan -->
                <div class="transparansi-card pembelanjaan-card">
                    <div class="card-header">
                        <i class="fa-regular fa-file-lines"></i>
                        <span class="card-title">Pembelanjaan</span>
                    </div>
                    <div class="card-content-3col">
                        @php $pemCols = $pembelanjaan; @endphp
                        @forelse($pemCols as $item)
                            @php
                                $title = $item->subcategory ?: $item->description;
                                $percent = $item->progress_percent;
                            @endphp
                            <div class="card-col">
                                <div class="card-subtitle">{{ $title }}</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">Rp.
                                    {{ number_format((float) $item->realisasi, 0, ',', '.') }}
                                </div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                <div class="card-percent">{{ $percent }}%</div>
                            </div>

                        @empty
                            <div class="card-col">
                                <div class="card-subtitle">Data pembelanjaan belum tersedia</div>
                                <div class="card-label">Realisasi</div>
                                <div class="card-value highlight">-</div>
                                <div class="card-label">Anggaran</div>
                                <div class="card-value">-</div>
                                <div class="card-bar"><span style="width:0%"></span></div>
                                <div class="card-percent">0%</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pegawai Desa -->
    <section class="pegawai-section">
        <div class="pegawai-header">
            <h2>Pegawai Desa</h2>
            <a href="{{ route('pegawai.page') }}" class="pegawai-link">Lihat Lebih Detail &gt;</a>
        </div>
        <p class="pegawai-desc">Daftar perangkat desa dan staf. Geser untuk melihat lainnya.</p>
        <div class="pegawai-slider-wrapper">
            @php
                $pegawaiList = $pegawaiList ?? collect();
                $kepalaDesa = $pegawaiList->first(fn($p) => Str::lower($p->jabatan ?? '') === 'kepala desa');
                $pegawaiSticky = $kepalaDesa ?: $pegawaiList->first();
            @endphp
            @if($kepalaDesa)
                @php
                    $stickyImg = $mediaUrl($kepalaDesa->image_url ?? $kepalaDesa->gambar ?? null, asset('img/Users/user1.png'));
                    $statusSticky = $kepalaDesa->status_kepegawaian ?? $kepalaDesa->status ?? 'Tidak diketahui';
                    $statusClassMap = [
                        'aktif' => 'status-aktif',
                        'tidak aktif' => 'status-tidak-aktif',
                        'nonaktif' => 'status-tidak-aktif',
                        'non-aktif' => 'status-tidak-aktif',
                        'cuti' => 'status-cuti',
                        'pensiun' => 'status-pensiun',
                    ];
                    $statusStickyClass = $statusClassMap[Str::lower($statusSticky)] ?? '';
                    $pegawaiStickyName = $kepalaDesa->nama;
                    $pegawaiStickyRole = $kepalaDesa->jabatan ?? 'Kepala Desa';
                @endphp
                <div class="pegawai-card pegawai-card-sticky" data-status="{{ Str::lower($statusSticky) }}">
                    <img src="{{ $stickyImg }}" alt="{{ $pegawaiSticky->nama }}">
                    <div class="pegawai-info">
                        <div class="pegawai-name">{{ $pegawaiStickyName }}</div>
                        <div class="pegawai-meta-row">
                            <div class="pegawai-role">{{ $pegawaiStickyRole }}</div>
                            <div class="pegawai-status {{ $statusStickyClass }}">{{ $statusSticky }}</div>
                        </div>
                    </div>
                </div>
            @elseif($pegawaiList->isNotEmpty())
                @php
                    $pegawaiSticky = $pegawaiList->first();
                    $stickyImg = $mediaUrl($pegawaiSticky->image_url ?? $pegawaiSticky->gambar ?? null, asset('img/Users/user1.png'));
                    $statusSticky = $pegawaiSticky->status_kepegawaian ?? $pegawaiSticky->status ?? 'Tidak diketahui';
                    $statusClassMap = [
                        'aktif' => 'status-aktif',
                        'tidak aktif' => 'status-tidak-aktif',
                        'nonaktif' => 'status-tidak-aktif',
                        'non-aktif' => 'status-tidak-aktif',
                        'cuti' => 'status-cuti',
                        'pensiun' => 'status-pensiun',
                    ];
                    $statusStickyClass = $statusClassMap[Str::lower($statusSticky)] ?? '';
                @endphp
                <div class="pegawai-card pegawai-card-sticky" data-status="{{ Str::lower($statusSticky) }}">
                    <img src="{{ $stickyImg }}" alt="{{ $pegawaiSticky->nama }}">
                    <div class="pegawai-info">
                        <div class="pegawai-name">{{ $pegawaiSticky->nama }}</div>
                        <div class="pegawai-meta-row">
                            <div class="pegawai-role">{{ $pegawaiSticky->jabatan ?? 'Perangkat Desa' }}</div>
                            <div class="pegawai-status {{ $statusStickyClass }}">{{ $statusSticky }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="pegawai-card pegawai-card-sticky" data-status="kosong">
                    <img src="{{ asset('img/Users/user1.png') }}" alt="Kepala Desa">
                    <div class="pegawai-info">
                        <div class="pegawai-name">Belum ada Kepala Desa</div>
                        <div class="pegawai-meta-row">
                            <div class="pegawai-role">Tidak ada</div>
                            <div class="pegawai-status status-tidak-aktif">Tidak ada</div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="pegawai-runner" aria-label="Daftar Pegawai">
                <div class="pegawai-track">
                    @php
                        $listToRender = $kepalaDesa ? $pegawaiList->filter(fn($p) => $p->id !== $kepalaDesa->id) : $pegawaiList->skip(1);
                    @endphp
                    @if($listToRender->isNotEmpty())
                        {{-- Duplicate loop to create seamless marquee --}}
                        @for($i = 0; $i < 2; $i++)
                            @foreach($listToRender as $pegawai)
                                @php
                                    $img = $mediaUrl($pegawai->image_url ?? $pegawai->gambar ?? null, asset('img/Users/user2.png'));
                                    $status = $pegawai->status_kepegawaian ?? $pegawai->status ?? 'Tidak diketahui';
                                    $statusKey = Str::lower($status);
                                    $statusClassMap = [
                                        'aktif' => 'status-aktif',
                                        'tidak aktif' => 'status-tidak-aktif',
                                        'nonaktif' => 'status-tidak-aktif',
                                        'non-aktif' => 'status-tidak-aktif',
                                        'cuti' => 'status-cuti',
                                        'pensiun' => 'status-pensiun',
                                    ];
                                    $statusClass = $statusClassMap[$statusKey] ?? '';
                                @endphp
                                <div class="pegawai-card" data-status="{{ $statusKey }}">
                                    <img src="{{ $img }}" alt="{{ $pegawai->nama }}">
                                    <div class="pegawai-info">
                                        <div class="pegawai-name">{{ $pegawai->nama }}</div>
                                        <div class="pegawai-meta-row">
                                            <div class="pegawai-role">{{ $pegawai->jabatan ?? 'Perangkat Desa' }}</div>
                                            <div class="pegawai-status {{ $statusClass }}">{{ $status }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endfor
                    @else
                        <div class="pegawai-card" data-status="-">
                            <img src="img/Users/user2.png" alt="Pegawai Desa">
                            <div class="pegawai-info">
                                <div class="pegawai-name">Pegawai Desa</div>
                                <div class="pegawai-meta-row">
                                    <div class="pegawai-role">Data belum tersedia</div>
                                    <div class="pegawai-status">Status tidak tersedia</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Galeri Video -->
    <section class="galeri-jam-section">
        <div class="galeri-jam-grid">
            <div class="galeri-video">
                <div class="galeri-header">
                    <h3>Galeri Video</h3>
                    <a href="{{ route('videos.index') }}" class="galeri-link">Lihat Lebih Detail &gt;</a>
                </div>
                @php $videos = $youtubeVideos ?? collect(); @endphp
                <div class="galeri-list" id="galeriList">
                    @forelse($videos as $video)
                        @php
                            $embedUrl = $video->embed_url ? $video->embed_url . '?controls=1&rel=0&modestbranding=1&iv_load_policy=3&playsinline=1' : '';
                            $watchUrl = $video->youtube_url ?: ($video->embed_url ?: '#');
                            $dateLabel = optional($video->published_at ?? $video->created_at)->format('d M Y') ?: 'Video';
                        @endphp
                        <div class="galeri-card">
                            <div class="video-wrapper">
                                @if($embedUrl)
                                    <iframe src="{{ $embedUrl }}" title="{{ $video->title }}" loading="lazy"
                                        allowfullscreen></iframe>
                                @else
                                    <div class="news-list-thumb--placeholder"
                                        style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                        <i class="fas fa-video"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="galeri-meta">
                                <h4 class="meta-title">{{ $video->title }}</h4>
                                <div class="meta-row">
                                    <span class="meta-pill">{{ $dateLabel }}</span>
                                    <a href="{{ $watchUrl }}" class="meta-link" target="_blank" rel="noopener">Tonton</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="galeri-card" style="align-items:center; text-align:center; padding:22px 16px;">
                            <div style="font-size:46px; color:#cbd5e1; margin-bottom:10px;">
                                <i class="fas fa-photo-video" aria-hidden="true"></i>
                            </div>
                            <div class="galeri-meta" style="width:100%;">
                                <h4 class="meta-title">Belum ada video</h4>
                                <div class="meta-row" style="justify-content:center; color:#94a3b8;">
                                    <span class="meta-pill">No video available</span>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                <!-- Pagination for Galeri Video -->
                <div class="news-pagination galeri-pagination" id="galeriPagination"></div>
            </div>
            <!-- Infografis slider on the right column -->
            <div class="infografis-card">
                <div class="infografis-header">
                    <h3>Infografis</h3>
                </div>
                <div class="infografis-slider" id="infografisSlider">
                    @php $infographics = $infographicBanners ?? collect(); @endphp
                    @forelse($infographics as $banner)
                        @php $img = $mediaUrl($banner->image_url ?? null, asset('img/Pelayanan/usia.jpg')); @endphp
                        <div class="infografis-slide {{ $loop->first ? 'active' : '' }}">
                            <img src="{{ $img }}" alt="{{ $banner->title ?? 'Infografis Desa' }}" loading="lazy">
                        </div>
                    @empty
                        <div class="infografis-slide active">
                            <img src="img/Pelayanan/usia.jpg" alt="Infografis Usia" loading="lazy">
                        </div>
                        <div class="infografis-slide">
                            <img src="img/Pelayanan/agama.jpg" alt="Infografis Agama" loading="lazy">
                        </div>
                        <div class="infografis-slide">
                            <img src="img/Pelayanan/produk-hukum.jpg" alt="Produk Hukum" loading="lazy">
                        </div>
                        <div class="infografis-slide">
                            <img src="img/Pelayanan/pernikahan.jpg" alt="Pernikahan" loading="lazy">
                        </div>
                    @endforelse
                </div>
                <div class="infografis-dots" id="infografisDots"></div>
            </div>
        </div>
    </section>




    <!-- Map Detail Modal -->
    <div id="mapDetailModal" class="custom-modal map-modal" aria-hidden="true" style="display: none;">
        <div class="custom-modal__overlay" data-map-modal-close></div>
        <div class="custom-modal__content custom-modal__content--xl">
            <button class="custom-modal__close" data-map-modal-close aria-label="Tutup Modal">&times;</button>
            <div class="map-modal__header">
                <div>
                    <h2 class="map-modal__title">Data Wilayah & Peta Desa</h2>
                    <p class="map-modal__subtitle">{{ $publicInfoSetting->map_title ?? 'Peta Wilayah Desa' }}</p>
                </div>
            </div>
            <div class="map-modal__body">
                <div class="map-modal__grid">
                    <!-- Column 1: Map & Description -->
                    <div class="map-modal__col-main">
                        <div class="map-wrapper">
                            <iframe
                                src="https://maps.google.com/maps?q=Kantor+Desa+Tanjung+Kesuma,+Purbolinggo,+Lampung+Timur&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                width="100%" height="600" style="border:0; height: 600px;" allowfullscreen=""
                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                        <div class="map-description-container">
                            <h4 class="map-desc-title">Deskripsi Peta</h4>
                            <div class="map-desc-text">
                                {!! nl2br(e($publicInfoSetting->map_description ?? '-')) !!}
                            </div>
                        </div>

                        <!-- Luas Wilayah Card -->
                        <div class="area-card">
                            <div class="area-card__content">
                                <h4 class="area-card__title">Luas Wilayah Desa</h4>
                                <p class="area-card__value">854 <span class="area-card__unit">Ha</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Data & Contact -->
                    <div class="map-modal__col-sidebar">

                        <!-- Working Hours (Simplified) -->
                        <div class="modal-card mb-4" style="height: auto;">
                            <h4 class="modal-card__title">
                                <span class="icon-wrap bg-green-100 text-green-600"><i class="far fa-clock"></i></span>
                                Jam Kerja
                            </h4>
                            <div
                                class="text-sm font-medium text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                Buka Senin–Jumat, 08.00–16.00 WIB
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="modal-card mb-4">
                            <h4 class="modal-card__title">
                                <span class="icon-wrap bg-red-100 text-red-600"><i
                                        class="fas fa-map-marker-alt"></i></span>
                                Alamat & Kontak
                            </h4>
                            <div class="text-sm text-gray-600 leading-relaxed address-box">
                                {!! nl2br(e($publicInfoSetting->footer_address ?? '-')) !!}
                            </div>
                        </div>

                        <!-- Wilayah Data (Moved to bottom) -->
                        <div class="modal-card">
                            <h4 class="modal-card__title">
                                <span class="icon-wrap bg-blue-100 text-blue-600"><i
                                        class="fas fa-chart-pie"></i></span>
                                Data Wilayah
                            </h4>
                            <div class="wilayah-list">
                                @forelse($wilayahData ?? [] as $data)
                                    <div class="wilayah-item">
                                        <span class="wilayah-name">{{ $data->label }}</span>
                                        <span class="wilayah-count">{{ $data->total }} Jiwa</span>
                                    </div>
                                @empty
                                    <div class="text-center text-gray-500 text-sm py-2">Data wilayah belum tersedia</div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('frontend.partials.footer', ['socialLinks' => $socialLinks ?? collect(), 'publicInfoSetting' => $publicInfoSetting ?? null])

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    @php
        $announcementsByCategory = $announcementsByCategory ?? [];
        $newsData = ($news ?? collect())->map(function ($item) use ($mediaUrl) {
            $img = $item->thumbnail ? $mediaUrl($item->thumbnail, null) : null;
            $publishedAt = $item->published_at ?? $item->created_at;

            return [
                'title' => $item->title,
                'date' => optional($publishedAt)->locale('id')->translatedFormat('l, d M Y'),
                'desc' => $item->summary ?: Str::limit(strip_tags($item->content), 140),
                'url' => route('news.show', $item->slug),
                'image' => $img,
                'category' => optional($item->category)->name,
                'icon' => 'fas fa-newspaper',
                'views' => (int) ($item->views ?? 0),
                'author' => optional($item->author)->name ?: ($item->created_by ?? 'Admin Desa'),
            ];
        })->values();

        // Helper to normalize keys and fallback to labels
        $categories = \App\Models\Announcement::categories();
        // Normalize groups to lowercase keys for easier lookup
        $normalizedGroups = collect($announcementsByCategory)->mapWithKeys(function ($item, $key) {
            return [Str::lower($key) => $item];
        });

        $announcementData = collect($categories)
            ->keys()
            ->mapWithKeys(function ($slug) use ($normalizedGroups, $mediaUrl, $categories) {
                $slugKey = Str::lower($slug);
                $labelKey = Str::lower($categories[$slug] ?? '');

                // Lookup by slug or label
                $group = $normalizedGroups[$slugKey] ?? $normalizedGroups[$labelKey] ?? collect();

                return [
                    $slug => $group->map(function ($item) use ($mediaUrl) {
                        $thumb = $item->thumbnail ?? null;
                        $img = $thumb ? $mediaUrl($thumb, null) : null;
                        // Try eager loaded first, then lazy load
                        if (!$img) {
                            $img = $item->imageAttachments->first()->url ?? null;
                        }
                        $img = $mediaUrl($img, asset('img/Logo/speaker.png'));

                        $dateObj = optional($item->published_at ?? $item->created_at);
                        $dateStr = $dateObj->locale('id')->translatedFormat('l, d M Y');

                        return [
                            'title' => $item->title,
                            'date' => $dateStr,
                            'raw_date' => $dateObj->toIso8601String(),
                            'desc' => $item->excerpt,
                            'url' => route('announcements.show', $item->slug),
                            'image' => $img,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();
    @endphp
    <script>
        window.isUserAuthenticated = @json(auth()->check());
        window.__NEWS_DATA__ = @json($newsData);
        window.__ANNOUNCEMENT_DATA__ = @json($announcementData);
        window.__STAT_DATA__ = @json($statistikData ?? []);
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const detailBtn = document.getElementById('mapDetailBtn');
            const modal = document.getElementById('mapDetailModal');
            const closeEls = modal?.querySelectorAll('[data-map-modal-close]');

            const openModal = () => {
                if (modal) {
                    modal.style.display = 'flex';
                    // Force reflow to enable transition
                    void modal.offsetWidth;
                    modal.classList.add('is-open');
                }
            };
            const closeModal = () => {
                if (modal) {
                    modal.classList.remove('is-open');
                    setTimeout(() => {
                        modal.style.display = 'none';
                    }, 300);
                }
            };

            detailBtn?.addEventListener('click', (e) => {
                e.preventDefault();
                openModal();
            });
            closeEls?.forEach((el) => el.addEventListener('click', closeModal));
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });
        });
    </script>




    <!-- File JS kamu -->
    <script src="{{ asset('assets/js/main.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>

</html>
