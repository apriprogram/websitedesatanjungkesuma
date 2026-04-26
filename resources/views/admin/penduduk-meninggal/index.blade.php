@extends('admin.layouts.app')

@section('title', 'Penduduk Meninggal')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-agenda.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-budget.css') }}?v={{ time() }}">
    <style>
        /* Global Page Font: Poppins (Excluding Icons) */
        div.admin-app,
        div.admin-app :not(i):not(.fas):not(.far):not(.fab),
        .page-title h1,
        .page-title p,
        .agenda-panel__header h2,
        .agenda-panel__header p,
        .table-budget th,
        .table-budget td:not(i):not(.fas):not(.far):not(.fab) {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Ensure Icons retain Font Awesome family */
        i.fas, i.far, i.fab, .fas, .far, .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        /* Table Alignment Refinement: All Left-Aligned & Orderly */
        .table-budget th,
        .table-budget td {
            text-align: left !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
            vertical-align: middle !important;
        }

        /* Consistent Column Spacing */
        .table-budget th:first-child,
        .table-budget td:first-child {
            padding-left: 20px !important;
        }

        /* Action Menu Alignment & Dots Button Style */
        .resident-actions {
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .action-button--dots {
            width: 36px !important;
            height: 36px !important;
            border-radius: 12px !important;
            background: rgba(148, 163, 184, 0.08) !important;
            color: #475569 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-button--dots:hover {
            background: rgba(148, 163, 184, 0.15) !important;
            color: #1e293b !important;
            transform: translateY(-1px);
        }

        /* Override header from grid to block so inner flex works */
        .resident-panel .resident-panel__header {
            display: block !important;
            background: #fff !important;
            padding: 24px 30px !important;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8) !important;
            width: 100% !important;
        }

        /* Sync Header Layout: title left, search/entries right */
        #residentPanelHeader {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
        }

        #residentSearchForm {
            margin-left: auto !important;
            flex: 0 0 auto !important;
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
        }

        #residentSearchForm .search-input-wrapper {
            min-width: 240px !important;
        }

        .panel-controls-group {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
        }

        /* Mobile Layout */
        @media (max-width: 768px) {
            #residentPanelHeader {
                flex-wrap: wrap !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 16px !important;
            }

            #residentSearchForm {
                margin-left: 0 !important;
                flex-direction: column !important;
                align-items: stretch !important;
                width: 100% !important;
            }

            #residentSearchForm .search-input-group,
            #residentSearchForm .search-input-wrapper {
                width: 100% !important;
                min-width: 100% !important;
            }
        }

        /* Action Dropdown Sync */
        .resident-action-dropdown {
            position: relative;
        }

        .resident-action-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            min-width: 160px;
            z-index: 3000;
            padding: 8px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-top: 8px;
            transform-origin: top right;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .resident-action-dropdown.is-upward .resident-action-menu {
            top: auto !important;
            bottom: 100% !important;
            margin-top: 0 !important;
            margin-bottom: 10px !important;
            transform-origin: bottom right !important;
            box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.15), 0 -8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }

        .resident-action-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            border: none;
            background: none;
            cursor: pointer;
            transition: all 0.15s ease;
            text-align: left;
        }

        .resident-action-item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .resident-action-item--danger { color: #dc2626; }
        .resident-action-item--danger:hover { background: rgba(220, 38, 38, 0.05); }

        /* Prevent clipping from table containers on desktop */
        @media (min-width: 1025px) {
            .news-table-wrap, .news-table, .resident-panel, .panel--flush {
                overflow: visible !important;
            }
            .table-budget, .table-budget td {
                overflow: visible !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/admin-entities.js') }}" defer></script>
    @if ($errors->any() && old('form_context'))
        <script>
            window.__entityModalToOpen = @json(old('form_context'));
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('exportDropdownToggleDeceased');
            const menu = document.getElementById('exportDropdownMenuDeceased');
            if (!toggle || !menu) return;

            const openMenu = () => {
                menu.hidden = false;
                requestAnimationFrame(() => menu.classList.add('is-visible'));
                toggle.setAttribute('aria-expanded', 'true');
            };
            const closeMenu = () => {
                menu.classList.remove('is-visible');
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                expanded ? closeMenu() : openMenu();
            });

            menu.addEventListener('click', (event) => event.stopPropagation());
            document.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMenu();
            });

            // Auto-Dropup Logic
            const handleDropup = (dropdown) => {
                const viewportHeight = window.innerHeight;
                const rect = dropdown.getBoundingClientRect();
                const spaceBelow = viewportHeight - rect.bottom;
                if (spaceBelow < 200) {
                    dropdown.classList.add('is-upward');
                } else {
                    dropdown.classList.remove('is-upward');
                }
            };

            document.querySelectorAll('[data-action-menu]').forEach((dropdown) => {
                const summary = dropdown.querySelector('summary');
                if (summary) {
                    summary.addEventListener('pointerdown', () => handleDropup(dropdown));
                }
                dropdown.addEventListener('toggle', () => {
                    if (dropdown.open) handleDropup(dropdown);
                });
            });
        });
    </script>
