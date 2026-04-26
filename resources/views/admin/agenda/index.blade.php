@extends('admin.layouts.app')

@section('title', 'Agenda Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-agenda.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-budget.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/admin-agenda.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const bulkForm = document.getElementById('agendaBulkForm');
            if (bulkForm) {
                const selectAllToggles = Array.from(
                    document.querySelectorAll(`[data-bulk-select-all][data-target-form="${bulkForm.id}"]`)
                );
                const countLabel = bulkForm.querySelector('[data-bulk-count]');
                const deleteTrigger = bulkForm.querySelector('[data-bulk-delete-trigger]');
                const confirmButton = document.querySelector('[data-bulk-confirm]');

                const getRowCheckboxes = () =>
                    Array.from(document.querySelectorAll(`[data-bulk-checkbox][form="${bulkForm.id}"]`));

                const updateBulkState = () => {
                    const checkboxes = getRowCheckboxes();
                    const checked = checkboxes.filter((checkbox) => checkbox.checked);
                    const total = checkboxes.length;
                    const selected = checked.length;

                    if (countLabel) {
                        countLabel.textContent = `${selected} agenda dipilih`;
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

                getRowCheckboxes().forEach((checkbox) => {
                    checkbox.addEventListener('change', updateBulkState);
                });

                updateBulkState();

                deleteTrigger?.addEventListener('click', (event) => {
                    if (deleteTrigger.disabled) {
                        event.preventDefault();
                        return;
                    }
                    const modal = document.getElementById('agendaBulkDeleteModal');
                    if (modal) {
                        modal.classList.add('is-visible');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                    if (confirmButton) {
                        confirmButton.dataset.targetForm = bulkForm.id;
                    }
                });

                confirmButton?.addEventListener('click', () => {
                    if (deleteTrigger?.disabled) {
                        return;
                    }
                    if (confirmButton.dataset.targetForm === bulkForm.id) {
                        bulkForm.submit();
                    }
                });
            }

            const historyBulkForm = document.getElementById('agendaHistoryBulkForm');
            if (historyBulkForm) {
                const selectAllToggles = Array.from(
                    document.querySelectorAll(`[data-bulk-select-all][data-target-form="${historyBulkForm.id}"]`)
                );
                const countLabel = historyBulkForm.querySelector('[data-bulk-count]');
                const deleteTrigger = historyBulkForm.querySelector('[data-bulk-delete-trigger]');
                const confirmButton = document.querySelector('[data-bulk-confirm]');

                const getRowCheckboxes = () =>
                    Array.from(document.querySelectorAll(`[data-bulk-checkbox][form="${historyBulkForm.id}"]`));

                const updateBulkState = () => {
                    const checkboxes = getRowCheckboxes();
                    const checked = checkboxes.filter((checkbox) => checkbox.checked);
                    const total = checkboxes.length;
                    const selected = checked.length;

                    if (countLabel) {
                        countLabel.textContent = `${selected} agenda dipilih`;
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

                getRowCheckboxes().forEach((checkbox) => {
                    checkbox.addEventListener('change', updateBulkState);
                });

                updateBulkState();

                deleteTrigger?.addEventListener('click', (event) => {
                    if (deleteTrigger.disabled) {
                        event.preventDefault();
                        return;
                    }
                    const modal = document.getElementById('agendaBulkDeleteModal');
                    if (modal) {
                        modal.classList.add('is-visible');
                        modal.setAttribute('aria-hidden', 'false');
                    }
                    if (confirmButton) {
                        confirmButton.dataset.targetForm = historyBulkForm.id;
                    }
                });

                confirmButton?.addEventListener('click', () => {
                    if (deleteTrigger?.disabled) {
                        return;
                    }
                    if (confirmButton.dataset.targetForm === historyBulkForm.id) {
                        historyBulkForm.submit();
                    }
                });
            }

            const TOAST_DURATION = 5000;
            const dismissToast = (toast) => {
                if (!toast || toast.classList.contains('is-leaving')) return;
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 260);
            };

            document.querySelectorAll('#agendaToastStack [data-toast]').forEach((toast) => {
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



@php
    $priorityLabels = [
        'high' => 'Tinggi',
        'medium' => 'Sedang',
        'low' => 'Rendah',
    ];

    $priorityHints = [
        'high' => 'Segera ditindaklanjuti',
        'medium' => 'Dipantau rutin',
        'low' => 'Bisa dijadwalkan ulang',
    ];

    $toastNotifications = [];
    $statusMessage = session('status');
    $statusVariant = session('status_variant');

    if ($statusMessage) {
        $feedbackTone = $statusVariant ?? 'success';
        if (!$statusVariant) {
            $normalizedStatus = \Illuminate\Support\Str::lower($statusMessage);
            if (\Illuminate\Support\Str::contains($normalizedStatus, ['hapus', 'dihapus', 'delete'])) {
                $feedbackTone = 'danger';
            } elseif (\Illuminate\Support\Str::contains($normalizedStatus, ['perbarui', 'update', 'ubah'])) {
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

    if (isset($errors) && $errors->any()) {
        $toastNotifications[] = [
            'variant' => 'error',
            'title' => 'Form tidak valid',
            'message' => $errors->first(),
        ];
    }
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
            <span class="header-title-text">Manajemen Agenda Desa</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.agendas.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Agenda Desa</a>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Agenda Desa</h1>
            <p>Kelola daftar tugas internal desa dalam tampilan tabel dan modal interaktif.</p>
        </div>
        <div class="title-actions">
            <button type="button" class="primary-btn" data-modal-open="agendaFormModal" data-form-mode="create">
                <i class="fas fa-plus"></i>
                <span>Tambah Agenda</span>
            </button>
        </div>
    </section>

    @if (!empty($toastNotifications))
        <div class="toast-stack" id="agendaToastStack" role="region" aria-live="polite">
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

    <section class="agenda-summary-grid">
        <article class="agenda-summary-card">
            <i class="fas fa-clipboard-list agenda-summary-icon"></i>
            <h3>Total Agenda</h3>
            <strong>{{ number_format($stats['total']) }}</strong>
            <span>{{ $stats['completion_rate'] }}% selesai</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--info">
            <i class="fas fa-clock agenda-summary-icon"></i>
            <h3>Masih Aktif</h3>
            <strong>{{ number_format($stats['active']) }}</strong>
            <span>Agenda yang belum diselesaikan</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--success">
            <i class="fas fa-check-circle agenda-summary-icon"></i>
            <h3>Sudah Selesai</h3>
            <strong>{{ number_format($stats['completed']) }}</strong>
            <span>Kegiatan tuntas oleh tim</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--danger">
            <i class="fas fa-exclamation-triangle agenda-summary-icon"></i>
            <h3>Lewat Tenggat</h3>
            <strong>{{ number_format($stats['overdue']) }}</strong>
            <span>Agenda perlu perhatian segera</span>
        </article>
        <article class="agenda-summary-card agenda-summary-card--warning">
            <i class="fas fa-hourglass-half agenda-summary-icon"></i>
            <h3>Jatuh Tempo 7 Hari</h3>
            <strong>{{ number_format($stats['upcoming']) }}</strong>
            <span>Agenda yang segera berakhir</span>
        </article>
    </section>

    {{-- Main Content (Full Width) --}}
    <div class="agenda-main-content">
        <article class="panel panel--flush agenda-panel" id="agenda-aktif">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div>
                        <h2>Agenda Aktif</h2>
                        <p>Pantau agenda berjalan, tandai selesai, atau perbarui detailnya.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.agendas.index') }}#agenda-aktif" class="panel-toolbar-inline">
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                        <input type="hidden" name="priority" value="{{ $filters['priority'] }}">

                        {{-- Filters as dropdowns --}}
                        <div class="panel-filters">
                            <select name="status" aria-label="Filter status" class="form-select-sm panel-filter-select"
                                onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Aktif
                                </option>
                                <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Selesai
                                </option>
                                <option value="overdue" {{ $filters['status'] === 'overdue' ? 'selected' : '' }}>
                                    Terlambat</option>
                            </select>
                            <select name="priority" aria-label="Filter prioritas" class="form-select-sm panel-filter-select"
                                onchange="this.form.submit()">
                                <option value="">Semua Prioritas</option>
                                <option value="high" {{ $filters['priority'] === 'high' ? 'selected' : '' }}>Tinggi
                                </option>
                                <option value="medium" {{ $filters['priority'] === 'medium' ? 'selected' : '' }}>Sedang
                                </option>
                                <option value="low" {{ $filters['priority'] === 'low' ? 'selected' : '' }}>Rendah
                                </option>
                            </select>
                        </div>

                        {{-- Search Bar --}}
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input type="search" name="search" value="{{ $filters['search'] }}"
                                    placeholder="Cari agenda..." aria-label="Cari agenda">
                            </div>
                        </div>
                    </form>
                </div>
            </header>

            <form id="agendaBulkForm" method="POST" action="{{ route('admin.agendas.bulk-destroy') }}"
                class="agenda-bulk-form">
                @csrf
                @method('DELETE')
                <div class="agenda-bulk-bar">
                    <label class="agenda-select-all">
                        <input type="checkbox" data-bulk-select-all data-target-form="agendaBulkForm">
                        <span>Pilih semua</span>
                    </label>
                    <span class="agenda-bulk-count" data-bulk-count>0 agenda dipilih</span>
                    <button type="button" class="danger-btn agenda-bulk-delete" data-bulk-delete-trigger disabled>
                        <i class="fas fa-trash"></i>
                        <span>Hapus Terpilih</span>
                    </button>
                </div>
            </form>

            <div class="news-table-wrap agenda-table-scroll-active">
                <div class="news-table">
                    <table class="table-budget">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" data-bulk-select-all data-target-form="agendaBulkForm">
                                </th>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th style="min-width: 250px; text-align: left;">Agenda</th>
                                <th style="width: 140px; text-align: center;">Tenggat</th>
                                <th style="width: 120px; text-align: center;">Prioritas</th>
                                <th style="width: 120px; text-align: center;">Status</th>
                                <th style="min-width: 165px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activeAgendas as $agenda)
                                @php
                                    $dueDate = $agenda->due_date;
                                    $isCompleted = $agenda->is_completed;
                                    $daysUntilDue = $dueDate ? now()->diffInDays($dueDate, false) : null;
                                    $isOverdue = $dueDate && !$isCompleted && $daysUntilDue !== null && $daysUntilDue < 0;
                                    $isUpcoming = $dueDate && !$isCompleted && $daysUntilDue !== null && $daysUntilDue >= 0 && $daysUntilDue <= 7;
                                    $owner = $agenda->updater?->nama ?? $agenda->creator?->nama ?? 'Sistem';
                                    $rowNumber = $activeAgendas->firstItem() ? $activeAgendas->firstItem() + $loop->index : $loop->iteration;
                                @endphp
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $agenda->id }}"
                                            data-bulk-checkbox form="agendaBulkForm">
                                    </td>
                                    <td style="text-align: center;">{{ $rowNumber }}</td>
                                    <td style="text-align: left;">
                                        <strong>{{ $agenda->title }}</strong>
                                        <p class="agenda-table__description">{{ strip_tags(html_entity_decode($agenda->description)) ?: 'Tidak ada deskripsi.' }}</p>
                                        <div class="agenda-meta">
                                            <span><i class="fas fa-user-edit"></i> {{ $owner }}</span>
                                            <span><i class="fas fa-clock"></i>
                                                {{ $agenda->updated_at?->diffForHumans() ?? 'Baru dibuat' }}</span>
                                            @if ($isOverdue)
                                                <span class="agenda-status-note agenda-status-note--overdue">
                                                    <i class="fas fa-triangle-exclamation"></i> Terlambat
                                                </span>
                                            @elseif ($isUpcoming)
                                                <span class="agenda-status-note agenda-status-note--upcoming">
                                                    <i class="fas fa-hourglass-half"></i> Hampir jatuh tempo
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        {{ $dueDate ? $dueDate->translatedFormat('d M Y') : 'Tidak ditentukan' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="priority-pill priority-pill--{{ $agenda->priority }}">
                                            {{ $priorityLabels[$agenda->priority] ?? ucfirst($agenda->priority) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="status-pill status-neutral">
                                            Terjadwal
                                        </span>
                                    </td>
                                    <td>
                                        <div class="resident-table__cell--actions" style="justify-content: center;">
                                            <form method="POST" action="{{ route('admin.agendas.toggle', $agenda) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="agenda-action-btn agenda-action-btn--complete"
                                                    aria-label="Tandai agenda selesai">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="agenda-action-btn agenda-action-btn--view"
                                                data-modal-open="agendaViewModal"
                                                data-agenda-title="{{ e($agenda->title) }}"
                                                data-agenda-description="{{ e($agenda->description ?? '') }}"
                                                data-agenda-due-date="{{ $dueDate?->translatedFormat('d F Y') ?? '-' }}"
                                                data-agenda-priority="{{ $agenda->priority }}"
                                                data-agenda-status="Terjadwal"
                                                data-agenda-owner="{{ $agenda->updater?->nama ?? $agenda->creator?->nama ?? 'Sistem' }}"
                                                data-agenda-created="{{ $agenda->created_at?->translatedFormat('d F Y, H:i') }}"
                                                title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="agenda-action-btn agenda-action-btn--edit"
                                                data-modal-open="agendaFormModal" data-form-mode="edit"
                                                data-agenda-id="{{ $agenda->id }}"
                                                data-agenda-title="{{ e($agenda->title) }}"
                                                data-agenda-description="{{ e($agenda->description ?? '') }}"
                                                data-agenda-due-date="{{ $dueDate?->format('Y-m-d') }}"
                                                data-agenda-priority="{{ $agenda->priority }}"
                                                data-update-url="{{ route('admin.agendas.update', $agenda) }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}"
                                                class="agenda-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="agenda-action-btn agenda-action-btn--delete"
                                                    data-delete-trigger data-agenda-title="{{ e($agenda->title) }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="banner-panel-empty">
                                        <div class="agenda-empty-state">
                                            <i class="fas fa-clipboard-list"></i>
                                            <strong>Belum ada agenda yang tercatat.</strong>
                                            <span>Gunakan tombol “Tambah Agenda” untuk membuat agenda baru.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                {{ $activeAgendas->fragment('agenda-aktif')->onEachSide(1)->links() }}
            </div>
        </article>

        <article class="panel panel--flush agenda-panel agenda-panel--history" id="agenda-riwayat">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div>
                        <h2>Riwayat Agenda Selesai</h2>
                        <p>Daftar agenda yang telah dituntaskan, dapat dikembalikan kapan pun dibutuhkan.</p>
                    </div>
                    <form method="GET" action="{{ route('admin.agendas.index') }}#agenda-riwayat"
                        class="panel-toolbar-inline">
                        <input type="hidden" name="status" value="{{ $filters['status'] }}">
                        <input type="hidden" name="priority" value="{{ $filters['priority'] }}">

                        <div class="panel-filters">
                            <select name="limit" aria-label="Tampilkan data riwayat" class="form-select-sm panel-filter-select"
                                onchange="this.form.submit()">
                                <option value="5" {{ $filters['limit'] == 5 ? 'selected' : '' }}>5 Data</option>
                                <option value="10" {{ $filters['limit'] == 10 ? 'selected' : '' }}>10 Data</option>
                                <option value="20" {{ $filters['limit'] == 20 ? 'selected' : '' }}>20 Data</option>
                                <option value="50" {{ $filters['limit'] == 50 ? 'selected' : '' }}>50 Data</option>
                            </select>
                        </div>

                        {{-- Search Bar --}}
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input type="search" name="search" value="{{ $filters['search'] }}"
                                    placeholder="Cari riwayat..." aria-label="Cari riwayat">
                            </div>
                        </div>
                    </form>
                </div>
            </header>
            <form id="agendaHistoryBulkForm" method="POST" action="{{ route('admin.agendas.bulk-destroy') }}"
                class="agenda-bulk-form">
                @csrf
                @method('DELETE')
                <div class="agenda-bulk-bar">
                    <label class="agenda-select-all">
                        <input type="checkbox" data-bulk-select-all data-target-form="agendaHistoryBulkForm">
                        <span>Pilih semua</span>
                    </label>
                    <span class="agenda-bulk-count" data-bulk-count>0 agenda dipilih</span>
                    <button type="button" class="danger-btn agenda-bulk-delete" data-bulk-delete-trigger disabled>
                        <i class="fas fa-trash"></i>
                        <span>Hapus Terpilih</span>
                    </button>
                </div>
            </form>

            <div class="news-table-wrap">
                <div class="news-table">
                    <table class="table-budget">
                        <thead>
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" data-bulk-select-all data-target-form="agendaHistoryBulkForm">
                                </th>
                                <th style="width: 50px; text-align: center;">#</th>
                                <th style="min-width: 250px; text-align: left;">Agenda</th>
                                <th style="width: 140px; text-align: center;">Tenggat</th>
                                <th style="width: 120px; text-align: center;">Prioritas</th>
                                <th style="width: 120px; text-align: center;">Status</th>
                                <th style="min-width: 135px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($historyAgendas as $agenda)
                                @php
                                    $dueDate = $agenda->due_date;
                                    $rowNumber = $historyAgendas->firstItem() ? $historyAgendas->firstItem() + $loop->index : $loop->iteration;
                                    $owner = $agenda->updater?->nama ?? $agenda->creator?->nama ?? 'Sistem';
                                @endphp
                                <tr>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $agenda->id }}"
                                            data-bulk-checkbox form="agendaHistoryBulkForm">
                                    </td>
                                    <td style="text-align: center;">{{ $rowNumber }}</td>
                                    <td style="text-align: left;">
                                        <strong>{{ $agenda->title }}</strong>
                                        <p class="agenda-table__description">{{ strip_tags(html_entity_decode($agenda->description)) ?: 'Tidak ada deskripsi.' }}</p>
                                        <div class="agenda-meta">
                                            <span><i class="fas fa-check-circle"></i> Selesai
                                                {{ $agenda->updated_at?->diffForHumans() }}</span>
                                            <span><i class="fas fa-user-check"></i> {{ $owner }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        {{ $dueDate ? $dueDate->translatedFormat('d M Y') : '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="priority-pill priority-pill--{{ $agenda->priority }}">
                                            {{ $priorityLabels[$agenda->priority] ?? ucfirst($agenda->priority) }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="status-pill status-success">
                                            Selesai
                                        </span>
                                    </td>
                                    <td>
                                        <div class="resident-table__cell--actions" style="justify-content: center;">
                                            <form method="POST" action="{{ route('admin.agendas.toggle', $agenda) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="agenda-action-btn agenda-action-btn--restore"
                                                    aria-label="Kembalikan agenda ke aktif" title="Kembalikan ke aktif">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="agenda-action-btn agenda-action-btn--view"
                                                data-modal-open="agendaViewModal"
                                                data-agenda-title="{{ e($agenda->title) }}"
                                                data-agenda-description="{{ e($agenda->description ?? '') }}"
                                                data-agenda-due-date="{{ $dueDate?->translatedFormat('d F Y') ?? '-' }}"
                                                data-agenda-priority="{{ $agenda->priority }}"
                                                data-agenda-status="Selesai"
                                                data-agenda-owner="{{ $agenda->updater?->nama ?? $agenda->creator?->nama ?? 'Sistem' }}"
                                                data-agenda-created="{{ $agenda->created_at?->translatedFormat('d F Y, H:i') }}"
                                                title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.agendas.destroy', $agenda) }}"
                                                class="agenda-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    class="agenda-action-btn agenda-action-btn--delete"
                                                    data-delete-trigger data-agenda-title="{{ e($agenda->title) }}"
                                                    title="Hapus permanen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="banner-panel-empty">
                                        <div class="agenda-empty-state">
                                            <i class="fas fa-history"></i>
                                            <strong>Belum ada riwayat agenda selesai.</strong>
                                            <span>Agenda yang ditandai selesai akan muncul di sini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel-footer">
                <div class="agenda-entries-info">
                    Menampilkan <strong>{{ $historyAgendas->firstItem() ?? 0 }}</strong> - <strong>{{ $historyAgendas->lastItem() ?? 0 }}</strong> dari <strong>{{ $historyAgendas->total() }}</strong> riwayat
                </div>
                {{ $historyAgendas->fragment('agenda-riwayat')->onEachSide(1)->links() }}
            </div>
        </article>

    </div> {{-- Closing agenda-main-content --}}

    {{-- Modal Form --}}
    <div class="dialog-backdrop" id="agendaFormModal" aria-hidden="true">
        <div class="dialog dialog--lg" role="dialog" aria-modal="true" aria-labelledby="agendaFormTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="agendaFormTitle">Tambah Agenda Baru</h2>
                    <p id="agendaFormSubtitle">
                        Isi detail agenda untuk mengatur prioritas dan jadwal pelaksanaannya.
                    </p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form agenda">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <form id="agendaForm" class="panel-form modal-form" method="POST"
                    action="{{ route('admin.agendas.store') }}" data-create-action="{{ route('admin.agendas.store') }}"
                    data-create-subtitle="Isi detail agenda untuk mengatur prioritas dan jadwal pelaksanaannya."
                    data-edit-subtitle=""
                    data-has-errors="{{ isset($errors) && $errors->any() ? 'true' : 'false' }}">
                    @csrf
                    <input type="hidden" name="_method" value="PUT" disabled>

                    <label class="form-field">
                        <span>Judul Agenda</span>
                        <input type="text" name="title" value="{{ old('title') }}" required>
                    </label>
                    <label class="form-field">
                        <span>Deskripsi</span>
                        <textarea name="description" rows="10" placeholder="Tuliskan catatan tambahan...">{{ old('description') }}</textarea>
                    </label>

                    <div class="settings-form-grid two-columns">
                        <label class="form-field">
                            <span>Tanggal Tenggat</span>
                            <input type="date" name="due_date" value="{{ old('due_date') }}">
                        </label>
                        <fieldset class="form-field">
                            <span>Prioritas Agenda</span>
                            <div class="priority-options" role="radiogroup" aria-label="Prioritas agenda">
                                <label class="priority-option priority-option--high">
                                    <input type="radio" name="priority" value="high"
                                        {{ old('priority') === 'high' ? 'checked' : '' }}>
                                    <span class="priority-option__label"><i class="fas fa-angles-up"></i> Tinggi</span>
                                </label>
                                <label class="priority-option priority-option--medium">
                                    <input type="radio" name="priority" value="medium"
                                        {{ old('priority', 'medium') === 'medium' ? 'checked' : '' }}>
                                    <span class="priority-option__label"><i class="fas fa-angle-up"></i> Sedang</span>
                                </label>
                                <label class="priority-option priority-option--low">
                                    <input type="radio" name="priority" value="low"
                                        {{ old('priority') === 'low' ? 'checked' : '' }}>
                                    <span class="priority-option__label"><i class="fas fa-angle-down"></i> Rendah</span>
                                </label>
                            </div>
                        </fieldset>
                    </div>
                    <div class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-close>Batalkan</button>
                        <button type="submit" class="primary-btn">
                            <span id="agendaFormSubmitLabel">Simpan Agenda</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="dialog-backdrop" id="agendaDeleteModal" aria-hidden="true">
        <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="agendaDeleteTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="agendaDeleteTitle">Hapus Agenda?</h2>
                    <p id="agendaDeleteSubtitle">Tindakan ini tidak dapat dibatalkan setelah dikonfirmasi.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <p>Anda yakin ingin menghapus agenda <strong id="agendaDeleteName">-</strong>?</p>
                <p class="table-cell__meta">Agenda yang dihapus tidak dapat dipulihkan.</p>
            </div>
            <div class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                <button type="button" class="danger-btn" id="agendaDeleteConfirm">Ya, hapus</button>
            </div>
        </div>
    </div>

    {{-- Bulk delete confirmation --}}
    <div class="dialog-backdrop" id="agendaBulkDeleteModal" aria-hidden="true">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="agendaBulkDeleteTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="agendaBulkDeleteTitle">Hapus Agenda Terpilih?</h2>
                    <p>Konfirmasi penghapusan beberapa agenda sekaligus. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <button class="ghost-btn" type="button" data-modal-close aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <p>Pastikan Anda sudah memilih agenda yang benar. Semua agenda yang dipilih akan dihapus permanen.</p>
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

    {{-- Detail View Modal --}}
    <div class="dialog-backdrop" id="agendaViewModal" aria-hidden="true">
        <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="agendaViewTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="agendaViewTitle">Detail Agenda</h2>
                </div>
                <button class="dialog__close" type="button" data-modal-close aria-label="Tutup detail">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <div class="detail-view">
                    <div class="detail-view__meta">
                        <span class="detail-status-chip" id="agendaViewStatus">
                            <i class="fas fa-circle" style="font-size: 8px; margin-right: 6px;"></i>
                            <span>Status</span>
                        </span>
                        <span class="priority-pill" id="agendaViewPriority">Prioritas</span>
                        <span style="display: inline-flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; font-weight: 500; color: #475569;">
                            <i class="fas fa-user-circle"></i>
                            <span id="agendaViewOwner" style="padding: 0.35rem 0.65rem; border-radius: 999px; background: rgba(148, 163, 184, 0.18); color: #1d4ed8; font-weight: 600; white-space: nowrap;">Sistem</span>
                        </span>
                    </div>

                    <dl class="detail-view__list">
                        <div>
                            <dt>Judul Agenda</dt>
                            <dd id="agendaViewName" style="font-size: 1.1rem; font-weight: 700; color: #1e293b;"></dd>
                        </div>
                        <div>
                            <dt>Deskripsi</dt>
                            <dd id="agendaViewDescription" style="white-space: pre-wrap; word-break: break-word; line-height: 1.5; color: #475569;"></dd>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                            <div>
                                <dt><i class="fas fa-calendar-alt"></i> Tenggat Waktu</dt>
                                <dd id="agendaViewDueDate" style="color: #dc2626; font-weight: 600;"></dd>
                            </div>
                            <div>
                                <dt><i class="fas fa-clock"></i> Dibuat Pada</dt>
                                <dd id="agendaViewCreated" style="font-size: 0.9rem;"></dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </div>
            <footer class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Tutup</button>
            </footer>
        </div>
    </div>
@endsection
