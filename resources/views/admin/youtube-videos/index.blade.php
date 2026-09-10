@extends('admin.layouts.app')

@section('title', 'Video YouTube')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select.css') }}">
    <style>
        .yt-grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .yt-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.25rem; }
        .yt-form { display: flex; flex-direction: column; gap: 0.75rem; }
        .yt-form h3 { margin: 0 0 0.35rem; }
        .yt-form small { color: #475569; }
        .yt-field { display: flex; flex-direction: column; gap: 0.35rem; }
        .yt-field input, .yt-field textarea { border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.65rem 0.75rem; font-size: 0.95rem; width: 100%; }
        .yt-field input:focus, .yt-field textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.16); }
        .yt-actions { display: flex; gap: 0.6rem; align-items: center; flex-wrap: wrap; }
        .yt-btn { border: 0; border-radius: 12px; padding: 0.65rem 1rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 0.4rem; text-decoration: none; }
        .yt-btn--primary { background: #2563eb; color: #fff; }
        .yt-btn--ghost { background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; }
        .yt-table { width: 100%; border-collapse: collapse; }
        .yt-table th, .yt-table td { padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: top; }
        .yt-badge { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem 0.65rem; border-radius: 999px; font-weight: 600; font-size: 0.85rem; }
        .yt-badge--active { background: #ecfdf3; color: #166534; }
        .yt-badge--muted { background: #f1f5f9; color: #475569; }
        .yt-thumb iframe { width: 260px; max-width: 100%; border: 0; border-radius: 12px; min-height: 146px; }
        .yt-empty { text-align: center; padding: 1rem; color: #6b7280; }
        /* Toggle switch style (match public-info switch) */
        .yt-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-weight: 600;
            color: #0f172a;
            user-select: none;
        }
        .yt-toggle input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .yt-toggle .yt-switch {
            width: 62px;
            height: 34px;
            border-radius: 999px;
            background: #e2e8f0;
            display: inline-flex;
            align-items: center;
            padding: 4px;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            box-shadow: inset 0 1px 1px rgba(0,0,0,0.08);
        }
        .yt-toggle .yt-knob {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            transform: translateX(0);
            transition: transform 0.22s ease;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }
        .yt-toggle input:checked + .yt-switch {
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
        }
        .yt-toggle input:checked + .yt-switch .yt-knob {
            transform: translateX(28px);
        }

        .yt-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }
        .entries-control {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 0.65rem;
            border-radius: 10px;
            background: #f8fafc;
            font-weight: 600;
            color: #0f172a;
        }
        .entries-control select {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.25rem 1.8rem 0.25rem 0.5rem;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' viewBox='0 0 12 7'%3E%3Cpath fill='%238297a4' d='M6 7 0 0h12z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        .yt-search {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }
        .yt-search input {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.45rem 0.65rem;
            min-width: 200px;
        }
        .yt-info {
            margin-top: 0.75rem;
            font-size: 0.9rem;
            color: #475569;
        }
        .yt-footer-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .yt-pagination nav {
            display: flex;
            justify-content: flex-end;
        }
        .yt-pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.75rem;
        }
        .yt-pagination .news-pagination {
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }
        .yt-pagination .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #0f172a;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
        }
        .yt-pagination .pagination-btn:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        .yt-pagination .pagination-btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        .yt-pagination .pagination-btn[aria-disabled="true"] {
            opacity: 0.55;
            cursor: not-allowed;
        }

        /* Modal */
        .yt-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .yt-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .yt-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            opacity: 0;
            transition: opacity 0.25s ease;
        }
        .yt-modal.is-visible .yt-modal__backdrop {
            opacity: 1;
        }
        .yt-modal__content {
            position: relative;
            width: min(960px, 96vw);
            max-height: 90vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 22px;
            padding: 1.4rem;
            box-shadow: 0 28px 70px rgba(15, 23, 42, 0.28);
            display: flex;
            flex-direction: column;
            transform: translateY(12px) scale(0.98);
            opacity: 0;
            transition: transform 0.25s ease, opacity 0.25s ease;
            scrollbar-width: thin;
            scrollbar-color: rgba(100, 116, 139, 0.4) rgba(148, 163, 184, 0.12);
        }
        .yt-modal__content::-webkit-scrollbar {
            width: 10px;
        }
        .yt-modal__content::-webkit-scrollbar-track {
            background: rgba(148, 163, 184, 0.12);
            border-radius: 12px;
        }
        .yt-modal__content::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.55);
            border-radius: 12px;
        }
        .yt-modal__content::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 0.7);
        }
        .yt-modal.is-visible .yt-modal__content {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .yt-modal__content .yt-form {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .yt-modal__content .yt-form,
        .yt-modal__content .yt-form * {
            font-family: "Poppins", "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        .yt-modal__close {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            border: none;
            background: rgba(148, 163, 184, 0.16);
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .yt-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1rem;
        }
        .yt-modal__content .yt-actions {
            margin-top: auto;
            padding-top: 0.75rem;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .yt-view-body { display: grid; gap: 0.5rem; }
        .yt-view-body small { color: #475569; }
        .yt-detail__header {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: flex-start;
        }
        .yt-detail__link {
            display: inline-block;
            color: #2563eb;
            word-break: break-all;
        }
        .yt-detail__link--below {
            font-weight: 700;
        }
        .yt-detail__desc {
            margin: 0.25rem 0 0.5rem;
            color: #475569;
        }
        .yt-detail__status {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .yt-detail__status.is-live {
            background: #ecfdf3;
            color: #166534;
        }
        .yt-detail__status.is-draft {
            background: #f1f5f9;
            color: #475569;
        }
        .yt-preview {
            display: grid;
            gap: 0.75rem;
            margin: 0.75rem 0;
        }
        .yt-preview__embed iframe {
            width: 100%;
            border: 0;
            border-radius: 12px;
            min-height: 520px;
            aspect-ratio: 16 / 9;
        }
        .yt-preview__empty {
            color: #94a3b8;
            text-align: center;
            font-size: 0.9rem;
        }
        .yt-confirm {
            display: grid;
            gap: 0.75rem;
        }
        .yt-confirm__text {
            margin: 0;
            color: #0f172a;
        }
        .yt-detail__grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem 1rem;
            margin-top: 0.5rem;
        }
        .yt-detail__grid dt {
            font-size: 0.78rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .yt-detail__grid dd {
            margin: 0;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        /* Dark mode */
        body.dark-mode .yt-card {
            background: #0f172a;
            border-color: #1f2937;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
        }

        /* Dark mode */
        body.dark-mode .yt-form {
            background: #0f172a;
            border-color: #1f2937;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.35);
        }
        body.dark-mode .yt-form small {
            color: #94a3b8;
        }
        body.dark-mode .yt-field span {
            color: #e5e7eb;
        }
        body.dark-mode .yt-field input,
        body.dark-mode .yt-field textarea {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .yt-field input:focus,
        body.dark-mode .yt-field textarea:focus {
            border-color: rgba(96, 165, 250, 0.7);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.2);
            background: #0f172a;
        }
        body.dark-mode .yt-btn--ghost {
            background: rgba(255, 255, 255, 0.04);
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .yt-table th,
        body.dark-mode .yt-table td {
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .yt-table thead th {
            color: #cbd5e1;
        }
        body.dark-mode .yt-empty {
            color: #94a3b8;
        }
        body.dark-mode .yt-badge--active {
            background: rgba(34, 197, 94, 0.16);
            color: #bbf7d0;
        }
        body.dark-mode .yt-badge--muted {
            background: rgba(148, 163, 184, 0.16);
            color: #e5e7eb;
        }
        body.dark-mode .yt-toggle {
            color: #e5e7eb;
        }
        body.dark-mode .yt-toggle .yt-switch {
            background: rgba(30, 41, 59, 0.85);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.35);
            border: 1px solid #1f2937;
        }
        body.dark-mode .yt-toggle .yt-knob {
            background: #1f2937;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.45);
        }
        body.dark-mode .yt-toggle input:checked + .yt-switch {
            background: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.3);
        }
        body.dark-mode .yt-toggle input:checked + .yt-switch .yt-knob {
            background: #fff;
        }
        body.dark-mode .yt-controls {
            color: #e5e7eb;
        }
        body.dark-mode .entries-control {
            background: rgba(255, 255, 255, 0.04);
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .entries-control select,
        body.dark-mode .yt-search input {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .yt-info {
            color: #cbd5e1;
        }
        body.dark-mode .yt-pagination .pagination li a,
        body.dark-mode .yt-pagination .pagination li span {
            border-color: #1f2937;
            background: #0b1221;
            color: #e5e7eb;
        }
        body.dark-mode .yt-pagination .pagination-btn:hover,
        body.dark-mode .yt-pagination .pagination-btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }
        body.dark-mode .yt-modal__content {
            background: #0f172a;
            border: 1px solid #1f2937;
            box-shadow: 0 22px 50px rgba(0, 0, 0, 0.55);
            color: #e5e7eb;
        }
        body.dark-mode .yt-view-body,
        body.dark-mode .yt-detail__desc {
            color: #e5e7eb;
        }
        body.dark-mode .yt-detail__link {
            color: #93c5fd;
        }
        body.dark-mode .yt-detail__status.is-live {
            background: rgba(34, 197, 94, 0.18);
            color: #bbf7d0;
        }
        body.dark-mode .yt-detail__status.is-draft {
            background: rgba(148, 163, 184, 0.18);
            color: #e5e7eb;
        }
        body.dark-mode .yt-preview__empty {
            color: #cbd5e1;
        }
        body.dark-mode .yt-modal__close {
            background: rgba(148, 163, 184, 0.22);
            color: #e5e7eb;
        }
        body.dark-mode .yt-confirm__text {
            color: #e5e7eb;
        }
    </style>
@endpush

@section('content')
    <div class="news-page announcements-page">
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
            <span class="header-title-text">Video YouTube</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Publikasi</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.youtube-videos.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Video YouTube</a>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Galeri Video YouTube</h1>
        </div>
        <div class="title-actions">
            <a href="{{ route('videos.index') }}" target="_blank" class="news-btn news-btn--ghost" style="text-decoration: none;">
                <i class="fas fa-external-link-alt"></i>
                Lihat Halaman Video
            </a>
            <button type="button" class="ann-btn ann-btn--submit" id="openCreateModal">
                <i class="fas fa-plus"></i>
                <span>Tambah Video</span>
            </button>
        </div>
    </section>

    <section class="summary-grid" style="grid-template-columns: repeat(5, 1fr);">
        <article class="stat-card stat-card--accent">
            <div class="stat-card__header">
                <div class="stat-card__pill">
                    <i class="fas fa-video"></i>
                    <span>Total Video</span>
                </div>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['total']) }}</p>
                <p class="stat-card__label">Semua video tersimpan.</p>
            </div>
        </article>
        <article class="stat-card" data-card="active">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--success">
                    <i class="fas fa-check-circle"></i>
                    <span>Tayang</span>
                </div>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['published']) }}</p>
                <p class="stat-card__label">Sedang ditampilkan.</p>
            </div>
        </article>
        <article class="stat-card" data-card="inactive">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--muted">
                    <i class="fas fa-pause-circle"></i>
                    <span>Draft</span>
                </div>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['draft']) }}</p>
                <p class="stat-card__label">Tidak ditampilkan.</p>
            </div>
        </article>
        <article class="stat-card" data-card="month">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--brand">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Terbit Bulan Ini</span>
                </div>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['publishedThisMonth'] ?? 0) }}</p>
                <p class="stat-card__label">Tayang bulan ini.</p>
            </div>
        </article>
        <article class="stat-card" data-card="new">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--muted">
                    <i class="fas fa-plus-circle"></i>
                    <span>Baru Ditambahkan</span>
                </div>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['newThisMonth'] ?? 0) }}</p>
                <p class="stat-card__label">Video ditambahkan bulan ini.</p>
            </div>
        </article>
    </section>

    <div>
        <div>
            <form method="GET" class="news-filter-form filter-toolbar">
                <div class="form-field" style="flex-grow: 1;">
                    <label>Pencarian</label>
                    <div style="display: flex; gap: 0.5rem; align-items: stretch; position: relative; z-index: 5;">
                        <input type="search" name="search" value="{{ $search ?? '' }}"
                            placeholder="Cari video..."
                            style="flex: 1; min-width: 0; position: relative; z-index: 5;">
                        <button type="submit"
                            style="background: rgba(148, 163, 184, 0.15); color: #475569; border: 1px solid rgba(148, 163, 184, 0.2); padding: 0 1rem; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: grid; place-items: center; min-width: 44px; position: relative; z-index: 10; pointer-events: auto;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="form-field">
                    <label>Per halaman</label>
                    <div class="cs-wrapper" data-cs-label="Per halaman">
                        <select name="per_page" class="cs-native">
                            @foreach([5,10,12,25,50] as $opt)
                                <option value="{{ $opt }}" @selected(($perPage ?? 12) == $opt)>{{ $opt }} Baris</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-toolbar__actions">
                    <button type="submit" class="ann-btn ann-btn--submit">Terapkan filter</button>
                    <a href="{{ route('admin.youtube-videos.index') }}" class="ann-btn ann-btn--cancel">Reset</a>
                </div>
            </form>

            <section class="news-panel" style="gap: 0; padding: 0; overflow: hidden; border: none; box-shadow: none; margin-top: 1.25rem;">
                <h2 style="font-weight: 600; font-size: 1.15rem; margin: 0; padding: 1rem 1rem 0.85rem 1rem;">Daftar Video</h2>
                <div class="news-table">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 60px;">#</th>
                                <th style="text-align: left;">Informasi Video</th>
                                <th style="text-align: center; width: 140px;">Status</th>
                                <th style="text-align: center; width: 80px;">Urutan</th>
                                <th style="text-align: center; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                @forelse($videos as $index => $video)
                    <tr>
                        <td style="text-align: center;">{{ $videos->firstItem() + $index }}</td>
                        <td style="text-align: left;">
                            <div class="yt-thumb">
                                <iframe src="{{ $video->embed_url }}?controls=0&rel=0&modestbranding=1&playsinline=1" title="{{ $video->title }}" loading="lazy" allowfullscreen></iframe>
                            </div>
                            <div style="margin-top:0.65rem;">
                                <strong style="font-size: 1.05rem;">{{ $video->title }}</strong><br>
                                <div style="display: flex; gap: 8px; margin-top: 4px; flex-wrap: wrap;">
                                    <span class="badge badge--muted" style="font-size: 0.75rem;"><i class="fab fa-youtube"></i> {{ $video->youtube_id }}</span>
                                    <small style="color: #64748b;">{{ $video->youtube_url }}</small>
                                </div>
                                @if($video->description)
                                    <p style="margin: 0.5rem 0 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">{{ \Illuminate\Support\Str::limit($video->description, 150) }}</p>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: center;">
                            @if($video->is_published)
                                <span class="yt-badge yt-badge--active" style="display: inline-flex; margin-bottom: 4px;"><i class="fas fa-check"></i> Tayang</span>
                            @else
                                <span class="yt-badge yt-badge--muted" style="display: inline-flex; margin-bottom: 4px;"><i class="fas fa-pause"></i> Draft</span>
                            @endif
                            @if($video->published_at)
                                <div><small style="font-size: 0.75rem; color: #64748b;">{{ $video->published_at->format('d M Y H:i') }}</small></div>
                            @endif
                            <form action="{{ route('admin.youtube-videos.toggle', $video) }}" method="POST" style="margin-top:0.5rem;">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="ann-btn {{ $video->is_published ? 'ann-btn--cancel' : 'ann-btn--submit' }}" style="font-size: 0.78rem; padding: 0.3rem 0.75rem; min-height: unset; width: 100%; justify-content: center; text-align: center;">
                                    {{ $video->is_published ? 'Sembunyikan' : 'Tampilkan' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align: center;"><strong>{{ $video->sort_order }}</strong></td>
                        <td class="news-table__cell--actions" style="text-align: center;">
                            <details class="news-table__action-dropdown" data-action-menu>
                                <summary class="action-button action-button--dots" aria-haspopup="menu" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </summary>
                                <div class="news-table__action-options" role="menu">
                                    <button type="button" class="news-table__action-item js-view" role="menuitem" data-video="{{ json_encode([
                                        'id' => $video->id,
                                        'title' => $video->title,
                                        'youtube_url' => $video->youtube_url,
                                        'youtube_id' => $video->youtube_id,
                                        'description' => $video->description,
                                        'thumbnail' => $video->thumbnail,
                                        'thumbnail_url' => $video->thumbnail_url,
                                        'sort_order' => $video->sort_order,
                                        'is_published' => $video->is_published,
                                        'published_at' => optional($video->published_at)->format('d M Y H:i'),
                                        'created_at' => optional($video->created_at)->format('d M Y H:i'),
                                        'updated_at' => optional($video->updated_at)->format('d M Y H:i'),
                                        'embed_url' => $video->embed_url,
                                    ]) }}">
                                        <i class="fas fa-eye"></i><span>Detail</span>
                                    </button>
                                    <button type="button" class="news-table__action-item js-edit" role="menuitem"
                                        data-id="{{ $video->id }}"
                                        data-title="{{ $video->title }}"
                                        data-url="{{ $video->youtube_url }}"
                                        data-description="{{ $video->description }}"
                                        data-thumbnail="{{ $video->thumbnail_url }}"
                                        data-sort="{{ $video->sort_order }}"
                                        data-published="{{ optional($video->published_at)->format('Y-m-d\\TH:i') }}"
                                        data-status="{{ $video->is_published ? 1 : 0 }}"
                                        data-action="{{ route('admin.youtube-videos.update', $video) }}">
                                        <i class="fas fa-pen"></i><span>Edit</span>
                                    </button>
                                    <button type="button"
                                        class="news-table__action-item news-table__action-item--danger js-delete" role="menuitem"
                                        data-action="{{ route('admin.youtube-videos.destroy', $video) }}"
                                        data-title="{{ $video->title }}">
                                        <i class="fas fa-trash"></i><span>Hapus</span>
                                    </button>
                                </div>
                            </details>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="yt-empty">Belum ada video.</td>
                    </tr>
                @endforelse
                </tbody>
                </table>
                </div>
                <div class="news-footer">
                    <div class="table-info">
                        Menampilkan {{ $videos->count() ? $videos->firstItem() : 0 }} - {{ $videos->count() ? $videos->lastItem() : 0 }} dari {{ $videos->total() }} video
                    </div>
                    <div class="news-pagination news-pagination--center">
                        {{ $videos->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- Modal: Form --}}
    <div class="yt-modal" id="ytFormModal" aria-hidden="true">
        <div class="yt-modal__backdrop" data-close-form></div>
        <div class="yt-modal__content">
            <button type="button" class="yt-modal__close" data-close-form><i class="fas fa-times"></i></button>
            <h3 id="ytFormTitle">Tambah Video</h3>
            <small>Tempelkan URL YouTube (contoh: https://youtu.be/xxxx atau https://www.youtube.com/watch?v=xxxx).</small>
            <form id="ytModalForm" action="{{ route('admin.youtube-videos.store') }}" method="POST" class="yt-form" style="margin-top:0.85rem;">
                @csrf
                <input type="hidden" name="_method" value="POST" id="ytFormMethod">
                <label class="yt-field">
                    <span>Judul</span>
                    <input type="text" name="title" id="ytTitle" required maxlength="255">
                </label>
                <label class="yt-field">
                    <span>Link YouTube</span>
                    <input type="text" name="youtube_url" id="ytUrl" required maxlength="255" placeholder="https://www.youtube.com/watch?v=...">
                </label>
                <label class="yt-field">
                    <span>Deskripsi (opsional)</span>
                    <textarea name="description" id="ytDescription" rows="3" placeholder="Deskripsi singkat video"></textarea>
                </label>
                <label class="yt-field">
                    <span>Thumbnail custom (opsional)</span>
                    <input type="url" name="thumbnail_url" id="ytThumbnail" placeholder="https://...jpg">
                    <small>Kosongkan untuk memakai thumbnail default YouTube.</small>
                </label>
                <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <label class="yt-field" style="flex:1; min-width: 160px;">
                        <span>Urutan</span>
                        <input type="number" name="sort_order" id="ytSort" min="0" step="1" value="0">
                    </label>
                    <label class="yt-field" style="flex:1; min-width: 200px;">
                        <span>Jadwal tayang</span>
                        <input type="datetime-local" name="published_at" id="ytPublished">
                    </label>
                </div>
                <label class="yt-toggle">
                    <input type="checkbox" name="is_published" value="1" id="ytPublishedToggle" checked>
                    <span class="yt-switch"><span class="yt-knob"></span></span>
                    <span>Tampilkan di situs</span>
                </label>
                <div class="yt-actions">
                    <button type="button" class="ann-btn ann-btn--cancel" data-close-form>Batal</button>
                    <button type="submit" class="ann-btn ann-btn--submit">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Detail --}}
    <div class="yt-modal" id="ytDetailModal" aria-hidden="true">
        <div class="yt-modal__backdrop" data-close-detail></div>
        <div class="yt-modal__content">
            <button type="button" class="yt-modal__close" data-close-detail><i class="fas fa-times"></i></button>
            <div class="yt-view-body">
                <div class="yt-detail__header">
                    <h3 id="ytDetailTitle">Detail Video</h3>
                    <span class="yt-detail__status" id="ytDetailStatus">-</span>
                </div>
                <p class="yt-detail__desc" id="ytDetailDesc"></p>
                <div class="yt-preview">
                    <div class="yt-preview__embed" id="ytDetailEmbed"></div>
                    <a id="ytDetailUrl" class="yt-detail__link yt-detail__link--below" href="#" target="_blank" rel="noopener">-</a>
                </div>
                <dl class="yt-detail__grid">
                    <div>
                        <dt>Judul</dt>
                        <dd id="ytDetailTitleText"></dd>
                    </div>
                    <div>
                        <dt>YouTube ID</dt>
                        <dd id="ytDetailYoutubeId"></dd>
                    </div>
                    <div>
                        <dt>Urutan</dt>
                        <dd id="ytDetailSort"></dd>
                    </div>
                    <div>
                        <dt>Jadwal Tayang</dt>
                        <dd id="ytDetailPublished"></dd>
                    </div>
                    <div>
                        <dt>Dibuat</dt>
                        <dd id="ytDetailCreated"></dd>
                    </div>
                    <div>
                        <dt>Diubah</dt>
                        <dd id="ytDetailUpdated"></dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- Modal: Delete confirm --}}
    <div class="yt-modal" id="ytDeleteModal" aria-hidden="true">
        <div class="yt-modal__backdrop" data-close-delete></div>
        <div class="yt-modal__content" style="max-width: 520px;">
            <button type="button" class="yt-modal__close" data-close-delete><i class="fas fa-times"></i></button>
            <div class="yt-confirm">
                <h3>Hapus Video?</h3>
                <p class="yt-confirm__text">Anda yakin ingin menghapus <strong id="ytDeleteTitle">video ini</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                <div class="yt-actions yt-modal__actions">
                    <button type="button" class="ann-btn ann-btn--cancel" data-close-delete>Batal</button>
                    <form id="ytDeleteForm" method="POST" style="margin:0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ann-btn ann-btn--submit" style="background:#dc2626; color:#fff;">
                            <i class="fas fa-trash"></i> Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formModal = document.getElementById('ytFormModal');
            const detailModal = document.getElementById('ytDetailModal');
            const deleteModal = document.getElementById('ytDeleteModal');
            const form = document.getElementById('ytModalForm');
            const methodField = document.getElementById('ytFormMethod');
            const formTitle = document.getElementById('ytFormTitle');
            const deleteForm = document.getElementById('ytDeleteForm');
            const deleteTitle = document.getElementById('ytDeleteTitle');
            const inputs = {
                title: document.getElementById('ytTitle'),
                url: document.getElementById('ytUrl'),
                desc: document.getElementById('ytDescription'),
                thumb: document.getElementById('ytThumbnail'),
                sort: document.getElementById('ytSort'),
                published: document.getElementById('ytPublished'),
                toggle: document.getElementById('ytPublishedToggle'),
            };

            const detailElems = {
                title: document.getElementById('ytDetailTitle'),
                url: document.getElementById('ytDetailUrl'),
                desc: document.getElementById('ytDetailDesc'),
                embed: document.getElementById('ytDetailEmbed'),
                status: document.getElementById('ytDetailStatus'),
                titleText: document.getElementById('ytDetailTitleText'),
                youtubeId: document.getElementById('ytDetailYoutubeId'),
                sort: document.getElementById('ytDetailSort'),
                published: document.getElementById('ytDetailPublished'),
                created: document.getElementById('ytDetailCreated'),
                updated: document.getElementById('ytDetailUpdated'),
            };

            const openForm = (mode = 'create', payload = {}) => {
                formTitle.textContent = mode === 'edit' ? 'Perbarui Video' : 'Tambah Video';
                form.action = mode === 'edit' && payload.action ? payload.action : '{{ route('admin.youtube-videos.store') }}';
                methodField.value = mode === 'edit' ? 'PUT' : 'POST';
                inputs.title.value = payload.title || '';
                inputs.url.value = payload.url || '';
                inputs.desc.value = payload.description || '';
                inputs.thumb.value = payload.thumbnail || '';
                inputs.sort.value = payload.sort ?? 0;
                inputs.published.value = payload.published || '';
                inputs.toggle.checked = payload.status ? true : false;
                formModal.classList.add('is-visible');
            };

            const closeForm = () => formModal.classList.remove('is-visible');
            document.getElementById('openCreateModal')?.addEventListener('click', () => openForm('create'));
            formModal?.querySelectorAll('[data-close-form]').forEach((btn) => btn.addEventListener('click', closeForm));

            document.querySelectorAll('.js-edit').forEach((btn) => {
                btn.addEventListener('click', () => {
                    openForm('edit', {
                        title: btn.dataset.title,
                        url: btn.dataset.url,
                        description: btn.dataset.description,
                        thumbnail: btn.dataset.thumbnail,
                        sort: btn.dataset.sort,
                        published: btn.dataset.published,
                        status: btn.dataset.status === '1',
                        action: btn.dataset.action,
                    });
                });
            });

            const openDetail = (video) => {
                const embedUrl = video.embed_url || (video.youtube_id ? `https://www.youtube.com/embed/${video.youtube_id}` : '');
                detailElems.title.textContent = video.title || 'Detail Video';
                detailElems.titleText.textContent = video.title || '-';
                detailElems.url.textContent = video.youtube_url || '-';
                detailElems.url.href = video.youtube_url || '#';
                detailElems.url.target = video.youtube_url ? '_blank' : '_self';
                detailElems.desc.textContent = video.description || 'Tidak ada deskripsi';
                detailElems.embed.innerHTML = embedUrl
                    ? `<iframe src="${embedUrl}?controls=1&rel=0&modestbranding=1" width="100%" height="280" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`
                    : '<div class="yt-preview__empty">Preview tidak tersedia</div>';
                detailElems.youtubeId.textContent = video.youtube_id || '-';
                detailElems.sort.textContent = (video.sort_order ?? video.sort ?? '-') || '-';
                detailElems.published.textContent = video.published_at || 'Belum dijadwalkan';
                detailElems.created.textContent = video.created_at || '-';
                detailElems.updated.textContent = video.updated_at || '-';
                const statusLabel = video.is_published ? 'Tayang' : 'Draft';
                detailElems.status.textContent = statusLabel;
                detailElems.status.className = `yt-detail__status ${video.is_published ? 'is-live' : 'is-draft'}`;
                detailModal.classList.add('is-visible');
            };

            const closeDetail = () => detailModal.classList.remove('is-visible');
            detailModal?.querySelectorAll('[data-close-detail]').forEach((btn) => btn.addEventListener('click', closeDetail));
            document.querySelectorAll('.js-view').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const data = btn.dataset.video ? JSON.parse(btn.dataset.video) : {};
                    openDetail(data);
                });
            });
            const openDelete = (payload = {}) => {
                const title = payload.title || 'video ini';
                deleteTitle.textContent = title;
                deleteForm.action = payload.action || '#';
                deleteModal.classList.add('is-visible');
            };
            const closeDelete = () => deleteModal.classList.remove('is-visible');
            deleteModal?.querySelectorAll('[data-close-delete]').forEach((btn) => btn.addEventListener('click', closeDelete));
            document.querySelectorAll('.js-delete').forEach((btn) => {
                btn.addEventListener('click', () => {
                    openDelete({
                        title: btn.dataset.title,
                        action: btn.dataset.action,
                    });
                });
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeForm();
                    closeDetail();
                    closeDelete();
                }
            });

            // Dropdown Auto-close & Animation Logic
            function closeDetails(details) {
                details.classList.add('is-closing');
                setTimeout(() => {
                    details.removeAttribute('open');
                    details.classList.remove('is-closing');
                }, 260); // Matches CSS transition duration
            }

            document.addEventListener('click', (e) => {
                const isDropdown = e.target.closest('details[data-action-menu]');
                
                // Close all if clicking outside
                if (!isDropdown) {
                    document.querySelectorAll('details[data-action-menu][open]').forEach(details => {
                        closeDetails(details);
                    });
                    return;
                }

                // If clicking a summary to open/close
                const summary = e.target.closest('summary');
                if (summary) {
                    e.preventDefault();
                    const currentDetails = summary.closest('details');

                    if (currentDetails.hasAttribute('open')) {
                        closeDetails(currentDetails);
                    } else {
                        currentDetails.setAttribute('open', '');
                        document.querySelectorAll('details[data-action-menu][open]').forEach(details => {
                            if (details !== currentDetails) {
                                closeDetails(details);
                            }
                        });
                    }
                }
            });

            // Initialize custom selects
            if (typeof CustomSelect !== 'undefined') {
                CustomSelect.init();
            }
        });
    </script>
    <script src="{{ asset('assets/js/custom-select.js') }}"></script>
@endpush