@endpush

@section('content')
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
            <span class="header-title-text">{{ config('app.name', 'Manajemen Desa') }}</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Penduduk</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.penduduk-meninggal.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Penduduk Meninggal</a>
        </nav>
    </header>

    @include('admin.partials.alerts')

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Riwayat Penduduk Meninggal</h1>
            <p>Catatan penduduk yang meninggal lengkap dengan penyebab dan lokasi.</p>
        </div>
        <div class="title-actions">
            <div class="export-dropdown">
                <button
                    type="button"
                    class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                    id="exportDropdownToggleDeceased"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="exportDropdownMenuDeceased"
                >
                    <i class="fas fa-file-export" aria-hidden="true"></i>
                    <span>Ekspor</span>
                    <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                </button>
                <div class="export-dropdown__menu" id="exportDropdownMenuDeceased" role="menu" hidden>
                    <div class="export-dropdown__header">Pilih Ekspor</div>
                    <div class="export-dropdown__grid">
                        <a
                            href="{{ route('admin.penduduk-meninggal.export.excel') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
                            <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Excel</strong>
                                <small>Unduh format spreadsheet</small>
                            </div>
                        </a>
                        <a
                            href="{{ route('admin.penduduk-meninggal.export.pdf') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
                            <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download PDF</strong>
                                <small>Siap cetak (F4)</small>
                            </div>
                        </a>
                        <a
                            href="{{ route('admin.penduduk-meninggal.export.word') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
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
            <button type="button" class="primary-btn" data-modal-open="deceasedCreateModal">
                <i class="fas fa-plus"></i>
                <span>Catat Kematian</span>
            </button>
        </div>
    </section>

    @php
        $entriesOptions = $entriesOptions ?? [10, 25, 50, 100];
        $periodOptions = $periodOptions ?? ['all' => 'Semua Waktu'];
        $entries = $entries ?? 10;
        $period = $period ?? 'all';
        $search = $search ?? '';
        $pendudukOptions = collect($pendudukOptions ?? []);
        $modalContext = old('form_context');
        $createContext = $modalContext === 'deceasedCreateModal';
        $queryWithoutSearch = collect(request()->except('search'))
            ->reject(fn ($value) => $value === null || $value === '')
            ->all();
        $resetSearchUrl = route('admin.penduduk-meninggal.index', $queryWithoutSearch);
        $currentUrl = request()->fullUrl();
    @endphp

    <article class="panel panel--flush agenda-panel resident-panel">
        <header class="panel-header panel-header--table agenda-panel__header">
            <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                <div>
                    <h2>Daftar Penduduk Meninggal</h2>
                    <p>Manajemen data administrasi penduduk yang telah meninggal dunia.</p>
                </div>

                <form id="deceasedFilterForm" method="GET" action="{{ route('admin.penduduk-meninggal.index') }}" class="panel-toolbar-inline">
                    {{-- Filters grouped in panel-filters --}}
                    <div class="panel-filters">
                        <select name="period" onchange="this.form.submit()" aria-label="Periode" class="form-select-sm panel-filter-select">
                            @foreach ($periodOptions as $value => $label)
                                <option value="{{ $value }}" @selected($period === (string) $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="entries" onchange="this.form.submit()" aria-label="Jumlah entri" class="form-select-sm panel-filter-select">
                            @foreach ($entriesOptions as $option)
                                <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Search Bar synchronized with Agenda style --}}
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon-left"></i>
                            <input id="deceasedSearch" type="search" name="search" value="{{ $search }}"
                                placeholder="Cari NIK atau nama..." aria-label="Cari NIK atau nama" autocomplete="off">
                        </div>
                    </div>
                </form>
            </div>
        </header>

            <div class="news-table-wrap agenda-table-scroll-active">
                <div class="news-table">
                    <table class="table-budget">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">#</th>
                                <th style="min-width: 250px; text-align: left;">Penduduk</th>
                                <th style="width: 180px; text-align: center;">Tanggal Meninggal</th>
                                <th style="min-width: 200px; text-align: left;">Penyebab Malapetaka</th>
                                <th style="min-width: 220px; text-align: left;">Lokasi & No. Akta</th>
                                <th style="width: 140px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $startIndex = ($records->currentPage() - 1) * $records->perPage(); @endphp
                            @forelse ($records as $record)
                                <tr>
                                    <td style="text-align: center;">{{ $startIndex + $loop->iteration }}</td>
                                    <td style="text-align: left;">
                                        <div class="resident-table__person-meta">
                                            <strong>{{ $record->penduduk?->nama ?? 'Tidak diketahui' }}</strong>
                                            <p style="margin: 2px 0 0; font-family: monospace; font-size: 0.8rem; color: #64748b;">
                                                NIK {{ $record->penduduk?->nik ?? '-' }}
                                            </p>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 600; color: #1e293b;">
                                        {{ optional($record->tanggal_meninggal)->translatedFormat('d M Y') ?? 'Tidak dicantumkan' }}
                                    </td>
                                    <td style="text-align: left; color: #475569; font-weight: 500;">
                                        {{ $record->penyebab ?: 'Tidak dicantumkan' }}
                                    </td>
                                    <td style="text-align: left;">
                                        <div style="display: flex; flex-direction: column; gap: 2px;">
                                            <span style="font-weight: 600; color: #334155;">{{ $record->tempat_meninggal ?: '-' }}</span>
                                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">Akta: {{ $record->akta_meninggal_no ?: 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="resident-actions">
                                            <details class="resident-action-dropdown" data-action-menu>
                                                <summary class="action-button--dots" aria-haspopup="menu" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </summary>
                                                <div class="resident-action-menu" role="menu">
                                                    <button type="button" class="resident-action-item"
                                                        data-modal-open="deceasedDetailModal-{{ $record->id }}">
                                                        <i class="fas fa-eye"></i><span>Detail</span>
                                                    </button>
                                                    <button type="button" class="resident-action-item"
                                                        data-modal-open="deceasedEditModal-{{ $record->id }}">
                                                        <i class="fas fa-pen"></i><span>Edit</span>
                                                    </button>
                                                    <button type="button" class="resident-action-item resident-action-item--danger"
                                                        data-modal-open="deceasedDeleteModal-{{ $record->id }}">
                                                        <i class="fas fa-trash"></i><span>Hapus</span>
                                                    </button>
                                                </div>
                                            </details>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 60px 0;">
                                        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; color: #94a3b8;">
                                            <i class="fas fa-cross" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <p style="font-size: 0.95rem; font-weight: 500;">Belum ada catatan penduduk meninggal.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel-footer resident-panel__footer">
                <div class="resident-panel__footer-info">
                    Menampilkan {{ $records->firstItem() ?? 0 }} - {{ $records->lastItem() ?? 0 }} dari
                    {{ $records->total() }} catatan
                </div>
                <div class="resident-pagination__links">
                    {{ $records->withQueryString()->onEachSide(1)->links('admin.partials.pagination') }}
                </div>
            </div>
        </article>

    {{-- Modal: Create --}}
    <div class="dialog-backdrop" id="deceasedCreateModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="deceasedCreateTitle">
            <header class="dialog__header">
                <h2 id="deceasedCreateTitle">Catat Penduduk Meninggal</h2>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form pencatatan">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.penduduk-meninggal.store') }}" class="dialog__form">
                @csrf
                <input type="hidden" name="redirect" value="{{ $currentUrl }}">
                <input type="hidden" name="form_context" value="deceasedCreateModal">
                <div class="dialog__body">
                    <div class="settings-form-grid two-columns">
                        <label class="form-field @if($createContext && $errors->has('penduduk_id')) form-field--error @endif">
                            <span>Penduduk <sup>*</sup></span>
                            <select name="penduduk_id" required>
                                <option value="">Pilih penduduk</option>
                                @foreach ($pendudukOptions as $option)
                                    <option value="{{ $option->id }}" @selected($createContext && old('penduduk_id') == $option->id)>
                                        {{ $option->nama }} &mdash; NIK {{ $option->nik }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($createContext)
                                @error('penduduk_id')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field @if($createContext && $errors->has('tanggal_meninggal')) form-field--error @endif">
                            <span>Tanggal Meninggal <sup>*</sup></span>
                            <input type="date" name="tanggal_meninggal" value="{{ $createContext ? old('tanggal_meninggal') : '' }}" required>
                            @if ($createContext)
                                @error('tanggal_meninggal')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field">
                            <span>Penyebab</span>
                            <input type="text" name="penyebab" value="{{ $createContext ? old('penyebab') : '' }}" maxlength="150">
                        </label>
                        <label class="form-field">
                            <span>Tempat Meninggal</span>
                            <input type="text" name="tempat_meninggal" value="{{ $createContext ? old('tempat_meninggal') : '' }}" maxlength="150">
                        </label>
                        <label class="form-field">
                            <span>No. Akta Kematian</span>
                            <input type="text" name="akta_meninggal_no" value="{{ $createContext ? old('akta_meninggal_no') : '' }}" maxlength="100">
                        </label>
                        <label class="form-field form-field--full">
                            <span>Keterangan Tambahan</span>
                            <textarea name="keterangan" rows="3">{{ $createContext ? old('keterangan') : '' }}</textarea>
                        </label>
                    </div>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-save"></i>
                        <span>Simpan</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    @foreach ($records as $record)
        @php
            $detailRows = [
                ['label' => 'Nama Penduduk', 'value' => $record->penduduk?->nama ?? '-'],
                ['label' => 'NIK', 'value' => $record->penduduk?->nik ?? '-'],
                ['label' => 'Tanggal Meninggal', 'value' => optional($record->tanggal_meninggal)->translatedFormat('d M Y') ?? '-'],
                ['label' => 'Penyebab', 'value' => $record->penyebab ?: '-'],
                ['label' => 'Tempat Meninggal', 'value' => $record->tempat_meninggal ?: '-'],
                ['label' => 'No. Akta Kematian', 'value' => $record->akta_meninggal_no ?: '-'],
                ['label' => 'Keterangan', 'value' => $record->keterangan ?: '-'],
            ];
            $editContext = $modalContext === 'deceasedEditModal-' . $record->id;
            $selectCollection = $pendudukOptions->contains('id', $record->penduduk_id)
                ? $pendudukOptions
                : $pendudukOptions->concat([$record->penduduk])->filter()->unique('id');
            $selectCollection = $selectCollection->sortBy('nama');
        @endphp

        {{-- Detail Modal --}}
        <div class="dialog-backdrop" id="deceasedDetailModal-{{ $record->id }}" aria-hidden="true">
            <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="deceasedDetailTitle-{{ $record->id }}">
                <header class="dialog__header">
                    <h2 id="deceasedDetailTitle-{{ $record->id }}">Detail Kematian {{ $record->penduduk?->nama ?? '-' }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail kematian">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body dialog__body--detail">
                    <dl class="detail-list">
                        @foreach ($detailRows as $row)
                            <div class="detail-list__item">
                                <dt>{{ $row['label'] }}</dt>
                                <dd>{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn" data-modal-close>Tutup</button>
                </footer>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div class="dialog-backdrop" id="deceasedEditModal-{{ $record->id }}" aria-hidden="true">
            <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="deceasedEditTitle-{{ $record->id }}">
                <header class="dialog__header">
                    <h2 id="deceasedEditTitle-{{ $record->id }}">Edit Catatan {{ $record->penduduk?->nama ?? '-' }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form edit">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.penduduk-meninggal.update', $record) }}" class="dialog__form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect" value="{{ $currentUrl }}">
                    <input type="hidden" name="form_context" value="deceasedEditModal-{{ $record->id }}">
                    <div class="dialog__body">
                        <div class="settings-form-grid two-columns">
                            <label class="form-field @if($editContext && $errors->has('penduduk_id')) form-field--error @endif">
                                <span>Penduduk <sup>*</sup></span>
                                <select name="penduduk_id" required>
                                    <option value="">Pilih penduduk</option>
                                    @foreach ($selectCollection as $option)
                                        <option value="{{ $option->id }}" @selected($editContext ? old('penduduk_id', $record->penduduk_id) == $option->id : $record->penduduk_id == $option->id)>
                                            {{ $option->nama }} &mdash; NIK {{ $option->nik }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($editContext)
                                    @error('penduduk_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field @if($editContext && $errors->has('tanggal_meninggal')) form-field--error @endif">
                                <span>Tanggal Meninggal <sup>*</sup></span>
                                <input type="date" name="tanggal_meninggal" value="{{ $editContext ? old('tanggal_meninggal', optional($record->tanggal_meninggal)->toDateString()) : optional($record->tanggal_meninggal)->toDateString() }}" required>
                                @if ($editContext)
                                    @error('tanggal_meninggal')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field">
                                <span>Penyebab</span>
                                <input type="text" name="penyebab" value="{{ $editContext ? old('penyebab', $record->penyebab) : ($record->penyebab ?? '') }}" maxlength="150">
                            </label>
                            <label class="form-field">
                                <span>Tempat Meninggal</span>
                                <input type="text" name="tempat_meninggal" value="{{ $editContext ? old('tempat_meninggal', $record->tempat_meninggal) : ($record->tempat_meninggal ?? '') }}" maxlength="150">
                            </label>
                            <label class="form-field">
                                <span>No. Akta Kematian</span>
                                <input type="text" name="akta_meninggal_no" value="{{ $editContext ? old('akta_meninggal_no', $record->akta_meninggal_no) : ($record->akta_meninggal_no ?? '') }}" maxlength="100">
                            </label>
                            <label class="form-field form-field--full">
                                <span>Keterangan Tambahan</span>
                                <textarea name="keterangan" rows="3">{{ $editContext ? old('keterangan', $record->keterangan) : ($record->keterangan ?? '') }}</textarea>
                            </label>
                        </div>
                    </div>
                    <footer class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">
                            <i class="fas fa-save"></i>
                            <span>Perbarui</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>

        {{-- Delete Modal --}}
        <div class="dialog-backdrop" id="deceasedDeleteModal-{{ $record->id }}" aria-hidden="true">
            <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="deceasedDeleteTitle-{{ $record->id }}">
                <header class="dialog__header">
                    <h2 id="deceasedDeleteTitle-{{ $record->id }}">Hapus Catatan {{ $record->penduduk?->nama ?? '-' }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <p>Anda yakin ingin menghapus catatan kematian for <strong>{{ $record->penduduk?->nama ?? '-' }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <footer class="dialog__footer dialog__footer--split">
                    <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                    <form method="POST" action="{{ route('admin.penduduk-meninggal.destroy', $record) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="{{ $currentUrl }}">
                        <input type="hidden" name="form_context" value="deceasedDeleteModal-{{ $record->id }}">
                        <button type="submit" class="danger-btn">
                            <i class="fas fa-trash"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </footer>
            </div>
        </div>
    @endforeach
@endsection
