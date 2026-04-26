@extends('admin.layouts.app')

@section('title', 'Transparansi Anggaran Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news-modal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-budget.css') }}">
    <style>
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

        body.dark-mode .icon-picker__selected-label {
            color: #e2e8f0;
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
                <span class="header-title-text">Transparansi Anggaran Desa</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <i class="fas fa-chevron-right"></i>
                <span>Transparansi Anggaran</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <p class="page-eyebrow">Anggaran & Realisasi</p>
                <h1>Transparansi Anggaran</h1>
                <p>Kelola data anggaran dan realisasi untuk ditampilkan ke warga.</p>
            </div>
            <div class="title-actions">
                <div class="export-dropdown">
                    <button type="button" class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                        id="exportDropdownToggleBudget" aria-haspopup="true" aria-expanded="false"
                        aria-controls="exportDropdownMenuBudget">
                        <i class="fas fa-file-export" aria-hidden="true"></i>
                        <span>Ekspor</span>
                        <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="export-dropdown__menu" id="exportDropdownMenuBudget" role="menu" hidden>
                        <div class="export-dropdown__header">Pilih Ekspor</div>
                        <div class="export-dropdown__grid">
                            <a href="{{ route('admin.budget-items.export.excel', request()->query()) }}"
                                class="export-dropdown__item" role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download Excel</strong>
                                    <small>Unduh format spreadsheet</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.budget-items.export.pdf', request()->query()) }}"
                                class="export-dropdown__item" role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                    <i class="fas fa-file-pdf"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download PDF</strong>
                                    <small>Siap cetak (F4)</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.budget-items.export.word', request()->query()) }}"
                                class="export-dropdown__item" role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--blue" aria-hidden="true">
                                    <i class="fas fa-file-word"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download Word</strong>
                                    <small>Format dokumen rapi</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <button class="soft-action-btn soft-action-btn--violet" id="openCreateModal">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Data</span>
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
                    <p class="stat-card__label">Item anggaran.</p>
                </div>
            </article>
            <article class="stat-card" data-card="published">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--success">
                        <i class="fas fa-bullhorn"></i>
                        <span>Published</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['published'] ?? 0) }}</p>
                    <p class="stat-card__label">Tampil di publik.</p>
                </div>
            </article>
            <article class="stat-card" data-card="anggaran">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--info">
                        <i class="fas fa-coins"></i>
                        <span>Anggaran</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">Rp {{ number_format($stats['anggaran'] ?? 0, 0, ',', '.') }}</p>
                    <p class="stat-card__label">Total anggaran (filter).</p>
                    <div class="stat-progress">
                        <div class="progress progress--wide">
                            <div class="progress-bar" style="width: {{ min(100, $stats['progress_percent'] ?? 0) }}%"></div>
                        </div>
                        <small class="progress-label">{{ $stats['progress_percent'] ?? 0 }}% terserap</small>
                    </div>
                </div>
            </article>
            <article class="stat-card" data-card="realisasi">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--brand">
                        <i class="fas fa-chart-line"></i>
                        <span>Realisasi</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">Rp {{ number_format($stats['realisasi'] ?? 0, 0, ',', '.') }}</p>
                    <p class="stat-card__label">Total realisasi (filter).</p>
                    <div class="stat-progress">
                        <div class="progress progress--wide">
                            <div class="progress-bar" style="width: {{ min(100, $stats['progress_percent'] ?? 0) }}%"></div>
                        </div>
                        <small class="progress-label">{{ $stats['progress_percent'] ?? 0 }}% realisasi</small>
                    </div>
                </div>
            </article>
        </section>

        <section class="news-panel">
            <div class="filter-row">
                <div class="filter-card stretch">
                    <div class="table-info">Opsi Pencarian</div>
                    <form method="GET" class="table-actions table-actions--filters">
                        <label class="filter-field">
                            <span>Tahun</span>
                            <select name="year" onchange="this.form.submit()">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @selected((int) $year === (int) $selectedYear)>{{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Kategori</span>
                            <select name="category" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" @selected($selectedCategory === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field">
                            <span>Per halaman</span>
                            <select name="per_page" onchange="this.form.submit()">
                                @foreach([10, 20, 50, 100, 200] as $opt)
                                    <option value="{{ $opt }}" {{ ($perPage ?? 20) == $opt ? 'selected' : '' }}>{{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="filter-field filter-field--wide">
                            <span>Cari</span>
                            <input type="search" name="search" placeholder="Subkategori/Deskripsi"
                                value="{{ $search ?? '' }}">
                        </label>
                        <div class="filter-actions filter-actions--inline">
                            <a href="{{ route('admin.budget-items.index') }}" class="ghost-btn ghost-btn--back no-underline"
                                style="height: 42px;"><i class="fas fa-undo"></i> Reset</a>
                        </div>
                    </form>
                </div>
                @if(count($years))
                    <div class="filter-card filter-card--compact">
                        <div class="table-info">Tahun Aktif</div>
                        <form method="POST" action="{{ route('admin.budget-items.activate-year') }}">
                            @csrf
                            <div class="filter-field-group">
                                <label class="filter-field">
                                    <span>Pilih Tahun</span>
                                    <select name="year">
                                        @foreach($years as $year)
                                            <option value="{{ $year }}" @selected((int) $year === (int) ($activeYear ?? null))>
                                                {{ $year }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <div class="filter-actions--inline">
                                    <button type="submit" class="soft-action-btn soft-action-btn--success"
                                        style="height: 42px;">
                                        <i class="fas fa-star"></i> Simpan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </section>

        <section class="news-panel">
            <div class="news-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subkategori</th>
                            <th>Kategori</th>
                            <th>Tahun</th>
                            <th>Anggaran</th>
                            <th>Realisasi</th>
                            <th>Progress</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $items->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $item->subcategory }}</strong>
                                    @if($item->description)
                                        <span class="row-summary">{{ $item->description }}</span>
                                    @endif
                                </td>
                                <td><span
                                        class="badge badge--muted">{{ $categories[$item->category] ?? $item->category }}</span>
                                </td>
                                <td>{{ $item->year }}</td>
                                <td>{{ 'Rp ' . number_format($item->anggaran, 0, ',', '.') }}</td>
                                <td>{{ 'Rp ' . number_format($item->realisasi, 0, ',', '.') }}</td>
                                <td>
                                    <div class="progress" role="presentation">
                                        <div class="progress-bar" style="width: {{ min(100, $item->progress_percent) }}%"></div>
                                    </div>
                                    <small class="progress-label">{{ $item->progress_percent }}%</small>
                                </td>
                                <td>{{ $item->order_no }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.budget-items.toggle', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="badge badge--toggle {{ $item->is_published ? 'is-active' : '' }}">
                                            {{ $item->is_published ? 'Published' : 'Draft' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <details class="news-table__action-dropdown" data-action-menu>
                                        <summary class="action-button action-button--dots" aria-haspopup="menu"
                                            aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </summary>
                                        <div class="news-table__action-options" role="menu">
                                            <button type="button" class="news-table__action-item js-view"
                                                data-subcategory="{{ $item->subcategory }}"
                                                data-description="{{ $item->description }}"
                                                data-category="{{ $categories[$item->category] ?? $item->category }}"
                                                data-year="{{ $item->year }}"
                                                data-anggaran="{{ number_format($item->anggaran, 0, ',', '.') }}"
                                                data-realisasi="{{ number_format($item->realisasi, 0, ',', '.') }}"
                                                data-progress="{{ $item->progress_percent }}"
                                                data-order="{{ $item->order_no }}">
                                                <i class="fas fa-eye"></i><span>Lihat</span>
                                            </button>
                                            <button type="button" class="news-table__action-item js-edit"
                                                data-id="{{ $item->id }}" data-year="{{ $item->year }}"
                                                data-category="{{ $item->category }}"
                                                data-subcategory="{{ $item->subcategory }}"
                                                data-description="{{ $item->description }}"
                                                data-anggaran="{{ $item->anggaran }}" data-realisasi="{{ $item->realisasi }}"
                                                data-order="{{ $item->order_no }}" data-icon="{{ $item->icon }}"
                                                data-published="{{ $item->is_published ? '1' : '0' }}"
                                                data-update-url="{{ route('admin.budget-items.update', $item) }}">
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            </button>
                                            <form method="POST" action="{{ route('admin.budget-items.destroy', $item) }}"
                                                class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="news-table__action-item news-table__action-item--danger js-delete-trigger">
                                                    <i class="fas fa-trash"></i><span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="banner-panel-empty">Belum ada data anggaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-summary" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
                @php
                    $from = $items->firstItem() ?? 0;
                    $to = $items->lastItem() ?? 0;
                    $total = $items->total() ?? 0;
                @endphp
                <div>Menampilkan {{ $from }} - {{ $to }} dari {{ $total }} data</div>
                <div class="table-pagination news-pagination">
                    {{ $items->withQueryString()->onEachSide(1)->links('admin.partials.pagination') }}
                </div>
            </div>
        </section>
    </div>

    {{-- Modal Tambah/Edit --}}
    <div class="modal" id="budgetModal" aria-hidden="true">
        <div class="modal-overlay" id="closeModal"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Data</h3>
                <button type="button" class="modal-close" id="modalClose"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="budgetForm" action="{{ route('admin.budget-items.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <div class="modal-body">
                    <div class="form-grid">
                        <label>
                            Tahun
                            <input type="number" id="fieldYear" name="year" min="2000" max="2100"
                                value="{{ $selectedYear }}">
                        </label>
                        <label>
                            Kategori
                            <select id="fieldCategory" name="category">
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subkategori
                            <input type="text" id="fieldSubcategory" name="subcategory" required>
                        </label>
                        <div class="inline-duo">
                            <label>
                                Urutan
                                <input type="number" id="fieldOrder" name="order_no" min="0" value="0" placeholder="Urutan">
                            </label>
                            <label>
                                Ikon (opsional)
                                <div class="icon-picker" id="iconPicker">
                                    <input type="hidden" name="icon" id="fieldIcon" value="">
                                    <button type="button" class="icon-picker__control" id="iconPickerToggle">
                                        <div class="icon-picker__preview is-empty" id="iconPickerPreview">
                                            <i class="fas fa-circle-question"></i>
                                        </div>
                                        <div class="icon-picker__selected">
                                            <span class="icon-picker__selected-label" id="iconPickerSelectedLabel">Pilih
                                                icon</span>
                                            <span class="icon-picker__selected-hint">Klik untuk memilih icon</span>
                                        </div>
                                        <div class="icon-picker__caret" aria-hidden="true"><i
                                                class="fas fa-chevron-down"></i></div>
                                    </button>
                                    <div class="icon-picker__dropdown" id="iconPickerDropdown">
                                        <div class="icon-picker__search">
                                            <i class="fas fa-search" aria-hidden="true"></i>
                                            <input type="search" id="iconPickerSearch" placeholder="Cari Icon">
                                        </div>
                                        <div class="icon-picker__grid" id="iconPickerGrid" role="listbox"
                                            aria-label="Daftar icon"></div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <label class="full">
                            Deskripsi
                            <textarea id="fieldDescription" name="description" rows="3"></textarea>
                        </label>
                        <div class="inline-duo">
                            <label>
                                Anggaran
                                <input type="text" id="fieldAnggaran" name="anggaran" inputmode="numeric" required
                                    placeholder="Anggaran">
                            </label>
                            <label>
                                Realisasi
                                <input type="text" id="fieldRealisasi" name="realisasi" inputmode="numeric" required
                                    placeholder="Realisasi">
                            </label>
                        </div>
                        <label class="checkbox toggle">
                            <input type="checkbox" id="fieldPublished" name="is_published" value="1" checked
                                data-on="Tampilkan di situs" data-off="Tidak tampil">
                            <span class="toggle-track"><span class="toggle-thumb"></span></span>
                            <span class="toggle-text" id="fieldPublishedLabel">Tampilkan di situs</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--ghost pill" id="cancelModal">Batal</button>
                    <button type="submit" class="btn btn--primary pill">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal konfirmasi hapus --}}
    <div class="modal" id="confirmModal" aria-hidden="true">
        <div class="modal-overlay" id="confirmOverlay"></div>
        <div class="modal-content modal-content--small">
            <div class="modal-header">
                <h3>Hapus Data</h3>
                <button type="button" class="modal-close" id="confirmClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p>Anda yakin ingin menghapus data ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost pill" id="confirmCancel">Batal</button>
                <button type="button" class="btn btn--danger pill" id="confirmSubmit">Hapus</button>
            </div>
        </div>
    </div>

    {{-- Modal detail --}}
    <div class="modal" id="detailModal" aria-hidden="true">
        <div class="modal-overlay" id="detailOverlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Detail Anggaran</h3>
                <button type="button" class="modal-close" id="detailClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item"><span class="label">Subkategori</span><span class="value"
                            id="dSubcategory">-</span></div>
                    <div class="detail-item"><span class="label">Kategori</span><span class="value" id="dCategory">-</span>
                    </div>
                    <div class="detail-item"><span class="label">Tahun</span><span class="value" id="dYear">-</span></div>
                    <div class="detail-item"><span class="label">Urutan</span><span class="value" id="dOrder">-</span></div>
                </div>
                <div class="detail-card">
                    <div class="detail-card-header"><i class="fas fa-money-bill-wave"></i> Nilai Anggaran</div>
                    <div class="detail-grid">
                        <div class="detail-item"><span class="label">Anggaran</span><span class="value"
                                id="dAnggaran">-</span></div>
                        <div class="detail-item"><span class="label">Realisasi</span><span class="value"
                                id="dRealisasi">-</span></div>
                        <div class="detail-item progress-stack">
                            <div class="label">Progress</div>
                            <div class="progress progress--wide">
                                <div class="progress-bar" id="dProgressBar" style="width:0%"></div>
                            </div>
                            <span class="value" id="dProgress">-</span>
                        </div>
                    </div>
                </div>
                <div class="detail-card">
                    <div class="detail-card-header"><i class="fas fa-align-left"></i> Deskripsi</div>
                    <div class="detail-desc" id="dDescription">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--ghost pill" id="detailClose2">Tutup</button>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('budgetModal');
                const openBtn = document.getElementById('openCreateModal');
                const closeBtn = document.getElementById('modalClose');
                const cancelBtn = document.getElementById('cancelModal');
                const overlay = document.getElementById('closeModal');
                const form = document.getElementById('budgetForm');
                const methodInput = document.getElementById('formMethod');
                const titleEl = document.getElementById('modalTitle');
                const publishToggle = document.getElementById('fieldPublished');
                const publishLabel = document.getElementById('fieldPublishedLabel');
                const confirmModal = document.getElementById('confirmModal');
                const confirmClose = document.getElementById('confirmClose');
                const confirmCancel = document.getElementById('confirmCancel');
                const confirmOverlay = document.getElementById('confirmOverlay');
                const confirmSubmit = document.getElementById('confirmSubmit');
                const detailModal = document.getElementById('detailModal');
                const detailOverlay = document.getElementById('detailOverlay');
                const detailClose = document.getElementById('detailClose');
                const detailClose2 = document.getElementById('detailClose2');
                const actionMenus = Array.from(document.querySelectorAll('[data-action-menu]'));
                let pendingDeleteForm = null;
                const exportToggle = document.getElementById('exportDropdownToggleBudget');
                const exportMenu = document.getElementById('exportDropdownMenuBudget');

                const openExportMenu = () => {
                    if (!exportMenu || !exportToggle) return;
                    exportMenu.hidden = false;
                    requestAnimationFrame(() => exportMenu.classList.add('is-visible'));
                    exportToggle.setAttribute('aria-expanded', 'true');
                };
                const closeExportMenu = () => {
                    if (!exportMenu || !exportToggle) return;
                    exportMenu.classList.remove('is-visible');
                    exportMenu.hidden = true;
                    exportToggle.setAttribute('aria-expanded', 'false');
                };

                if (exportToggle && exportMenu) {
                    exportToggle.addEventListener('click', (event) => {
                        event.stopPropagation();
                        const expanded = exportToggle.getAttribute('aria-expanded') === 'true';
                        expanded ? closeExportMenu() : openExportMenu();
                    });
                    exportMenu.addEventListener('click', (event) => event.stopPropagation());
                    document.addEventListener('click', closeExportMenu);
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') closeExportMenu();
                    });
                }

                // Icon Picker
                const iconPicker = document.getElementById('iconPicker');
                const iconPickerToggle = document.getElementById('iconPickerToggle');
                const iconPickerSearch = document.getElementById('iconPickerSearch');
                const iconPickerGrid = document.getElementById('iconPickerGrid');
                const iconInput = document.getElementById('fieldIcon');
                const iconPreview = document.getElementById('iconPickerPreview');
                const iconLabelEl = document.getElementById('iconPickerSelectedLabel');

                // Fallbacks for icon rendering compatibility
                const iconRenderFallbacks = {
                    'fa-solid fa-people-group': 'fas fa-users',
                    'fa-people-group': 'fas fa-users',
                    'fa-solid fa-shield-halved': 'fas fa-shield-alt',
                    'fa-shield-halved': 'fas fa-shield-alt',
                    'fa-solid fa-house': 'fas fa-home',
                    'fa-solid fa-house-chimney': 'fas fa-home',
                    'fa-solid fa-building': 'fas fa-building',
                    'fa-solid fa-landmark': 'fas fa-landmark',
                    'fa-solid fa-phone': 'fas fa-phone',
                    'fa-solid fa-envelope': 'fas fa-envelope',
                    'fa-solid fa-sitemap': 'fas fa-sitemap',
                    'fa-solid fa-bell': 'fas fa-bell',
                    'fa-solid fa-globe': 'fas fa-globe',
                    'fa-solid fa-map-location-dot': 'fas fa-map-marked-alt',
                    'fa-solid fa-location-dot': 'fas fa-map-marker-alt',
                    'fa-solid fa-handshake': 'fas fa-handshake',
                    'fa-solid fa-clipboard-list': 'fas fa-clipboard-list',
                    'fa-solid fa-calendar-days': 'fas fa-calendar-alt',
                    'fa-solid fa-clipboard-check': 'fas fa-clipboard-check',
                    'fa-solid fa-list-check': 'fas fa-tasks',
                    'fa-solid fa-tree': 'fas fa-tree',
                    'fa-solid fa-users': 'fas fa-users',
                    'fa-solid fa-user-group': 'fas fa-users',
                    'fa-solid fa-circle-question': 'fas fa-question-circle',
                    'fa-solid fa-lightbulb': 'fas fa-lightbulb',
                    'fa-solid fa-coins': 'fas fa-coins',
                    'fa-solid fa-money-bill-wave': 'fas fa-money-bill-wave',
                    'fa-solid fa-chart-line': 'fas fa-chart-line',
                    'fa-solid fa-chart-bar': 'fas fa-chart-bar',
                    'fa-solid fa-chart-pie': 'fas fa-chart-pie',
                    'fa-solid fa-wallet': 'fas fa-wallet',
                    'fa-solid fa-hand-holding-dollar': 'fas fa-hand-holding-usd',
                    'fa-solid fa-piggy-bank': 'fas fa-piggy-bank',
                    'fa-solid fa-receipt': 'fas fa-receipt',
                    'fa-solid fa-file-invoice-dollar': 'fas fa-file-invoice-dollar',
                    'fa-solid fa-calculator': 'fas fa-calculator',
                    'fa-solid fa-university': 'fas fa-university',
                    'fa-solid fa-city': 'fas fa-city',
                    'fa-solid fa-store': 'fas fa-store',
                    'fa-solid fa-hospital': 'fas fa-hospital',
                    'fa-solid fa-school': 'fas fa-school',
                    'fa-solid fa-graduation-cap': 'fas fa-graduation-cap',
                    'fa-solid fa-book': 'fas fa-book',
                    'fa-solid fa-heart': 'fas fa-heart',
                    'fa-solid fa-seedling': 'fas fa-seedling',
                    'fa-solid fa-droplet': 'fas fa-tint',
                    'fa-solid fa-bolt': 'fas fa-bolt',
                    'fa-solid fa-road': 'fas fa-road',
                    'fa-solid fa-bridge': 'fas fa-road', // fallback
                    'fa-solid fa-truck': 'fas fa-truck',
                    'fa-solid fa-shield-alt': 'fas fa-shield-alt',
                    'fa-solid fa-recycle': 'fas fa-recycle',
                    'fa-solid fa-check-circle': 'fas fa-check-circle',
                    'fa-solid fa-ellipsis-vertical': 'fas fa-ellipsis-v',
                    'fa-solid fa-file-arrow-down': 'fas fa-file-download',
                    'fa-solid fa-headset': 'fas fa-headset',
                    'fa-solid fa-layer-group': 'fas fa-layer-group',
                    'fa-solid fa-link': 'fas fa-link',
                };

                // Custom label overrides for better readability
                const iconLabelOverrides = {
                    'fas fa-users': 'People Group',
                    'fa-solid fa-people-group': 'People Group',
                    'fas fa-shield-alt': 'Shield Halved',
                    'fa-solid fa-shield-halved': 'Shield Halved',
                    'fas fa-map-marked-alt': 'Map Location',
                    'fa-solid fa-map-location-dot': 'Map Location',
                    'fas fa-hand-holding-usd': 'Hand Holding Dollar',
                    'fa-solid fa-hand-holding-dollar': 'Hand Holding Dollar',
                    'fas fa-tint': 'Droplet',
                    'fa-solid fa-droplet': 'Droplet',
                    'fas fa-file-download': 'File Download',
                    'fa-solid fa-file-arrow-down': 'File Download',
                };

                // Preset budget-related icons (modern format)
                const iconPresetOptions = [
                    'fa-solid fa-coins',
                    'fa-solid fa-money-bill-wave',
                    'fa-solid fa-chart-line',
                    'fa-solid fa-chart-bar',
                    'fa-solid fa-chart-pie',
                    'fa-solid fa-wallet',
                    'fa-solid fa-hand-holding-dollar',
                    'fa-solid fa-piggy-bank',
                    'fa-solid fa-receipt',
                    'fa-solid fa-file-invoice-dollar',
                    'fa-solid fa-calculator',
                    'fa-solid fa-building',
                    'fa-solid fa-landmark',
                    'fa-solid fa-university',
                    'fa-solid fa-house',
                    'fa-solid fa-city',
                    'fa-solid fa-store',
                    'fa-solid fa-hospital',
                    'fa-solid fa-school',
                    'fa-solid fa-graduation-cap',
                    'fa-solid fa-book',
                    'fa-solid fa-users',
                    'fa-solid fa-people-group',
                    'fa-solid fa-heart',
                    'fa-solid fa-handshake',
                    'fa-solid fa-seedling',
                    'fa-solid fa-tree',
                    'fa-solid fa-droplet',
                    'fa-solid fa-bolt',
                    'fa-solid fa-lightbulb',
                    'fa-solid fa-road',
                    'fa-solid fa-bridge',
                    'fa-solid fa-map-location-dot',
                    'fa-solid fa-truck',
                    'fa-solid fa-shield-alt',
                    'fa-solid fa-recycle',
                    'fa-solid fa-circle-question'
                ];

                // Extended icon options (backward compatibility with fas format + modern)
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
                        "fab fa-facebook",
                        "fab fa-instagram",
                        "fab fa-twitter",
                        "fas fa-coins", "fas fa-money-bill-wave", "fas fa-chart-line", "fas fa-chart-bar",
                        "fas fa-chart-pie", "fas fa-wallet", "fas fa-hand-holding-usd", "fas fa-donate",
                        "fas fa-piggy-bank", "fas fa-receipt", "fas fa-file-invoice-dollar", "fas fa-calculator",
                        "fas fa-building", "fas fa-landmark", "fas fa-university", "fas fa-home",
                        "fas fa-house", "fas fa-city", "fas fa-store", "fas fa-hospital",
                        "fas fa-school", "fas fa-graduation-cap", "fas fa-book", "fas fa-book-open",
                        "fas fa-users", "fas fa-user-group", "fas fa-people-group", "fas fa-user",
                        "fas fa-user-tie", "fas fa-user-shield", "fas fa-user-check", "fas fa-user-plus",
                        "fas fa-heart", "fas fa-hand-holding-heart", "fas fa-hands-helping", "fas fa-handshake",
                        "fas fa-seedling", "fas fa-tree", "fas fa-leaf", "fas fa-tractor",
                        "fas fa-wheat-awn", "fas fa-carrot", "fas fa-apple-alt", "fas fa-fish",
                        "fas fa-water", "fas fa-droplet", "fas fa-faucet", "fas fa-shower",
                        "fas fa-bolt", "fas fa-lightbulb", "fas fa-plug", "fas fa-solar-panel",
                        "fas fa-road", "fas fa-bridge", "fas fa-route", "fas fa-map",
                        "fas fa-map-marked-alt", "fas fa-map-location-dot", "fas fa-location-dot", "fas fa-map-pin",
                        "fas fa-truck", "fas fa-bus", "fas fa-car", "fas fa-motorcycle",
                        "fas fa-bicycle", "fas fa-walking", "fas fa-person-walking", "fas fa-running",
                        "fas fa-dumbbell", "fas fa-futbol", "fas fa-basketball-ball", "fas fa-volleyball-ball",
                        "fas fa-hospital-alt", "fas fa-stethoscope", "fas fa-syringe", "fas fa-pills",
                        "fas fa-heartbeat", "fas fa-briefcase-medical", "fas fa-user-md", "fas fa-ambulance",
                        "fas fa-shield-alt", "fas fa-shield-virus", "fas fa-fire-extinguisher", "fas fa-hard-hat",
                        "fas fa-exclamation-triangle", "fas fa-radiation", "fas fa-biohazard", "fas fa-skull-crossbones",
                        "fas fa-recycle", "fas fa-trash", "fas fa-trash-alt", "fas fa-dumpster",
                        "fas fa-broom", "fas fa-spray-can", "fas fa-wind", "fas fa-cloud",
                        "fas fa-sun", "fas fa-moon", "fas fa-star", "fas fa-cloud-sun",
                        "fas fa-snowflake", "fas fa-umbrella", "fas fa-rainbow", "fas fa-meteor",
                        "fas fa-wifi", "fas fa-signal", "fas fa-tower-broadcast", "fas fa-satellite-dish",
                        "fas fa-phone", "fas fa-mobile-alt", "fas fa-tablet-alt", "fas fa-laptop",
                        "fas fa-desktop", "fas fa-tv", "fas fa-keyboard", "fas fa-mouse",
                        "fas fa-print", "fas fa-fax", "fas fa-scanner", "fas fa-camera",
                        "fas fa-video", "fas fa-film", "fas fa-photo-video", "fas fa-image",
                        "fas fa-images", "fas fa-file", "fas fa-file-alt", "fas fa-file-pdf",
                        "fas fa-file-word", "fas fa-file-excel", "fas fa-file-powerpoint", "fas fa-file-archive",
                        "fas fa-folder", "fas fa-folder-open", "fas fa-save", "fas fa-download",
                        "fas fa-upload", "fas fa-cloud-upload-alt", "fas fa-cloud-download-alt", "fas fa-share",
                        "fas fa-share-alt", "fas fa-link", "fas fa-paperclip", "fas fa-copy",
                        "fas fa-cut", "fas fa-paste", "fas fa-edit", "fas fa-pen",
                        "fas fa-pencil-alt", "fas fa-eraser", "fas fa-highlighter", "fas fa-marker",
                        "fas fa-check", "fas fa-check-circle", "fas fa-check-square", "fas fa-times",
                        "fas fa-times-circle", "fas fa-ban", "fas fa-exclamation", "fas fa-exclamation-circle",
                        "fas fa-question", "fas fa-question-circle", "fas fa-info", "fas fa-info-circle",
                        "fas fa-plus", "fas fa-plus-circle", "fas fa-minus", "fas fa-minus-circle",
                        "fas fa-arrow-up", "fas fa-arrow-down", "fas fa-arrow-left", "fas fa-arrow-right",
                        "fas fa-chevron-up", "fas fa-chevron-down", "fas fa-chevron-left", "fas fa-chevron-right",
                        "fas fa-angle-up", "fas fa-angle-down", "fas fa-angle-left", "fas fa-angle-right",
                        "fas fa-caret-up", "fas fa-caret-down", "fas fa-caret-left", "fas fa-caret-right",
                        "fas fa-sort", "fas fa-sort-up", "fas fa-sort-down", "fas fa-filter",
                        "fas fa-search", "fas fa-search-plus", "fas fa-search-minus", "fas fa-zoom-in",
                        "fas fa-zoom-out", "fas fa-eye", "fas fa-eye-slash", "fas fa-lock",
                        "fas fa-unlock", "fas fa-key", "fas fa-cog", "fas fa-cogs",
                        "fas fa-wrench", "fas fa-screwdriver", "fas fa-hammer", "fas fa-tools",
                        "fas fa-sliders-h", "fas fa-bars", "fas fa-th", "fas fa-th-large",
                        "fas fa-th-list", "fas fa-list", "fas fa-list-ul", "fas fa-list-ol",
                        "fas fa-align-left", "fas fa-align-center", "fas fa-align-right", "fas fa-align-justify",
                        "fas fa-indent", "fas fa-outdent", "fas fa-bold", "fas fa-italic",
                        "fas fa-underline", "fas fa-strikethrough", "fas fa-subscript", "fas fa-superscript",
                        "fas fa-text-height", "fas fa-text-width", "fas fa-font", "fas fa-paragraph",
                        "fas fa-heading", "fas fa-quote-left", "fas fa-quote-right", "fas fa-code",
                        "fas fa-terminal", "fas fa-bug", "fas fa-database", "fas fa-server",
                        "fas fa-hdd", "fas fa-microchip", "fas fa-memory", "fas fa-sitemap",
                        "fas fa-project-diagram", "fas fa-network-wired", "fas fa-ethernet", "fas fa-plug-circle-bolt",
                        "fas fa-bell", "fas fa-bell-slash", "fas fa-envelope", "fas fa-envelope-open",
                        "fas fa-inbox", "fas fa-paper-plane", "fas fa-comment", "fas fa-comment-dots",
                        "fas fa-comments", "fas fa-sms", "fas fa-at", "fas fa-hashtag",
                        "fas fa-calendar", "fas fa-calendar-alt", "fas fa-calendar-check", "fas fa-calendar-times",
                        "fas fa-calendar-plus", "fas fa-calendar-minus", "fas fa-calendar-day", "fas fa-calendar-week",
                        "fas fa-clock", "fas fa-stopwatch", "fas fa-hourglass", "fas fa-hourglass-half",
                        "fas fa-history", "fas fa-undo", "fas fa-redo", "fas fa-sync",
                        "fas fa-sync-alt", "fas fa-rotate", "fas fa-circle-question"
                    ]
                ]));

                // Add initial icon value if not in options
                const initialIconValue = (iconInput?.value || '').trim();
                if (initialIconValue && !iconOptions.includes(initialIconValue)) {
                    iconOptions.unshift(initialIconValue);
                }

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
                        iconPickerGrid.innerHTML = '<div class="icon-picker__empty">Icon tidak ditemukan.</div>';
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

                iconPickerSearch?.addEventListener('input', (e) => {
                    renderIcons(e.target.value);
                });

                document.addEventListener('click', (e) => {
                    if (!iconPicker?.contains(e.target)) {
                        closeIconPicker();
                    }
                });

                // Initialize icon picker
                renderIcons();
                const initialIcon = iconInput?.value || '';
                if (initialIcon) {
                    setIconValue(initialIcon);
                }

                const formatNumber = (value) => {
                    const digits = (value || '').toString().replace(/[^0-9]/g, '');
                    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                };

                const openModal = () => {
                    modal.classList.add('is-visible');
                    requestAnimationFrame(() => modal.classList.add('is-active'));
                };

                const closeModal = () => {
                    modal.classList.remove('is-active');
                    setTimeout(() => modal.classList.remove('is-visible'), 200);
                };

                const syncPublishLabel = () => {
                    if (!publishToggle || !publishLabel) return;
                    publishLabel.textContent = publishToggle.checked ? publishToggle.dataset.on : publishToggle.dataset.off;
                };

                const resetForm = () => {
                    form.reset();
                    form.action = "{{ route('admin.budget-items.store') }}";
                    methodInput.value = 'POST';
                    titleEl.textContent = 'Tambah Data';
                    document.getElementById('fieldYear').value = "{{ $selectedYear }}";
                    document.getElementById('fieldCategory').selectedIndex = 0;
                    document.getElementById('fieldOrder').value = 0;
                    publishToggle.checked = true;
                    syncPublishLabel();
                    resetIconPicker();
                };

                const openConfirm = (formEl) => {
                    pendingDeleteForm = formEl;
                    confirmModal.classList.add('is-visible');
                };

                const closeConfirm = () => {
                    pendingDeleteForm = null;
                    confirmModal.classList.remove('is-visible');
                };

                const fillForm = (button) => {
                    form.action = button.dataset.updateUrl;
                    methodInput.value = 'PUT';
                    titleEl.textContent = 'Edit Data';
                    document.getElementById('fieldYear').value = button.dataset.year;
                    document.getElementById('fieldCategory').value = button.dataset.category;
                    document.getElementById('fieldSubcategory').value = button.dataset.subcategory;
                    document.getElementById('fieldDescription').value = button.dataset.description || '';
                    document.getElementById('fieldAnggaran').value = formatNumber(button.dataset.anggaran);
                    document.getElementById('fieldRealisasi').value = formatNumber(button.dataset.realisasi);
                    document.getElementById('fieldOrder').value = button.dataset.order || 0;
                    // Set icon value and update picker
                    const iconValue = button.dataset.icon || '';
                    setIconValue(iconValue);
                    publishToggle.checked = button.dataset.published === '1';
                    syncPublishLabel();
                }

                [openBtn, closeBtn, cancelBtn, overlay].forEach(el => {
                    if (el) {
                        el.addEventListener('click', (e) => {
                            e.preventDefault();
                            if (el === openBtn) {
                                resetForm();
                                openModal();
                            } else {
                                closeModal();
                            }
                        });
                    }
                });

                document.querySelectorAll('.js-edit').forEach(button => {
                    button.addEventListener('click', () => {
                        fillForm(button);
                        openModal();
                    });
                });

                ['fieldAnggaran', 'fieldRealisasi'].forEach(id => {
                    const input = document.getElementById(id);
                    input.addEventListener('input', () => {
                        const pos = input.selectionStart;
                        input.value = formatNumber(input.value);
                        input.setSelectionRange(pos, pos);
                    });
                });

                if (publishToggle) {
                    publishToggle.addEventListener('change', syncPublishLabel);
                    syncPublishLabel();
                }

                document.querySelectorAll('.js-delete-trigger').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const formEl = btn.closest('form');
                        if (formEl) openConfirm(formEl);
                    });
                });

                [confirmClose, confirmCancel, confirmOverlay].forEach(el => {
                    if (el) el.addEventListener('click', (e) => { e.preventDefault(); closeConfirm(); });
                });
                if (confirmSubmit) {
                    confirmSubmit.addEventListener('click', () => {
                        if (pendingDeleteForm) pendingDeleteForm.submit();
                    });
                }

                const fillDetail = (btn) => {
                    document.getElementById('dSubcategory').textContent = btn.dataset.subcategory || '-';
                    document.getElementById('dCategory').textContent = btn.dataset.category || '-';
                    document.getElementById('dYear').textContent = btn.dataset.year || '-';
                    document.getElementById('dOrder').textContent = btn.dataset.order || '-';
                    document.getElementById('dAnggaran').textContent = btn.dataset.anggaran ? `Rp ${btn.dataset.anggaran}` : '-';
                    document.getElementById('dRealisasi').textContent = btn.dataset.realisasi ? `Rp ${btn.dataset.realisasi}` : '-';
                    document.getElementById('dProgress').textContent = btn.dataset.progress ? `${btn.dataset.progress}%` : '-';
                    const bar = document.getElementById('dProgressBar');
                    if (bar) {
                        const width = btn.dataset.progress ? Math.min(100, Number(btn.dataset.progress)) : 0;
                        bar.style.width = `${width}%`;
                    }
                    document.getElementById('dDescription').textContent = btn.dataset.description || '-';
                };

                const openDetail = (btn) => {
                    fillDetail(btn);
                    detailModal.classList.add('is-visible');
                };

                const closeDetail = () => {
                    detailModal.classList.add('is-closing');
                    setTimeout(() => {
                        detailModal.classList.remove('is-visible', 'is-closing');
                    }, 200);
                };

                document.querySelectorAll('.js-view').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        openDetail(btn);
                    });
                });

                [detailOverlay, detailClose, detailClose2].forEach(el => {
                    if (el) el.addEventListener('click', (e) => { e.preventDefault(); closeDetail(); });
                });

                // Tutup dropdown aksi lain saat satu menu dibuka
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
            });
        </script>
    @endpush
@endsection
