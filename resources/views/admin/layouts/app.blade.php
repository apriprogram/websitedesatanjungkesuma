<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard Admin Desa Tanjung Kesuma')</title>
    <link rel="icon" href="{{ asset('img/logo/logo_lampung_timur.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    {{-- Global styles --}}
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-ai.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    @stack('head')
</head>

<body class="{{ trim($__env->yieldContent('body_class', '')) }}">
    @php
        $appClass = trim($__env->yieldContent('app_class', ''));
        $user = auth()->user();

    @endphp

    <div class="admin-app {{ $appClass }}">
        <aside class="admin-sidebar" aria-label="Navigasi utama">
            <div class="sidebar-brand">
                <img class="brand-logo" src="{{ asset('img/Logo/logo_lampung_timur.png') }}"
                    alt="Logo Desa Tanjung Kesuma" loading="lazy">
                <div class="brand-meta">
                    <span class="brand-name">
                        Desa <br>Tanjung Kesuma
                    </span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">

                    {{-- DASHBOARD --}}
                    <ul class="nav-group">
                        <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                <span class="nav-text">Dashboard</span>
                            </a>
                        </li>
                    </ul>

                    {{-- UPDATE WEBSITE --}}
                    <span class="nav-label">Update Website</span>
                    <ul class="nav-group">

                        {{-- Beranda --}}
                        <li class="nav-item has-children">
                            <ul class="nav-submenu">
                                <li>
                                    <a class="is-active" href="{{ route('admin.hero-slides.index') }}">
                                        <i class="fas fa-feather"></i>
                                        <span class="nav-text">Hero &amp; Sambutan</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span class="nav-text">Agenda Desa</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fas fa-images"></i>
                                        <span class="nav-text">Banner &amp; Highlight</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Profil Desa --}}
                        @php
                            $profilActive = request()->routeIs(['admin.hero-banners.*', 'admin.social-links.*', 'admin.sekilas-info.*', 'admin.public-info.*', 'admin.navigation-menus.*']);
                        @endphp
                        <li class="nav-item has-children {{ $profilActive ? 'is-open' : '' }}"
                            data-initial-open="{{ $profilActive ? 'true' : 'false' }}">
                            <a href="#" class="nav-link-toggle" aria-expanded="{{ $profilActive ? 'true' : 'false' }}">
                                <i class="fas fa-university"></i>
                                <span class="nav-text">Pengaturan Desa</span>
                                <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li class="{{ request()->routeIs('admin.hero-banners.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.hero-banners.index') }}"
                                        class="{{ request()->routeIs('admin.hero-banners.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-image"></i>
                                        <span class="nav-text">Banner</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.social-links.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.social-links.index') }}"
                                        class="{{ request()->routeIs('admin.social-links.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-share-alt"></i>
                                        <span class="nav-text">Media Sosial</span>
                                    </a>
                                </li>

                                <li class="{{ request()->routeIs('admin.sekilas-info.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.sekilas-info.index') }}"
                                        class="{{ request()->routeIs('admin.sekilas-info.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="nav-text">Sekilas Info</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.public-info.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.public-info.index') }}"
                                        class="{{ request()->routeIs('admin.public-info.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-city"></i>
                                        <span class="nav-text">Informasi Publik</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.navigation-menus.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.navigation-menus.index') }}"
                                        class="{{ request()->routeIs('admin.navigation-menus.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-sitemap"></i>
                                        <span class="nav-text">Pengaturan Navigasi</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        {{-- Publikasi --}}
                        <?php
