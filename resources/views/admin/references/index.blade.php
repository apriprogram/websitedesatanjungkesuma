@extends('admin.layouts.app')

@section('title', 'Referensi Data Kependudukan')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-references.css') }}">
    <style>
        /* REFERENCES TABLE - DIV GRID SCROLL (FORCE SYNC WITH DATA PINDAH) */
        
        /* BREAK THE EXPANSION CHAIN: Force all parents to respect viewport width */
        .reference-layout,
        .resident-panel--references,
        .reference-bulk-form {
            display: flex !important;
            flex-direction: column !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            overflow: hidden !important; /* Contain the scrollable child */
            box-sizing: border-box !important;
        }

        /* The container that actually scrolls */
        .resident-panel--references .resident-table-wrapper {
            display: block !important;
            width: 100% !important;
            max-width: calc(100vw - 3rem) !important; /* Definitively force it to be smaller than viewport on mobile */
            min-width: 0 !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            border-radius: 0 0 16px 16px !important;
            border: 1px solid rgba(226, 232, 240, 0.75) !important;
            border-top: none !important;
            position: relative !important;
            z-index: 1 !important;
            margin-bottom: 1rem !important;
            box-sizing: border-box !important;
        }

        /* The table that is wider than the wrapper on mobile */
        .resident-table--references {
            display: grid !important;
            --resident-table-columns: 50px 70px 1fr 180px 180px 140px !important;
            grid-template-columns: var(--resident-table-columns) !important;
            min-width: 1050px !important; /* FORCE scroll on mobile */
            width: 100% !important; /* Stretch on desktop */
            background: #fff !important;
        }

        /* Mobile specific overrides to force overflow */
        @media (max-width: 1024px) {
            .resident-table--references {
                --resident-table-columns: 50px 70px 350px 220px 220px 140px !important;
                width: 1050px !important;
                min-width: 1050px !important;
            }
        }

        @media (max-width: 768px) {
            .resident-table--references {
                --resident-table-columns: 45px 60px 300px 200px 200px 125px !important;
                width: 930px !important;
                min-width: 930px !important;
            }
        }

        /* Ensure row-level elements don't break the grid */
        .resident-table--references .resident-table__header,
        .resident-table--references .resident-table__body,
        .resident-table--references .resident-table__row {
            display: contents !important;
        }

        /* Aesthetic adjustments for the grid cells */
        .resident-table--references .resident-table__cell {
            padding: 12px 16px !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: flex-start !important; /* Horizontal left for data (column mode) */
            justify-content: center !important; /* Vertical center */
            text-align: left !important;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12) !important;
            font-size: 0.88rem !important;
            color: #1e293b !important;
            min-height: 52px !important;
            word-break: break-word !important;
        }

        /* Center specific utility columns */
        .resident-table--references .resident-table__cell--no,
        .resident-table--references .resident-table__cell--checkbox,
        .resident-table--references .resident-table__cell--actions {
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
        }

        .resident-table--references .resident-table__header .resident-table__cell {
            background: #f1f5f9 !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 0.75rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border-bottom: 2px solid rgba(148, 163, 184, 0.3) !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 2 !important;
            flex-direction: row !important; /* Header icons side by side */
            justify-content: flex-start !important; /* Horizontal left for header (row mode) */
            align-items: center !important; /* Vertical center */
        }

        /* Center header text only for centered columns */
        .resident-table--references .resident-table__header .resident-table__cell--no,
        .resident-table--references .resident-table__header .resident-table__cell--checkbox,
        .resident-table--references .resident-table__header .resident-table__cell--actions {
            justify-content: center !important;
        }


        .resident-table--references .resident-table__row:hover .resident-table__cell {
            background: rgba(241, 245, 249, 0.6) !important;
        }

        /* Dark mode support */
        body.dark-mode .resident-table--references {
            background: #1e293b !important;
        }
        body.dark-mode .resident-table--references .resident-table__header .resident-table__cell {
            background: rgba(15,23,42,0.7) !important;
            color: rgba(226,232,240,0.78) !important;
        }
        body.dark-mode .resident-table--references .resident-table__cell {
            color: rgba(226,232,240,0.9) !important;
            border-color: rgba(71,85,105,0.2) !important;
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
            const instances = [];

                const closeAllSelects = (exception = null) => {
                    instances.forEach(({ wrapper, trigger, dropdown, options }) => {
                        if (wrapper === exception) {
                            return;
                        }
                        wrapper.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    dropdown.hidden = true;
                    options.forEach((option) => option.tabIndex = -1);
                });
            };

            document.querySelectorAll('[data-fancy-select]').forEach((wrapper) => {
                if (wrapper.dataset.fancyReady === 'true') {
                    return;
                }
                wrapper.dataset.fancyReady = 'true';

                const select = wrapper.querySelector('[data-fancy-native]');
                const trigger = wrapper.querySelector('[data-fancy-trigger]') || wrapper.querySelector('.select-trigger');
                const label = wrapper.querySelector('[data-fancy-label]');
                const dropdown = wrapper.querySelector('[data-fancy-dropdown]');
                const options = Array.from(wrapper.querySelectorAll('[data-fancy-option]'));

                if (!select || !trigger || !label || !dropdown || options.length === 0) {
                    return;
                }

                const setValue = (value, emitChange = false) => {
                    const option = options.find((item) => (item.dataset.value ?? item.value) === value) ?? options[0];
                    const resolvedValue = option?.dataset.value ?? option?.value ?? value;
                    const resolvedLabel = option?.dataset.label ?? option?.textContent?.trim() ?? resolvedValue;

                    options.forEach((item) => {
                        const isActive = (item.dataset.value ?? item.value) === resolvedValue;
                        item.classList.toggle('is-active', isActive);
                        item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                    });

                    label.textContent = resolvedLabel;
                    if (select.value !== resolvedValue) {
                        select.value = resolvedValue;
                        if (emitChange) {
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                };

                const open = () => {
                    closeAllSelects(wrapper);
                    wrapper.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                    dropdown.hidden = false;
                    options.forEach((option) => option.tabIndex = 0);
                    const active = options.find((item) => item.classList.contains('is-active')) ?? options[0];
                    requestAnimationFrame(() => active?.focus({ preventScroll: true }));
                };

                const close = (focusTrigger = true) => {
                    wrapper.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                    dropdown.hidden = true;
                    options.forEach((option) => option.tabIndex = -1);
                    if (focusTrigger) {
                        trigger.focus({ preventScroll: true });
                    }
                };

                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    if (wrapper.classList.contains('is-open')) {
                        close();
                    } else {
                        open();
                    }
                });

                trigger.addEventListener('keydown', (event) => {
                    if (event.key === ' ' || event.key === 'Enter' || event.key === 'Spacebar') {
                        event.preventDefault();
                        wrapper.classList.contains('is-open') ? close() : open();
                    }
                });

                options.forEach((option, index) => {
                    option.addEventListener('click', (event) => {
                        event.preventDefault();
                        const value = option.dataset.value ?? option.value ?? '';
                        setValue(value, true);
                        close();
                    });

                    option.addEventListener('keydown', (event) => {
                        const key = event.key;
                        if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
                            event.preventDefault();
                            const value = option.dataset.value ?? option.value ?? '';
                            setValue(value, true);
                            close();
                            return;
                        }
                        if (key === 'ArrowDown') {
                            event.preventDefault();
                            const next = options[(index + 1) % options.length];
                            next?.focus({ preventScroll: true });
                            return;
                        }
                        if (key === 'ArrowUp') {
                            event.preventDefault();
                            const previous = options[(index - 1 + options.length) % options.length];
                            previous?.focus({ preventScroll: true });
                            return;
                        }
                        if (key === 'Escape') {
                            event.preventDefault();
                            close();
                        }
                    });
                });

                select.addEventListener('change', () => setValue(select.value, false));

                dropdown.hidden = true;
                options.forEach((option) => option.tabIndex = -1);
                setValue(select.value || options[0]?.dataset.value || '', false);

                instances.push({ wrapper, trigger, dropdown, options });
                wrapper.dataset.fancyReady = 'true';
            });

            document.addEventListener('click', (event) => {
                const wrapper = event.target.closest('[data-fancy-select]');
                closeAllSelects(wrapper);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAllSelects();
                }
            });

            const bulkForm = document.getElementById('referenceBulkForm');
            if (bulkForm) {
                const selectAllToggles = Array.from(bulkForm.querySelectorAll('[data-bulk-select-all]'));
                const countLabel = bulkForm.querySelector('[data-bulk-count]');
                const deleteTrigger = bulkForm.querySelector('[data-bulk-delete-trigger]');
                const confirmButton = document.querySelector('[data-bulk-confirm]');

                const getRowCheckboxes = () => Array.from(bulkForm.querySelectorAll('[data-bulk-checkbox]'));

                const updateBulkState = () => {
                    const checkboxes = getRowCheckboxes();
                    const checked = checkboxes.filter((checkbox) => checkbox.checked);
                    const total = checkboxes.length;
                    const selected = checked.length;

                    if (countLabel) {
                        countLabel.textContent = `${selected} data dipilih`;
                    }

                    if (deleteTrigger) {
                        deleteTrigger.disabled = selected === 0;
                    }

                    const allChecked = total > 0 && selected === total;
                    selectAllToggles.forEach((toggle) => {
                        toggle.checked = allChecked;
                        toggle.indeterminate = !allChecked && selected > 0;
                    });
                };

                selectAllToggles.forEach((toggle) => {
                    toggle.addEventListener('change', () => {
                        const checked = toggle.checked;
                        getRowCheckboxes().forEach((checkbox) => {
                            checkbox.checked = checked;
                        });
                        updateBulkState();
                    });
                });

                bulkForm.addEventListener('change', (event) => {
                    if (event.target.matches('[data-bulk-checkbox]')) {
                        updateBulkState();
                    }
                });

                updateBulkState();

                deleteTrigger?.addEventListener('click', (event) => {
                    if (deleteTrigger.disabled) {
                        event.preventDefault();
                        return;
                    }
                    const modal = document.getElementById('referenceBulkDeleteModal');
                    if (modal) {
                        modal.classList.add('is-visible');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                });

                confirmButton?.addEventListener('click', () => {
                    if (deleteTrigger?.disabled) {
                        return;
                    }
                    bulkForm.submit();
                });
            }

            const TOAST_DURATION = 5000;
            document.querySelectorAll('#referenceToastStack [data-toast]').forEach((toast) => {
                const closeButton = toast.querySelector('[data-toast-close]');
                let timerId;

                const dismissToast = () => {
                    if (toast.classList.contains('is-leaving')) return;
                    toast.classList.add('is-leaving');
                    window.setTimeout(() => toast.remove(), 250);
                };

                const startTimer = () => {
                    timerId = window.setTimeout(dismissToast, TOAST_DURATION);
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
                    dismissToast();
                });

                requestAnimationFrame(() => toast.classList.add('is-visible'));
                startTimer();
            });
        });
    </script>
