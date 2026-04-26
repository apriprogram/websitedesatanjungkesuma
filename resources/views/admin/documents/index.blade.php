@extends('admin.layouts.app')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Dokumen Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news-modal.css') }}">
    <style>
        .doc-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 220;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .doc-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .doc-modal__dialog {
            background: #fff;
            border-radius: 20px;
            padding: 1.4rem;
            width: min(720px, 94vw);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 90px rgba(15, 23, 42, 0.18);
            display: grid;
            gap: 1rem;
            transform: translateY(12px) scale(0.98);
            opacity: 0;
            transition: transform 0.28s cubic-bezier(.2, .75, .4, 1), opacity 0.2s ease;
        }

        .doc-modal.is-visible .doc-modal__dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }

        .doc-modal__dialog--wide {
            width: min(1200px, 96vw);
            max-height: 92vh;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .doc-modal__body {
            display: grid;
            gap: 0.85rem;
        }

        .doc-modal__body--scrollable {
            max-height: calc(92vh - 140px);
            overflow: auto;
        }

        .doc-modal__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }

        .doc-modal__title {
            margin: 0;
            font-size: 1.25rem;
        }

        .doc-modal__close {
            border: none;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }

        .doc-modal__body {
            display: grid;
            gap: 0.85rem;
        }

        .doc-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Toggle switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 56px;
            height: 32px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, #edeff4, #dcdfe6);
            border: 1px solid #d2d6df;
            transition: 0.3s ease;
            border-radius: 999px;
            box-shadow: inset 0 2px 4px rgba(15, 23, 42, 0.1);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.12);
            transition: 0.25s ease;
        }

        .switch input:checked+.slider {
            background: linear-gradient(180deg, #3b82f6, #2563eb);
            border-color: #2563eb;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.4);
        }

        .switch input:checked+.slider:before {
            transform: translateX(22px);
        }

        .toggle-label {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
            display: inline-block;
        }

        .detail-metadata {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.95rem;
            color: #1f2937;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.6rem 0.75rem;
        }

        .detail-metadata strong {
            font-size: 0.85rem;
            color: #0f172a;
            letter-spacing: 0.02em;
        }

        .detail-preview {
            background: #0f172a;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid #1f2937;
            min-height: 520px;
            height: clamp(680px, 75vh, 960px);
            display: flex;
            flex-direction: column;
        }

        .detail-preview__frame {
            flex: 1 1 auto;
            border: none;
            width: 100%;
            height: 100%;
            background: #0b1224;
        }

        .detail-preview__hint {
            margin: 0;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            color: #e2e8f0;
            background: rgba(15, 23, 42, 0.75);
            border-top: 1px solid rgba(148, 163, 184, 0.25);
        }

        .detail-layout {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        .detail-preview {
            flex: 0 0 67%;
            max-width: 72vw;
            min-width: 360px;
        }

        .detail-meta-panel {
            flex: 1 1 30%;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            min-width: 300px;
        }

        .detail-metadata {
            width: 100%;
        }

        .detail-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .doc-index-cell {
            text-align: center;
            font-weight: 700;
            color: #0f172a;
        }

        body.dark-mode .doc-index-cell {
            color: #ffffff;
        }

        .doc-action-cell {
            text-align: right !important;
        }

        .doc-action-cell .news-table__action-dropdown {
            display: inline-flex;
            margin-left: auto;
        }

        /* Dark mode adjustments */
        body.dark-mode .news-page {
            color: #e2e8f0;
        }

        body.dark-mode .summary-grid .stat-card {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.92), rgba(8, 15, 30, 0.9));
            border: 1px solid rgba(99, 102, 241, 0.25);
            box-shadow: none;
        }

        body.dark-mode .stat-card__value {
            color: #f8fafc;
        }

        body.dark-mode .stat-card__label {
            color: #cbd5e1;
        }

        body.dark-mode .stat-card__pill {
            background: rgba(148, 163, 184, 0.15);
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.25);
        }

        body.dark-mode .filter-card,
        body.dark-mode .table-toolbar,
        body.dark-mode .news-panel {
            background: rgba(12, 18, 34, 0.65);
            border-color: rgba(148, 163, 184, 0.2);
        }

        body.dark-mode .table-toolbar input,
        body.dark-mode .table-toolbar select {
            background: rgba(15, 23, 42, 0.9);
            color: #e5e7eb;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        body.dark-mode .table-toolbar input::placeholder {
            color: #94a3b8;
        }

        body.dark-mode .news-table table {
            background: transparent;
        }

        body.dark-mode .news-table thead th {
            background: rgba(15, 23, 42, 0.85);
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.25);
        }

        body.dark-mode .news-table tbody tr {
            background: rgba(15, 23, 42, 0.78);
            border-color: rgba(148, 163, 184, 0.15);
        }

        body.dark-mode .news-table tbody tr:hover {
            background: rgba(30, 41, 59, 0.85);
        }

        body.dark-mode .news-table td {
            color: #e2e8f0;
            border-color: rgba(148, 163, 184, 0.12);
        }

        body.dark-mode .news-table tbody td:first-child {
            color: #ffffff;
            font-weight: 700;
        }

        body.dark-mode .table-summary {
            color: #cbd5e1;
        }

        body.dark-mode .doc-modal__dialog {
            background: rgba(12, 18, 34, 0.96);
            color: #e2e8f0;
        }

        body.dark-mode .detail-metadata {
            background: rgba(30, 41, 59, 0.85);
            border-color: rgba(148, 163, 184, 0.25);
            color: #e5e7eb;
        }

        body.dark-mode .detail-metadata strong {
            color: #f8fafc;
        }
    </style>