$publikasiActive = request()->routeIs(['admin.news.*', 'admin.announcements.*']);
$anggaranActive = request()->routeIs('admin.budget-items.*');
                        ?>
                        <li class="nav-item has-children {{ $publikasiActive ? 'is-open' : '' }}">
                            <a href="#" class="nav-link-toggle"
                                aria-expanded="{{ $publikasiActive ? 'true' : 'false' }}">
                                <i class="fas fa-newspaper"></i>
                                <span class="nav-text">Publikasi</span>
                                <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu">
                                <li class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.pages.index') }}"
                                        class="{{ request()->routeIs('admin.pages.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-file-alt"></i>
                                        <span class="nav-text">Halaman</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.news.index') }}"
                                        class="{{ request()->routeIs('admin.news.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-newspaper"></i>
                                        <span class="nav-text">Berita</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.announcements.index') }}"
                                        class="{{ request()->routeIs('admin.announcements.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-bullhorn"></i>
                                        <span class="nav-text">Pengumuman</span>
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.youtube-videos.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.youtube-videos.index') }}"
                                        class="{{ request()->routeIs('admin.youtube-videos.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-video"></i>
                                        <span class="nav-text">Video YouTube</span>
                                    </a>
                                </li>


                            </ul>
                        </li>



                    </ul>


                    {{-- DATA DESA --}}
                    @php
                        $kependudukanActive = request()->routeIs([
                            'admin.penduduks.*',
                            'admin.penduduk-pindah.*',
                            'admin.penduduk-meninggal.*',
                            'admin.keluargas.*',
                        ]);

                        $wilayahActive = request()->routeIs([
                            'admin.dusuns.*',
                            'admin.rws.*',
                            'admin.rts.*',
                        ]);

                        $referensiActive = request()->routeIs('admin.references.*');
                    @endphp

                    <span class="nav-label">Data Desa</span>
                    <ul class="nav-group">
                        <li class="nav-item has-children {{ $kependudukanActive ? 'is-open' : '' }}"
                            data-initial-open="{{ $kependudukanActive ? 'true' : 'false' }}">
                            <a href="#" class="nav-link-toggle"
                                aria-expanded="{{ $kependudukanActive ? 'true' : 'false' }}">
                                <i class="fas fa-users"></i>
                                <span class="nav-text">Kependudukan</span>
                                <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu" aria-hidden="{{ $kependudukanActive ? 'false' : 'true' }}">
                                <li>
                                    <a href="{{ route('admin.penduduks.index') }}"
                                        class="{{ request()->routeIs('admin.penduduks.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-user-group"></i>
                                        <span class="nav-text">Data Penduduk</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.penduduk-pindah.index') }}"
                                        class="{{ request()->routeIs('admin.penduduk-pindah.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span class="nav-text">Data Pindah</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.penduduk-meninggal.index') }}"
                                        class="{{ request()->routeIs('admin.penduduk-meninggal.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-book-dead"></i>
                                        <span class="nav-text">Data Meninggal</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.keluargas.index') }}"
                                        class="{{ request()->routeIs('admin.keluargas.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-address-card"></i>
                                        <span class="nav-text">Kartu Keluarga</span>
                                    </a>
                                </li>
                                <li class="{{ $referensiActive ? 'active' : '' }}">
                                    <a href="{{ route('admin.references.index') }}"
                                        class="{{ $referensiActive ? 'is-active' : '' }}">
                                        <i class="fas fa-database"></i>
                                        <span class="nav-text">Referensi</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-children {{ $wilayahActive ? 'is-open' : '' }}"
                            data-initial-open="{{ $wilayahActive ? 'true' : 'false' }}">
                            <a href="#" class="nav-link-toggle" aria-expanded="{{ $wilayahActive ? 'true' : 'false' }}">
                                <i class="fas fa-map-location-dot"></i>
                                <span class="nav-text">Wilayah</span>
                                <i class="fas fa-chevron-right nav-chevron" aria-hidden="true"></i>
                            </a>
                            <ul class="nav-submenu" aria-hidden="{{ $wilayahActive ? 'false' : 'true' }}">
                                <li>
                                    <a href="{{ route('admin.dusuns.index') }}"
                                        class="{{ request()->routeIs('admin.dusuns.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-home"></i>
                                        <span class="nav-text">Dusun</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.rws.index') }}"
                                        class="{{ request()->routeIs('admin.rws.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-layer-group"></i>
                                        <span class="nav-text">RW</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.rts.index') }}"
                                        class="{{ request()->routeIs('admin.rts.*') ? 'is-active' : '' }}">
                                        <i class="fas fa-list-check"></i>
                                        <span class="nav-text">RT</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ request()->routeIs('admin.agendas.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.agendas.index') }}">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="nav-text">Agenda Desa</span>
                            </a>
                        </li>
                    </ul>

                    {{-- ADMINISTRATIF --}}
                    <span class="nav-label">Administratif</span>
                    <ul class="nav-group">
                        <li class="nav-item {{ $anggaranActive ? 'active' : '' }}">
                            <a href="{{ route('admin.budget-items.index') }}"
                                class="{{ $anggaranActive ? 'is-active' : '' }}">
                                <i class="fas fa-money-check-alt"></i>
                                <span class="nav-text">Transparansi Anggaran</span>
                            </a>
                        </li>
                        {{-- Dokumen Desa --}}
                        <li class="nav-item {{ request()->routeIs('admin.documents.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.documents.index') }}">
                                <i class="fas fa-folder-open"></i>
                                <span class="nav-text">Dokumen Desa</span>
                            </a>
                        </li>
                    </ul>


                    {{-- PENGATURAN --}}
                    <span class="nav-label">SISTEM</span>
                    <ul class="nav-group">
                        <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.users.index') }}">
                                <i class="fas fa-cog"></i>
                                <span class="nav-text">Pengaturan Umum</span>
                            </a>
                        </li>

                    </ul>

                </div> {{-- /.nav-section --}}
            </nav>
        </aside>

        <main class="admin-main" aria-label="Konten utama">
            @yield('content')
        </main>

        @include('admin.partials.agenda-widget')
        {{-- AI widget is now embedded inside agenda-widget --}}
    </div>

    {{-- drawer / modal stacks --}}
    @yield('drawers')

    {{-- Scripts --}}
    <script src="{{ asset('assets/js/admin.js') }}" defer></script>
    @stack('scripts')

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
</body>

</html>