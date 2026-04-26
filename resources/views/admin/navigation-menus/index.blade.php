@extends('admin.layouts.app')

@section('title', 'Pengaturan Navigasi')
@section('body_class', 'nav-page')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news-modal.css') }}">
    <style>
        body.dark-mode .stat-card {
            border-color: rgba(148, 163, 184, 0.35);
        }
        body.dark-mode .stat-card__value,
        body.dark-mode .stat-card__eyebrow,
        body.dark-mode .stat-card__label {
            color: #f8fafc;
        }
        body.dark-mode .stat-card__pill {
            color: #e2e8f0;
            background: rgba(255, 255, 255, 0.08);
        }
        body.dark-mode .stat-card__pill--success {
            background: rgba(16, 185, 129, 0.18);
        }
        body.dark-mode .stat-card__pill--warning {
            background: rgba(245, 158, 11, 0.18);
        }
        body.dark-mode .stat-card__pill--muted {
            background: rgba(148, 163, 184, 0.22);
            color: #e2e8f0;
        }
        body.dark-mode .stat-card__icon {
            color: #cbd5f5;
            background: rgba(255, 255, 255, 0.06);
        }
        body.dark-mode .stat-card__icon--primary {
            color: #bfdbfe;
            background: rgba(59, 130, 246, 0.18);
        }
        body.dark-mode .stat-card__icon--success {
            color: #bbf7d0;
            background: rgba(16, 185, 129, 0.24);
        }
        body.dark-mode .stat-card__icon--warning {
            color: #fed7aa;
            background: rgba(245, 158, 11, 0.22);
        }
        body.dark-mode .stat-card__icon--muted {
            color: #e2e8f0;
            background: rgba(148, 163, 184, 0.28);
        }
        /* Dark mode for navigation modal */
        body.dark-mode .news-modal__overlay {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(3px);
        }
        body.dark-mode .news-modal__dialog {
            background: #0f172a;
            border-color: rgba(148, 163, 184, 0.35);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
        }
        body.dark-mode .news-modal__header h3 {
            color: #e2e8f0;
        }
        body.dark-mode .news-modal__header p {
            color: #cbd5e1;
        }
        body.dark-mode .news-modal__close {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
        }
        body.dark-mode .news-input-control label,
        body.dark-mode .news-input-control small {
            color: #e2e8f0;
        }
        body.dark-mode .news-input-control input,
        body.dark-mode .news-input-control select,
        body.dark-mode .news-input-control textarea {
            background: #111827;
            border-color: #334155;
            color: #e5e7eb;
        }
        body.dark-mode .news-input-control input::placeholder,
        body.dark-mode .news-input-control textarea::placeholder {
            color: #94a3b8;
        }
        body.dark-mode .news-input-control select option {
            color: #e5e7eb;
            background: #0f172a;
        }
        body.dark-mode .news-modal__body {
            color: #e2e8f0;
        }
        /* Icon picker */
        .icon-picker {
            position: relative;
            width: 100%;
        }
        .icon-picker__control {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid rgba(148, 163, 184, 0.5);
            border-radius: 12px;
            background: #fff;
            padding: 0.55rem 0.65rem;
            cursor: pointer;
            min-height: 56px;
            width: 100%;
        }
        .icon-picker__control:hover {
            border-color: rgba(99, 102, 241, 0.55);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.12);
        }
        .icon-picker__control:focus-visible {
            outline: 3px solid rgba(99, 102, 241, 0.25);
            outline-offset: 2px;
            border-color: rgba(99, 102, 241, 0.65);
        }
        .icon-picker__preview {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(99, 102, 241, 0.1);
            color: #4338ca;
            display: grid;
            place-items: center;
            flex: 0 0 42px;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }
        .icon-picker__preview.is-empty {
            background: rgba(148, 163, 184, 0.12);
            color: #475569;
        }
        .icon-picker__control input {
            display: none;
        }
        .icon-picker__selected {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
            min-width: 0;
            flex: 1;
        }
        .icon-picker__selected-label {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.98rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .icon-picker__selected-hint {
            font-size: 0.85rem;
            color: #64748b;
        }
        .icon-picker__caret {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: #4338ca;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(148, 163, 184, 0.4);
            flex: 0 0 auto;
        }
        .icon-picker__dropdown {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 10px);
            background: #f7f7f9;
            border-radius: 14px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            padding: 0.55rem;
            display: none;
            z-index: 5;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.12);
        }
        .icon-picker.is-open .icon-picker__dropdown {
            display: block;
        }
        .icon-picker__search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.5rem 0.7rem;
            background: #f3f4f6;
            margin-bottom: 0.5rem;
            box-shadow: none;
        }
        .icon-picker__search input {
            border: none;
            outline: none;
            flex: 1;
            background: transparent;
            font-size: 0.95rem;
            color: #0f172a;
            padding: 0.1rem 0 0.1rem 0.2rem;
        }
        .icon-picker__search i {
            color: #6b7280;
        }
        .icon-picker__grid {
            display: flex;
            flex-direction: column;
            gap: 0;
            max-height: 280px;
            overflow-y: auto;
            padding: 0.15rem 0.05rem 0.2rem;
        }
        .icon-picker__item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 0.65rem;
            border: 1px solid transparent;
            border-radius: 10px;
            background: #ffffff;
            cursor: pointer;
            transition: background 0.12s ease, border-color 0.12s ease;
            text-align: left;
            box-shadow: inset 0 -1px 0 rgba(226, 232, 240, 0.9);
        }
        .icon-picker__item:hover {
            background: #f3f4f6;
            border-color: rgba(226, 232, 240, 0.9);
        }
        .icon-picker__item.is-selected {
            border-color: rgba(59, 130, 246, 0.4);
            background: #eff6ff;
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.28);
        }
        .icon-picker__item-icon {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #f7f7f9;
            color: #1f2937;
            border: 1px solid rgba(226, 232, 240, 0.9);
        }
        .icon-picker__item-label {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.92rem;
            line-height: 1.3;
            word-break: break-word;
        }
        .icon-picker__empty {
            padding: 1rem;
            text-align: center;
            color: #475569;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px dashed rgba(148, 163, 184, 0.5);
        }
        body.dark-mode .icon-picker__control {
            background: #0f172a;
            border-color: #334155;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        body.dark-mode .icon-picker__preview {
            background: rgba(79, 70, 229, 0.22);
            color: #c7d2fe;
            border-color: #475569;
        }
        body.dark-mode .icon-picker__preview.is-empty {
            background: rgba(71, 85, 105, 0.45);
            color: #e2e8f0;
        }
        body.dark-mode .icon-picker__control input {
            color: #e5e7eb;
        }
        body.dark-mode .icon-picker__dropdown {
            background: #0f172a;
            border-color: #334155;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.55);
        }
        body.dark-mode .icon-picker__search {
            background: #111827;
            border-color: #334155;
            color: #e2e8f0;
        }
        body.dark-mode .icon-picker__search input {
            color: #e2e8f0;
        }
        body.dark-mode .icon-picker__search i {
            color: #cbd5e1;
        }
        body.dark-mode .icon-picker__item {
            background: #111827;
            border-color: #334155;
            color: #e2e8f0;
        }
        body.dark-mode .icon-picker__item-icon {
            background: #0f172a;
            border-color: #475569;
            color: #cbd5f5;
        }
        body.dark-mode .icon-picker__item-label {
            color: #e2e8f0;
        }
        body.dark-mode .icon-picker__item.is-selected {
            border-color: rgba(74, 222, 128, 0.75);
            background: rgba(22, 163, 74, 0.12);
            box-shadow: 0 12px 30px rgba(22, 163, 74, 0.28);
        }
        body.dark-mode .icon-picker__empty {
            background: rgba(15, 23, 42, 0.8);
            border-color: #334155;
            color: #cbd5e1;
        }
        body.dark-mode .icon-picker__selected-hint {
            color: #94a3b8;
        }
        /* Layout refinements for menu modal */
        .menu-form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.1rem;
            align-items: start;
        }
        .menu-toggle-stack {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding-top: 0.15rem;
        }
        .menu-select {
            height: 56px;
            padding: 0.75rem 0.9rem;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            background: #fff;
            font-size: 1rem;
            color: #0f172a;
        }
        /* Toggle switches */
        .toggle-field {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            font-weight: 600;
            color: #0f172a;
            user-select: none;
        }
        .toggle-field input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-switch {
            position: relative;
            width: 56px;
            height: 32px;
            border-radius: 999px;
            background: #e2e8f0;
            box-shadow: inset 0 2px 6px rgba(15, 23, 42, 0.12), 0 4px 10px rgba(15, 23, 42, 0.08);
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            transition: transform 0.2s ease;
        }
        .toggle-field input[type="checkbox"]:checked + .toggle-switch {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
        }
        .toggle-field input[type="checkbox"]:checked + .toggle-switch::after {
            transform: translateX(24px);
        }
        .toggle-field input[type="checkbox"]:focus-visible + .toggle-switch {
            outline: 3px solid rgba(37, 99, 235, 0.35);
            outline-offset: 3px;
        }
        body.dark-mode .toggle-field {
            color: #e2e8f0;
        }
        body.dark-mode .toggle-switch {
            background: #1f2937;
        }
        body.dark-mode .toggle-field input[type="checkbox"]:checked + .toggle-switch {
            background: linear-gradient(135deg, #60a5fa, #2563eb);
        }
    </style>
@endpush

@section('content')
    <div class="news-page">
        @include('admin.partials.alerts')
        <header class="main-header">
            <div class="header-controls">
                <div class="header-cluster header-cluster-left">
                    <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Sembunyikan sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="header-icon sidebar-trigger" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <span class="header-title-text">Pengaturan Navigasi</span>
                <div class="header-cluster header-cluster-right">
                    @include('admin.partials.header-controls')
                </div>
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <i class="fas fa-chevron-right"></i>
                <span>Navigasi</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <p class="page-eyebrow">Sistem Navigasi</p>
                <h1>Pengaturan Menu Navigasi</h1>
                <p>Kelola menu utama, submenu, tautan page/internal, ikon, dan urutan navigasi.</p>
            </div>
            <div class="title-actions">
                <button type="button" id="openMenuModal" class="soft-action-btn soft-action-btn--violet">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Menu</span>
                </button>
            </div>
        </section>

        <section class="summary-grid summary-grid--wide">
            <article class="stat-card" data-card="total">
                <div class="stat-card__header">
                    <div class="stat-card__pill">
                        <i class="fas fa-layer-group"></i>
                        <span>Total</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['total'] ?? 0) }}</p>
                    <p class="stat-card__label">Menu terdaftar.</p>
                </div>
            </article>
            <article class="stat-card" data-card="active">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--success">
                        <i class="fas fa-check-circle"></i>
                        <span>Aktif</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['active'] ?? 0) }}</p>
                    <p class="stat-card__label">Menu aktif saat ini.</p>
                </div>
            </article>
            <article class="stat-card" data-card="submenu">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--info">
                        <i class="fas fa-sitemap"></i>
                        <span>Submenu</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['submenu'] ?? 0) }}</p>
                    <p class="stat-card__label">Total submenu.</p>
                </div>
            </article>
            <article class="stat-card" data-card="custom">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--warning">
                        <i class="fas fa-link"></i>
                        <span>Custom Link</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['custom'] ?? 0) }}</p>
                    <p class="stat-card__label">Menu dengan URL custom.</p>
                </div>
            </article>
        </section>

        <div id="menuModal" class="news-modal {{ $editing ? 'is-visible' : '' }}" data-store="{{ route('admin.navigation-menus.store') }}">
            <div class="news-modal__overlay"></div>
            <div class="news-modal__dialog">
                <div class="news-modal__header">
                    <div>
                        <h3 id="menuModalTitle">{{ $editing ? 'Edit Menu' : 'Tambah Menu' }}</h3>
                        <p id="menuModalSubtitle">{{ $editing ? 'Perbarui menu navigasi.' : 'Isi judul, tujuan link, parent (opsional), dan posisi.' }}</p>
                    </div>
                    <button type="button" class="news-modal__close" id="closeMenuModal" aria-label="Tutup modal"><i class="fas fa-times"></i></button>
                </div>
                <div class="news-modal__body">
                    @php $currentIcon = old('icon', $editing->icon ?? ''); @endphp
                    <form id="menuForm" class="news-form" method="post"
                          action="{{ $editing ? route('admin.navigation-menus.update', $editing) : route('admin.navigation-menus.store') }}">
                        @csrf
                        @if($editing)
                            @method('PUT')
                        @endif
                        <div class="news-form-grid menu-form-grid">
                            <div class="news-input-control">
                                <label>Judul *</label>
                                <input type="text" name="title" value="{{ old('title', $editing->title ?? '') }}" required>
                            </div>
                            <div class="news-input-control">
                                <label>Icon (opsional)</label>
                                <div class="icon-picker" id="iconPicker">
                                    <input type="hidden" name="icon" id="iconInput" value="{{ $currentIcon }}">
                                    <button type="button" class="icon-picker__control" id="iconPickerToggle">
                                        <div class="icon-picker__preview {{ trim($currentIcon) ? '' : 'is-empty' }}" id="iconPickerPreview">
                                            <i class="{{ trim($currentIcon) ? $currentIcon : 'fas fa-circle-question' }}"></i>
                                        </div>
                                        <div class="icon-picker__selected">
                                            <span class="icon-picker__selected-label" id="iconPickerSelectedLabel">Pilih icon</span>
                                            <span class="icon-picker__selected-hint">Klik untuk memilih icon</span>
                                        </div>
                                        <div class="icon-picker__caret" aria-hidden="true"><i class="fas fa-chevron-down"></i></div>
                                    </button>
                                    <div class="icon-picker__dropdown" id="iconPickerDropdown">
                                        <div class="icon-picker__search">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                            <input type="search" id="iconPickerSearch" placeholder="Cari Icon">
                                        </div>
                                        <div class="icon-picker__grid" id="iconPickerGrid" role="listbox" aria-label="Daftar icon"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="news-form-grid menu-form-grid">
                            <div class="news-input-control">
                                <label>Tipe link</label>
                                <select name="type">
                                    <option value="page" @selected(old('type', $editing->type ?? 'page') === 'page')>Page internal</option>
                                    <option value="custom" @selected(old('type', $editing->type ?? '') === 'custom')>Custom URL</option>
                                </select>
                            </div>
                            <div class="news-input-control">
                                <label>Pilih Page (jika tipe page)</label>
                                <select name="page_slug" class="menu-select">
                                    <option value="">-- Pilih halaman --</option>
                                    @foreach($pages as $page)
                                        <option value="/pages/{{ $page->slug }}" @selected(old('page_slug', $editing->page_slug ?? '') === '/pages/'.$page->slug)>
                                            {{ $page->title }} ({{ $page->status }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="news-input-control__hint">Slug diisi otomatis dengan /pages/slug</small>
                            </div>
                            <div class="news-input-control">
                                <label>Parent (submenu dari)</label>
                                <select name="parent_id">
                                    <option value="">(Menu utama)</option>
                                    @foreach($allMenus as $menu)
                                        <option value="{{ $menu->id }}" @selected(old('parent_id', $editing->parent_id ?? '') == $menu->id)>{{ $menu->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="news-input-control">
                                <label>URL custom (jika tipe custom)</label>
                                <input type="text" name="url" value="{{ old('url', $editing->url ?? '') }}" placeholder="https://...">
                            </div>
                        </div>
                        <div class="news-form-grid menu-form-grid">
                            <div class="news-input-control">
                                <label>Posisi</label>
                                <input type="number" name="position" value="{{ old('position', $editing->position ?? 0) }}" min="0">
                            </div>
                            <div class="news-input-control menu-toggle-stack">
                                <label class="toggle-field">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing->is_active ?? true))>
                                    <span class="toggle-switch" aria-hidden="true"></span>
                                    <span class="toggle-label">Aktif</span>
                                </label>
                                <label class="toggle-field">
                                    <input type="hidden" name="target_blank" value="0">
                                    <input type="checkbox" name="target_blank" value="1" @checked(old('target_blank', $editing->target_blank ?? false))>
                                    <span class="toggle-switch" aria-hidden="true"></span>
                                    <span class="toggle-label">Buka di tab baru</span>
                                </label>
                            </div>
                        </div>
                        <div class="table-toolbar" style="padding:0;">
                            <div class="table-info">
                                {{ $editing ? 'Sedang mengedit menu: '.$editing->title : 'Tambah menu baru' }}
                            </div>
                            <div class="table-actions" style="display:flex; gap:0.5rem;">
                                @if($editing)
                                    <a class="ghost-btn ghost-btn--back" href="{{ route('admin.navigation-menus.index') }}"><i class="fas fa-arrow-left"></i> Batal edit</a>
                                @endif
                                <button type="submit" class="soft-action-btn soft-action-btn--success">
                                    <i class="fas fa-check"></i> {{ $editing ? 'Simpan Perubahan' : 'Tambah Menu' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <section class="news-panel">
            <div class="news-card-heading">
                <div>
                    <h2>Struktur Navigasi</h2>
                    <p>Kelola urutan dan status menu.</p>
                </div>
            </div>
            <div class="news-table">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Posisi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                            @include('admin.navigation-menus.partials.row', ['menu' => $menu, 'level' => 0])
                        @empty
                            <tr><td colspan="5" class="banner-panel-empty">Belum ada menu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

{{-- Delete modal (inline so always available) --}}
<div class="news-delete-modal" id="newsDeleteModal" aria-hidden="true" style="display: none;">
    <div class="news-delete-modal__backdrop" data-modal-backdrop></div>
    <div class="news-delete-modal__content" role="dialog" aria-modal="true" aria-labelledby="newsDeleteTitle">
        <header class="news-delete-modal__header">
            <h3 id="newsDeleteTitle">Hapus menu?</h3>
            <button type="button" class="news-delete-modal__close" data-modal-close aria-label="Tutup dialog">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <p class="news-delete-modal__body-text">
            Apakah Anda yakin ingin menghapus menu ini? Data yang dihapus tidak dapat dikembalikan.
        </p>
        <div class="news-delete-modal__actions">
            <button type="button" class="news-delete-modal__option" data-modal-close>Batal</button>
            <button type="button" class="news-delete-modal__option news-delete-modal__option--danger" data-delete-confirm>Hapus</button>
        </div>
    </div>
</div>
<form id="newsDeleteForm" method="POST" class="news-delete-form">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('menuModal');
        if (!modal) return;
        const overlay = modal.querySelector('.news-modal__overlay');
        const closeBtn = document.getElementById('closeMenuModal');
        const openBtn = document.getElementById('openMenuModal');
        const form = document.getElementById('menuForm');
        const titleEl = document.getElementById('menuModalTitle');
        const subtitleEl = document.getElementById('menuModalSubtitle');
        const storeUrl = modal.dataset.store;
        const iconPicker = document.getElementById('iconPicker');
        const iconPickerToggle = document.getElementById('iconPickerToggle');
        const iconPickerSearch = document.getElementById('iconPickerSearch');
        const iconPickerGrid = document.getElementById('iconPickerGrid');
        const iconInput = document.getElementById('iconInput');
        const iconPreview = document.getElementById('iconPickerPreview');
        const iconLabelEl = document.getElementById('iconPickerSelectedLabel');
        const iconRenderFallbacks = {
            'fa-solid fa-people-group': 'fas fa-users',
            'fa-people-group': 'fas fa-users',
            'fa-solid fa-shield-halved': 'fas fa-shield-alt',
            'fa-shield-halved': 'fas fa-shield-alt',
        };
        const iconLabelOverrides = {
            'fas fa-users': 'People Group',
            'fa-solid fa-people-group': 'People Group',
            'fas fa-shield-alt': 'Shield Halved',
            'fa-solid fa-shield-halved': 'Shield Halved',
        };

        const iconPresetOptions = [
            'fa-solid fa-house',
            'fa-solid fa-house-chimney',
            'fa-solid fa-building',
            'fa-solid fa-landmark',
            'fa-solid fa-phone',
            'fa-solid fa-envelope',
            'fa-solid fa-sitemap',
            'fa-solid fa-bell',
            'fa-solid fa-globe',
            'fa-solid fa-map-location-dot',
            'fa-solid fa-location-dot',
            'fa-solid fa-handshake',
            'fa-solid fa-clipboard-list',
            'fa-solid fa-calendar-days',
            'fa-solid fa-clipboard-check',
            'fa-solid fa-list-check',
            'fa-solid fa-tree',
            'fa-solid fa-users',
            'fa-solid fa-user-group',
            'fa-solid fa-circle-question',
            'fa-solid fa-lightbulb',
            'fab fa-facebook',
            'fab fa-instagram',
            'fab fa-twitter'
        ];
        const iconOptions = Array.from(new Set([
            ...iconPresetOptions,
            ...[
                "fa-regular fa-calendar",
                "fa-regular fa-clock",
                "fa-regular fa-compass",
                "fa-regular fa-file-lines",
                "fa-regular fa-file-pdf",
                "fa-regular fa-id-card",
                "fa-regular fa-image",
                "fa-regular fa-money-bill-1",
                "fa-regular fa-wallet",
                "fa-solid fa-check-circle",
                "fa-solid fa-ellipsis-vertical",
                "fa-solid fa-file-arrow-down",
                "fa-solid fa-headset",
                "fa-solid fa-layer-group",
                "fa-solid fa-link",
                "fa-solid fa-people-group",
                "fa-solid fa-shield-halved",
                "fa-solid fa-users",
                "fa-solid fa-wallet",
                "fab fa-whatsapp",
                "fab fa-youtube",
                "far fa-calendar",
                "fas fa-address-book",
                "fas fa-address-card",
                "fas fa-adjust",
                "fas fa-align-center",
                "fas fa-align-justify",
                "fas fa-align-left",
                "fas fa-align-right",
                "fas fa-archive",
                "fas fa-arrow-down",
                "fas fa-arrow-left",
                "fas fa-arrow-pointer",
                "fas fa-arrow-right",
                "fas fa-arrow-up",
                "fas fa-arrow-up-right-from-square",
                "fas fa-arrows-left-right",
                "fas fa-arrows-up-down",
                "fas fa-asterisk",
                "fas fa-award",
                "fas fa-baby",
                "fas fa-ban",
                "fas fa-bars",
                "fas fa-bell",
                "fas fa-birthday-cake",
                "fas fa-bold",
                "fas fa-bolt",
                "fas fa-book",
                "fas fa-book-dead",
                "fas fa-book-open",
                "fas fa-book-reader",
                "fas fa-box",
                "fas fa-briefcase",
                "fas fa-bullhorn",
                "fas fa-bullseye",
                "fas fa-business-time",
                "fas fa-calendar",
                "fas fa-calendar-alt",
                "fas fa-calendar-check",
                "fas fa-calendar-day",
                "fas fa-calendar-times",
                "fas fa-camera",
                "fas fa-chart-bar",
                "fas fa-chart-line",
                "fas fa-chart-pie",
                "fas fa-check",
                "fas fa-check-circle",
                "fas fa-chevron-down",
                "fas fa-chevron-left",
                "fas fa-chevron-right",
                "fas fa-child",
                "fas fa-circle",
                "fas fa-circle-check",
                "fas fa-circle-exclamation",
                "fas fa-circle-info",
                "fas fa-circle-xmark",
                "fas fa-city",
                "fas fa-clipboard-list",
                "fas fa-clock",
                "fas fa-clock-rotate-left",
                "fas fa-cloud",
                "fas fa-cloud-arrow-up",
                "fas fa-cloud-upload-alt",
                "fas fa-cog",
                "fas fa-coins",
                "fas fa-copy",
                "fas fa-cross",
                "fas fa-database",
                "fas fa-diagram-project",
                "fas fa-download",
                "fas fa-droplet",
                "fas fa-ellipsis-v",
                "fas fa-envelope-open-text",
                "fas fa-exchange-alt",
                "fas fa-exclamation",
                "fas fa-exclamation-triangle",
                "fas fa-external-link-alt",
                "fas fa-eye",
                "fas fa-feather",
                "fas fa-female",
                "fas fa-file-alt",
                "fas fa-file-arrow-up",
                "fas fa-file-excel",
                "fas fa-file-export",
                "fas fa-file-import",
                "fas fa-file-lines",
                "fas fa-file-pdf",
                "fas fa-file-pen",
                "fas fa-file-signature",
                "fas fa-file-upload",
                "fas fa-file-word",
                "fas fa-fill-drip",
                "fas fa-filter",
                "fas fa-flag",
                "fas fa-folder-open",
                "fas fa-gavel",
                "fas fa-gear",
                "fas fa-globe",
                "fas fa-graduation-cap",
                "fas fa-hand-holding-heart",
                "fas fa-hand-holding-usd",
                "fas fa-hashtag",
                "fas fa-headset",
                "fas fa-heart",
                "fas fa-heart-broken",
                "fas fa-heartbeat",
                "fas fa-history",
                "fas fa-home",
                "fas fa-hourglass-half",
                "fas fa-id-badge",
                "fas fa-id-card",
                "fas fa-image",
                "fas fa-images",
                "fas fa-inbox",
                "fas fa-indent",
                "fas fa-info",
                "fas fa-info-circle",
                "fas fa-italic",
                "fas fa-layer-group",
                "fas fa-life-ring",
                "fas fa-link",
                "fas fa-list",
                "fas fa-list-check",
                "fas fa-list-ol",
                "fas fa-list-ul",
                "fas fa-lock",
                "fas fa-male",
                "fas fa-map",
                "fas fa-map-location-dot",
                "fas fa-map-marked-alt",
                "fas fa-map-marker-alt",
                "fas fa-minus",
                "fas fa-money-bill-wave",
                "fas fa-money-check-alt",
                "fas fa-moon",
                "fas fa-newspaper",
                "fas fa-outdent",
                "fas fa-paper-plane",
                "fas fa-passport",
                "fas fa-pause",
                "fas fa-pause-circle",
                "fas fa-pen",
                "fas fa-pencil-alt",
                "fas fa-people-group",
                "fas fa-people-roof",
                "fas fa-person-walking-arrow-right",
                "fas fa-photo-video",
                "fas fa-place-of-worship",
                "fas fa-plane-departure",
                "fas fa-plus",
                "fas fa-pray",
                "fas fa-praying-hands",
                "fas fa-project-diagram",
                "fas fa-right-from-bracket",
                "fas fa-ring",
                "fas fa-rotate-left",
                "fas fa-route",
                "fas fa-save",
                "fas fa-school",
                "fas fa-screwdriver-wrench",
                "fas fa-search",
                "fas fa-share-alt",
                "fas fa-shield-alt",
                "fas fa-sign-out-alt",
                "fas fa-sitemap",
                "fas fa-sliders-h",
                "fas fa-star",
                "fas fa-store",
                "fas fa-stream",
                "fas fa-sun",
                "fas fa-table",
                "fas fa-tachometer-alt",
                "fas fa-tag",
                "fas fa-tags",
                "fas fa-text-height",
                "fas fa-th-large",
                "fas fa-times",
                "fas fa-tint",
                "fas fa-trash",
                "fas fa-trash-alt",
                "fas fa-tree",
                "fas fa-triangle-exclamation",
                "fas fa-umbrella-beach",
                "fas fa-underline",
                "fas fa-undo",
                "fas fa-universal-access",
                "fas fa-university",
                "fas fa-upload",
                "fas fa-user",
                "fas fa-user-check",
                "fas fa-user-circle",
                "fas fa-user-clock",
                "fas fa-user-edit",
                "fas fa-user-friends",
                "fas fa-user-graduate",
                "fas fa-user-group",
                "fas fa-user-plus",
                "fas fa-user-shield",
                "fas fa-user-slash",
                "fas fa-user-tie",
                "fas fa-user-times",
                "fas fa-users",
                "fas fa-users-gear",
                "fas fa-users-slash",
                "fas fa-venus-mars",
                "fas fa-video",
                "fas fa-volume-high",
                "fas fa-walking",
                "fas fa-wallet",
                "fas fa-wheelchair",
                "fas fa-xmark"
            ]
        ]));
        const initialIconValue = (iconInput?.value || '').trim();
        if (initialIconValue && !iconOptions.includes(initialIconValue)) {
            iconOptions.unshift(initialIconValue);
        }

        const methodField = () => form.querySelector('input[name=\"_method\"]');

        const iconLabel = (cls) => {
            const trimmed = (cls || '').trim();
            if (iconLabelOverrides[trimmed]) return iconLabelOverrides[trimmed];
            const cleaned = trimmed
                .replace(/^(fa-(solid|regular|brands)|fas|far|fab)\s+/, '')
                .replace(/^fa-/, '')
                .replace(/fa-/g, '')
                .replace(/-/g, ' ');
            return cleaned.replace(/\b\w/g, (c) => c.toUpperCase());
        };
        const highlightSelectedIcon = (value) => {
            if (!iconPickerGrid) return;
            iconPickerGrid.querySelectorAll('.icon-picker__item').forEach(btn => {
                btn.classList.toggle('is-selected', !!value && btn.dataset.icon === value);
            });
        };
        const setIconLabel = (value) => {
            if (iconLabelEl) {
                const label = value ? iconLabel(value) : 'Pilih icon';
                iconLabelEl.textContent = label;
            }
        };
        const setIconPreview = (value) => {
            if (!iconPreview) return;
            const raw = (value || '').trim() || 'fas fa-circle-question';
            const iconClass = iconRenderFallbacks[raw] || raw;
            iconPreview.textContent = '';
            const iconEl = document.createElement('i');
            iconEl.className = iconClass;
            iconEl.setAttribute('aria-hidden', 'true');
            iconPreview.appendChild(iconEl);
            iconPreview.classList.toggle('is-empty', !value);
        };
        const renderIcons = (searchTerm = '') => {
            if (!iconPickerGrid) return;
            const term = searchTerm.trim().toLowerCase();
            const filtered = iconOptions.filter((cls) => {
                if (!term) return true;
                return cls.toLowerCase().includes(term) || iconLabel(cls).toLowerCase().includes(term);
            });
            if (!filtered.length) {
                iconPickerGrid.innerHTML = '<div class=\"icon-picker__empty\">Icon tidak ditemukan.</div>';
                return;
            }
            iconPickerGrid.innerHTML = filtered.map((cls) => `
                <button type="button" class="icon-picker__item" data-icon="${cls}" aria-label="${cls}">
                    <span class="icon-picker__item-icon"><i class="${iconRenderFallbacks[cls] || cls}"></i></span>
                    <span class="icon-picker__item-label">${iconLabel(cls)}</span>
                </button>
            `).join('');
            const currentIcon = (iconInput?.value || '').trim();
            highlightSelectedIcon(currentIcon);
        };
        const closeIconPicker = () => {
            iconPicker?.classList.remove('is-open');
        };
        const openIconPicker = () => {
            if (!iconPicker) return;
            iconPicker.classList.add('is-open');
            renderIcons(iconPickerSearch?.value || '');
            iconPickerSearch?.focus();
        };
        const setIconValue = (value) => {
            const safeValue = (value || '').trim();
            if (iconInput) iconInput.value = safeValue;
            setIconPreview(safeValue);
            setIconLabel(safeValue);
            highlightSelectedIcon(safeValue);
        };
        const resetIconPicker = () => {
            setIconValue('');
            if (iconPickerSearch) iconPickerSearch.value = '';
            renderIcons('');
            closeIconPicker();
        };

        const showModal = () => {
            modal.classList.add('is-visible');
            requestAnimationFrame(() => modal.classList.add('is-active'));
        };
        const hideModal = () => {
            closeIconPicker();
            modal.classList.remove('is-active');
            setTimeout(() => modal.classList.remove('is-visible'), 200);
        };

        const resetFormForCreate = () => {
            form.action = storeUrl;
            const m = methodField();
            if (m) m.remove();
            form.querySelector('[name=\"title\"]').value = '';
            form.querySelector('[name=\"type\"]').value = 'page';
            form.querySelector('[name=\"page_slug\"]').value = '';
            form.querySelector('[name=\"url\"]').value = '';
            form.querySelector('[name=\"parent_id\"]').value = '';
            form.querySelector('[name=\"position\"]').value = '0';
            const targetBlank = form.querySelector('input[type=\"checkbox\"][name=\"target_blank\"]');
            const isActive = form.querySelector('input[type=\"checkbox\"][name=\"is_active\"]');
            if (targetBlank) targetBlank.checked = false;
            if (isActive) isActive.checked = true;
            resetIconPicker();
            titleEl.textContent = 'Tambah Menu';
            subtitleEl.textContent = 'Isi judul, tujuan link, parent (opsional), dan posisi.';
        };

        openBtn?.addEventListener('click', () => {
            resetFormForCreate();
            showModal();
        });

        closeBtn?.addEventListener('click', hideModal);
        overlay?.addEventListener('click', hideModal);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (iconPicker?.classList.contains('is-open')) {
                    closeIconPicker();
                    return;
                }
                hideModal();
            }
        });

        if (modal.classList.contains('is-visible')) {
            showModal();
        }

        iconPickerToggle?.addEventListener('click', (e) => {
            e.preventDefault();
            const isOpen = iconPicker?.classList.contains('is-open');
            isOpen ? closeIconPicker() : openIconPicker();
        });
        iconPickerGrid?.addEventListener('click', (e) => {
            const btn = e.target.closest('.icon-picker__item');
            if (!btn) return;
            setIconValue(btn.dataset.icon || '');
            closeIconPicker();
        });
        iconPickerSearch?.addEventListener('input', (e) => renderIcons(e.target.value));
        document.addEventListener('click', (e) => {
            if (iconPicker && iconPicker.classList.contains('is-open')) {
                if (!iconPicker.contains(e.target) && !iconPickerToggle?.contains(e.target)) {
                    closeIconPicker();
                }
            }
        });
        renderIcons(iconPickerSearch?.value || '');
        const initialIcon = initialIconValue;
        setIconPreview(initialIcon);
        setIconLabel(initialIcon);
        highlightSelectedIcon(initialIcon);

        // action dropdown like berita
        const actionMenus = document.querySelectorAll('[data-action-menu]');
        actionMenus.forEach((details) => {
            const summary = details.querySelector('summary');
            summary?.addEventListener('click', (e) => {
                e.preventDefault();
                const isOpen = details.hasAttribute('open');
                actionMenus.forEach(d => d.removeAttribute('open'));
                if (!isOpen) details.setAttribute('open', '');
            });
        });
        document.addEventListener('click', (e) => {
            if (!(e.target.closest && e.target.closest('[data-action-menu]'))) {
                actionMenus.forEach(d => d.removeAttribute('open'));
            }
        });

        // Delete confirmation modal (reuse news style)
        const deleteModal = document.getElementById('newsDeleteModal');
        const deleteForm = document.getElementById('newsDeleteForm');
        const deleteTitle = document.getElementById('newsDeleteTitle');
        const deleteConfirm = deleteModal?.querySelector('[data-delete-confirm]');
        const deleteClosers = deleteModal?.querySelectorAll('[data-modal-close], [data-modal-backdrop]');

        document.addEventListener('click', (ev) => {
            const delBtn = ev.target.closest('[data-nav-delete]');
            if (delBtn && deleteModal && deleteForm) {
                ev.preventDefault();
                ev.stopPropagation();
                deleteForm.action = delBtn.getAttribute('data-nav-delete');
                deleteTitle.textContent = delBtn.getAttribute('data-nav-title') || 'Hapus data?';
                
                deleteModal.style.display = 'flex';
                // Force reflow to enable transition
                deleteModal.offsetHeight;
                
                deleteModal.classList.add('news-delete-modal--visible');
                deleteModal.setAttribute('aria-hidden', 'false');
                // tutup dropdown aksi jika sedang terbuka
                actionMenus.forEach(d => d.removeAttribute('open'));
            }
        });

        deleteConfirm?.addEventListener('click', () => deleteForm.submit());
        deleteClosers?.forEach(btn => btn.addEventListener('click', () => {
            deleteModal.classList.remove('news-delete-modal--visible');
            deleteModal.setAttribute('aria-hidden', 'true');
            setTimeout(() => {
                deleteModal.style.display = 'none';
            }, 300); // Wait for transition
        }));
    });
</script>
@endpush
