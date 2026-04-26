@extends('admin.layouts.app')

@section('title', 'Data Penduduk')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}?v={{ time() }}">
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

        /* Action Menu Alignment (Sejajar Rata Kiri) & Dots Button Style (Sync with Budget) */
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

        /* Override .resident-panel__header from grid to block so inner flex works */
        .resident-panel .resident-panel__header {
            display: block !important;
        }

        /* Daftar Penduduk Header Layout: title left, search right */
        #residentPanelHeader {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            width: 100% !important;
        }

        #residentPanelHeader .resident-panel__title {
            flex: 0 1 auto !important;
        }

        /* Form pencarian di panel header — pojok kanan */
        #residentSearchForm {
            margin-left: auto !important;
            flex: 0 0 auto !important;
            display: flex !important;
            align-items: center !important;
        }

        #residentSearchForm .search-input-wrapper {
            min-width: 220px !important;
        }

        /* Mobile: Pencarian turun ke bawah deskripsi judul */
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

        /* Disable Vertical Scroll for the residents table */
        .agenda-table-scroll-active {
            max-height: none !important;
            overflow-y: visible !important;
        }

        /* Remove old CSS artifacts */
        .resident-panel__search-wrapper,
        .resident-panel__search-bar,
        .resident-panel__toolbar {
            display: none !important;
        }


        /* Sync Header & Row Colors/Spacing (Sync with Budget) */
        .table-budget thead th {
            background-color: #f8fafc !important;
            border-bottom: 2px solid rgba(226, 232, 240, 1) !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.04em !important;
        }

        .table-budget tbody tr {
            border-bottom: 1px solid rgba(226, 232, 240, 0.9) !important;
            transition: background 0.12s ease !important;
        }

        .table-budget tbody tr:hover {
            background-color: #f5f7fb !important;
        }

        .table-budget td {
            border-bottom: 1px solid rgba(226, 232, 240, 0.9) !important;
        }

        /* ── Action Dropdown & Dropup Fix ────────────────── */
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

        .resident-action-item--danger {
            color: #dc2626;
        }

        .resident-action-item--danger:hover {
            background: rgba(220, 38, 38, 0.05);
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
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/admin-residents.js') }}" defer></script>
    <script src="{{ asset('assets/js/filter-toggle.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('exportDropdownTogglePenduduks');
            const menu = document.getElementById('exportDropdownMenuPenduduks');
            const container = toggle ? toggle.closest('.export-dropdown') : null;
            if (!toggle || !menu) return;

            let placeholder = null;

            const openMenu = () => {
                menu.hidden = false;
                if (container) container.classList.add('is-active');

                const isMobile = window.innerWidth <= 1024;
                if (isMobile) {
                    if (menu.parentElement !== document.body) {
                        placeholder = document.createElement('div');
                        placeholder.style.display = 'none';
                        menu.parentNode.insertBefore(placeholder, menu);
                        document.body.appendChild(menu);
                    }
                    // add backdrop to body dynamically to avoid nesting issues
                    if (!document.getElementById('exportBackdrop')) {
                        const backdrop = document.createElement('div');
                        backdrop.id = 'exportBackdrop';
                        backdrop.className = 'export-bottom-sheet-backdrop';
                        document.body.appendChild(backdrop);

                        requestAnimationFrame(() => {
                            backdrop.style.opacity = '1';
                            backdrop.style.pointerEvents = 'auto';
                        });

                        backdrop.addEventListener('click', closeMenu);
                    }
                }

                requestAnimationFrame(() => menu.classList.add('is-visible'));
                toggle.setAttribute('aria-expanded', 'true');
            };
            const closeMenu = () => {
                menu.classList.remove('is-visible');
                if (container) container.classList.remove('is-active');

                const backdrop = document.getElementById('exportBackdrop');
                if (backdrop) {
                    backdrop.style.opacity = '0';
                    backdrop.style.pointerEvents = 'none';
                    setTimeout(() => backdrop.remove(), 300);
                }

                setTimeout(() => {
                    if (!menu.classList.contains('is-visible')) {
                        menu.hidden = true;
                        if (placeholder && placeholder.parentNode) {
                            placeholder.parentNode.insertBefore(menu, placeholder);
                            placeholder.remove();
                            placeholder = null;
                        }
                    }
                }, 300);
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                expanded ? closeMenu() : openMenu();
            });

            menu.addEventListener('click', (event) => {
                if (event.target.closest('.export-dropdown__item')) {
                    closeMenu();
                } else {
                    event.stopPropagation();
                }
            });
            document.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMenu();
            });

            // ── Action Dropdown "Auto-Dropup" Detection ─────────────
            const handleDropup = (dropdown) => {
                const viewportHeight = window.innerHeight;
                const rect = dropdown.getBoundingClientRect();
                const spaceBelow = viewportHeight - rect.bottom;
                
                // If space below is less than 200px, open upward
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

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp



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
            <span class="header-title-text">Manajemen Penduduk</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Kependudukan</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.penduduks.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Data Penduduk</a>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Pengelolaan Penduduk</h1>
            <p>Kelola data kependudukan lengkap dengan status dasar, relasi keluarga, dan wilayah.</p>
        </div>
        <div class="title-actions" id="titleActionsPenduduks">
            <button type="button" class="soft-action-btn soft-action-btn--outline" id="toggleStatsButton">
                <i class="fas fa-chart-pie" aria-hidden="true"></i>
                <span id="toggleStatsLabel">Statistik</span>
                <i class="fas fa-chevron-down" id="toggleStatsIcon" aria-hidden="true"></i>
            </button>

            <div class="header-actions-group">
                <div class="resident-dropdown filter-dropdown">
                    <button type="button"
                        class="soft-action-btn soft-action-btn--outline resident-dropdown__toggle filter-dropdown__toggle"
                        id="filterDropdownToggle" aria-haspopup="true" aria-expanded="false"
                        aria-controls="filterDropdownMenu">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        <span>Filter</span>
                        <i class="fas fa-chevron-down resident-dropdown__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="resident-dropdown__menu filter-dropdown__menu" id="filterDropdownMenu" role="menu" hidden>
                        <div class="resident-dropdown__header">
                            <strong>Filter Data</strong>
                            <span>Sesuaikan tampilan data</span>
                        </div>
                        <form method="GET" action="{{ route('admin.penduduks.index') }}" class="filter-dropdown__form"
                            id="residentFilterForm">
                            <input type="hidden" name="search" value="{{ $filters['search'] }}">
                            <div class="filter-dropdown__grid">
                                <div class="filter-dropdown__group">
                                    <label class="filter-field">
                                        <span class="filter-field__label">Status Dasar</span>
                                        <div class="filter-field__control">
                                            <select name="status_dasar_id">
                                                <option value="">Semua Status</option>
                                                @foreach ($statusDasarOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((string) $filters['status_dasar_id'] === (string) $option->id)>
                                                        {{ Str::title(Str::lower($option->nama)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </label>
                                </div>
                                <div class="filter-dropdown__group">
                                    <label class="filter-field">
                                        <span class="filter-field__label">Wilayah Dusun</span>
                                        <div class="filter-field__control">
                                            <select name="dusun_id">
                                                <option value="">Semua Dusun</option>
                                                @foreach ($dusunOptions as $option)
                                                    <option value="{{ $option->id }}" @selected((string) $filters['dusun_id'] === (string) $option->id)>
                                                        {{ $option->nama }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </label>
                                </div>
                                <div class="filter-dropdown__group">
                                    <label class="filter-field">
                                        <span class="filter-field__label">Tampilkan</span>
                                        <div class="filter-field__control">
                                            <select name="entries">
                                                @foreach ($entriesOptions as $option)
                                                    <option value="{{ $option }}" @selected((int) $filters['entries'] === (int) $option)>
                                                        {{ $option }} Baris
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="filter-dropdown__footer">
                                <button type="button" class="action-btn action-btn--ghost"
                                    id="resetFilterBtn">Reset</button>
                                <button type="submit" class="action-btn action-btn--primary">Terapkan Filter</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="export-dropdown export-dropdown--bottom-sheet">
                    <button type="button" class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                        id="exportDropdownTogglePenduduks" aria-haspopup="true" aria-expanded="false"
                        aria-controls="exportDropdownMenuPenduduks">
                        <i class="fas fa-file-export" aria-hidden="true"></i>
                        <span>Ekspor</span>
                        <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="export-dropdown__menu" id="exportDropdownMenuPenduduks" role="menu" hidden>
                        <div class="export-dropdown__header">Pilih Ekspor</div>
                        <div class="export-dropdown__grid">
                            <button type="button" class="export-dropdown__item" role="menuitem"
                                data-modal-open="residentImportModal">
                                <span class="export-dropdown__icon export-dropdown__icon--violet" aria-hidden="true">
                                    <i class="fas fa-file-import"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Impor Excel</strong>
                                    <small>Unggah data penduduk</small>
                                </div>
                            </button>
                            <a href="{{ route('admin.penduduks.export.excel') }}" class="export-dropdown__item"
                                role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                    <i class="fas fa-file-excel"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download Excel</strong>
                                    <small>Unduh format spreadsheet</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.penduduks.export.pdf') }}" class="export-dropdown__item"
                                role="menuitem">
                                <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                    <i class="fas fa-file-pdf"></i>
                                </span>
                                <div class="export-dropdown__meta">
                                    <strong>Download PDF</strong>
                                    <small>Versi siap cetak</small>
                                </div>
                            </a>
                            <a href="{{ route('admin.penduduks.export.word') }}" class="export-dropdown__item"
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

            <button type="button" class="soft-action-btn soft-action-btn--violet" data-modal-open="residentFormModal"
                data-form-mode="create">
                <i class="fas fa-user-plus"></i>
                <span>Tambah Penduduk</span>
            </button>
        </div>
    </section>

    @php
        $metrics = $metrics ?? [];
        $formHasErrors = $formHasErrors ?? $errors->any();
        $entriesOptions = $entriesOptions ?? [25, 50, 100];
        $statusOptionsLookup = $statusOptionsLookup ?? collect();
        $metricsStatus = $metricsStatus ?? ($metrics['status'] ?? null);
        $metricsGender = $metricsGender ?? ($metrics['gender'] ?? null);
        $metricsOverview = $metricsOverview ?? ($metrics['overview'] ?? null);
        $metricsCategories = $metricsCategories ?? ($metrics['categories'] ?? []);
        $filters = array_merge([
            'search' => '',
            'entries' => $entriesOptions[0] ?? 25,
            'status_dasar_id' => '',
            'dusun_id' => '',
        ], $filters ?? []);
        $oldResident = $oldResident ?? [];
        $oldMode = $oldMode ?? 'create';
        $oldUpdateUrl = $oldUpdateUrl ?? '';
        $hamilValue = $hamilValue ?? old('hamil', '');
        $ktpElValue = $ktpElValue ?? old('ktp_el', '');
        $statusDasarOptions = $statusDasarOptions ?? collect();
        $dusunOptions = $dusunOptions ?? collect();
        $keluargaOptions = $keluargaOptions ?? collect();
        $jenisKelaminOptions = $jenisKelaminOptions ?? collect();
        $agamaOptions = $agamaOptions ?? collect();
        $pendidikanOptions = $pendidikanOptions ?? collect();
        $pekerjaanOptions = $pekerjaanOptions ?? collect();
        $statusKawinOptions = $statusKawinOptions ?? collect();
        $hubunganKkOptions = $hubunganKkOptions ?? collect();
        $warganegaraOptions = $warganegaraOptions ?? collect();
        $golonganDarahOptions = $golonganDarahOptions ?? collect();
        $cacatOptions = $cacatOptions ?? collect();
        $caraKbOptions = $caraKbOptions ?? collect();
        $statusRekamOptions = $statusRekamOptions ?? collect();
        $sukuOptions = $sukuOptions ?? collect();
        $rwOptions = $rwOptions ?? collect();
        $rtOptions = $rtOptions ?? collect();
        $residentDetailSections = $residentDetailSections ?? [];
        $toastNotifications = $toastNotifications ?? [];
        $statusMessage = session('status');
        $statusVariant = session('status_variant');

        if ($statusMessage) {
            $feedbackTone = $statusVariant ?? 'success';
            if (!$statusVariant) {
                $normalizedStatus = Str::lower($statusMessage);
                if (Str::contains($normalizedStatus, ['hapus', 'dihapus', 'delete'])) {
                    $feedbackTone = 'danger';
                } elseif (Str::contains($normalizedStatus, ['perbarui', 'update', 'ubah'])) {
                    $feedbackTone = 'info';
                }
            }

            $variantMap = [
                'success' => 'success',
                'info' => 'info',
                'danger' => 'error',
                'warning' => 'warning',
                'error' => 'error',
            ];

            $toastNotifications[] = [
                'variant' => $variantMap[$feedbackTone] ?? 'neutral',
                'title' => $statusMessage,
                'message' => session('status_description') ?? 'Perubahan berhasil disimpan.',
            ];
        }

        if ($errors->any()) {
            $toastNotifications[] = [
                'variant' => 'error',
                'title' => 'Form tidak valid',
                'message' => $errors->first(),
            ];
        }
    @endphp

    @if (!empty($toastNotifications))
        <div class="toast-stack" id="residentToastStack" role="region" aria-live="polite">
            @foreach ($toastNotifications as $toast)
                @php
                    $variant = $toast['variant'] ?? 'neutral';
                    $iconMap = [
                        'success' => 'fa-check',
                        'warning' => 'fa-triangle-exclamation',
                        'error' => 'fa-circle-xmark',
                        'info' => 'fa-circle-info',
                        'neutral' => 'fa-circle-info',
                    ];
                    $icon = $iconMap[$variant] ?? $iconMap['neutral'];
                @endphp
                <article class="toast" data-toast data-variant="{{ $variant }}">
                    <div class="toast__icon" aria-hidden="true">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="toast__content">
                        <strong>{{ $toast['title'] }}</strong>
                        @if (!empty($toast['message']))
                            <p>{{ $toast['message'] }}</p>
                        @endif
                    </div>
                    <button type="button" class="toast__close" data-toast-close aria-label="Tutup notifikasi">
                        <i class="fas fa-times"></i>
                    </button>
                    <span class="toast__progress" aria-hidden="true"></span>
                </article>
            @endforeach
        </div>
    @endif
    @php
        if (empty($residentDetailSections)) {
            $residentDetailSections = [
                [
                    'title' => 'Identitas & Dokumen',
                    'icon' => 'fa-id-card',
                    'accent' => 'primary',
                    'fields' => [
                        ['label' => 'Nama Lengkap', 'key' => 'nama'],
                        ['label' => 'NIK', 'key' => 'nik'],
                        ['label' => 'No. KK', 'key' => 'no_kk'],
                        ['label' => 'Hubungan dalam KK', 'key' => 'hubungan'],
                        ['label' => 'No. HP', 'key' => 'nomor_hp', 'optional' => true],
                        ['label' => 'Email', 'key' => 'email', 'optional' => true],
                        ['label' => 'Status Dasar', 'key' => 'status_dasar'],
                        ['label' => 'Status Rekam KTP-el', 'key' => 'status_rekam'],
                        ['label' => 'KTP-el', 'key' => 'ktp_el_label'],
                        ['label' => 'Tag ID Card', 'key' => 'tag_id_card', 'optional' => true],
                        ['label' => 'ID Asuransi', 'key' => 'id_asuransi', 'optional' => true],
                        ['label' => 'No. Asuransi', 'key' => 'no_asuransi', 'optional' => true],
                    ],
                ],
                [
                    'title' => 'Foto & Dokumen',
                    'icon' => 'fa-camera',
                    'accent' => 'slate',
                    'fields' => [
                        ['label' => 'Foto Profil', 'key' => 'foto_profil_url', 'type' => 'image', 'optional' => true],
                        ['label' => 'Foto KTP', 'key' => 'foto_ktp_url', 'type' => 'image', 'optional' => true],
                        ['label' => 'Foto KK', 'key' => 'foto_kk_url', 'type' => 'image', 'optional' => true],
                    ],
                ],
                [
                    'title' => 'Demografi & Pendidikan',
                    'icon' => 'fa-user-graduate',
                    'accent' => 'violet',
                    'fields' => [
                        ['label' => 'Jenis Kelamin', 'key' => 'jenis_kelamin'],
                        ['label' => 'Agama', 'key' => 'agama'],
                        ['label' => 'Status Perkawinan', 'key' => 'status_kawin'],
                        ['label' => 'Tempat, Tanggal Lahir', 'key' => 'lahir'],
                        ['label' => 'Pendidikan (KK)', 'key' => 'pendidikan_kk'],
                        ['label' => 'Pendidikan Sedang Ditempuh', 'key' => 'pendidikan_sedang'],
                        ['label' => 'Pekerjaan', 'key' => 'pekerjaan'],
                        ['label' => 'Warganegara', 'key' => 'warganegara'],
                        ['label' => 'Golongan Darah', 'key' => 'golongan_darah'],
                        ['label' => 'Suku', 'key' => 'suku'],
                    ],
                ],
                [
                    'title' => 'Alamat & Wilayah',
                    'icon' => 'fa-map-location-dot',
                    'accent' => 'teal',
                    'fields' => [
                        ['label' => 'Alamat KK', 'key' => 'alamat'],
                        ['label' => 'Alamat Saat Ini', 'key' => 'alamat_sekarang', 'optional' => true],
                        ['label' => 'Dusun', 'key' => 'dusun'],
                        ['label' => 'RW', 'key' => 'rw'],
                        ['label' => 'RT', 'key' => 'rt'],
                    ],
                ],
                [
                    'title' => 'Kesehatan & Status Khusus',
                    'icon' => 'fa-heartbeat',
                    'accent' => 'rose',
                    'fields' => [
                        ['label' => 'Cara KB', 'key' => 'cara_kb', 'optional' => true],
                        ['label' => 'Disabilitas', 'key' => 'cacat', 'optional' => true],
                        ['label' => 'Hamil', 'key' => 'hamil_label', 'optional' => true],
                        ['label' => 'Akta Lahir', 'key' => 'akta_lahir', 'optional' => true],
                        ['label' => 'Paspor', 'key' => 'dokumen_pasport', 'optional' => true],
                        ['label' => 'Masa Berlaku Paspor', 'key' => 'tanggal_akhir_paspor', 'optional' => true],
                        ['label' => 'KITAS', 'key' => 'dokumen_kitas', 'optional' => true],
                        ['label' => 'Akta Perkawinan', 'key' => 'akta_perkawinan', 'optional' => true],
                        ['label' => 'Tanggal Perkawinan', 'key' => 'tanggal_perkawinan', 'optional' => true],
                        ['label' => 'Akta Perceraian', 'key' => 'akta_perceraian', 'optional' => true],
                        ['label' => 'Tanggal Perceraian', 'key' => 'tanggal_perceraian', 'optional' => true],
                    ],
                ],
                [
                    'title' => 'Orang Tua',
                    'icon' => 'fa-user-friends',
                    'accent' => 'amber',
                    'fields' => [
                        ['label' => 'NIK Ayah', 'key' => 'ayah_nik', 'optional' => true],
                        ['label' => 'Nama Ayah', 'key' => 'nama_ayah', 'optional' => true],
                        ['label' => 'NIK Ibu', 'key' => 'ibu_nik', 'optional' => true],
                        ['label' => 'Nama Ibu', 'key' => 'nama_ibu', 'optional' => true],
                    ],
                ],
                [
                    'title' => 'Informasi Sistem',
                    'icon' => 'fa-database',
                    'accent' => 'indigo',
                    'fields' => [
                        ['label' => 'Terdaftar pada', 'key' => 'created_at_label'],
                        ['label' => 'Diperbarui pada', 'key' => 'updated_at_label'],
                    ],
                ],
            ];
        }

        $statusCardItems = [];
        if ($metricsStatus) {
            $statusCardItems = collect($metricsStatus['items'] ?? [])->map(function ($item) use ($statusOptionsLookup) {
                $statusId = $item['status_id'] ?? null;
                $label = $item['label'] ?? null;
                if (!$label && $statusId !== null && $statusOptionsLookup->has($statusId)) {
                    $label = $statusOptionsLookup[$statusId]->nama ?? null;
                }
                $label = $label ? Str::title(Str::lower($label)) : 'Status lainnya';

                return [
                    'label' => $label,
                    'value' => (int) ($item['value'] ?? 0),
                    'percent' => (float) ($item['percent'] ?? 0),
                    'color' => $item['color'] ?? 'slate',
                ];
            })->values()->all();
        }

        $genderCardItems = [];
        if ($metricsGender) {
            $genderCardItems = collect($metricsGender['items'] ?? [])->map(function ($item) {
                return [
                    'label' => $item['label'] ?? '-',
                    'value' => (int) ($item['value'] ?? 0),
                    'percent' => (float) ($item['percent'] ?? 0),
                    'color' => $item['color'] ?? 'slate',
                    'icon' => $item['icon'] ?? null,
                ];
            })->values()->all();
        }
        $hasResidentStats = $metricsOverview || !empty($metricsCategories) || !empty($genderCardItems);
        $formatPercentage = $formatPercentage ?? function ($value, int $decimals = 1): string {
            $numeric = is_numeric($value) ? (float) $value : 0;
            $rounded = round($numeric, $decimals);
            $formatted = number_format($rounded, $decimals);
            $trimmed = rtrim(rtrim($formatted, '0'), '.');
            return ($trimmed === '' ? '0' : $trimmed) . '%';
        };
    @endphp

    <section class="resident-stats" id="residentStatsPanel" hidden aria-hidden="true">
        <div class="resident-stats__viewport">
            @if ($hasResidentStats)
                @if ($metricsOverview)
                    @php
                        $genderColorMap = [
                            'emerald' => '#34d399',
                            'amber' => '#fbbf24',
                            'rose' => '#f87171',
                            'slate' => '#94a3b8',
                            'indigo' => '#6366f1',
                            'blue' => '#60a5fa',
                            'pink' => '#f472b6',
                        ];

                        $genderSegments = collect($genderCardItems)->values();
                        $genderTotal = $genderSegments->sum('value');
                        $gradientStops = [];
                        $progressCursor = 0;

                        foreach ($genderSegments as $segment) {
                            $segmentPercent = max(min($segment['percent'] ?? 0, 100), 0);
                            $colorHex = $genderColorMap[$segment['color'] ?? 'slate'] ?? '#94a3b8';
                            $start = $progressCursor;
                            $progressCursor = min(100, $progressCursor + $segmentPercent);
                            $gradientStops[] = sprintf('%s %.2f%% %.2f%%', $colorHex, $start, $progressCursor);
                        }

                        if ($progressCursor < 100) {
                            $gradientStops[] = sprintf('rgba(148, 163, 184, 0.24) %.2f%% 100%%', $progressCursor);
                        }

                        $genderGradient = implode(', ', $gradientStops);
                    @endphp

                    <article class="stats-card stats-card--gender" style="--gender-gradient: {{ $genderGradient }};">
                        <div class="stats-card__top">
                            <div class="stats-card__title">
                                <span class="stats-card__icon stats-card__icon--rose" aria-hidden="true">
                                    <i class="fas fa-venus-mars"></i>
                                </span>
                                <div>
                                    <h3 class="stats-card__heading">{{ $metricsGender['title'] ?? 'Komposisi Gender' }}</h3>
                                    <p class="stats-card__subtitle">
                                        {{ $metricsGender['subtitle'] ?? 'Perbandingan penduduk berdasarkan gender.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="gender-chart">
                            <div class="gender-chart__donut" role="img" aria-label="Sebaran gender penduduk">
                                <div class="gender-chart__inner">
                                    <span class="gender-chart__icon" aria-hidden="true">
                                        <i class="fas fa-user-friends"></i>
                                    </span>
                                    <span class="gender-chart__number">{{ number_format($genderTotal) }}</span>
                                    <span class="gender-chart__label">Penduduk</span>
                                </div>
                            </div>

                            <ul class="gender-chart__legend">
                                @foreach ($genderSegments as $segment)
                                    <li class="gender-chart__item gender-chart__item--{{ $segment['color'] ?? 'slate' }}">
                                        <span class="gender-chart__dot" aria-hidden="true"></span>
                                        <div class="gender-chart__meta">
                                            <span class="gender-chart__name">{{ $segment['label'] }}</span>
                                            <span class="gender-chart__amount">{{ number_format($segment['value']) }} org</span>
                                        </div>
                                        <span class="gender-chart__percent">{{ $formatPercentage($segment['percent']) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endif
                @if ($metricsOverview)
                    @php
                        $overviewSegments = collect($metricsOverview['segments'] ?? [])->map(function ($segment) {
                            return [
                                'label' => $segment['label'] ?? '-',
                                'value' => (int) ($segment['value'] ?? 0),
                                'percent' => (float) ($segment['percent'] ?? 0),
                                'color' => $segment['color'] ?? 'slate',
                            ];
                        })->values()->all();
                        $overviewTrend = $metricsOverview['trend'] ?? [];
                        $trendDirection = $overviewTrend['direction'] ?? 'flat';
                        $trendValue = abs((int) ($overviewTrend['value'] ?? 0));
                        $trendLabel = $overviewTrend['label'] ?? 'Penduduk baru 30 hari terakhir';
                        $trendPrefix = $trendDirection === 'down' ? '-' : ($trendDirection === 'up' ? '+' : '');
                        $trendText = $trendValue > 0 ? "{$trendPrefix}" . number_format($trendValue) . ' ' . $trendLabel : $trendLabel;
                        $overviewTiles = [
                            [
                                'label' => 'Total Terdata',
                                'value' => number_format($metricsOverview['total'] ?? 0),
                                'note' => $trendText,
                                'direction' => $trendDirection,
                                'icon' => 'fa-users',
                                'color' => 'primary',
                            ],
                        ];

                        foreach ($overviewSegments as $segment) {
                            $segmentIcon = match ($segment['color']) {
                                'emerald' => 'fa-user-check',
                                'amber' => 'fa-route',
                                'rose' => 'fa-user-minus',
                                default => 'fa-layer-group',
                            };

                            $overviewTiles[] = [
                                'label' => $segment['label'] ?? 'Segment',
                                'value' => number_format($segment['value'] ?? 0),
                                'note' => $formatPercentage($segment['percent'] ?? 0) . ' dari total',
                                'direction' => 'flat',
                                'icon' => $segmentIcon,
                                'color' => $segment['color'] ?? 'slate',
                            ];
                        }
                    @endphp
                    <article class="stats-matrix stats-matrix--overview">
                        <header class="stats-matrix__head">
                            <div class="stats-matrix__head-info">
                                <span class="stats-matrix__head-icon">
                                    <i class="fas fa-layer-group"></i>
                                </span>
                                <div>
                                    <h3>{{ $metricsOverview['title'] ?? 'Total Penduduk' }}</h3>
                                    <p>{{ $metricsOverview['subtitle'] ?? 'Seluruh penduduk yang terdaftar dalam sistem.' }}</p>
                                </div>
                            </div>
                            @if (!empty($lastUpdatedLabel))
                                <span class="stats-matrix__meta">Diperbarui {{ $lastUpdatedLabel }}</span>
                            @endif
                        </header>
                        <div class="stats-matrix__grid">
                            @foreach ($overviewTiles as $tile)
                                <div class="stats-matrix__cell stats-matrix__cell--{{ $tile['color'] }}">
                                    <div class="stats-matrix__cell-icon">
                                        <i class="fas {{ $tile['icon'] }}"></i>
                                    </div>
                                    <p class="stats-matrix__label">{{ $tile['label'] }}</p>
                                    <div class="stats-matrix__value">{{ $tile['value'] }}</div>
                                    @if ($tile['direction'] !== 'flat')
                                        <p class="stats-matrix__trend stats-matrix__trend--{{ $tile['direction'] }}">
                                            <i class="fas {{ $tile['direction'] === 'down' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                            {{ $tile['note'] }}
                                        </p>
                                    @else
                                        <p class="stats-matrix__note">{{ $tile['note'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endif


                @if (!empty($metricsCategories))
                    @foreach ($metricsCategories as $category)
                        @continue(empty($category['items']))
                        <article class="stats-matrix stats-matrix--category">
                            <header class="stats-matrix__head">
                                <div class="stats-matrix__head-info">
                                    <span class="stats-matrix__head-icon stats-matrix__head-icon--{{ $category['color'] ?? 'slate' }}">
                                        <i class="fas {{ $category['icon'] ?? 'fa-chart-bar' }}"></i>
                                    </span>
                                    <div>
                                        <h3>{{ $category['title'] ?? 'Statistik' }}</h3>
                                        <p>Top {{ count($category['items'] ?? []) }} kategori teratas.</p>
                                    </div>
                                </div>
                            </header>
                            <div class="stats-category-marquee" role="list" aria-live="polite">
                                @foreach ($category['items'] as $item)
                                    <div class="stats-card-pill stats-card-pill--{{ $item['color'] ?? 'slate' }}" role="listitem">
                                        <span class="stats-card-pill__icon">
                                            <i class="fas {{ $category['icon'] ?? 'fa-chart-bar' }}" aria-hidden="true"></i>
                                        </span>
                                        <div class="stats-card-pill__content">
                                            <strong class="stats-card-pill__label">{{ $item['label'] }}</strong>
                                            <span class="stats-card-pill__value">{{ number_format($item['value']) }} org</span>
                                            <span class="stats-card-pill__meta">{{ $formatPercentage($item['percent'] ?? 0) }} dari
                                                total</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                @endif
            @else
                <article class="stats-card stats-card--empty">
                    <div class="stats-card__title">
                        <span class="stats-card__icon" aria-hidden="true">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        <div>
                            <h3>Statistik belum tersedia</h3>
                            <p>Tambahkan data penduduk terlebih dahulu untuk menampilkan grafik dan ringkasan.</p>
                        </div>
                    </div>
                </article>
            @endif
        </div>
    </section>

    <article class="panel panel--flush agenda-panel resident-panel">
        <header class="panel-header panel-header--table agenda-panel__header resident-panel__header">
            <div id="residentPanelHeader" class="resident-panel__title-row resident-panel__title-row--inline">
                <div class="resident-panel__title">
                    <h2>Daftar Penduduk</h2>
                    <p>Manajemen data penduduk desa dengan filter pencarian cepat.</p>
                </div>
                <form id="residentSearchForm" method="GET" action="{{ route('admin.penduduks.index') }}"
                    class="panel-toolbar-inline">
                    <div class="search-input-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon-left"></i>
                            <input id="searchPenduduk" type="search" name="search" value="{{ $filters['search'] }}"
                                placeholder="Cari Penduduk..." autocomplete="off" aria-label="Cari Penduduk">
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
                            <th style="min-width: 320px; text-align: left;">Penduduk</th>
                            <th style="width: 240px; text-align: left;">KK & Hubungan</th>
                            <th style="width: 140px; text-align: left;">Jenis Kelamin</th>
                            <th style="width: 160px; text-align: left;">Kontak</th>
                            <th style="width: 150px; text-align: left;">Status Dasar</th>
                            <th style="width: 240px; text-align: left;">Wilayah</th>
                            <th style="width: 140px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $startIndex = ($penduduks->currentPage() - 1) * $penduduks->perPage(); @endphp
                        @forelse ($penduduks as $index => $resident)
                            @php
                                $rowNumber = $startIndex + $index + 1;
                                $birthInfo = trim(($resident->tempat_lahir ? $resident->tempat_lahir . ', ' : '') . ($resident->tanggal_lahir?->translatedFormat('d M Y') ?? ''));
                                $fotoProfilUrl = $resident->foto_profil ? Storage::url($resident->foto_profil) : null;
                                $fotoKtpUrl = $resident->foto_ktp ? Storage::url($resident->foto_ktp) : null;
                                $fotoKkUrl = $resident->foto_kk ? Storage::url($resident->foto_kk) : null;
                                $detailPayload = [
                                    'nama' => $resident->nama,
                                    'nik' => $resident->nik,
                                    'no_kk' => $resident->no_kk ?? ($resident->keluarga?->no_kk ?? '-'),
                                    'hubungan' => $resident->kkLevel?->nama ?? '-',
                                    'nomor_hp' => $resident->nomor_hp ?? '-',
                                    'email' => $resident->email ?? '-',
                                    'status_dasar' => $resident->statusDasar?->nama ?? '-',
                                    'status_kawin' => $resident->statusKawin?->nama ?? '-',
                                    'status_rekam' => $resident->statusRekam?->nama ?? '-',
                                    'dusun' => $resident->dusun?->nama ? ('Dusun ' . $resident->dusun->nama) : '-',
                                    'rw' => $resident->rw?->nomor ? ('RW ' . $resident->rw->nomor) : '-',
                                    'rt' => $resident->rt?->nomor ? ('RT ' . $resident->rt->nomor) : '-',
                                    'lahir' => $birthInfo !== '' ? $birthInfo : 'Data kelahiran belum lengkap',
                                    'alamat' => $resident->alamat ?? '-',
                                    'alamat_sekarang' => $resident->alamat_sekarang ?? '-',
                                    'jenis_kelamin' => $resident->jenisKelamin?->nama ?? '-',
                                    'agama' => $resident->agama?->nama ?? '-',
                                    'pendidikan_kk' => $resident->pendidikanKk?->nama ?? '-',
                                    'pendidikan_sedang' => $resident->pendidikanSedang?->nama ?? '-',
                                    'pekerjaan' => $resident->pekerjaan?->nama ?? '-',
                                    'warganegara' => $resident->warganegara?->nama ?? '-',
                                    'golongan_darah' => $resident->golonganDarah?->nama ?? '-',
                                    'suku' => $resident->suku?->nama ?? '-',
                                    'cara_kb' => $resident->caraKb?->nama ?? '-',
                                    'cacat' => $resident->cacat?->nama ?? '-',
                                    'ktp_el_label' => $resident->ktp_el ? 'Memiliki' : 'Belum',
                                    'hamil_label' => $resident->hamil === null ? 'Tidak diketahui' : ($resident->hamil ? 'Ya' : 'Tidak'),
                                    'akta_lahir' => $resident->akta_lahir ?? '-',
                                    'dokumen_pasport' => $resident->dokumen_pasport ?? '-',
                                    'tanggal_akhir_paspor' => optional($resident->tanggal_akhir_paspor)?->translatedFormat('d M Y') ?? '-',
                                    'dokumen_kitas' => $resident->dokumen_kitas ?? '-',
                                    'akta_perkawinan' => $resident->akta_perkawinan ?? '-',
                                    'tanggal_perkawinan' => optional($resident->tanggal_perkawinan)?->translatedFormat('d M Y') ?? '-',
                                    'akta_perceraian' => $resident->akta_perceraian ?? '-',
                                    'tanggal_perceraian' => optional($resident->tanggal_perceraian)?->translatedFormat('d M Y') ?? '-',
                                    'id_asuransi' => $resident->id_asuransi ?? '-',
                                    'no_asuransi' => $resident->no_asuransi ?? '-',
                                    'tag_id_card' => $resident->tag_id_card ?? '-',
                                    'foto_profil_url' => $fotoProfilUrl,
                                    'foto_ktp_url' => $fotoKtpUrl,
                                    'foto_kk_url' => $fotoKkUrl,
                                    'ayah_nik' => $resident->ayah_nik ?? '-',
                                    'nama_ayah' => $resident->nama_ayah ?? '-',
                                    'ibu_nik' => $resident->ibu_nik ?? '-',
                                    'nama_ibu' => $resident->nama_ibu ?? '-',
                                    'created_at_label' => optional($resident->created_at)?->translatedFormat('d M Y, H:i') ?? '-',
                                    'updated_at_label' => optional($resident->updated_at)?->translatedFormat('d M Y, H:i') ?? '-',
                                ];
                                $editPayload = [
                                    'id' => $resident->id,
                                    'no_kk' => $resident->no_kk ?? ($resident->keluarga?->no_kk ?? ''),
                                    'nik' => $resident->nik,
                                    'nama' => $resident->nama,
                                    'nomor_hp' => $resident->nomor_hp,
                                    'email' => $resident->email,
                                    'jenis_kelamin_id' => $resident->jenis_kelamin_id,
                                    'tempat_lahir' => $resident->tempat_lahir,
                                    'tanggal_lahir' => optional($resident->tanggal_lahir)->toDateString(),
                                    'agama_id' => $resident->agama_id,
                                    'pendidikan_kk_id' => $resident->pendidikan_kk_id,
                                    'pendidikan_sedang_id' => $resident->pendidikan_sedang_id,
                                    'pekerjaan_id' => $resident->pekerjaan_id,
                                    'status_kawin_id' => $resident->status_kawin_id,
                                    'kk_level_id' => $resident->kk_level_id,
                                    'warganegara_id' => $resident->warganegara_id,
                                    'ayah_nik' => $resident->ayah_nik,
                                    'nama_ayah' => $resident->nama_ayah,
                                    'ibu_nik' => $resident->ibu_nik,
                                    'nama_ibu' => $resident->nama_ibu,
                                    'golongan_darah_id' => $resident->golongan_darah_id,
                                    'akta_lahir' => $resident->akta_lahir,
                                    'dokumen_pasport' => $resident->dokumen_pasport,
                                    'tanggal_akhir_paspor' => optional($resident->tanggal_akhir_paspor)->toDateString(),
                                    'dokumen_kitas' => $resident->dokumen_kitas,
                                    'akta_perkawinan' => $resident->akta_perkawinan,
                                    'tanggal_perkawinan' => optional($resident->tanggal_perkawinan)->toDateString(),
                                    'akta_perceraian' => $resident->akta_perceraian,
                                    'tanggal_perceraian' => optional($resident->tanggal_perceraian)->toDateString(),
                                    'cacat_id' => $resident->cacat_id,
                                    'cara_kb_id' => $resident->cara_kb_id,
                                    'hamil' => $resident->hamil === null ? '' : ($resident->hamil ? '1' : '0'),
                                    'ktp_el' => $resident->ktp_el ? '1' : '0',
                                    'status_rekam_id' => $resident->status_rekam_id,
                                    'alamat' => $resident->alamat,
                                    'alamat_sekarang' => $resident->alamat_sekarang,
                                    'dusun_id' => $resident->dusun_id,
                                    'rw_id' => $resident->rw_id,
                                    'rt_id' => $resident->rt_id,
                                    'status_dasar_id' => $resident->status_dasar_id,
                                    'suku_id' => $resident->suku_id,
                                    'tag_id_card' => $resident->tag_id_card,
                                    'id_asuransi' => $resident->id_asuransi,
                                    'no_asuransi' => $resident->no_asuransi,
                                    'foto_profil_url' => $fotoProfilUrl,
                                    'foto_ktp_url' => $fotoKtpUrl,
                                    'foto_kk_url' => $fotoKkUrl,
                                ];
                                $statusSlug = Str::slug($resident->statusDasar?->nama ?? 'lainnya');
                            @endphp
                            <tr>
                                <td>
                                    <span class="resident-table__index">{{ $rowNumber }}</span>
                                </td>
                                <td>
                                    <div class="resident-table__person">
                                        <div class="resident-avatar">
                                            @if ($fotoProfilUrl)
                                                <img src="{{ $fotoProfilUrl }}" alt="Foto {{ $resident->nama }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px;">
                                            @else
                                                <span
                                                    class="resident-avatar__placeholder">{{ Str::upper(Str::substr($resident->nama, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div class="resident-table__person-meta">
                                            <strong>{{ $resident->nama }}</strong>
                                            <p style="margin: 2px 0 0; font-family: monospace; font-size: 0.8rem; color: var(--text-secondary); letter-spacing: 0.02em;">
                                                {{ $resident->nik }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="line-height: 1.3;">
                                        <strong>{{ $resident->no_kk ?? ($resident->keluarga?->no_kk ?? '-') }}</strong>
                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary);">
                                            {{ $resident->kkLevel?->nama ?? 'Hubungan belum tercatat' }}
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $genderRaw = Str::lower($resident->jenisKelamin?->nama ?? '');
                                        $genderClass = $genderRaw === 'perempuan' ? 'gender-pill--female' : ($genderRaw === 'laki-laki' ? 'gender-pill--male' : 'gender-pill--neutral');
                                        $genderLabel = $resident->jenisKelamin?->nama ?? '-';
                                        $genderIcon = $genderRaw === 'perempuan' ? 'fa-venus' : ($genderRaw === 'laki-laki' ? 'fa-mars' : 'fa-user');
                                    @endphp
                                    <span class="gender-pill {{ $genderClass }}">
                                        <i class="fas {{ $genderIcon }}" aria-hidden="true"></i>
                                        <span>{{ $genderLabel }}</span>
                                    </span>
                                </td>
                                <td>
                                    <div style="line-height: 1.3;">
                                        <strong>{{ $resident->nomor_hp ?: '-' }}</strong>
                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary); word-break: break-all;">
                                            {{ $resident->email ?: '-' }}
                                        </p>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusName = $resident->statusDasar?->nama ?? '-';
                                        $statusIcon = match(Str::lower($statusName)) {
                                            'hidup', 'aktif' => 'fa-heartbeat',
                                            'mati' => 'fa-skull',
                                            default => 'fa-info-circle',
                                        };
                                    @endphp
                                    <span class="resident-status resident-status--{{ $statusSlug }}">
                                        <i class="fas {{ $statusIcon }}" aria-hidden="true"></i>
                                        <span>{{ $statusName }}</span>
                                    </span>
                                </td>
                                <td>
                                    <div style="line-height: 1.3;">
                                        <strong>{{ $resident->dusun?->nama ?? '-' }}</strong>
                                        <p style="margin: 2px 0 0; font-size: 0.75rem; color: var(--text-secondary);">
                                            RW {{ $resident->rw?->nomor ?? '-' }} | RT {{ $resident->rt?->nomor ?? '-' }}
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
                                                    data-modal-open="residentDetailModal" data-resident='@json($detailPayload)'>
                                                    <i class="fas fa-eye"></i><span>Detail</span>
                                                </button>
                                                <button type="button" class="resident-action-item resident-action-item--edit"
                                                    data-modal-open="residentFormModal" data-form-mode="edit"
                                                    data-update-url="{{ route('admin.penduduks.update', $resident) }}"
                                                    data-resident-edit='@json($editPayload)'>
                                                    <i class="fas fa-pen"></i><span>Edit</span>
                                                </button>
                                                <form method="POST" action="{{ route('admin.penduduks.destroy', $resident) }}"
                                                    class="resident-delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button"
                                                        class="resident-action-item resident-action-item--danger"
                                                        data-delete-trigger data-delete-name="{{ $resident->nama }}">
                                                        <i class="fas fa-trash"></i><span>Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem !important;">
                                    <div class="resident-table__empty">
                                        <i class="fas fa-users-slash"
                                            style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 1rem; display: block;"></i>
                                        <p style="font-weight: 600; color: var(--text);">Belum ada data penduduk yang
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
                Menampilkan {{ $penduduks->firstItem() ?? 0 }} - {{ $penduduks->lastItem() ?? 0 }} dari
                {{ $penduduks->total() }} penduduk
            </div>
            <div class="resident-pagination__links">
                {{ $penduduks->withQueryString()->onEachSide(1)->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>
    <div class="dialog-backdrop" id="residentFormModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="residentFormTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="residentFormTitle">Tambah Data Penduduk</h2>
                    <p id="residentFormSubtitle">Lengkapi formulir untuk menambahkan penduduk baru ke sistem.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form penduduk">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                @if ($formHasErrors)
                    <div class="form-alert form-alert--error" role="alert">
                        <div class="form-alert__icon"><i class="fas fa-circle-exclamation" aria-hidden="true"></i></div>
                        <div class="form-alert__body">
                            <strong>Periksa kembali isian Anda.</strong>
                            <ul class="form-alert__list">
                                @foreach ($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                            <p class="form-alert__hint">Isi kolom wajib (berlabel "Wajib") sesuai instruksi agar data bisa
                                disimpan.</p>
                        </div>
                    </div>
                @endif

                <form id="residentForm" class="resident-form" method="POST" action="{{ route('admin.penduduks.store') }}"
                    enctype="multipart/form-data" data-create-action="{{ route('admin.penduduks.store') }}"
                    data-has-errors="{{ $formHasErrors ? 'true' : 'false' }}" data-old-payload='@json($oldResident)'
                    data-old-mode="{{ $oldMode }}" data-old-update-url="{{ $oldUpdateUrl }}">
                    @csrf
                    <input type="hidden" name="form_mode" value="{{ $oldMode }}">
                    <input type="hidden" name="resident_id" value="{{ $oldResident['resident_id'] ?? '' }}">
                    <input type="hidden" name="resident_update_url" value="{{ $oldResident['resident_update_url'] ?? '' }}">
                    <input type="hidden" name="_method" value="PUT" disabled>

                    <div class="resident-form__grid">
                        <section class="form-section">
                            <div class="form-section__header">
                                <div class="form-section__title">
                                    <span class="form-section__icon form-section__icon--primary"><i
                                            class="fas fa-id-card"></i></span>
                                    <div>
                                        <h2>Identitas Penduduk</h2>
                                        <p>Isi data dasar sesuai KTP dan kartu keluarga resmi.</p>
                                    </div>
                                </div>
                                <span class="form-section__badge"><i class="fas fa-asterisk"></i> Bidang bertanda "Wajib"
                                    harus diisi.</span>
                            </div>
                            <div class="form-section__body">
                                <div class="settings-form-grid two-columns">
                                    <div class="form-field kk-dropdown" data-kk-picker>
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-address-book"></i>No. KK</span>
                                            <span class="form-field__tag form-field__tag--required">Wajib</span>
                                        </span>
                                        <input type="hidden" name="no_kk" value="{{ old('no_kk') }}" data-kk-value>
                                        <div class="kk-dropdown__field" data-kk-display-wrapper>
                                            <input type="text" class="kk-dropdown__display" data-kk-display readonly
                                                required placeholder="Pilih No. KK" value="{{ old('no_kk') }}">
                                            <i class="fas fa-chevron-down kk-dropdown__caret" aria-hidden="true"></i>
                                        </div>
                                        <div class="kk-dropdown__panel" data-kk-panel>
                                            <div class="kk-dropdown__search">
                                                <i class="fas fa-search"></i>
                                                <input type="text" data-kk-search placeholder="Cari No. KK">
                                            </div>
                                            <div class="kk-dropdown__list">
                                                @foreach ($keluargaOptions as $keluarga)
                                                    <button type="button" class="kk-dropdown__option" data-kk-option
                                                        data-value="{{ $keluarga->no_kk }}">
                                                        {{ $keluarga->no_kk }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-address-card"></i>NIK</span>
                                            <span class="form-field__tag form-field__tag--required">Wajib</span>
                                        </span>
                                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="20" required>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-user"></i>Nama Lengkap</span>
                                            <span class="form-field__tag form-field__tag--required">Wajib</span>
                                        </span>
                                        <input type="text" name="nama" value="{{ old('nama') }}" maxlength="100" required>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-venus-mars"></i>Jenis
                                                Kelamin</span>
                                            <span class="form-field__tag form-field__tag--required">Wajib</span>
                                        </span>
                                        <select name="jenis_kelamin_id" required>
                                            <option value="">Pilih jenis kelamin</option>
                                            @foreach ($jenisKelaminOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('jenis_kelamin_id') == $option->id)>{{ $option->nama }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-map-marker-alt"></i>Tempat
                                                Lahir</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-calendar-alt"></i>Tanggal
                                                Lahir</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-pray"></i>Agama</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="agama_id">
                                            <option value="">Pilih agama</option>
                                            @foreach ($agamaOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('agama_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-book-reader"></i>Pendidikan
                                                (dalam KK)</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="pendidikan_kk_id">
                                            <option value="">Pilih pendidikan</option>
                                            @foreach ($pendidikanOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('pendidikan_kk_id') == $option->id)>{{ $option->nama }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-school"></i>Pendidikan Sedang
                                                Ditempuh</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="pendidikan_sedang_id">
                                            <option value="">Pilih pendidikan</option>
                                            @foreach ($pendidikanOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('pendidikan_sedang_id') == $option->id)>{{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-briefcase"></i>Pekerjaan</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <div class="job-input-group">
                                            <select name="pekerjaan_id">
                                                <option value="">Pilih pekerjaan</option>
                                                @foreach ($pekerjaanOptions as $option)
                                                    <option value="{{ $option->id }}"
                                                        @selected(old('pekerjaan_id') == $option->id)>{{ $option->nama }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="pekerjaan_custom" list="pekerjaanList"
                                                value="{{ old('pekerjaan_custom') }}" placeholder="Tulis pekerjaan lain...">
                                        </div>
                                        <small class="form-field__hint">Pilih dari daftar atau isi manual jika belum
                                            tersedia.</small>
                                    </label>
                                    <datalist id="pekerjaanList">
                                        @foreach ($pekerjaanOptions as $option)
                                            <option value="{{ $option->nama }}"></option>
                                        @endforeach
                                    </datalist>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-phone"></i>No. HP / WhatsApp</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="nomor_hp" value="{{ old('nomor_hp') }}" maxlength="20" placeholder="Contoh: 0812...">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-envelope"></i>Email Aktif</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="email" name="email" value="{{ old('email') }}" maxlength="100" placeholder="contoh@email.com">
                                    </label>
                                </div>
                            </div>
                        </section>

                        <section class="form-section form-section--accent">
                            <div class="form-section__header">
                                <div class="form-section__title">
                                    <span class="form-section__icon form-section__icon--slate"><i
                                            class="fas fa-camera"></i></span>
                                    <div>
                                        <h2>Foto Penduduk</h2>
                                        <p>Unggah foto profil serta dokumentasi KTP dan KK. Kosongkan jika tidak ingin
                                            mengubah.</p>
                                    </div>
                                </div>
                                <span class="form-section__badge form-section__badge--soft"><i class="fas fa-images"></i>
                                    Format JPG/PNG, maks 5 MB per berkas.</span>
                            </div>
                            <div class="form-section__body">
                                <div class="settings-form-grid three-columns">
                                    <div class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-user-circle"></i>Foto
                                                Profil</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="file" name="foto_profil" accept="image/*">
                                        <div class="media-preview" data-photo-preview="foto_profil">
                                            <span class="media-preview__placeholder">Belum ada foto.</span>
                                        </div>
                                        <label class="media-preview__remove">
                                            <input type="checkbox" name="remove_foto_profil" value="1"
                                                @checked(old('remove_foto_profil'))>
                                            <span>Hapus foto profil saat ini</span>
                                        </label>
                                        <small class="form-field__hint">Gunakan foto wajah terbaru. Biarkan kosong jika
                                            tidak ingin mengganti.</small>
                                    </div>
                                    <div class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-card"></i>Foto KTP</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="file" name="foto_ktp" accept="image/*">
                                        <div class="media-preview" data-photo-preview="foto_ktp">
                                            <span class="media-preview__placeholder">Belum ada foto.</span>
                                        </div>
                                        <label class="media-preview__remove">
                                            <input type="checkbox" name="remove_foto_ktp" value="1"
                                                @checked(old('remove_foto_ktp'))>
                                            <span>Hapus foto KTP saat ini</span>
                                        </label>
                                        <small class="form-field__hint">Pastikan teks KTP terbaca jelas.</small>
                                    </div>
                                    <div class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-users"></i>Foto KK</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="file" name="foto_kk" accept="image/*">
                                        <div class="media-preview" data-photo-preview="foto_kk">
                                            <span class="media-preview__placeholder">Belum ada foto.</span>
                                        </div>
                                        <label class="media-preview__remove">
                                            <input type="checkbox" name="remove_foto_kk" value="1"
                                                @checked(old('remove_foto_kk'))>
                                            <span>Hapus foto KK saat ini</span>
                                        </label>
                                        <small class="form-field__hint">Gunakan salinan KK terbaru.</small>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="form-section__header">
                                <div class="form-section__title">
                                    <span class="form-section__icon form-section__icon--emerald"><i
                                            class="fas fa-users"></i></span>
                                    <div>
                                        <h2>Relasi Keluarga</h2>
                                        <p>Lengkapi hubungan keluarga, kewarganegaraan, serta data orang tua.</p>
                                    </div>
                                </div>
                                <span class="form-section__badge form-section__badge--soft"><i
                                        class="fas fa-circle-info"></i> Data boleh dikosongkan bila belum tersedia.</span>
                            </div>
                            <div class="form-section__body">
                                <div class="settings-form-grid three-columns">
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-heart"></i>Status
                                                Perkawinan</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="status_kawin_id">
                                            <option value="">Pilih status perkawinan</option>
                                            @foreach ($statusKawinOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('status_kawin_id') == $option->id)>{{ $option->nama }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-user-friends"></i>Hubungan
                                                dalam KK</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="kk_level_id">
                                            <option value="">Pilih hubungan</option>
                                            @foreach ($hubunganKkOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('kk_level_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i
                                                    class="fas fa-flag"></i>Kewarganegaraan</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="warganegara_id">
                                            <option value="">Pilih kewarganegaraan</option>
                                            @foreach ($warganegaraOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('warganegara_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-user-tie"></i>Nama Ayah</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-card"></i>NIK Ayah</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="ayah_nik" value="{{ old('ayah_nik') }}" maxlength="20">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-female"></i>Nama Ibu</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-card"></i>NIK Ibu</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="ibu_nik" value="{{ old('ibu_nik') }}" maxlength="20">
                                    </label>
                                </div>
                            </div>
                        </section>
                        <section class="form-section form-section--accent">
                            <div class="form-section__header">
                                <div class="form-section__title">
                                    <span class="form-section__icon form-section__icon--amber"><i
                                            class="fas fa-file-alt"></i></span>
                                    <div>
                                        <h2>Dokumen &amp; Status Administrasi</h2>
                                        <p>Catat dokumen penting, status kependudukan, serta identifikasi tambahan.</p>
                                    </div>
                                </div>
                                <span class="form-section__badge"><i class="fas fa-circle-check"></i> Pastikan status dasar
                                    terisi.</span>
                            </div>
                            <div class="form-section__body">
                                <div class="settings-form-grid two-columns">
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-tint"></i>Golongan Darah</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="golongan_darah_id">
                                            <option value="">Pilih golongan darah</option>
                                            @foreach ($golonganDarahOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('golongan_darah_id') == $option->id)>{{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-file-signature"></i>Akta
                                                Lahir</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="akta_lahir" value="{{ old('akta_lahir') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-passport"></i>Dokumen
                                                Paspor</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="dokumen_pasport" value="{{ old('dokumen_pasport') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-calendar-alt"></i>Tanggal Akhir
                                                Paspor</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="date" name="tanggal_akhir_paspor"
                                            value="{{ old('tanggal_akhir_paspor') }}">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-badge"></i>Dokumen
                                                KITAS</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="dokumen_kitas" value="{{ old('dokumen_kitas') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-heart"></i>Akta
                                                Perkawinan</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="akta_perkawinan" value="{{ old('akta_perkawinan') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-calendar-check"></i>Tanggal
                                                Perkawinan</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="date" name="tanggal_perkawinan"
                                            value="{{ old('tanggal_perkawinan') }}">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-user-times"></i>Akta
                                                Perceraian</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="akta_perceraian" value="{{ old('akta_perceraian') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-calendar-times"></i>Tanggal
                                                Perceraian</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="date" name="tanggal_perceraian"
                                            value="{{ old('tanggal_perceraian') }}">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-wheelchair"></i>Jenis
                                                Cacat</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="cacat_id">
                                            <option value="">Tidak ada</option>
                                            @foreach ($cacatOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('cacat_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-pray"></i>Cara KB</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="cara_kb_id">
                                            <option value="">Tidak menggunakan</option>
                                            @foreach ($caraKbOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('cara_kb_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-card"></i>Status Rekam
                                                KTP</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="status_rekam_id">
                                            <option value="">Belum ditentukan</option>
                                            @foreach ($statusRekamOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('status_rekam_id') == $option->id)>{{ $option->nama }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-project-diagram"></i>Status
                                                Dasar</span>
                                            <span class="form-field__tag form-field__tag--required">Wajib</span>
                                        </span>
                                        <select name="status_dasar_id" required>
                                            <option value="">Pilih status</option>
                                            @foreach ($statusDasarOptions as $option)
                                                <option value="{{ $option->id }}"
                                                    @selected(old('status_dasar_id') == $option->id)>{{ $option->nama }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-users"></i>Suku</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="suku_id">
                                            <option value="">Tidak diketahui</option>
                                            @foreach ($sukuOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('suku_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-female"></i>Sedang
                                                Hamil?</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="hamil">
                                            <option value="" @selected($hamilValue === '')>Tidak diketahui</option>
                                            <option value="1" @selected($hamilValue === '1')>Ya</option>
                                            <option value="0" @selected($hamilValue === '0')>Tidak</option>
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-id-card"></i>Memiliki
                                                KTP-el</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="ktp_el">
                                            <option value="0" @selected($ktpElValue === '0')>Tidak</option>
                                            <option value="1" @selected($ktpElValue === '1')>Ya</option>
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-tag"></i>Tag ID Card</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="tag_id_card" value="{{ old('tag_id_card') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-shield-alt"></i>ID
                                                Asuransi</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="id_asuransi" value="{{ old('id_asuransi') }}"
                                            maxlength="100">
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-hashtag"></i>No.
                                                Asuransi</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <input type="text" name="no_asuransi" value="{{ old('no_asuransi') }}"
                                            maxlength="100">
                                    </label>
                                </div>
                            </div>
                        </section>

                        <section class="form-section">
                            <div class="form-section__header">
                                <div class="form-section__title">
                                    <span class="form-section__icon form-section__icon--slate"><i
                                            class="fas fa-map-marked-alt"></i></span>
                                    <div>
                                        <h2>Alamat &amp; Wilayah</h2>
                                        <p>Perbarui domisili sesuai KK dan alamat terkini termasuk dusun/RW/RT.</p>
                                    </div>
                                </div>
                                <span class="form-section__badge form-section__badge--soft"><i
                                        class="fas fa-map-marker-alt"></i> Lengkapi jika sudah diketahui.</span>
                            </div>
                            <div class="form-section__body">
                                <div class="settings-form-grid two-columns">
                                    <label class="form-field form-field--full">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-home"></i>Alamat Domisili
                                                (sesuai KK)</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <textarea name="alamat" rows="2">{{ old('alamat') }}</textarea>
                                    </label>
                                    <label class="form-field form-field--full">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-map-marker-alt"></i>Alamat Saat
                                                Ini</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <textarea name="alamat_sekarang" rows="2">{{ old('alamat_sekarang') }}</textarea>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-tree"></i>Dusun</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="dusun_id">
                                            <option value="">Pilih dusun</option>
                                            @foreach ($dusunOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('dusun_id') == $option->id)>
                                                    {{ $option->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-stream"></i>RW</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="rw_id">
                                            <option value="">Pilih RW</option>
                                            @foreach ($rwOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('rw_id') == $option->id)>
                                                    Dusun {{ $option->dusun?->nama }} - RW {{ $option->nomor }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="form-field">
                                        <span class="form-field__label">
                                            <span class="form-field__title"><i class="fas fa-align-left"></i>RT</span>
                                            <span class="form-field__tag form-field__tag--optional">Opsional</span>
                                        </span>
                                        <select name="rt_id">
                                            <option value="">Pilih RT</option>
                                            @foreach ($rtOptions as $option)
                                                <option value="{{ $option->id }}" @selected(old('rt_id') == $option->id)>
                                                    RW {{ $option->rw?->nomor }} - RT {{ $option->nomor }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="dialog__footer form-actions-bar">
                        <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>
                            <i class="fas fa-arrow-left"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="primary-btn">
                            <i class="fas fa-save"></i>
                            <span id="residentFormSubmitLabel">Simpan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="dialog-backdrop" id="residentDetailModal" aria-hidden="true">
        <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="residentDetailTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="residentDetailTitle">Detail Penduduk</h2>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail penduduk">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <div class="resident-detail">
                    @foreach ($residentDetailSections as $section)
                        @php
                            $accent = $section['accent'] ?? null;
                            $accentClass = $accent ? 'resident-detail__section--' . $accent : '';
                        @endphp
                        <section class="resident-detail__section {{ $accentClass }}">
                            <header class="resident-detail__section-header">
                                <span class="resident-detail__section-icon">
                                    <i class="fas {{ $section['icon'] ?? 'fa-circle-info' }}"></i>
                                </span>
                                <div>
                                    <h3>{{ $section['title'] }}</h3>
                                </div>
                            </header>
                            <div class="resident-detail__grid">
                                @foreach ($section['fields'] as $field)
                                    @php
                                        $type = $field['type'] ?? '';
                                        $isLink = $type === 'link';
                                        $isImage = $type === 'image';
                                    @endphp
                                    <div class="resident-detail__item">
                                        <p class="resident-detail__label">
                                            {{ $field['label'] }}
                                        </p>
                                        @if ($isImage)
                                            <div class="resident-detail__image media-preview" data-detail-image="{{ $field['key'] }}"
                                                @if (!empty($field['optional'])) data-optional="true" @endif>
                                                <span class="media-preview__placeholder">Belum ada foto.</span>
                                            </div>
                                        @elseif ($isLink)
                                            <a class="resident-detail__value resident-detail__link" data-detail="{{ $field['key'] }}"
                                                data-link-label="{{ $field['link_label'] ?? 'Lihat berkas' }}" @if (!empty($field['optional'])) data-optional="true" @endif target="_blank"
                                                rel="noopener">-</a>
                                        @else
                                            <p class="resident-detail__value" data-detail="{{ $field['key'] }}" @if (!empty($field['optional'])) data-optional="true" @endif>-</p>
                                        @endif
                                        @if (!empty($field['hint']))
                                            <p class="resident-detail__hint">{{ $field['hint'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="dialog-backdrop" id="residentImportModal" aria-hidden="true">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="residentImportTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="residentImportTitle">Impor Data Penduduk</h2>
                    <p>Unggah berkas Excel untuk menambahkan atau memperbarui catatan penduduk.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup dialog impor">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.penduduks.import') }}" enctype="multipart/form-data"
                class="dialog__form" data-import-form>
                @csrf
                <div class="dialog__body dialog__body--flush">
                    <div class="import-card">
                        <div class="import-card__header">
                            <div class="import-card__icon">
                                <img src="{{ asset('img/Icon/logo_exel.png') }}" alt="Excel Logo">
                            </div>
                            <div>
                                <h3>Unggah Berkas Excel</h3>
                                <p>Seret & lepas atau pilih file Excel (.xls/.xlsx) maksimal 10 MB.</p>
                            </div>
                        </div>
                        <div class="import-dropzone" data-import-dropzone>
                            <div class="import-dropzone__inner">
                                <div class="import-dropzone__icon">
                                    <i class="fas fa-cloud-arrow-up"></i>
                                </div>
                                <p class="import-dropzone__title">Seret & lepas berkas ke sini</p>
                                <p class="import-dropzone__subtitle">atau</p>
                                <button type="button" class="outline-btn" data-import-trigger>
                                    <i class="fas fa-folder-open"></i>
                                    <span>Pilih Berkas</span>
                                </button>
                                <p class="import-dropzone__hint">Dukungan format: .xls, .xlsx • Maks. 10 MB</p>
                                <input type="file" name="file" accept=".xlsx,.xls" class="sr-only" data-import-input>
                            </div>
                        </div>
                        <div class="import-dropzone__filename" data-import-filename>Belum ada berkas dipilih</div>
                        <div class="import-file-list" data-import-file-list></div>
                        <div class="import-status" data-import-status></div>
                        <p class="import-footnote">
                            Tip: Unduh <a href="{{ route('admin.penduduks.export.template') }}">TEMPLATE EXCEL</a> untuk
                            memastikan kolom sesuai. Data dengan NIK sama akan diperbarui otomatis.
                        </p>
                    </div>
                </div>
                <div class="dialog__footer">
                    <button type="button" class="soft-action-btn soft-action-btn--neutral" data-modal-close>
                        <i class="fas fa-circle-xmark"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" class="soft-action-btn soft-action-btn--violet" data-import-submit disabled>
                        <span class="button-spinner" aria-hidden="true"></span>
                        <span data-import-submit-text>Mulai Impor</span>
                    </button>
                </div>
                <template data-import-file-template>
                    <div class="import-file is-pending" data-file-item>
                        <div class="import-file__thumb">
                            <img src="{{ asset('img/Icon/logo_exel.png') }}" alt="Excel">
                        </div>
                        <div class="import-file__info">
                            <span class="import-file__name" data-file-name></span>
                            <span class="import-file__meta">
                                <span class="import-file__status-icon" data-file-status-icon></span>
                                <span data-file-size>0 KB</span>
                                <span aria-hidden="true">•</span>
                                <span data-file-status>Menunggu</span>
                            </span>
                            <div class="import-file__progress-track" role="presentation">
                                <div class="import-file__progress-bar" data-file-progress role="progressbar"
                                    aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>
                            </div>
                        </div>
                        <button type="button" class="import-file__remove" data-file-remove aria-label="Batalkan unggahan">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>
            </form>
        </div>
    </div>

    <div class="dialog-backdrop" id="residentDeleteModal" aria-hidden="true">
        <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="residentDeleteTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="residentDeleteTitle">Hapus Data Penduduk?</h2>
                    <p>Konfirmasi untuk memindahkan data ke riwayat pengelolaan.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <p>Anda yakin ingin menghapus penduduk <strong id="residentDeleteName">ini</strong>? Data dapat dipulihkan
                    dari arsip jika diperlukan.</p>
            </div>
            <div class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                <button type="button" class="danger-btn" id="residentDeleteConfirm">Ya, hapus</button>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
@endsection

@include('admin.penduduks.partials.kk-picker-script')

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const TOAST_DURATION = 5000;

            const dismissToast = (toast) => {
                if (!toast || toast.classList.contains('is-leaving')) return;
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 250);
            };

            document.querySelectorAll('#residentToastStack [data-toast]').forEach((toast) => {
                const closeButton = toast.querySelector('[data-toast-close]');
                let timerId;

                const startTimer = () => {
                    timerId = window.setTimeout(() => dismissToast(toast), TOAST_DURATION);
                    toast.classList.remove('is-paused');
                };

                const stopTimer = () => {
                    if (timerId) {
                        window.clearTimeout(timerId);
                        timerId = null;
                    }
                    toast.classList.add('is-paused');
                };

                toast.addEventListener('mouseenter', stopTimer);
                toast.addEventListener('mouseleave', startTimer);
                closeButton?.addEventListener('click', () => {
                    stopTimer();
                    dismissToast(toast);
                });

                requestAnimationFrame(() => toast.classList.add('is-visible'));
                startTimer();
            });
        });
    </script>
@endpush