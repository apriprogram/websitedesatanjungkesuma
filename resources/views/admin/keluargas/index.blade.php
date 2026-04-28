@extends('admin.layouts.app')

@section('title', 'Data Kartu Keluarga')

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
        i.fas,
        i.far,
        i.fab,
        .fas,
        .far,
        .fab {
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

        /* Mobile Layout Stacking Fix */
        @media (max-width: 768px) {
            #residentPanelHeader {
                display: flex !important;
                flex-direction: column !important;
                /* Forces vertical stacking */
                align-items: flex-start !important;
                /* Aligns both to the left */
                gap: 16px !important;
            }

            .resident-panel__title {
                order: 1 !important;
                /* Ensures title stays on top */
                width: 100% !important;
            }

            #residentSearchForm {
                order: 2 !important;
                /* Ensures search stays below title */
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

            .page-title p {
                margin-bottom: 6px !important;
            }

            .title-actions {
                display: flex !important;
                flex-direction: row !important;
                gap: 6px !important;
                width: 100% !important;
                margin-top: 0 !important;
            }

            .title-actions>* {
                flex: 1 !important;
                margin: 0 !important;
                height: 32px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                font-size: 0.68rem !important;
            }

            .title-actions i {
                font-size: 0.7rem !important;
                margin-right: 4px !important;
            }

            .export-dropdown__toggle {
                height: 100% !important;
                width: 100% !important;
            }
        }

        /* Action Dropdown Sync */
        .resident-action-dropdown {
            position: relative;
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
            z-index: 100;
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

        .resident-action-item--danger {
            color: #dc2626;
        }

        .resident-action-item--danger:hover {
            background: rgba(220, 38, 38, 0.05);
        }

        .avatar-stack {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 8px;
        }

        .avatar-item {
            position: relative;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 2px solid #fff;
            margin-left: -12px;
            overflow: hidden;
            background: #f1f5f9;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .avatar-item:first-child {
            margin-left: 0;
        }

        .avatar-item:hover {
            transform: translateY(-3px);
            z-index: 10;
            border-color: #2563eb;
        }

        .avatar-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-item .avatar-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
        }

        .avatar-more {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #f1f5f9;
            border: 2px solid #fff;
            margin-left: -12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Detail Modal Professional Style (Penduduk Pindah Inspired) */
        .family-detail-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding: 4px;
        }

        .family-detail-full {
            grid-column: span 2;
        }

        @media (max-width: 992px) {
            .family-detail-wrapper {
                grid-template-columns: 1fr;
            }

            .family-detail-full {
                grid-column: span 1;
            }
        }

        @media (max-width: 768px) {
            .dialog--form {
                width: 98% !important;
                max-height: 98vh !important;
                overflow-x: hidden !important;
            }

            .dialog__body {
                padding: 8px !important;
                overflow-x: hidden !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .family-detail-wrapper {
                gap: 8px !important;
                width: 100% !important;
                max-width: 100% !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .detail-card {
                padding: 10px !important;
                border-radius: 10px !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            .detail-card__header {
                margin-bottom: 8px !important;
                padding-bottom: 6px !important;
                gap: 6px !important;
            }

            .detail-card__header i {
                width: 24px !important;
                height: 24px !important;
                font-size: 0.7rem !important;
            }

            .detail-card__header h3 {
                font-size: 0.75rem !important;
            }

            .head-profile-section {
                flex-direction: row !important;
                align-items: flex-start !important;
                text-align: left !important;
                gap: 12px !important;
            }

            .head-profile-section h4 {
                font-size: 0.9rem !important;
                margin-bottom: 2px !important;
            }

            .head-profile-section span[style*="font-size: 0.95rem"] {
                font-size: 0.7rem !important;
            }

            .head-profile-section .info-item__value {
                font-size: 0.7rem !important;
                line-height: 1.2 !important;
            }

            .head-photo-container {
                width: 80px !important;
                height: 100px !important;
                border-radius: 8px !important;
            }

            .head-photo-container i {
                font-size: 1.8rem !important;
            }

            .info-grid {
                grid-template-columns: 1fr !important;
                gap: 6px !important;
            }

            .info-item__label {
                font-size: 0.6rem !important;
            }

            .info-item__value {
                font-size: 0.72rem !important;
            }

            .member-table th,
            .member-table td {
                padding: 6px 8px !important;
                font-size: 0.65rem !important;
            }

            .member-table-wrapper {
                margin: 0 -4px;
                border-radius: 8px;
                width: 100% !important;
                max-width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            .dialog__footer {
                flex-direction: column-reverse;
                padding: 10px !important;
                gap: 6px !important;
            }

            .dialog__footer .primary-btn,
            .dialog__footer a.primary-btn {
                width: 100% !important;
                justify-content: center !important;
                padding: 8px !important;
                font-size: 0.7rem !important;
            }

            .dialog__footer .primary-btn i {
                font-size: 0.65rem !important;
            }
        }

        .detail-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            transition: border-color 0.2s ease;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }

        .detail-card:hover {
            border-color: #2563eb;
        }

        .detail-card__header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(226, 232, 240, 1);
        }

        .detail-card__header i {
            width: 30px;
            height: 30px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-size: 0.85rem;
        }

        .detail-card__header h3 {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            text-transform: none;
            letter-spacing: normal;
            margin: 0;
        }

        .head-profile-section {
            display: flex;
            align-items: start;
            gap: 24px;
            margin-bottom: 20px;
            flex: 1;
        }

        .head-photo-container {
            width: 160px;
            height: 200px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: #f8fafc;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .head-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .head-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            gap: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-item--full {
            grid-column: span 2;
        }

        .info-item__label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: none;
            letter-spacing: normal;
        }

        .info-item__value {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
        }

        .info-item__value--long {
            line-height: 1.5;
            color: #475569;
            font-weight: 500;
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }

        .member-list-section {
            margin-top: 24px;
        }

        .member-table-wrapper {
            overflow-x: auto;
            border-radius: 16px;
            border: 1px solid #f1f5f9;
        }

        .member-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        .member-table th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-weight: 700;
            color: #64748b;
            font-size: 0.7rem;
            text-transform: none;
            border-bottom: 2px solid #f1f5f9;
            white-space: nowrap;
        }

        .member-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: #475569;
            font-size: 0.82rem;
        }

        .member-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            overflow: hidden;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            flex-shrink: 0;
        }

        .member-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        .member-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 0.85rem;
            line-height: 1.2;
        }

        .member-nik {
            font-family: monospace;
            font-size: 0.72rem;
            color: #64748b;
        }

        .badge-role {
            display: inline-flex;
            padding: 3px 8px;
            border-radius: 8px;
            font-size: 0.68rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            white-space: nowrap;
        }

        .badge-role--head {
            background: #eff6ff;
            color: #2563eb;
        }

        .badge-role--spouse {
            background: #fef2f2;
            color: #ef4444;
        }

        .badge-role--child {
            background: #f0fdf4;
            color: #22c55e;
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
            const toggle = document.getElementById('exportDropdownToggleFamilies');
            const menu = document.getElementById('exportDropdownMenuFamilies');
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
            <span class="breadcrumb-link">Kependudukan</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.keluargas.index') }}" class="breadcrumb-link breadcrumb-link--active"
                aria-current="page">Data Kartu Keluarga</a>
        </nav>
    </header>

    @include('admin.partials.alerts')

    @php
        $entriesOptions = $entriesOptions ?? [10, 12, 25, 50, 100];
        $modalContext = old('form_context');
    @endphp

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Data Kartu Keluarga</h1>
            <p>Kelola informasi kepala keluarga, alamat, serta relasi wilayah administrasi.</p>
        </div>
        <div class="title-actions">
            <div class="export-dropdown">
                <button type="button" class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                    id="exportDropdownToggleFamilies" aria-haspopup="true" aria-expanded="false"
                    aria-controls="exportDropdownMenuFamilies">
                    <i class="fas fa-file-export" aria-hidden="true"></i>
                    <span>Ekspor</span>
                    <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                </button>
                <div class="export-dropdown__menu" id="exportDropdownMenuFamilies" role="menu" hidden>
                    <div class="export-dropdown__header">Pilih Ekspor</div>
                    <div class="export-dropdown__grid">
                        <a href="{{ route('admin.keluargas.export.excel') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Excel</strong>
                                <small>Unduh format spreadsheet</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.keluargas.export.pdf') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download PDF</strong>
                                <small>Siap cetak (F4)</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.keluargas.export.word') }}" class="export-dropdown__item" role="menuitem">
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
                <button type="button" class="primary-btn" data-modal-open="familyCreateModal">
                    <i class="fas fa-plus"></i>
                    <span>Tambah KK</span>
                </button>
            </div>
    </section>

    <article class="panel panel--flush agenda-panel resident-panel">
        <header class="panel-header panel-header--table agenda-panel__header">
            <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                <div>
                    <h2>Daftar Kartu Keluarga</h2>
                    <p>Manajemen data kartu keluarga (KK) dan relasi wilayah administrasi desa.</p>
                </div>

                <form id="familyFilterForm" method="GET" action="{{ route('admin.keluargas.index') }}"
                    class="panel-toolbar-inline">
                    <div class="panel-filters">
                        <select id="familyDusun" name="dusun_id" onchange="this.form.submit()" aria-label="Filter Dusun"
                            class="form-select-sm panel-filter-select">
                            <option value="">Semua Dusun</option>
                            @foreach ($dusuns as $dusun)
                                <option value="{{ $dusun->id }}" @selected($dusunId === $dusun->id)>{{ $dusun->nama }}</option>
                            @endforeach
                        </select>
                        <select id="familyEntries" name="entries" onchange="this.form.submit()" aria-label="Jumlah entri"
                            class="form-select-sm panel-filter-select">
                            @foreach ($entriesOptions as $option)
                                <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon-left"></i>
                            <input id="familySearch" type="search" name="search" value="{{ $search }}"
                                placeholder="Cari nomor KK atau kepala..." aria-label="Cari nomor KK atau kepala"
                                autocomplete="off">
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
                            <th style="width: 180px; text-align: center;">Nomor KK</th>
                            <th style="width: 200px; text-align: left;">Kepala Keluarga</th>
                            <th style="min-width: 250px; text-align: left;">Alamat Lengkap</th>
                            <th style="width: 220px; text-align: left;">Wilayah (RW/RT)</th>
                            <th style="width: 100px; text-align: center;">Anggota</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $startIndex = ($keluargas->currentPage() - 1) * $keluargas->perPage(); @endphp
                        @forelse ($keluargas as $keluarga)
                            <tr>
                                <td style="text-align: center;">{{ $startIndex + $loop->iteration }}</td>
                                <td style="text-align: center;">
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <strong style="color: #1e293b; font-size: 0.95rem;">{{ $keluarga->no_kk }}</strong>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">Dibuat:
                                            {{ $keluarga->created_at?->format('d/m/Y') ?? '-' }}</span>
                                    </div>
                                </td>
                                <td style="text-align: left;">
                                    @php
                                        $headOfFamily = $keluarga->penduduks->firstWhere('kk_level_id', 1) ?: $keluarga->penduduks->first();
                                    @endphp
                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                        <strong
                                            style="color: #334155;">{{ $headOfFamily?->nama ?: ($keluarga->kepala_nama ?: 'Belum terdata') }}</strong>
                                        <span
                                            style="font-family: monospace; font-size: 0.8rem; color: #64748b; letter-spacing: 0.02em;">NIK
                                            {{ $headOfFamily?->nik ?: ($keluarga->kepala_nik ?: '-') }}</span>
                                    </div>
                                </td>
                                <td
                                    style="text-align: left; color: #475569; font-weight: 500; font-size: 0.88rem; line-height: 1.5;">
                                    {{ $keluarga->alamat ?: 'Belum diisi' }}
                                </td>
                                <td style="text-align: left;">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span
                                                style="display: inline-block; padding: 2px 8px; background: #f1f5f9; border-radius: 4px; font-size: 0.75rem; font-weight: 700; color: #475569;">Dusun</span>
                                            <span
                                                style="font-weight: 600; color: #334155; font-size: 0.88rem;">{{ $keluarga->dusun?->nama ?? '-' }}</span>
                                        </div>
                                        <span style="font-size: 0.8rem; color: #64748b; font-weight: 500; padding-left: 4px;">
                                            RW {{ $keluarga->rw?->nomor ?? '-' }} &middot; RT {{ $keluarga->rt?->nomor ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td style="text-align: center !important;">
                                    <div class="avatar-stack">
                                        @foreach ($keluarga->penduduks->take(4) as $member)
                                            <div class="avatar-item" title="{{ $member->nama }} ({{ $member->kkLevel?->nama }})">
                                                @if ($member->foto_profil)
                                                    <img src="{{ asset('storage/' . $member->foto_profil) }}" alt="{{ $member->nama }}">
                                                @else
                                                    <div class="avatar-placeholder">
                                                        {{ strtoupper(substr($member->nama, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($keluarga->penduduks_count > 4)
                                            <div class="avatar-more">+{{ $keluarga->penduduks_count - 4 }}</div>
                                        @endif
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
                                                    data-modal-open="familyDetailModal-{{ $keluarga->id }}">
                                                    <i class="fas fa-eye"></i><span>Detail</span>
                                                </button>
                                                <button type="button" class="resident-action-item"
                                                    data-modal-open="familyEditModal-{{ $keluarga->id }}">
                                                    <i class="fas fa-pen"></i><span>Edit</span>
                                                </button>
                                                <button type="button" class="resident-action-item resident-action-item--danger"
                                                    data-modal-open="familyDeleteModal-{{ $keluarga->id }}">
                                                    <i class="fas fa-trash"></i><span>Hapus</span>
                                                </button>
                                            </div>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 60px 0;">
                                    <div
                                        style="display: flex; flex-direction: column; align-items: center; gap: 12px; color: #94a3b8;">
                                        <i class="fas fa-people-roof" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p style="font-size: 0.95rem; font-weight: 500;">Belum ada data kartu keluarga yang
                                            tercatat.</p>
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
                Menampilkan {{ $keluargas->firstItem() ?? 0 }} - {{ $keluargas->lastItem() ?? 0 }} dari
                {{ $keluargas->total() }} keluarga
            </div>
            <div class="resident-pagination__links">
                {{ $keluargas->withQueryString()->onEachSide(1)->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>

    {{-- Modal: Create --}}
    <div class="dialog-backdrop" id="familyCreateModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="familyCreateTitle">
            <header class="dialog__header">
                <h2 id="familyCreateTitle">Tambah Kartu Keluarga</h2>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form tambah KK">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.keluargas.store') }}" class="dialog__form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                <input type="hidden" name="form_context" value="familyCreateModal">
                <div class="dialog__body">
                    <div class="form-grid--2 mb-4">
                        <label
                            class="form-field @if($modalContext === 'familyCreateModal' && $errors->has('no_kk')) form-field--error @endif">
                            <span>Nomor KK <sup>*</sup></span>
                            <input type="text" name="no_kk"
                                value="{{ $modalContext === 'familyCreateModal' ? old('no_kk') : '' }}" maxlength="30"
                                required>
                            @if ($modalContext === 'familyCreateModal')
                                @error('no_kk')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label
                            class="form-field @if($modalContext === 'familyCreateModal' && $errors->has('kepala_nik')) form-field--error @endif">
                            <span>NIK Kepala Keluarga</span>
                            <input type="text" name="kepala_nik"
                                value="{{ $modalContext === 'familyCreateModal' ? old('kepala_nik') : '' }}" maxlength="20">
                            @if ($modalContext === 'familyCreateModal')
                                @error('kepala_nik')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="form-field form-field--full">
                            <span>Alamat Lengkap</span>
                            <textarea name="alamat"
                                rows="2">{{ $modalContext === 'familyCreateModal' ? old('alamat') : '' }}</textarea>
                        </label>
                    </div>

                    <div class="form-grid--3">
                        <label class="form-field">
                            <span>Dusun</span>
                            <select name="dusun_id">
                                <option value="">Pilih dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected($modalContext === 'familyCreateModal' && old('dusun_id') == $dusun->id)>
                                        {{ $dusun->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>RW</span>
                            <select name="rw_id">
                                <option value="">Pilih RW</option>
                                @foreach ($rws as $rw)
                                    <option value="{{ $rw->id }}" @selected($modalContext === 'familyCreateModal' && old('rw_id') == $rw->id)>
                                        RW {{ $rw->nomor }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>RT</span>
                            <select name="rt_id">
                                <option value="">Pilih RT</option>
                                @foreach ($rts as $rt)
                                    <option value="{{ $rt->id }}" @selected($modalContext === 'familyCreateModal' && old('rt_id') == $rt->id)>
                                        RT {{ $rt->nomor }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-save"></i>
                        <span>Simpan</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Modal: Detail, Edit, Delete --}}
    @foreach ($keluargas as $keluarga)
        @php
            $editContext = $modalContext === 'familyEditModal-' . $keluarga->id;
        @endphp
        <div class="dialog-backdrop" id="familyDetailModal-{{ $keluarga->id }}" aria-hidden="true">
            <div class="dialog dialog--form" style="width: 95%; max-width: 1600px;" role="dialog" aria-modal="true"
                aria-labelledby="familyDetailTitle-{{ $keluarga->id }}">
                <header class="dialog__header" style="padding: 12px 20px 8px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div
                            style="width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px; display: grid; place-items: center;">
                            <i class="fas fa-file-invoice" style="font-size: 0.9rem;"></i>
                        </div>
                        <div>
                            <h2 id="familyDetailTitle-{{ $keluarga->id }}"
                                style="margin: 0; font-size: 1.1rem; font-weight: 700;">Detail Kartu Keluarga</h2>
                        </div>
                    </div>
                    <button type="button" class="dialog__close" data-modal-close
                        aria-label="Tutup detail KK {{ $keluarga->no_kk }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body" style="background: #f8fafc; padding: 16px;">
                    <div class="family-detail-wrapper">
                        {{-- Row 1, Left: Head Info --}}
                        <div class="detail-card">
                            <div class="detail-card__header">
                                <i class="fas fa-user-tie"></i>
                                <h3>Data Kepala Keluarga</h3>
                            </div>

                            @php
                                $kepala = $keluarga->penduduks->firstWhere('kk_level_id', 1);
                                if (!$kepala) {
                                    $kepala = $keluarga->penduduks->first();
                                }
                            @endphp

                            <div class="head-profile-section">
                                <div class="head-photo-container">
                                    @if($kepala && $kepala->foto_profil)
                                        <a href="javascript:void(0)"
                                            onclick="openImagePreview('{{ asset('storage/' . $kepala->foto_profil) }}')"
                                            title="Buka foto profil"
                                            style="display: block; width: 100%; height: 100%; cursor: pointer;">
                                            <img src="{{ asset('storage/' . $kepala->foto_profil) }}" alt="Foto {{ $kepala->nama }}"
                                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                                onmouseover="this.style.transform='scale(1.05)'"
                                                onmouseout="this.style.transform='scale(1)'">
                                        </a>
                                    @else
                                        <div class="head-placeholder">
                                            <i class="fas fa-user" style="font-size: 3rem; opacity: 0.3;"></i>
                                            <span style="font-size: 0.75rem; font-weight: 600;">Foto Tidak Ada</span>
                                        </div>
                                    @endif
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div>
                                        <h4 style="margin: 0 0 4px; font-size: 1.15rem; color: #1e293b;">
                                            {{ $kepala?->nama ?: ($keluarga->kepala_nama ?: 'Belum terdata') }}</h4>
                                        <span
                                            style="font-family: monospace; font-size: 0.95rem; color: #2563eb; font-weight: 700;">NIK
                                            {{ $kepala?->nik ?: ($keluarga->kepala_nik ?: '-') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-item__label">Alamat Lengkap</span>
                                        <div class="info-item__value"
                                            style="font-size: 0.88rem; color: #475569; font-weight: 500; line-height: 1.4;">
                                            {{ $keluarga->alamat ?: 'Alamat belum diisi.' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-item__label">Nomor KK</span>
                                    <span class="info-item__value" style="color: #2563eb;">{{ $keluarga->no_kk }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Jumlah Anggota</span>
                                    <span class="info-item__value">{{ $keluarga->penduduks_count }} Orang</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Nomor WhatsApp</span>
                                    <span class="info-item__value">{{ $kepala?->nomor_hp ?: '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Email</span>
                                    <span class="info-item__value">{{ $kepala?->email ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Row 1, Right: Region Info --}}
                        <div class="detail-card">
                            <div class="detail-card__header">
                                <i class="fas fa-map-marked-alt"></i>
                                <h3>Informasi Wilayah</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-item__label">Dusun</span>
                                    <span class="info-item__value">{{ $keluarga->dusun?->nama ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">RW / RT</span>
                                    <span class="info-item__value">RW {{ $keluarga->rw?->nomor ?? '-' }} &middot; RT
                                        {{ $keluarga->rt?->nomor ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Kabupaten</span>
                                    <span class="info-item__value">LAMPUNG TIMUR</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-item__label">Terakhir Diperbarui</span>
                                    <span class="info-item__value"
                                        style="font-size: 0.85rem; color: #64748b;">{{ $keluarga->updated_at?->format('d M Y, H:i') ?: '-' }}</span>
                                </div>
                            </div>

                            <!-- Berkas Kartu Keluarga Box -->
                            <div
                                style="margin: 20px 0; padding: 15px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; display: block;">
                                <div
                                    style="font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: none; letter-spacing: normal; margin-bottom: 8px;">
                                    Berkas Kartu Keluarga</div>
                                @if($kepala && $kepala->foto_kk)
                                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; cursor: pointer;"
                                        onclick="openImagePreview('{{ asset('storage/' . $kepala->foto_kk) }}')">
                                        <div
                                            style="width: 44px; height: 44px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                            <img src="{{ asset('storage/' . $kepala->foto_kk) }}" alt="Foto KK"
                                                style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; font-size: 0.9rem; color: #1e293b;">Lihat Pindai KK</div>
                                            <div
                                                style="font-size: 0.75rem; color: #2563eb; display: flex; align-items: center; gap: 4px;">
                                                <span>Buka pratinjau</span>
                                                <i class="fas fa-external-link-alt" style="font-size: 0.65rem;"></i>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div
                                        style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(241, 245, 249, 0.5); border: 1px dashed #cbd5e1; border-radius: 8px;">
                                        <div
                                            style="width: 44px; height: 44px; background: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #cbd5e1; border: 1px solid #e2e8f0;">
                                            <i class="fas fa-image" style="font-size: 1.2rem;"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="font-weight: 600; font-size: 0.85rem; color: #94a3b8;">Belum diunggah</div>
                                            <div style="font-size: 0.7rem; color: #cbd5e1;">Berkas KK tidak tersedia</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div
                                style="margin-top: auto; padding-top: 20px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; gap: 10px; color: #64748b; font-size: 0.82rem;">
                                <i class="fas fa-info-circle"></i>
                                <span>Data disinkronkan dengan basis data kependudukan desa.</span>
                            </div>
                        </div>

                        {{-- Row 2: Member Table (Full Width) --}}
                        <div class="detail-card family-detail-full">
                            <div class="detail-card__header" style="margin-bottom: 20px;">
                                <i class="fas fa-users"></i>
                                <h3>Detail Anggota Keluarga</h3>
                            </div>

                            <div class="member-table-wrapper">
                                <table class="member-table">
                                    <thead>
                                        <tr>
                                            <th>Nama / NIK</th>
                                            <th>No. KK</th>
                                            <th>Hubungan</th>
                                            <th>Kontak</th>
                                            <th>Status Dasar</th>
                                            <th>Rekam KTP-el</th>
                                            <th>KTP-el</th>
                                            <th>ID Asuransi</th>
                                            <th>No Asuransi</th>
                                            <th>Foto Dokumen</th>
                                            <th>JK</th>
                                            <th>Agama</th>
                                            <th>Status Perkawinan</th>
                                            <th>Tempat, Tgl Lahir</th>
                                            <th>Pendidikan (KK)</th>
                                            <th>Pend. Sedang Ditempuh</th>
                                            <th>Pekerjaan</th>
                                            <th>Warganegara</th>
                                            <th>Gol. Darah</th>
                                            <th>Suku</th>
                                            <th>Alamat KK</th>
                                            <th>Alamat Saat Ini</th>
                                            <th>Wilayah (Dusun/RW/RT)</th>
                                            <th>Cara KB</th>
                                            <th>Disabilitas</th>
                                            <th>Hamil</th>
                                            <th>Akta Lahir</th>
                                            <th>Dok. Paspor</th>
                                            <th>Berlaku Paspor</th>
                                            <th>Dok. KITAS</th>
                                            <th>Akta Perkawinan</th>
                                            <th>Tgl Perkawinan</th>
                                            <th>Akta Perceraian</th>
                                            <th>Tgl Perceraian</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($keluarga->penduduks as $member)
                                            @php
                                                $roleClass = '';
                                                $roleName = $member->kkLevel?->nama ?? 'Anggota';
                                                if (str_contains(strtolower($roleName), 'kepala'))
                                                    $roleClass = 'badge-role--head';
                                                elseif (str_contains(strtolower($roleName), 'istri') || str_contains(strtolower($roleName), 'suami'))
                                                    $roleClass = 'badge-role--spouse';
                                                elseif (str_contains(strtolower($roleName), 'anak'))
                                                    $roleClass = 'badge-role--child';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="member-profile">
                                                        <div class="member-avatar">
                                                            @if($member->foto_profil)
                                                                <a href="javascript:void(0)"
                                                                    onclick="openImagePreview('{{ asset('storage/' . $member->foto_profil) }}')"
                                                                    title="Buka foto profil"
                                                                    style="display: block; width: 100%; height: 100%;">
                                                                    <img src="{{ asset('storage/' . $member->foto_profil) }}"
                                                                        alt="{{ $member->nama }}"
                                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                                </a>
                                                            @else
                                                                <div
                                                                    style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8; font-size: 0.75rem;">
                                                                    <i class="fas fa-user"></i>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="member-info">
                                                            <span class="member-name">{{ $member->nama }}</span>
                                                            <span class="member-nik"
                                                                style="font-size: 0.65rem;">{{ $member->nik }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="white-space: nowrap;">{{ $member->no_kk ?: '-' }}</td>
                                                <td><span class="badge-role {{ $roleClass }}">{{ $roleName }}</span></td>
                                                <td style="white-space: nowrap;">
                                                    <div style="font-weight: 600;">{{ $member->nomor_hp ?: '-' }}</div>
                                                    <div style="font-size: 0.72rem; color: #94a3b8;">{{ $member->email ?: '-' }}
                                                    </div>
                                                </td>
                                                <td style="white-space: nowrap;">{{ $member->statusDasar?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->statusRekam?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->ktp_el ? 'KTP-el' : '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->id_asuransi ?: '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->no_asuransi ?: '-' }}</td>
                                                <td style="white-space: nowrap;">
                                                    <div style="display: flex; gap: 8px; align-items: center;">
                                                        @if($member->foto_ktp)
                                                            <a href="javascript:void(0)"
                                                                onclick="openImagePreview('{{ asset('storage/' . $member->foto_ktp) }}')"
                                                                title="Buka foto KTP"
                                                                style="display: block; width: 40px; height: 28px; border-radius: 4px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s;"
                                                                onmouseover="this.style.transform='scale(1.1)'"
                                                                onmouseout="this.style.transform='scale(1)'">
                                                                <img src="{{ asset('storage/' . $member->foto_ktp) }}"
                                                                    alt="KTP {{ $member->nama }}"
                                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                                            </a>
                                                        @else
                                                            <div
                                                                style="width: 40px; height: 28px; border-radius: 4px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; color: #94a3b8; border: 1px dashed #cbd5e1; text-transform: uppercase;">
                                                                KTP</div>
                                                        @endif

                                                        @if($member->foto_kk)
                                                            <a href="javascript:void(0)"
                                                                onclick="openImagePreview('{{ asset('storage/' . $member->foto_kk) }}')"
                                                                title="Buka foto KK"
                                                                style="display: block; width: 40px; height: 28px; border-radius: 4px; overflow: hidden; border: 1px solid #cbd5e1; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s;"
                                                                onmouseover="this.style.transform='scale(1.1)'"
                                                                onmouseout="this.style.transform='scale(1)'">
                                                                <img src="{{ asset('storage/' . $member->foto_kk) }}"
                                                                    alt="KK {{ $member->nama }}"
                                                                    style="width: 100%; height: 100%; object-fit: cover;">
                                                            </a>
                                                        @else
                                                            <div
                                                                style="width: 40px; height: 28px; border-radius: 4px; background: #f8fafc; display: flex; align-items: center; justify-content: center; font-size: 0.55rem; color: #94a3b8; border: 1px dashed #cbd5e1; text-transform: uppercase;">
                                                                KK</div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $member->jenisKelamin?->nama === 'LAKI-LAKI' ? 'L' : 'P' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->agama?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->statusKawin?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">
                                                    <div>{{ $member->tempat_lahir ?: '-' }}</div>
                                                    <div style="font-size: 0.72rem; color: #94a3b8;">
                                                        {{ $member->tanggal_lahir?->format('d/m/Y') ?: '-' }}</div>
                                                </td>
                                                <td style="white-space: nowrap;" title="{{ $member->pendidikanKk?->nama }}">
                                                    {{ \Illuminate\Support\Str::limit($member->pendidikanKk?->nama ?? '-', 20) }}
                                                </td>
                                                <td style="white-space: nowrap;" title="{{ $member->pendidikanSedang?->nama }}">
                                                    {{ \Illuminate\Support\Str::limit($member->pendidikanSedang?->nama ?? '-', 20) }}
                                                </td>
                                                <td style="white-space: nowrap;" title="{{ $member->pekerjaan?->nama }}">
                                                    {{ \Illuminate\Support\Str::limit($member->pekerjaan?->nama ?? '-', 20) }}</td>
                                                <td style="white-space: nowrap;">{{ $member->warganegara?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->golonganDarah?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->suku?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;" title="{{ $keluarga->alamat }}">
                                                    {{ $keluarga->alamat ?: '-' }}</td>
                                                <td style="white-space: nowrap;" title="{{ $member->alamat_sekarang }}">
                                                    {{ $member->alamat_sekarang ?: '-' }}</td>
                                                <td style="white-space: nowrap;">Dusun {{ $keluarga->dusun?->nama ?? '-' }} - RT
                                                    {{ $keluarga->rt?->nomor ?? '-' }}/RW {{ $keluarga->rw?->nomor ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->caraKb?->nama ?? '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->cacat?->nama ?? '-' }}</td>
                                                <td>{{ $member->hamil ? 'Ya' : 'Tidak' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->akta_lahir ?: '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->dokumen_pasport ?: '-' }}</td>
                                                <td style="white-space: nowrap;">
                                                    {{ $member->tanggal_akhir_paspor?->format('d/m/Y') ?: '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->dokumen_kitas ?: '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->akta_perkawinan ?: '-' }}</td>
                                                <td style="white-space: nowrap;">
                                                    {{ $member->tanggal_perkawinan?->format('d/m/Y') ?: '-' }}</td>
                                                <td style="white-space: nowrap;">{{ $member->akta_perceraian ?: '-' }}</td>
                                                <td style="white-space: nowrap;">
                                                    {{ $member->tanggal_perceraian?->format('d/m/Y') ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="34" style="text-align: center; color: #94a3b8; padding: 30px;">
                                                    <i class="fas fa-user-slash"
                                                        style="display: block; font-size: 1.5rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                                    Belum ada anggota terdaftar.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="dialog__footer"
                    style="padding: 12px 24px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                    <a href="{{ route('admin.keluargas.print', $keluarga) }}" target="_blank" class="primary-btn"
                        style="background: #ef4444; border-color: #ef4444; color: #ffffff; font-weight: 600; padding: 7px 15px; font-size: 0.8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: 50px; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.25);"
                        onmouseover="this.style.background='#dc2626'; this.style.borderColor='#dc2626'; this.style.transform='translateY(-1px)';"
                        onmouseout="this.style.background='#ef4444'; this.style.borderColor='#ef4444'; this.style.transform='translateY(0)';">
                        <i class="fas fa-print" style="font-size: 0.75rem;"></i>
                        <span>Cetak Detail (PDF)</span>
                    </a>
                    <button type="button" class="primary-btn" data-modal-open="familyEditModal-{{ $keluarga->id }}"
                        style="font-weight: 600; padding: 7px 15px; font-size: 0.8rem; border-radius: 50px; transition: all 0.2s ease; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);"
                        onmouseover="this.style.transform='translateY(-1px)';"
                        onmouseout="this.style.transform='translateY(0)';">
                        <i class="fas fa-pen" style="font-size: 0.75rem;"></i>
                        <span>Edit Data</span>
                    </button>
                </footer>
            </div>
        </div>

        <div class="dialog-backdrop" id="familyEditModal-{{ $keluarga->id }}" aria-hidden="true">
            <div class="dialog dialog--form" role="dialog" aria-modal="true"
                aria-labelledby="familyEditTitle-{{ $keluarga->id }}">
                <header class="dialog__header">
                    <h2 id="familyEditTitle-{{ $keluarga->id }}">Edit KK {{ $keluarga->no_kk }}</h2>
                    <button type="button" class="dialog__close" data-modal-close
                        aria-label="Tutup form edit KK {{ $keluarga->no_kk }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.keluargas.update', $keluarga) }}" class="dialog__form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="form_context" value="familyEditModal-{{ $keluarga->id }}">
                    <div class="dialog__body">
                        <div class="form-grid--2 mb-4">
                            <label class="form-field @if($editContext && $errors->has('no_kk')) form-field--error @endif">
                                <span>Nomor KK <sup>*</sup></span>
                                <input type="text" name="no_kk"
                                    value="{{ $editContext ? old('no_kk', $keluarga->no_kk) : $keluarga->no_kk }}"
                                    maxlength="30" required>
                                @if ($editContext)
                                    @error('no_kk')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field @if($editContext && $errors->has('kepala_nik')) form-field--error @endif">
                                <span>NIK Kepala Keluarga</span>
                                <input type="text" name="kepala_nik"
                                    value="{{ $editContext ? old('kepala_nik', $keluarga->kepala_nik) : $keluarga->kepala_nik }}"
                                    maxlength="20">
                                @if ($editContext)
                                    @error('kepala_nik')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                        </div>
                        <div class="mb-4">
                            <label class="form-field form-field--full">
                                <span>Alamat Lengkap</span>
                                <textarea name="alamat"
                                    rows="2">{{ $editContext ? old('alamat', $keluarga->alamat) : $keluarga->alamat }}</textarea>
                            </label>
                        </div>
                        <div class="form-grid--3">
                            <label class="form-field">
                                <span>Dusun</span>
                                <select name="dusun_id">
                                    <option value="">Pilih dusun</option>
                                    @foreach ($dusuns as $dusun)
                                        <option value="{{ $dusun->id }}" @selected(($editContext ? old('dusun_id', $keluarga->dusun_id) : $keluarga->dusun_id) == $dusun->id)>
                                            {{ $dusun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field">
                                <span>RW</span>
                                <select name="rw_id">
                                    <option value="">Pilih RW</option>
                                    @foreach ($rws as $rw)
                                        <option value="{{ $rw->id }}" @selected(($editContext ? old('rw_id', $keluarga->rw_id) : $keluarga->rw_id) == $rw->id)>
                                            RW {{ $rw->nomor }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field">
                                <span>RT</span>
                                <select name="rt_id">
                                    <option value="">Pilih RT</option>
                                    @foreach ($rts as $rt)
                                        <option value="{{ $rt->id }}" @selected(($editContext ? old('rt_id', $keluarga->rt_id) : $keluarga->rt_id) == $rt->id)>
                                            RT {{ $rt->nomor }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                    <footer class="dialog__footer">
                        <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">
                            <i class="fas fa-save"></i>
                            <span>Perbarui</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>

        <div class="dialog-backdrop" id="familyDeleteModal-{{ $keluarga->id }}" aria-hidden="true">
            <div class="dialog dialog--confirm" role="dialog" aria-modal="true"
                aria-labelledby="familyDeleteTitle-{{ $keluarga->id }}">
                <header class="dialog__header">
                    <h2 id="familyDeleteTitle-{{ $keluarga->id }}">Hapus KK {{ $keluarga->no_kk }}</h2>
                    <button type="button" class="dialog__close" data-modal-close
                        aria-label="Tutup konfirmasi hapus {{ $keluarga->no_kk }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <p>Anda yakin ingin menghapus data keluarga dengan nomor KK <strong>{{ $keluarga->no_kk }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <footer class="dialog__footer dialog__footer--split">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <form method="POST" action="{{ route('admin.keluargas.destroy', $keluarga) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                        <input type="hidden" name="form_context" value="familyDeleteModal-{{ $keluarga->id }}">
                        <button type="submit" class="danger-btn">
                            <i class="fas fa-trash"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </footer>
            </div>
        </div>
    @endforeach

    <!-- Image Preview Lightbox -->
    <div id="imagePreviewModal"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;">
        <div style="position: relative; max-width: 90vw; max-height: 90vh;">
            <button type="button" onclick="closeImagePreview()"
                style="position: absolute; right: -15px; top: -40px; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; text-shadow: 0 2px 4px rgba(0,0,0,0.5); z-index: 100000;">
                <i class="fas fa-times"></i>
            </button>
            <img id="imagePreviewElement" src="" alt="Pratinjau Dokumen"
                style="max-height: 80vh; max-width: 100%; object-fit: contain; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            <div style="text-align: center; margin-top: 15px;">
                <a id="imagePreviewDownload" href="#" download
                    style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(255,255,255,0.9); color: #1e293b; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 0.85rem; font-family: 'Poppins', sans-serif;">
                    <i class="fas fa-download"></i> Unduh Gambar
                </a>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        window.openImagePreview = function (url) {
            const modal = document.getElementById('imagePreviewModal');
            const img = document.getElementById('imagePreviewElement');
            const downloadBtn = document.getElementById('imagePreviewDownload');
            if (modal && img) {
                img.src = url;
                downloadBtn.href = url;
                modal.style.opacity = '1';
                modal.style.pointerEvents = 'auto';
                document.addEventListener('keydown', window.handleEscKey);
            }
        };

        window.closeImagePreview = function () {
            const modal = document.getElementById('imagePreviewModal');
            if (modal) {
                modal.style.opacity = '0';
                modal.style.pointerEvents = 'none';
                document.removeEventListener('keydown', window.handleEscKey);
                setTimeout(() => {
                    document.getElementById('imagePreviewElement').src = '';
                }, 300);
            }
        };

        window.handleEscKey = function (e) {
            if (e.key === 'Escape') {
                window.closeImagePreview();
            }
        };

        // Click outside image to close
        document.getElementById('imagePreviewModal').addEventListener('click', function (e) {
            if (e.target === this) {
                window.closeImagePreview();
            }
        });
    </script>
@endpush