@endpush

@section('content')
    <div class="news-page">
        @include('admin.partials.alerts')

        <header class="main-header">
            <div class="header-controls">
                <div class="header-cluster header-cluster-left">
                    <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn"
                        aria-label="Sembunyikan sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="header-icon sidebar-trigger" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <span class="header-title-text">Dokumen Desa</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <i class="fas fa-chevron-right"></i>
                <span>Dokumen Desa</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <p class="page-eyebrow">Dokumen & Arsip</p>
                <h1>Manajemen Dokumen Desa</h1>
                <p>Unggah, kategorikan, dan atur akses dokumen desa.</p>
            </div>
            <div class="title-actions">
                <a class="soft-action-btn soft-action-btn--outline" href="{{ route('admin.document-categories.index') }}">
                    <i class="fas fa-tags"></i>
                    <span>Kelola Kategori</span>
                </a>
                <button class="soft-action-btn soft-action-btn--violet" type="button" id="openCreateDocument">
                    <i class="fas fa-upload"></i>
                    <span>Upload Dokumen</span>
                </button>
            </div>
        </section>

        <section class="summary-grid summary-grid--wide">
            <article class="stat-card" data-card="total">
                <div class="stat-card__header">
                    <div class="stat-card__pill">
                        <i class="fas fa-folder-open"></i>
                        <span>Total</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['total'] ?? 0) }}</p>
                    <p class="stat-card__label">Dokumen terunggah.</p>
                </div>
            </article>
            <article class="stat-card" data-card="public">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--success">
                        <i class="fas fa-globe"></i>
                        <span>Publik</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['public'] ?? 0) }}</p>
                    <p class="stat-card__label">Dapat diakses umum.</p>
                </div>
            </article>
            <article class="stat-card" data-card="private">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--warning">
                        <i class="fas fa-lock"></i>
                        <span>Privat</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['private'] ?? 0) }}</p>
                    <p class="stat-card__label">Tidak ditampilkan publik.</p>
                </div>
            </article>
            <article class="stat-card" data-card="categories">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--info">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['categories'] ?? 0) }}</p>
                    <p class="stat-card__label">Kategori tersedia.</p>
                </div>
            </article>
        </section>

        <!-- Upload/Edit handled via modal -->

        <section class="news-panel">
            <div class="table-toolbar">
                <div class="table-info">
                    Filter dokumen
                </div>
                <form method="get" class="table-actions">
                    <div class="filter-field">
                        <span>Pencarian</span>
                        <input type="search" name="search" value="{{ request('search') }}"
                            placeholder="Cari judul/deskripsi...">
                    </div>
                    <div class="filter-field">
                        <span>Kategori</span>
                        <select name="category" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <span>Publik</span>
                        <select name="public" onchange="this.form.submit()">
                            <option value="">Semua</option>
                            <option value="1" @selected(request('public') === '1')>Ya</option>
                            <option value="0" @selected(request('public') === '0')>Tidak</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <span>Per halaman</span>
                        <select name="per_page" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="news-table">
                <table>
                    <thead>
                        <tr>
                            <th style="width:70px;">No</th>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Jenis</th>
                            <th>Publik</th>
                            <th>Upload</th>
                            <th class="doc-action-cell">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $categoryPalette = [
                                ['bg' => '#e0f2fe', 'border' => '#38bdf8', 'text' => '#075985'], // blue
                                ['bg' => '#dcfce7', 'border' => '#22c55e', 'text' => '#166534'], // green
                                ['bg' => '#fff7ed', 'border' => '#f59e0b', 'text' => '#b45309'], // amber
                                ['bg' => '#fee2e2', 'border' => '#ef4444', 'text' => '#991b1b'], // red
                                ['bg' => '#f5f3ff', 'border' => '#a855f7', 'text' => '#7e22ce'], // purple
                                ['bg' => '#e0e7ff', 'border' => '#6366f1', 'text' => '#312e81'], // indigo
                                ['bg' => '#ecfeff', 'border' => '#22d3ee', 'text' => '#0ea5e9'], // cyan
                                ['bg' => '#fdf2f8', 'border' => '#f472b6', 'text' => '#9d174d'], // pink
                                ['bg' => '#fefce8', 'border' => '#facc15', 'text' => '#92400e'], // yellow
                                ['bg' => '#f1f5f9', 'border' => '#94a3b8', 'text' => '#0f172a'], // slate
                                ['bg' => '#eef2ff', 'border' => '#4f46e5', 'text' => '#1e1b4b'], // deep indigo
                                ['bg' => '#e0f7f4', 'border' => '#14b8a6', 'text' => '#115e59'], // teal
                            ];
                            $categoryColorMap = [];
                        @endphp
                        @forelse($documents as $doc)
                            <tr>
                                <td class="doc-index-cell">
                                    {{ ($documents->firstItem() ?? 0) + $loop->index }}
                                </td>
                                <td>
                                    <strong>{{ $doc->title }}</strong>
                                    <span class="row-summary">{{ Str::limit($doc->description, 60) }}</span>
                                </td>
                                @php
                                    $catName = $doc->category->name ?? '-';
                                    $catKey = $doc->document_category_id ?? $catName;
                                    if (!isset($categoryColorMap[$catKey])) {
                                        $paletteIndex = count($categoryColorMap) % count($categoryPalette);
                                        $categoryColorMap[$catKey] = $categoryPalette[$paletteIndex];
                                    }
                                    $color = $categoryColorMap[$catKey];
                                @endphp
                                <td>
                                    <span class="pill-badge"
                                        style="background: {{ $color['bg'] }}; border: 2px solid {{ $color['border'] }}; color: {{ $color['text'] }};">
                                        {{ $catName }}
                                    </span>
                                </td>
                                <td>{{ $doc->year ?? '-' }}</td>
                                @php
                                    $ext = Str::lower($doc->file_type ?? '');
                                    $typeMeta = [
                                        'pdf' => ['icon' => 'fa-file-pdf', 'class' => 'badge--pdf'],
                                        'doc' => ['icon' => 'fa-file-word', 'class' => 'badge--word'],
                                        'docx' => ['icon' => 'fa-file-word', 'class' => 'badge--word'],
                                        'xls' => ['icon' => 'fa-file-excel', 'class' => 'badge--excel'],
                                        'xlsx' => ['icon' => 'fa-file-excel', 'class' => 'badge--excel'],
                                        'ppt' => ['icon' => 'fa-file-powerpoint', 'class' => 'badge--ppt'],
                                        'pptx' => ['icon' => 'fa-file-powerpoint', 'class' => 'badge--ppt'],
                                        'zip' => ['icon' => 'fa-file-zipper', 'class' => 'badge--archive'],
                                        'rar' => ['icon' => 'fa-file-zipper', 'class' => 'badge--archive'],
                                        'jpg' => ['icon' => 'fa-file-image', 'class' => 'badge--image'],
                                        'jpeg' => ['icon' => 'fa-file-image', 'class' => 'badge--image'],
                                        'png' => ['icon' => 'fa-file-image', 'class' => 'badge--image'],
                                    ];
                                    $meta = $typeMeta[$ext] ?? ['icon' => 'fa-file-lines', 'class' => 'badge--other'];
                                    $typeLabel = strtoupper($doc->file_type ?: 'LAINNYA');
                                    $docEditPayload = json_encode([
                                        'id' => $doc->id,
                                        'title' => $doc->title,
                                        'description' => $doc->description,
                                        'year' => $doc->year,
                                        'category_id' => $doc->document_category_id,
                                        'is_public' => $doc->is_public,
                                        'update_url' => route('admin.documents.update', $doc),
                                    ]);
                                    $docDeletePayload = json_encode([
                                        'title' => $doc->title,
                                        'delete_url' => route('admin.documents.destroy', $doc),
                                    ]);
                                    $docDetailPayload = json_encode([
                                        'title' => $doc->title,
                                        'description' => $doc->description,
                                        'category' => $catName,
                                        'year' => $doc->year,
                                        'type' => strtoupper($doc->file_type ?: 'LAINNYA'),
                                        'size' => $doc->file_size ? number_format($doc->file_size) . ' KB' : 'Tidak diketahui',
                                        'public' => $doc->is_public ? 'Ya' : 'Tidak',
                                        'uploaded' => optional($doc->uploaded_at)->translatedFormat('d M Y') ?? '-',
                                        'url' => Storage::url($doc->file_path),
                                    ]);
                                @endphp
                                <td>
                                    <span class="badge {{ $meta['class'] }}">
                                        <i class="fas {{ $meta['icon'] }}" aria-hidden="true"></i>
                                        {{ $typeLabel }}
                                    </span>
                                </td>
                                <td>
                                    @if($doc->is_public)
                                        <span class="status-pill status-pill--published">Ya</span>
                                    @else
                                        <span class="status-pill status-pill--draft status-pill--danger">Tidak</span>
                                    @endif
                                </td>
                                <td>{{ optional($doc->uploaded_at)->format('d M Y') }}</td>
                                <td class="doc-action-cell">
                                    <details class="news-table__action-dropdown" data-action-menu>
                                        <summary class="action-button action-button--dots" aria-haspopup="menu"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </summary>
                                        <div class="news-table__action-options" role="menu">
                                            <a class="news-table__action-item" href="{{ Storage::url($doc->file_path) }}"
                                                target="_blank" rel="noopener">
                                                <i class="fas fa-eye"></i><span>Lihat File</span>
                                            </a>
                                            <button class="news-table__action-item" type="button"
                                                data-doc-detail='{{ $docDetailPayload }}'>
                                                <i class="fas fa-circle-info"></i><span>Lihat Detail</span>
                                            </button>
                                            <a class="news-table__action-item"
                                                href="{{ route('admin.documents.download', $doc) }}">
                                                <i class="fas fa-download"></i><span>Unduh</span>
                                            </a>
                                            <button class="news-table__action-item" type="button"
                                                data-doc-edit='{{ $docEditPayload }}'>
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            </button>
                                            <button class="news-table__action-item news-table__action-item--danger"
                                                type="button" data-doc-delete='{{ $docDeletePayload }}'>
                                                <i class="fas fa-trash"></i><span>Hapus</span>
                                            </button>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="banner-panel-empty">Belum ada dokumen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-summary" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
                <div>Menampilkan {{ $documents->firstItem() ?? 0 }} - {{ $documents->lastItem() ?? 0 }} dari
                    {{ $documents->total() }} dokumen
                </div>
                <div class="table-pagination news-pagination">{{ $documents->onEachSide(1)->links('admin.partials.pagination') }}</div>
            </div>
        </section>

        {{-- Modal: Create/Edit Document --}}
        <div class="doc-modal" id="documentModal" aria-hidden="true">
            <div class="doc-modal__dialog" role="dialog" aria-modal="true">
                <div class="doc-modal__header">
                    <div>
                        <p class="page-eyebrow" style="margin:0;">Dokumen</p>
                        <h3 class="doc-modal__title" id="documentModalTitle">Upload Dokumen</h3>
                    </div>
                    <button class="doc-modal__close" type="button" data-doc-modal-close><i
                            class="fas fa-times"></i></button>
                </div>
                <form id="documentForm" class="doc-modal__body" method="post" enctype="multipart/form-data"
                    action="{{ route('admin.documents.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" id="documentFormMethod">
                    <input type="hidden" name="is_public" id="docPublicHidden" value="1">
                    <div class="news-form-grid">
                        <div class="news-input-control">
                            <label>Judul Dokumen *</label>
                            <input type="text" name="title" id="docTitle" required>
                        </div>
                        <div class="news-input-control">
                            <label>Kategori *</label>
                            <select name="document_category_id" id="docCategory" required>
                                <option value="">-- Pilih kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="news-form-grid">
                        <div class="news-input-control news-input-control--full">
                            <label>Deskripsi (opsional)</label>
                            <textarea name="description" id="docDescription" rows="3"
                                placeholder="Ringkasan dokumen"></textarea>
                        </div>
                    </div>
                    <div class="news-form-grid">
                        <div class="news-input-control">
                            <label>Tahun (opsional)</label>
                            <input type="number" name="year" id="docYear" min="1900" max="{{ now()->year + 1 }}">
                        </div>
                        <div class="news-input-control">
                            <label class="toggle-label">Tampilkan ke publik</label>
                            <label class="switch">
                                <input type="checkbox" value="1" id="docPublicToggle">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                    <div class="news-form-grid">
                        <div class="news-input-control news-input-control--full">
                            <label>Upload File <span id="docFileLabel">*</span></label>
                            <input type="file" name="file" id="docFileInput"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip">
                            <small class="news-input-control__hint">Maks 20MB. pdf/doc/xls/ppt/jpg/png/zip.</small>
                        </div>
                    </div>
                    <div class="doc-modal__actions">
                        <button type="button" class="ghost-btn ghost-btn--subtle" data-doc-modal-close><i
                                class="fas fa-arrow-left"></i> Batal</button>
                        <button type="submit" class="soft-action-btn soft-action-btn--success">
                            <i class="fas fa-check"></i>
                            <span id="docSubmitLabel">Upload</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Delete confirm --}}
        <div class="doc-modal" id="deleteDocModal" aria-hidden="true">
            <div class="doc-modal__dialog" role="dialog" aria-modal="true">
                <div class="doc-modal__header">
                    <div>
                        <p class="page-eyebrow" style="margin:0;">Konfirmasi</p>
                        <h3 class="doc-modal__title">Hapus Dokumen?</h3>
                    </div>
                    <button class="doc-modal__close" type="button" data-doc-modal-close><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="doc-modal__body">
                    <p id="deleteDocText">Anda yakin ingin menghapus dokumen ini?</p>
                    <form id="deleteDocForm" method="post" action="#">
                        @csrf @method('DELETE')
                        <div class="doc-modal__actions">
                            <button type="button" class="ghost-btn ghost-btn--subtle" data-doc-modal-close><i
                                    class="fas fa-arrow-left"></i> Batal</button>
                            <button type="submit" class="soft-action-btn soft-action-btn--danger"><i
                                    class="fas fa-trash"></i>
                                Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Detail dokumen --}}
    <div class="doc-modal" id="detailDocModal" aria-hidden="true">
        <div class="doc-modal__dialog doc-modal__dialog--wide" role="dialog" aria-modal="true">
            <div class="doc-modal__header">
                <div>
                    <p class="page-eyebrow" style="margin:0;">Detail</p>
                    <h3 class="doc-modal__title" id="detailDocTitle">Detail Dokumen</h3>
                </div>
                <button class="doc-modal__close" type="button" data-doc-modal-close><i class="fas fa-times"></i></button>
            </div>
            <div class="doc-modal__body doc-modal__body--scrollable" style="gap:0.8rem;">
                <div class="detail-layout">
                    <div class="detail-preview" id="detailDocPreviewWrapper">
                        <iframe id="detailDocPreview" class="detail-preview__frame" title="Pratinjau dokumen"></iframe>
                        <p class="detail-preview__hint" id="detailDocPreviewHint">Gunakan tombol "Buka File" jika pratinjau
                            tidak muncul.</p>
                    </div>
                    <div class="detail-meta-panel">
                        <div class="detail-metadata"><strong>Judul</strong><span id="detailDocName">-</span></div>
                        <div class="detail-metadata"><strong>Kategori</strong><span id="detailDocCategory">-</span></div>
                        <div class="detail-metadata"><strong>Tahun</strong><span id="detailDocYear">-</span></div>
                        <div class="detail-metadata"><strong>Publik</strong><span id="detailDocPublic">-</span></div>
                        <div class="detail-metadata"><strong>Jenis</strong><span id="detailDocType">-</span></div>
                        <div class="detail-metadata"><strong>Ukuran</strong><span id="detailDocSize">-</span></div>
                        <div class="detail-metadata"><strong>Upload</strong><span id="detailDocUploaded">-</span></div>
                        <div class="detail-metadata">
                            <strong>Deskripsi</strong>
                            <p id="detailDocDescription" style="margin:4px 0 0; color:#4b5563;">-</p>
                        </div>
                        <div class="detail-actions">
                            <a id="detailDocLink" class="soft-action-btn soft-action-btn--violet" href="#" target="_blank"
                                rel="noopener">
                                <i class="fas fa-eye"></i> Buka File
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const docModal = document.getElementById('documentModal');
            const deleteModal = document.getElementById('deleteDocModal');
            const docForm = document.getElementById('documentForm');
            const docMethod = document.getElementById('documentFormMethod');
            const docPublicToggle = document.getElementById('docPublicToggle');
            const docPublicHidden = document.getElementById('docPublicHidden');
            const docTitle = document.getElementById('docTitle');
            const docCategory = document.getElementById('docCategory');
            const docYear = document.getElementById('docYear');
            const docDescription = document.getElementById('docDescription');
            const docFileInput = document.getElementById('docFileInput');
            const docFileLabel = document.getElementById('docFileLabel');
            const docModalTitle = document.getElementById('documentModalTitle');
            const docSubmitLabel = document.getElementById('docSubmitLabel');
            const deleteDocText = document.getElementById('deleteDocText');
            const deleteDocForm = document.getElementById('deleteDocForm');
            const detailDocModal = document.getElementById('detailDocModal');
            const detailDocTitle = document.getElementById('detailDocTitle');
            const detailDocName = document.getElementById('detailDocName');
            const detailDocCategory = document.getElementById('detailDocCategory');
            const detailDocYear = document.getElementById('detailDocYear');
            const detailDocPublic = document.getElementById('detailDocPublic');
            const detailDocType = document.getElementById('detailDocType');
            const detailDocSize = document.getElementById('detailDocSize');
            const detailDocUploaded = document.getElementById('detailDocUploaded');
            const detailDocDescription = document.getElementById('detailDocDescription');
            const detailDocLink = document.getElementById('detailDocLink');
            const detailDocPreview = document.getElementById('detailDocPreview');
            const detailDocPreviewHint = document.getElementById('detailDocPreviewHint');
            const detailDocPreviewWrapper = document.getElementById('detailDocPreviewWrapper');

            const openModal = (el) => { el?.classList.add('is-visible'); };
            const closeModal = (el) => { el?.classList.remove('is-visible'); };
            document.querySelectorAll('[data-doc-modal-close]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(btn.closest('.doc-modal')));
            });
            [docModal, deleteModal, detailDocModal].forEach(modal => {
                modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
            });

            const resetForm = () => {
                docForm.action = "{{ route('admin.documents.store') }}";
                docMethod.value = 'POST';
                docTitle.value = '';
                docCategory.value = '';
                docYear.value = '';
                docDescription.value = '';
                if (docPublicToggle) docPublicToggle.checked = true;
                if (docPublicHidden) {
                    docPublicHidden.name = 'is_public';
                    docPublicHidden.value = 1;
                }
                docFileInput.required = true;
                docFileInput.value = '';
                docFileLabel.textContent = '*';
                docModalTitle.textContent = 'Upload Dokumen';
                docSubmitLabel.textContent = 'Upload';
            };

            document.getElementById('openCreateDocument')?.addEventListener('click', () => {
                resetForm();
                openModal(docModal);
            });

            document.querySelectorAll('[data-doc-edit]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const payload = JSON.parse(btn.dataset.docEdit || '{}');
                    resetForm();
                    docModalTitle.textContent = 'Edit Dokumen';
                    docSubmitLabel.textContent = 'Simpan Perubahan';
                    docForm.action = payload.update_url || docForm.action;
                    docMethod.value = 'PUT';
                    docTitle.value = payload.title || '';
                    docCategory.value = payload.category_id || '';
                    docYear.value = payload.year || '';
                    docDescription.value = payload.description || '';
                    if (docPublicToggle) docPublicToggle.checked = !!payload.is_public;
                    if (docPublicHidden) {
                        docPublicHidden.name = 'is_public';
                        docPublicHidden.value = payload.is_public ? 1 : 0;
                    }
                    docFileInput.required = false;
                    docFileLabel.textContent = '(opsional)';
                    openModal(docModal);
                });
            });

            const syncPublicField = () => {
                if (!docPublicHidden) return;
                const checked = docPublicToggle ? docPublicToggle.checked : false;
                docPublicHidden.name = 'is_public';
                docPublicHidden.value = checked ? 1 : 0;
            };

            docPublicToggle?.addEventListener('change', syncPublicField);
            docForm?.addEventListener('submit', syncPublicField);

            document.querySelectorAll('[data-doc-delete]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const payload = JSON.parse(btn.dataset.docDelete || '{}');
                    deleteDocText.innerHTML = payload.title
                        ? `Anda yakin ingin menghapus dokumen <strong>${payload.title}</strong>?`
                        : 'Anda yakin ingin menghapus dokumen ini?';
                    deleteDocForm.action = payload.delete_url || '#';
                    openModal(deleteModal);
                });
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeModal(docModal);
                    closeModal(deleteModal);
                    closeModal(detailDocModal);
                }
            });

            // Tutup dropdown aksi lain saat satu menu dibuka
            const actionMenus = Array.from(document.querySelectorAll('[data-action-menu]'));
            actionMenus.forEach(menu => {
                menu.addEventListener('toggle', () => {
                    if (menu.open) {
                        actionMenus.forEach(other => {
                            if (other !== menu) other.removeAttribute('open');
                        });
                    }
                });
            });
            document.addEventListener('click', (e) => {
                if (!e.target.closest('[data-action-menu]')) {
                    actionMenus.forEach(menu => menu.removeAttribute('open'));
                }
            });

            document.querySelectorAll('[data-doc-detail]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const payload = JSON.parse(btn.dataset.docDetail || '{}');
                    detailDocTitle.textContent = payload.title || 'Detail Dokumen';
                    detailDocName.textContent = payload.title || '-';
                    detailDocCategory.textContent = payload.category || '-';
                    detailDocYear.textContent = payload.year || '-';
                    detailDocPublic.textContent = payload.public || '-';
                    detailDocType.textContent = payload.type || '-';
                    detailDocSize.textContent = payload.size || '-';
                    detailDocUploaded.textContent = payload.uploaded || '-';
                    detailDocDescription.textContent = payload.description || '-';
                    if (detailDocLink) {
                        detailDocLink.href = payload.url || '#';
                        detailDocLink.style.pointerEvents = payload.url ? 'auto' : 'none';
                        detailDocLink.style.opacity = payload.url ? '1' : '0.6';
                    }
                    if (detailDocPreview && detailDocPreviewWrapper && detailDocPreviewHint) {
                        if (payload.url) {
                            detailDocPreview.src = payload.url;
                            detailDocPreviewWrapper.style.display = '';
                            detailDocPreviewHint.textContent = 'Gunakan tombol "Buka File" jika pratinjau tidak muncul.';
                        } else {
                            detailDocPreview.src = '';
                            detailDocPreviewWrapper.style.display = 'none';
                            detailDocPreviewHint.textContent = 'Pratinjau tidak tersedia.';
                        }
                    }
                    openModal(detailDocModal);
                });
            });
        });
    </script>
@endpush