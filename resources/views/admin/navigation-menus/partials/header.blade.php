@php
    $headerUser = $headerUser ?? auth()->user();
    $defaultAvatar = \Illuminate\Support\Facades\Storage::disk('public')->exists('default/user.jpg')
        ? \Illuminate\Support\Facades\Storage::url('default/user.jpg')
        : asset('assets/default/user.jpg');

    $headerAvatar = $headerAvatar ?? ($headerUser?->avatar_url ?: $defaultAvatar);
    $headerName = $headerName ?? ($headerUser?->nama ?? 'Admin Desa');
    $headerEmail = $headerEmail ?? ($headerUser?->email ?? 'admin@tanjungkesuma.id');
    $isSuperAdmin = $headerUser?->is_admin ?? false;
    $headerRole = $headerRole ?? ($isSuperAdmin ? 'Super Admin' : 'Administrator');
@endphp

<section class="hero-surface hero-surface--header">
    <div class="hero-top hero-top--simple">
        <div class="hero-top-left">
            <button class="ghost-btn" id="sidebarCollapseBtn" aria-label="Sembunyikan sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="ghost-btn" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="header-breadcrumbs" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.navigation-menus.index') }}">Navigasi</a>
            </nav>
        </div>
        <div class="hero-actions">
            @include('admin.partials.header-controls')
        </div>
    </div>

    <div class="hero-title-row">
        <div>
            <h1 class="page-title-large">Pengaturan Menu Navigasi</h1>
            <p class="header-subtitle">Kelola menu utama, submenu, tautan page/internal, ikon, dan urutan navigasi situs.</p>
        </div>
        <div class="title-actions">
            <a class="primary-btn" href="#menuForm"><i class="fas fa-plus"></i> Tambah Menu</a>
        </div>
    </div>

    <div class="hero-cta">
        <div class="hero-title-center">Navigasi Situs Desa</div>
        <a class="primary-btn" href="#menuForm"><i class="fas fa-plus"></i> Tambah Data</a>
    </div>
</section>

<section class="hero-banner-card">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card__header">
                <div>
                    <p class="stat-card__eyebrow">Total</p>
                    <p class="stat-card__label">Menu</p>
                </div>
                <div class="stat-card__icon stat-card__icon--primary">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
            <div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__header">
                <div>
                    <p class="stat-card__eyebrow">Status</p>
                    <p class="stat-card__label">Aktif</p>
                </div>
                <div class="stat-card__icon stat-card__icon--primary">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-card__value">{{ $stats['active'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__header">
                <div>
                    <p class="stat-card__eyebrow">Total</p>
                    <p class="stat-card__label">Submenu</p>
                </div>
                <div class="stat-card__icon stat-card__icon--muted">
                    <i class="fas fa-sitemap"></i>
                </div>
            </div>
            <div class="stat-card__value">{{ $stats['submenu'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__header">
                <div>
                    <p class="stat-card__eyebrow">Jenis</p>
                    <p class="stat-card__label">Custom Link</p>
                </div>
                <div class="stat-card__icon stat-card__icon--muted">
                    <i class="fas fa-link"></i>
                </div>
            </div>
            <div class="stat-card__value">{{ $stats['custom'] ?? 0 }}</div>
        </div>
    </div>
</section>
