@extends('admin.layouts.app')

@section('title', 'Riwayat Penduduk Pindah')

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

        /* Table Alignment Refinement */
        .table-budget th,
        .table-budget td {
            text-align: left !important;
            padding-left: 20px !important;
            padding-right: 20px !important;
            vertical-align: middle !important;
        }

        .table-budget th:first-child,
        .table-budget td:first-child {
            padding-left: 20px !important;
        }

        /* Daftar Penduduk Header Layout: title left, search right */
        .resident-panel__header {
            display: block !important;
            background: #fff !important;
            width: 100% !important;
        }

        #residentPanelHeader {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
        }

        .resident-panel__title {
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }

        .resident-panel__title h2 {
            margin: 0 !important;
            line-height: 1.2 !important;
            font-size: 1.25rem !important;
            font-weight: 700 !important;
        }

        .resident-panel__title p {
            margin: 6px 0 0 !important;
            font-size: 0.88rem !important;
            color: #64748b !important;
        }

        #residentSearchForm {
            margin-left: auto !important;
            flex: 0 0 auto !important;
            display: flex !important;
            align-items: center !important;
        }

        #residentSearchForm .search-input-wrapper {
            min-width: 220px !important;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #residentPanelHeader {
                flex-wrap: wrap !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
            }

            #residentPanelHeader .resident-panel__title {
                flex: 1 1 100% !important;
                order: 1 !important;
            }

            #residentSearchForm,
            #residentSearchForm:focus-within {
                margin-left: 0 !important;
                flex: 1 1 100% !important;
                width: 100% !important;
                min-width: 100% !important;
                order: 2 !important;
            }

            #residentSearchForm .search-input-group,
            #residentSearchForm .search-input-group:focus-within {
                width: 100% !important;
                min-width: 100% !important;
                flex: 1 1 100% !important;
                box-sizing: border-box !important;
            }

            #residentSearchForm .search-input-wrapper,
            #residentSearchForm .search-input-wrapper:focus-within {
                width: 100% !important;
                min-width: 100% !important;
                flex: 1 1 100% !important;
            }
        }

        /* Custom Styles for Moved Residents Table */
        .dest-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
            max-width: 280px;
        }

        .dest-address {
            color: #1e293b;
            font-weight: 700;
            font-size: 0.88rem;
            line-height: 1.3;
        }

        .dest-meta {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .origin-cell {
            color: #475569;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .origin-cell .meta-sub {
            display: block;
            margin-top: 2px;
            color: #64748b;
            font-weight: 500;
        }

        /* Donut Chart Animation Fix */
        .gender-chart__donut {
            background: conic-gradient(var(--gender-gradient));
        }

        /* Action Dropdown Fix */
        .action-dropdown {
            position: relative;
            display: inline-block;
        }

        .action-dropdown__menu {
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
            transform-origin: top right;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .action-dropdown__menu[hidden] {
            display: none;
        }

        .action-dropdown__item {
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
            text-decoration: none;
        }

        .action-dropdown__item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .action-dropdown__item--detail:hover {
            color: #2563eb;
            background: rgba(37, 99, 235, 0.05);
        }

        .action-dropdown__item--edit:hover {
            color: #d97706;
            background: rgba(217, 119, 6, 0.05);
        }

        .action-dropdown__item--delete:hover {
            color: #dc2626;
            background: rgba(220, 38, 38, 0.05);
        }

        .action-dropdown hr {
            margin: 6px 0;
            border: none;
            border-top: 1px solid #f1f5f9;
        }

        /* Standard Premium Action Dropdown Alignment (Sync with other modules) */
        .resident-actions {
            position: relative;
            display: flex;
            justify-content: flex-start;
        }

        .resident-action-dropdown {
            position: relative;
        }

        .resident-action-dropdown[open] .action-button--dots {
            background-color: var(--primary-light, #f1f5f9);
            color: var(--primary-color, #6366f1);
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
            /* Increased z-index to stay above scrollbars and panels */
            padding: 8px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-top: 8px;
            transform-origin: top right;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Upward Opening Menu (Dropup) */
        .resident-action-dropdown.is-upward .resident-action-menu {
            top: auto !important;
            bottom: 100% !important;
            margin-top: 0 !important;
            margin-bottom: 10px !important;
            transform-origin: bottom right !important;
            box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.15), 0 -8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        }

        /* Prevent clipping from table containers on desktop */
        @media (min-width: 1025px) {

            .news-table-wrap,
            .news-table,
            .agenda-panel,
            .resident-panel,
            .panel--flush {
                overflow: visible !important;
            }

            .table-budget,
            .table-budget td {
                overflow: visible !important;
            }
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

        /* ── Premium Modal Transitions ─────────────────── */
        .dialog-backdrop {
            display: flex !important;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.4s;
        }

        .dialog-backdrop.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .dialog-backdrop[aria-hidden="false"] {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .dialog {
            transform: scale(0.92) translateY(30px);
            opacity: 0;
            transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s ease;
        }

        .dialog-backdrop.is-visible .dialog,
        .dialog-backdrop[aria-hidden="false"] .dialog {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* ── Premium Modal Header with Icon ───────────── */
        .dialog__header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 24px 28px 16px;
        }

        .dialog__header-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.12), rgba(99, 102, 241, 0.06));
            color: #6366f1;
            border: 1px solid rgba(99, 102, 241, 0.15);
        }

        .dialog__header-icon--info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.12), rgba(59, 130, 246, 0.06));
            color: #3b82f6;
            border-color: rgba(59, 130, 246, 0.15);
        }

        .dialog__header-icon--warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.06));
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.15);
        }

        .dialog__header-icon--danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(239, 68, 68, 0.06));
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.15);
        }

        .dialog__header-text {
            flex: 1;
            min-width: 0;
        }

        .dialog__header-text h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
        }

        .dialog__header-text p {
            margin: 4px 0 0;
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 400;
        }

        .dialog__close {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(148, 163, 184, 0.08);
            border: none;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
            font-size: 1rem;
        }

        .dialog__close:hover {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
            transform: rotate(90deg);
        }

        /* ── Modern Info Grid for Details ─────────────────── */
        .detail-grid {
            display: grid;
            gap: 24px;
        }

        .detail-section {
            border-radius: 16px;
            background: rgba(248, 250, 252, 0.5);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 20px;
        }

        .detail-section__title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }

        .detail-section__title i {
            color: #3b82f6;
            font-size: 0.9rem;
        }

        .detail-items-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px 24px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .detail-item--full {
            grid-column: 1 / -1;
        }

        .detail-item__label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.78rem;
            font-weight: 600;
            color: #94a3b8;
        }

        .detail-item__label i {
            font-size: 0.8rem;
            width: 14px;
        }

        .detail-item__value {
            font-size: 0.92rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.5;
            word-break: break-word;
        }

        .detail-item__value--highlight {
            color: #2563eb;
            font-weight: 700;
        }

        .detail-item__value--long {
            color: #475569;
            font-weight: 500;
            background: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-top: 2px;
        }

        @media (max-width: 640px) {
            .detail-items-list {
                grid-template-columns: 1fr;
            }

            .detail-section {
                padding: 16px;
            }
        }

        /* ── Premium Form Fields ─────────────────────── */
        .dialog__body .form-field input,
        .dialog__body .form-field select,
        .dialog__body .form-field textarea {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 0.92rem;
            transition: all 0.2s ease;
            background: #fff;
        }

        .dialog__body .form-field input:focus,
        .dialog__body .form-field select:focus,
        .dialog__body .form-field textarea:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .dialog__body .form-field input::placeholder,
        .dialog__body .form-field textarea::placeholder {
            color: #cbd5e1;
            font-weight: 400;
        }

        .dialog__body .form-field span {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
        }

        .dialog__body .form-field span sup {
            color: #ef4444;
        }

        /* ── Premium Footer Buttons ───────────────────── */
        .dialog__footer .ghost-btn {
            padding: 10px 24px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            color: #475569;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .dialog__footer .ghost-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #1e293b;
        }

        .dialog__footer .primary-btn {
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .dialog__footer .primary-btn:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
        }

        .dialog__footer .danger-btn {
            padding: 10px 24px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .dialog__footer .danger-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            transform: translateY(-1px);
        }

        @media (max-width: 640px) {
            .detail-list__item {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }
    </style>
@endpush

@php
    use Illuminate\Support\Str;

    $formatPercentage = function ($value, int $decimals = 1): string {
        $numeric = is_numeric($value) ? (float) $value : 0;
        return number_format($numeric, $decimals) . '%';
    };

    $metricsOverview = $metrics['overview'] ?? null;
    $metricsGender = $metrics['gender'] ?? null;
    $metricsReasons = $metrics['reasons'] ?? null;
    $metricsDestinations = $metrics['destinations'] ?? null;

    $maleCount = 0;
    $femaleCount = 0;
    if ($metricsGender && !empty($metricsGender['items'])) {
        foreach ($metricsGender['items'] as $item) {
            if (str_contains(strtolower($item['label']), 'laki'))
                $maleCount = $item['value'];
            if (str_contains(strtolower($item['label']), 'perempuan'))
                $femaleCount = $item['value'];
        }
    }

    $hasStats = $metricsOverview && (!empty($metricsGender['items']) || !empty($metricsReasons['items']));
@endphp

@section('content')
    <header class="main-header">
        <div class="header-controls">
            <div class="header-cluster header-cluster-left">
                <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="header-icon sidebar-trigger" id="sidebarExpandBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <span class="header-title-text">Pengelolaan Penduduk Pindah</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Kependudukan</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.penduduk-pindah.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Penduduk Pindah</a>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Riwayat Penduduk Pindah</h1>
            <p>Monitor dan kelola data penduduk yang telah pindah keluar wilayah desa.</p>
        </div>
        <div class="title-actions" id="titleActionsPindah">
            <div class="header-actions-group">
                <div class="export-dropdown export-dropdown--bottom-sheet">
                    <button type="button" class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                        id="exportDropdownTogglePindah" aria-haspopup="true" aria-expanded="false"
                        aria-controls="exportDropdownMenuPindah">
                        <i class="fas fa-file-export" aria-hidden="true"></i>
                        <span>Ekspor</span>
                        <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="export-dropdown__menu" id="exportDropdownMenuPindah" role="menu" hidden>
                        <div class="export-dropdown__header">Pilih Ekspor</div>
                        <div class="export-dropdown__grid">
                            <a href="{{ route('admin.penduduk-pindah.export.excel') }}" class="export-dropdown__item"
                                role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download Excel</strong>
                                    <small>Unduh format spreadsheet</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.penduduk-pindah.export.pdf') }}" class="export-dropdown__item"
                                role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                    <i class="fas fa-file-pdf"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download PDF</strong>
                                    <small>Versi siap cetak</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.penduduk-pindah.export.word') }}" class="export-dropdown__item"
                                role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--blue" aria-hidden="true">
                                    <i class="fas fa-file-word"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download Word</strong>
                                    <small>Format dokumen</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="primary-btn" data-modal-open="migrantCreateModal">
                <i class="fas fa-plus"></i>
                <span>Catat Perpindahan</span>
            </button>
        </div>
    </section>

    <section class="agenda-summary-grid">
        <article class="agenda-summary-card">
            <i class="fas fa-walking agenda-summary-icon"></i>
            <h3>Total Pindah</h3>
            <strong>{{ number_format($metricsOverview['total'] ?? 0) }}</strong>
            <span>Seluruh Riwayat Kepindahan</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--info">
            <i class="fas fa-calendar-check agenda-summary-icon"></i>
            <h3>Bulan Ini</h3>
            <strong>{{ number_format($metricsOverview['this_month'] ?? 0) }}</strong>
            <span>
                @if(($metricsOverview['growth'] ?? 0) > 0)
                    <i class="fas fa-arrow-up"></i> {{ $metricsOverview['growth'] }}% dibanding bln lalu
                @elseif(($metricsOverview['growth'] ?? 0) < 0)
                    <i class="fas fa-arrow-down"></i> {{ abs($metricsOverview['growth']) }}% dibanding bln lalu
                @else
                    Tetap dibanding bln lalu
                @endif
            </span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--warning">
            <i class="fas fa-history agenda-summary-icon"></i>
            <h3>Tahun Ini</h3>
            <strong>{{ number_format($metricsOverview['this_year'] ?? 0) }}</strong>
            <span>Akumulasi Tahun {{ now()->year }}</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--info">
            <i class="fas fa-mars agenda-summary-icon"></i>
            <h3>Laki-laki</h3>
            <strong>{{ number_format($maleCount) }}</strong>
            <span>Total penduduk laki-laki pindah</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--danger">
            <i class="fas fa-venus agenda-summary-icon"></i>
            <h3>Perempuan</h3>
            <strong>{{ number_format($femaleCount) }}</strong>
            <span>Total penduduk perempuan pindah</span>
        </article>
    </section>

    <article class="panel panel--flush agenda-panel resident-panel">
        <header class="panel-header panel-header--table agenda-panel__header resident-panel__header">
            <div id="residentPanelHeader" class="resident-panel__title-row resident-panel__title-row--inline">
                <div class="resident-panel__title">
                    <h2>Daftar Riwayat Pindah</h2>
                    <p>Manajemen data perpindahan penduduk keluar wilayah administratif desa.</p>
                </div>
                <form id="residentSearchForm" method="GET" action="{{ route('admin.penduduk-pindah.index') }}"
                    class="panel-toolbar-inline">
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon-left"></i>
                            <input id="searchPindah" type="search" name="search" value="{{ $search }}"
                                placeholder="Cari NIK atau Nama..." autocomplete="off" aria-label="Cari data pindah">
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
                            <th style="min-width: 280px; text-align: left;">Penduduk</th>
                            <th style="min-width: 260px; text-align: left;">Alasan</th>
                            <th style="min-width: 260px; text-align: left;">Tujuan Pindah</th>
                            <th style="width: 130px; text-align: center;">Jenis Kelamin</th>
                            <th style="width: 180px; text-align: left;">Wilayah Asal</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $startIndex = ($records->currentPage() - 1) * $records->perPage(); @endphp
                        @forelse ($records as $index => $record)
                                            @php
                                                $resident = $record->penduduk;
                                                $rowNumber = $startIndex + $index + 1;
                                                $birthInfo = $resident
                                                    ? trim(($resident->tempat_lahir ? $resident->tempat_lahir . ', ' : '') . ($resident->tanggal_lahir?->translatedFormat('d M Y') ?? ''))
                                                    : 'Data tidak tersedia';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="resident-table__index">{{ $rowNumber }}</span>
                                                </td>
                                                <td>
                                                    <div class="resident-table__person">
                                                        <div class="resident-avatar">
                                                            @if ($resident && $resident->foto_profil)
                                                                <img src="{{ asset('storage/' . ltrim($resident->foto_profil, '/')) }}"
                                                                    alt="Foto {{ $resident->nama }}" loading="lazy"
                                                                    style="width: 100%; height: 100%; border-radius: 14px; object-fit: cover;">
                                                            @else
                                                                <span
                                                                    class="resident-avatar__placeholder">{{ $resident ? Str::upper(Str::substr($resident->nama, 0, 1)) : '?' }}</span>
                                                            @endif
                                                        </div>
                                                        <div class="resident-table__person-meta">
                                                            <strong>{{ $resident->nama ?? 'Penduduk Terhapus' }}</strong>
                                                            <p
                                                                style="margin: 2px 0 0; font-family: monospace; font-size: 0.8rem; color: var(--text-secondary); letter-spacing: 0.02em;">
                                                                {{ $resident->nik ?? '-' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="line-height: 1.3;">
                                                        <strong>{{ $record->alasan_pindah ?? 'Lainnya' }}</strong>
                                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary);">
                                                            {{ $record->tanggal_pindah?->translatedFormat('d M Y') ?? '-' }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div style="line-height: 1.3;">
                                                        <strong>{{ $record->alamat_tujuan ?? '-' }}</strong>
                                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary);">
                                                            {{ collect([$record->desa_tujuan, $record->kecamatan_tujuan, $record->kabupaten_tujuan, $record->provinsi_tujuan])->filter()->join(', ') }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $genderRaw = $resident ? Str::lower($resident->jenisKelamin?->nama ?? '') : '';
                                                        $genderClass = $genderRaw === 'perempuan' ? 'gender-pill--female' : ($genderRaw === 'laki-laki' ? 'gender-pill--male' : 'gender-pill--neutral');
                                                        $genderLabel = $resident?->jenisKelamin?->nama ?? '-';
                                                        $genderIcon = $genderRaw === 'perempuan' ? 'fa-venus' : ($genderRaw === 'laki-laki' ? 'fa-mars' : 'fa-user');
                                                    @endphp
                                                    <span class="gender-pill {{ $genderClass }}">
                                                        <i class="fas {{ $genderIcon }}" aria-hidden="true"></i>
                                                        <span>{{ $genderLabel }}</span>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div style="line-height: 1.3;">
                                                        <strong>{{ $resident?->dusun?->nama ?? '-' }}</strong>
                                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary);">
                                                            RW {{ $resident?->rw?->nomor ?? '-' }} | RT {{ $resident?->rt?->nomor ?? '-' }}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="resident-actions">
                                                        <details class="resident-action-dropdown" data-action-menu>
                                                            <summary class="action-button action-button--dots" aria-haspopup="menu"
                                                                aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </summary>
                                                            <div class="resident-action-menu" role="menu">
                                                                <button type="button" class="resident-action-item resident-action-item--detail"
                                                                    data-modal-open="migrantDetailModal" data-pindah-detail="{{ json_encode([
                                'nama' => $resident->nama ?? 'Penduduk Terhapus',
                                'nik' => $resident->nik ?? '-',
                                'foto' => $resident?->foto_profil ? asset('storage/' . ltrim($resident->foto_profil, '/')) : null,
                                'tanggal' => $record->tanggal_pindah?->translatedFormat('d M Y'),
                                'alasan' => $record->alasan_pindah,
                                'tujuan' => $record->alamat_tujuan,
                                'wilayah_tujuan' => collect([$record->desa_tujuan, $record->kecamatan_tujuan, $record->kabupaten_tujuan, $record->provinsi_tujuan])->filter()->join(', '),
                                'keterangan' => $record->keterangan ?? '-'
                            ]) }}">
                                                                    <i class="fas fa-eye"></i><span>Detail</span>
                                                                </button>
                                                                <button type="button" class="resident-action-item resident-action-item--edit"
                                                                    data-modal-open="migrantEditModal-{{ $record->id }}">
                                                                    <i class="fas fa-pen"></i><span>Edit</span>
                                                                </button>
                                                                <button type="button" class="resident-action-item resident-action-item--danger"
                                                                    data-modal-open="migrantDeleteModal-{{ $record->id }}">
                                                                    <i class="fas fa-trash"></i><span>Hapus</span>
                                                                </button>
                                                            </div>
                                                        </details>
                                                    </div>
                                                </td>
                                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 3rem !important;">
                                    <div class="resident-table__empty">
                                        <i class="fas fa-users-slash"
                                            style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-weight: 600; color: var(--text);">Belum ada data perpindahan penduduk
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
                Menampilkan {{ $records->firstItem() ?? 0 }} - {{ $records->lastItem() ?? 0 }} dari
                {{ $records->total() }} penduduk pindah
            </div>
            <div class="resident-pagination__links">
                {{ $records->withQueryString()->onEachSide(1)->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>

    @include('admin.penduduk-pindah.modals')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/admin-entities.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // ── Detail Modal population ──────────────────────────────────
            document.body.addEventListener('click', (e) => {
                const trigger = e.target.closest('[data-pindah-detail]');
                if (!trigger) return;
                const detailDataStr = trigger.getAttribute('data-pindah-detail');
                if (detailDataStr) {
                    try {
                        const data = JSON.parse(detailDataStr);
                        const container = document.getElementById('detailContent');
                        if (container) {
                            let html = `
                                                    <div class="detail-grid">
                                                        <div class="detail-section">
                                                            <div class="detail-section__title"><i class="fas fa-user"></i> Identitas Penduduk</div>
                                                            <div class="detail-items-list">
                                                                <div class="detail-item">
                                                                    <div class="detail-item__label"><i class="fas fa-id-card"></i> Nama Lengkap</div>
                                                                    <div class="detail-item__value detail-item__value--highlight">${data.nama}</div>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <div class="detail-item__label"><i class="fas fa-fingerprint"></i> NIK</div>
                                                                    <div class="detail-item__value" style="font-family: monospace; font-size: 1rem;">${data.nik}</div>
                                                                </div>
                                                                <div class="detail-item detail-item--full" style="display: flex; justify-content: center; margin-top: 10px;">
                                                                    ${data.foto ?
                                    `<img src="${data.foto}" alt="Foto ${data.nama}" style="width: 100%; max-width: 400px; height: 280px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; display: block; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">` :
                                    `<div style="width: 100%; max-width: 400px; height: 280px; background: #f8fafc; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; border: 2px dashed #cbd5e1; margin: 0 auto;">
                                                                            <i class="fas fa-user" style="font-size: 4rem; opacity: 0.5; margin-bottom: 12px;"></i>
                                                                            <span style="font-size: 0.85rem; font-weight: 500;">Foto tidak tersedia</span>
                                                                        </div>`
                                }
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="detail-section">
                                                            <div class="detail-section__title"><i class="fas fa-route"></i> Informasi Kepindahan</div>
                                                            <div class="detail-items-list">
                                                                <div class="detail-item">
                                                                    <div class="detail-item__label"><i class="fas fa-calendar-day"></i> Tanggal Pindah</div>
                                                                    <div class="detail-item__value">${data.tanggal}</div>
                                                                </div>
                                                                <div class="detail-item">
                                                                    <div class="detail-item__label"><i class="fas fa-comment-dots"></i> Alasan</div>
                                                                    <div class="detail-item__value">${data.alasan || '-'}</div>
                                                                </div>
                                                                <div class="detail-item detail-item--full">
                                                                    <div class="detail-item__label"><i class="fas fa-map-location-dot"></i> Alamat Tujuan</div>
                                                                    <div class="detail-item__value detail-item__value--long">${data.tujuan || '-'}</div>
                                                                </div>
                                                                <div class="detail-item detail-item--full">
                                                                    <div class="detail-item__label"><i class="fas fa-city"></i> Wilayah Tujuan</div>
                                                                    <div class="detail-item__value">${data.wilayah_tujuan || '-'}</div>
                                                                </div>
                                                                <div class="detail-item detail-item--full">
                                                                    <div class="detail-item__label"><i class="fas fa-circle-info"></i> Keterangan Tambahan</div>
                                                                    <div class="detail-item__value detail-item__value--long">${data.keterangan || '-'}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>`;
                            container.innerHTML = html;
                        }
                    } catch (err) {
                        console.error('Data Detail Error:', err);
                    }
                }
            });

            // ── Export Dropdown (with Bottom-Sheet on mobile) ─────────────
            const exportToggle = document.getElementById('exportDropdownTogglePindah');
            const exportMenu = document.getElementById('exportDropdownMenuPindah');
            const exportContainer = exportToggle ? exportToggle.closest('.export-dropdown') : null;
            let exportPlaceholder = null;

            const openExport = () => {
                exportMenu.hidden = false;
                if (exportContainer) exportContainer.classList.add('is-active');
                const isMobile = window.innerWidth <= 1024;
                if (isMobile) {
                    if (exportMenu.parentElement !== document.body) {
                        exportPlaceholder = document.createElement('div');
                        exportPlaceholder.style.display = 'none';
                        exportMenu.parentNode.insertBefore(exportPlaceholder, exportMenu);
                        document.body.appendChild(exportMenu);
                    }
                    if (!document.getElementById('pindahExportBackdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.id = 'pindahExportBackdrop';
                        backdrop.className = 'export-bottom-sheet-backdrop';
                        document.body.appendChild(backdrop);
                        requestAnimationFrame(() => { backdrop.style.opacity = '1'; backdrop.style.pointerEvents = 'auto'; });
                        backdrop.addEventListener('click', closeExport);
                    }
                }
                requestAnimationFrame(() => exportMenu.classList.add('is-visible'));
                exportToggle.setAttribute('aria-expanded', 'true');
            };

            const closeExport = () => {
                exportMenu.classList.remove('is-visible');
                if (exportContainer) exportContainer.classList.remove('is-active');
                const backdrop = document.getElementById('pindahExportBackdrop');
                if (backdrop) {
                    backdrop.style.opacity = '0';
                    backdrop.style.pointerEvents = 'none';
                    setTimeout(() => backdrop.remove(), 300);
                }
                setTimeout(() => {
                    if (!exportMenu.classList.contains('is-visible')) {
                        exportMenu.hidden = true;
                        if (exportPlaceholder && exportPlaceholder.parentNode) {
                            exportPlaceholder.parentNode.insertBefore(exportMenu, exportPlaceholder);
                            exportPlaceholder.remove();
                            exportPlaceholder = null;
                        }
                    }
                }, 300);
                exportToggle.setAttribute('aria-expanded', 'false');
            };

            if (exportToggle && exportMenu) {
                exportToggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const expanded = exportToggle.getAttribute('aria-expanded') === 'true';
                    expanded ? closeExport() : openExport();
                });

                exportMenu.addEventListener('click', (event) => {
                    if (event.target.closest('.export-dropdown__item')) {
                        closeExport();
                    } else {
                        event.stopPropagation();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (exportMenu.classList.contains('is-visible') && !exportContainer.contains(event.target)) {
                        closeExport();
                    }
                });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    if (exportMenu && exportMenu.classList.contains('is-visible')) closeExport();
                }
            });
            // ── Action Dropdown "Auto-Dropup" Detection ─────────────
            const handleDropup = (dropdown) => {
                const viewportHeight = window.innerHeight;
                const rect = dropdown.getBoundingClientRect();
                const spaceBelow = viewportHeight - rect.bottom;

                // If space below is less than 200px (standard menu height + buffer), open upward
                if (spaceBelow < 200) {
                    dropdown.classList.add('is-upward');
                } else {
                    dropdown.classList.remove('is-upward');
                }
            };

            document.querySelectorAll('[data-action-menu]').forEach((dropdown) => {
                const summary = dropdown.querySelector('summary');
                if (summary) {
                    // Trigger on mousedown or pointerdown to calculate before opening
                    summary.addEventListener('pointerdown', () => handleDropup(dropdown));
                }

                // Fallback for toggle
                dropdown.addEventListener('toggle', () => {
                    if (dropdown.open) handleDropup(dropdown);
                });
            });
        });
    </script>
@endpush