@endpush



@php
    $lastUpdateAt = $lastUpdateAt ?? null;
    $lastCreatedAt = $lastCreatedAt ?? null;
    $tabBaseQuery = $tabBaseQuery ?? request()->except('page');
    $tableFrom = $tableFrom ?? 0;
    $tableTo = $tableTo ?? 0;
    $tableTotal = $tableTotal ?? 0;
    $toastNotifications = $toastNotifications ?? [];
    $recentNames = $recentNames ?? [];
    $categories = $categories ?? [];
    if (empty($categories)) {
        $categories = [
            [
                'slug' => 'default',
                'label' => 'Referensi',
                'description' => 'Belum ada deskripsi kategori.',
                'icon' => 'fas fa-tags',
                'count' => 0,
                'active' => true,
            ],
        ];
    }
    $activeCategory = $activeCategory ?? ($categories[0]['slug'] ?? 'default');
    $currentCategory = $currentCategory ?? [
        'slug' => $activeCategory,
        'label' => $categories[0]['label'] ?? 'Referensi',
        'description' => $categories[0]['description'] ?? 'Kelola entri referensi.',
    ];
    $entriesOptions = $entriesOptions ?? [25, 50, 100];
    $entries = $entries ?? ($entriesOptions[0] ?? 25);
    $search = $search ?? '';
    $sort = $sort ?? 'recent';
    $metrics = array_merge([
        'total' => 0,
        'today_changes' => 0,
    ], $metrics ?? []);
    $formContext = $formContext ?? old('form_context', '');
    $createCategoryValue = $createCategoryValue ?? ($activeCategory ?? ($categories[0]['slug'] ?? ''));
    $categorySlugs = array_column($categories, 'slug');
    $createCategoryIndex = array_search($createCategoryValue, $categorySlugs, true);
    $createCategoryLabel = $createCategoryLabel ?? ($createCategoryIndex !== false ? ($categories[$createCategoryIndex]['label'] ?? $currentCategory['label']) : $currentCategory['label']);
    $resetSearchQuery = $resetSearchQuery ?? [];
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
            <span class="header-title-text">Manajemen Referensi</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Kependudukan</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.references.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Referensi Data</a>
        </nav>
    </header>

    @if (! empty($toastNotifications))
        <div class="toast-stack" id="referenceToastStack" role="region" aria-live="polite">
            @foreach ($toastNotifications as $toast)
                @php $variant = $toast['variant'] ?? 'neutral'; @endphp
                <article class="toast" data-toast data-variant="{{ $variant }}">
                    <div class="toast__icon" aria-hidden="true">
                        @switch($variant)
                            @case('success') <i class="fas fa-check"></i> @break
                            @case('error') <i class="fas fa-xmark"></i> @break
                            @case('warning') <i class="fas fa-exclamation"></i> @break
                            @default <i class="fas fa-info"></i>
                        @endswitch
                    </div>
                    <div class="toast__content">
                        <strong>{{ $toast['title'] }}</strong>
                        @if (! empty($toast['message']))
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

    @include('admin.partials.alerts')

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Referensi Kependudukan</h1>
            <p>Kelola seluruh kategori referensi yang digunakan pada data penduduk Desa Tanjung Kesuma.</p>
        </div>
        <div class="title-actions">
            <button type="button" class="primary-btn" data-modal-open="referenceCreateModal">
                <i class="fas fa-plus"></i>
                <span>Tambah Referensi</span>
            </button>
        </div>
    </section>

    <div class="reference-layout">
        <section class="reference-metrics" aria-label="Ringkasan referensi">
            <article class="reference-metric">
                <span class="reference-metric__label">Total {{ $currentCategory['label'] }}</span>
                <p class="reference-metric__value">{{ number_format($metrics['total']) }}</p>
                <p class="reference-metric__subtitle">Entri aktif pada kategori ini</p>
                <span class="reference-metric__icon"><i class="fas fa-database"></i></span>
            </article>
            <article class="reference-metric">
                <span class="reference-metric__label">Perubahan Hari Ini</span>
                <p class="reference-metric__value">{{ number_format($metrics['today_changes']) }}</p>
                <p class="reference-metric__subtitle">
                    {{ $lastUpdateAt ? 'Pembaruan terakhir ' . $lastUpdateAt->diffForHumans() : 'Belum ada pembaruan' }}
                </p>
                <span class="reference-metric__icon"><i class="fas fa-bolt"></i></span>
            </article>
            <article class="reference-metric">
                <span class="reference-metric__label">Highlight Terbaru</span>
                <p class="reference-metric__value reference-metric__value--text">
                    {{ $lastCreatedAt ? $lastCreatedAt->translatedFormat('d M Y, H:i') : 'Belum ada data' }}
                </p>
                <p class="reference-metric__subtitle">Daftar entri yang baru diperbarui</p>
                @if (! empty($recentNames))
                    <div class="reference-metric__list">
                        @foreach ($recentNames as $name)
                            <span>{{ $name }}</span>
                        @endforeach
                    </div>
                @endif
                <span class="reference-metric__icon"><i class="fas fa-clock-rotate-left"></i></span>
        </article>
    </section>

        <nav class="tab-bar reference-tab-bar" role="tablist" aria-label="Daftar referensi">
            @foreach ($categories as $category)
                @php
                    $tabQuery = array_merge($tabBaseQuery, ['category' => $category['slug']]);
                @endphp
                <a
                    href="{{ route('admin.references.index', $tabQuery) }}"
                    class="reference-tab {{ $category['active'] ? 'is-active' : '' }}"
                    role="tab"
                    aria-selected="{{ $category['active'] ? 'true' : 'false' }}"
                >
                    <span class="reference-tab__icon">
                        <i class="{{ $category['icon'] }}"></i>
                    </span>
                    <div class="reference-tab__meta">
                        <strong>{{ $category['label'] }}</strong>
                    </div>
                    <span class="reference-tab__count">{{ number_format($category['count']) }}</span>
                </a>
            @endforeach
        </nav>

        <article class="resident-panel resident-panel--references reference-panel">
            <form method="GET" action="{{ route('admin.references.index') }}" id="referenceFilterForm">
                <input type="hidden" name="category" value="{{ $activeCategory }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <header class="panel-header panel-header--table agenda-panel__header">
                    <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                        <div class="resident-panel__title">
                            <h2>{{ $currentCategory['label'] }}</h2>
                            <p>{{ $currentCategory['description'] }}</p>
                        </div>
                        <div class="panel-toolbar-inline">
                            <div class="panel-filters">
                                {{-- Entries select grouped in panel-filters --}}
                                <select id="referenceEntriesTop" name="entries" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                    @foreach ($entriesOptions as $option)
                                        <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="search-input-group">
                                <div class="search-input-wrapper">
                                    <i class="fas fa-search search-icon-left"></i>
                                    <input
                                        id="referenceSearch"
                                        type="search"
                                        name="search"
                                        value="{{ $search }}"
                                        placeholder="Cari referensi..."
                                        aria-label="Cari referensi"
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            </form>

            <form id="referenceBulkForm" method="POST" action="{{ route('admin.references.bulk-destroy') }}" class="reference-bulk-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="category" value="{{ $activeCategory }}">
                <input type="hidden" name="entries" value="{{ $entries }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="sort" value="{{ $sort }}">

                <div class="reference-bulk-bar">
                    <label class="reference-select-all">
                        <input type="checkbox" data-bulk-select-all>
                        <span>Pilih semua</span>
                    </label>
                    <span class="reference-bulk-count" data-bulk-count>0 data dipilih</span>
                    <button type="button" class="danger-btn reference-bulk-delete" data-bulk-delete-trigger disabled>
                        <i class="fas fa-trash"></i>
                        <span>Hapus Terpilih</span>
                    </button>
                </div>

                <div class="resident-table-wrapper">
                    <section class="resident-table resident-table--references">
                        <header class="resident-table__header">
                            <div class="resident-table__cell resident-table__cell--checkbox" style="text-align: center; width: 50px;">
                                <input type="checkbox" data-bulk-select-all>
                            </div>
                            <div class="resident-table__cell resident-table__cell--no" style="text-align: center; width: 70px;">#</div>
                            <div class="resident-table__cell" style="text-align: left;">Nama Referensi</div>
                            <div class="resident-table__cell" style="text-align: center;">Dibuat Pada</div>
                            <div class="resident-table__cell" style="text-align: center;">Terakhir Diperbarui</div>
                            <div class="resident-table__cell resident-table__cell--actions" style="text-align: center;">Aksi</div>
                        </header>

                        <div class="resident-table__body">
                            @forelse ($references as $reference)
                                @php
                                    $rowNumber = $references->firstItem() ? $references->firstItem() + $loop->index : $loop->iteration;
                                    $createdLabel = $reference->created_at?->translatedFormat('d M Y, H:i') ?? '—';
                                    $updatedLabel = $reference->updated_at?->translatedFormat('d M Y, H:i') ?? '—';
                                    $detailModalId = 'referenceDetailModal-' . $reference->id;
                                    $editModalId = 'referenceEditModal-' . $reference->id;
                                    $deleteModalId = 'referenceDeleteModal-' . $reference->id;
                                @endphp
                                <article class="resident-table__row">
                                    <div class="resident-table__cell resident-table__cell--checkbox">
                                        <input
                                            type="checkbox"
                                            name="selected_ids[]"
                                            value="{{ $reference->id }}"
                                            data-bulk-checkbox
                                        >
                                    </div>
                                    <div class="resident-table__cell resident-table__cell--no">
                                        <span class="resident-table__index">{{ $rowNumber }}</span>
                                    </div>
                                    <div class="resident-table__cell" style="text-align: left;">
                                        <strong>{{ $reference->nama }}</strong>
                                    </div>
                                    <div class="resident-table__cell" style="text-align: center; align-items: center;">
                                        <span>{{ $createdLabel }}</span>
                                    </div>
                                    <div class="resident-table__cell" style="text-align: center; align-items: center;">
                                        <span>{{ $updatedLabel }}</span>
                                    </div>
                                    <div class="resident-table__cell resident-table__cell--actions">
                                        <div class="resident-actions">
                                            <button type="button" class="action-button action-button--primary" data-modal-open="{{ $detailModalId }}" title="Lihat detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="action-button action-button--neutral" data-modal-open="{{ $editModalId }}" title="Edit data">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <button type="button" class="action-button action-button--danger" data-modal-open="{{ $deleteModalId }}" title="Hapus data">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="resident-table__empty">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Belum ada data untuk kategori ini. Tambahkan referensi baru untuk mulai menggunakan kategori {{ $currentCategory['label'] }}.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <footer class="resident-panel__footer reference-panel__footer">
                    <div class="reference-footer-meta">
                        <div class="table-info">
                            Menampilkan {{ number_format($tableFrom) }}–{{ number_format($tableTo) }} dari {{ number_format($tableTotal) }} entri {{ strtolower($currentCategory['label']) }}
                        </div>
                        <div class="resident-pagination__links reference-pagination">
                            {{ $references->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
                        </div>
                    </div>
                </footer>
            </form>
        </article>
    </div>

    {{-- Create Modal --}}
    <div class="dialog-backdrop" id="referenceCreateModal" aria-hidden="true">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="referenceCreateTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="referenceCreateTitle">Tambah Referensi</h2>
                    <p>Masukkan nama referensi baru sesuai kategori yang dipilih.</p>
                </div>
                <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.references.store') }}" class="dialog__body">
                @csrf
                <input type="hidden" name="form_context" value="referenceCreateModal">
                <div class="settings-form-grid">
                    <label class="form-field">
                        <span>Kategori Referensi</span>
                        <div class="select-wrapper" data-fancy-select>
                            <select name="category" data-fancy-native hidden required>
                                @foreach ($categories as $slug => $category)
                                    <option value="{{ $slug }}" @selected($createCategoryValue === $slug)>{{ $category['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="select-trigger" data-fancy-trigger aria-haspopup="listbox" aria-expanded="false">
                                <span data-fancy-label>{{ $createCategoryLabel }}</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path fill="currentColor" d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z"></path>
                                </svg>
                            </button>
                            <div class="select-dropdown" data-fancy-dropdown role="listbox" hidden>
                                @foreach ($categories as $slug => $category)
                                    @php $isActive = $createCategoryValue === $slug; @endphp
                                    <button
                                        type="button"
                                        class="select-option{{ $isActive ? ' is-active' : '' }}"
                                        data-fancy-option
                                        data-value="{{ $slug }}"
                                        data-label="{{ $category['label'] }}"
                                        role="option"
                                        aria-selected="{{ $isActive ? 'true' : 'false' }}"
                                        tabindex="-1"
                                    >
                                        <span>{{ $category['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </label>
                    <label class="form-field">
                        <span>Nama Referensi <sup>*</sup></span>
                        <input
                            type="text"
                            name="nama"
                            value="{{ $formContext === 'referenceCreateModal' ? old('nama') : '' }}"
                            maxlength="100"
                            required
                        >
                        @if ($formContext === 'referenceCreateModal' && $errors->has('nama'))
                            <span class="form-error">{{ $errors->first('nama') }}</span>
                        @endif
                    </label>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-save"></i>
                        <span>Simpan Referensi</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Detail / Edit / Delete Modals --}}
    @foreach ($references as $reference)
        @php
            $detailModalId = 'referenceDetailModal-' . $reference->id;
            $editModalId = 'referenceEditModal-' . $reference->id;
            $deleteModalId = 'referenceDeleteModal-' . $reference->id;
            $matchesContext = $formContext === $editModalId;
        @endphp

        <div class="dialog-backdrop" id="{{ $detailModalId }}" aria-hidden="true">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $detailModalId }}-title">
                <header class="dialog__header">
                    <div>
                        <h2 id="{{ $detailModalId }}-title">Detail Referensi</h2>
                        <p>Informasi lengkap untuk entri "{{ $reference->nama }}".</p>
                    </div>
                    <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Nama Referensi</dt>
                            <dd>{{ $reference->nama }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>ID Referensi</dt>
                            <dd>#{{ $reference->id }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Dibuat pada</dt>
                            <dd>{{ $reference->created_at?->translatedFormat('d M Y, H:i') ?? '—' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Terakhir diperbarui</dt>
                            <dd>{{ $reference->updated_at?->translatedFormat('d M Y, H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn" data-modal-close>Tutup</button>
                    <button type="button" class="primary-btn" data-modal-open="{{ $editModalId }}">Edit Referensi</button>
                </footer>
            </div>
        </div>

        <div class="dialog-backdrop" id="{{ $editModalId }}" aria-hidden="true">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $editModalId }}-title">
                <header class="dialog__header">
                    <div>
                        <h2 id="{{ $editModalId }}-title">Edit {{ $reference->nama }}</h2>
                        <p>Perbarui nama referensi sesuai kebutuhan.</p>
                    </div>
                    <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.references.update', ['category' => $activeCategory, 'reference' => $reference->id]) }}" class="dialog__body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_context" value="{{ $editModalId }}">
                    <div class="settings-form-grid">
                        <label class="form-field">
                            <span>Nama Referensi <sup>*</sup></span>
                            <input
                                type="text"
                                name="nama"
                                value="{{ $matchesContext ? old('nama', $reference->nama) : $reference->nama }}"
                                maxlength="100"
                                required
                            >
                            @if ($matchesContext && $errors->has('nama'))
                                <span class="form-error">{{ $errors->first('nama') }}</span>
                            @endif
                        </label>
                    </div>
                    <footer class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">
                            <i class="fas fa-save"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>

        <div class="dialog-backdrop" id="{{ $deleteModalId }}" aria-hidden="true">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="{{ $deleteModalId }}-title">
                <header class="dialog__header">
                    <div>
                        <h2 id="{{ $deleteModalId }}-title">Hapus Referensi</h2>
                        <p>Konfirmasi penghapusan "{{ $reference->nama }}". Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.references.destroy', ['category' => $activeCategory, 'reference' => $reference->id]) }}" class="dialog__body">
                    @csrf
                    @method('DELETE')
                    <p>Apakah Anda yakin ingin menghapus referensi <strong>{{ $reference->nama }}</strong>?</p>
                    <footer class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                    <button type="submit" class="danger-btn" data-modal-close>
                            <i class="fas fa-trash"></i>
                            <span>Ya, hapus</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Bulk delete confirmation --}}
    <div class="dialog-backdrop" id="referenceBulkDeleteModal" aria-hidden="true">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="referenceBulkDeleteTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="referenceBulkDeleteTitle">Hapus Data Terpilih?</h2>
                    <p>Konfirmasi penghapusan beberapa referensi sekaligus. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <p>Pastikan Anda sudah memilih data yang benar. Semua data yang dipilih akan dihapus permanen.</p>
            </div>
            <footer class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                <button type="button" class="danger-btn" data-bulk-confirm>
                    <i class="fas fa-trash"></i>
                    <span>Ya, hapus semua</span>
                </button>
            </footer>
        </div>
    </div>
@endsection
