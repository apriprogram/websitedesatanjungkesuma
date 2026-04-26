@extends('admin.layouts.app')

@section('title', 'Sekilas Info')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-references.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-sekilas-info.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/admin-sekilas-info.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const detailModal = document.getElementById('infoDetailModal');
            const detailOverlay = detailModal?.querySelector('[data-detail-close]');
            const detailTitle = document.getElementById('detailTitle');
            const detailStatus = document.getElementById('detailStatus');
            const detailPublished = document.getElementById('detailPublished');
            const detailContent = document.getElementById('detailContent');

            const closeDetail = () => {
                detailModal?.classList.remove('is-visible');
            };
            detailOverlay?.addEventListener('click', closeDetail);
            detailModal?.querySelectorAll('[data-detail-close]').forEach((btn) => btn.addEventListener('click', closeDetail));

            document.querySelectorAll('.js-detail').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const payload = btn.dataset.detail ? JSON.parse(btn.dataset.detail) : {};
                    if (detailTitle) detailTitle.textContent = payload.title || 'Detail Sekilas Info';
                    if (detailStatus) detailStatus.textContent = `${payload.status || '-'} • Urutan ${payload.order ?? '-'}`;
                    if (detailPublished) detailPublished.textContent = `Ditayangkan: ${payload.published || '-'}`;
                    if (detailContent) detailContent.innerHTML = payload.content || '';
                    detailModal?.classList.add('is-visible');
                });
            });

            const deleteModal = document.getElementById('infoDeleteModal');
            const deleteForm = document.getElementById('infoDeleteForm');
            const deleteTitle = document.getElementById('deleteInfoTitle');
            const deleteLabel = document.getElementById('deleteInfoLabel');
            const deleteOverlay = deleteModal?.querySelector('[data-delete-close]');
            const deleteCloseBtns = deleteModal?.querySelectorAll('[data-delete-close]');
            const deleteConfirm = deleteModal?.querySelector('[data-delete-confirm]');
            let deleteAction = null;

            const closeDelete = () => {
                deleteModal?.classList.remove('is-visible');
                deleteAction = null;
            };

            document.querySelectorAll('.js-delete').forEach((btn) => {
                btn.addEventListener('click', () => {
                    deleteAction = btn.dataset.deleteUrl || null;
                    const title = btn.dataset.deleteTitle || '';
                    if (deleteTitle) deleteTitle.textContent = 'Hapus Sekilas Info?';
                    if (deleteLabel) deleteLabel.textContent = title ? `Anda akan menghapus "${title}".` : 'Anda akan menghapus item ini.';
                    deleteModal?.classList.add('is-visible');
                });
            });

            deleteConfirm?.addEventListener('click', () => {
                if (!deleteForm || !deleteAction) return;
                deleteForm.action = deleteAction;
                deleteForm.submit();
            });

            deleteCloseBtns?.forEach((btn) => btn.addEventListener('click', closeDelete));
            deleteOverlay?.addEventListener('click', closeDelete);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeDetail();
                    closeDelete();
                }
            });
        });
    </script>
@endpush

@section('content')
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
            <span class="header-title-text">Sekilas Info</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Dashboard</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>Publikasi</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>Sekilas Info</span>
        </nav>
    </header>

    <section class="page-title page-title--with-actions info-hero">
        <div>
            <h1>Sekilas Info</h1>
            <p>Teks singkat yang tampil di beranda sebagai informasi cepat.</p>
        </div>
        <div class="title-actions">
            <button class="soft-action-btn soft-action-btn--violet sekilas-add-btn" id="openCreateModal" type="button">
                <i class="fas fa-plus"></i>
                <span>Tambah Info</span>
            </button>
        </div>
    </section>

    <section class="summary-grid summary-grid--wide">
        <article class="stat-card stat-card--accent">
            <div class="stat-card__header">
                <div class="stat-card__pill">
                    <i class="fas fa-bullhorn"></i>
                    <span>Total Info</span>
                </div>
                <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Semua status</p>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['total']) }}</p>
                <p class="stat-card__label">Jumlah seluruh sekilas info yang tersimpan.</p>
            </div>
        </article>

        <article class="stat-card" data-card="active">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--success">
                    <i class="fas fa-check-circle"></i>
                    <span>Aktif</span>
                </div>
                <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Tayang</p>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['active']) }}</p>
                <p class="stat-card__label">Sedang ditampilkan di beranda.</p>
            </div>
        </article>

        <article class="stat-card" data-card="inactive">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--warning">
                    <i class="fas fa-pause-circle"></i>
                    <span>Nonaktif</span>
                </div>
                <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Disembunyikan</p>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['inactive']) }}</p>
                <p class="stat-card__label">Belum tayang atau dinonaktifkan.</p>
            </div>
        </article>

        <article class="stat-card" data-card="schedule">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--brand">
                    <i class="fas fa-calendar-day"></i>
                    <span>Terjadwal</span>
                </div>
                <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Siap tayang</p>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value">{{ number_format($stats['scheduled']) }}</p>
                <p class="stat-card__label">Memiliki tanggal publikasi.</p>
            </div>
        </article>

        <article class="stat-card" data-card="updated">
            <div class="stat-card__header">
                <div class="stat-card__pill stat-card__pill--muted">
                    <i class="fas fa-history"></i>
                    <span>Pembaruan Terakhir</span>
                </div>
                <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Riwayat</p>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__value stat-card__value--small">
                    {{ $stats['lastUpdated'] ? $stats['lastUpdated']->translatedFormat('d M Y') : '-' }}
                </p>
            </div>
        </article>
    </section>

    <div class="info-layout">
    <div class="budget-card info-card">

        <div class="info-table-wrap">
            <table class="table info-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Isi</th>
                    <th>Ditayangkan</th>
                    <th>Urutan</th>
                    <th>Tampilkan</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $index => $item)
                    @php
                        $detailPayload = [
                            'title' => $item->title,
                            'content' => $item->content,
                            'published' => $item->published_at?->format('d M Y H:i') ?? 'Belum dijadwalkan',
                            'status' => $item->is_active ? 'Aktif' : 'Nonaktif',
                            'order' => $item->sort_order,
                        ];
                    @endphp
                    <tr>
                        <td data-label="#"> {{ $index + 1 }} </td>
                        <td data-label="Judul" class="info-table__title">
                            <strong>{{ $item->title }}</strong>
                        </td>
                        <td data-label="Isi" class="info-table__content">
                            {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 140) }}
                        </td>
                        <td data-label="Ditayangkan">
                            @if($item->published_at)
                                <span class="badge badge--soft">
                                    <i class="fas fa-clock"></i>
                                    {{ $item->published_at->format('d M Y') }}
                                </span>
                            @else
                                <span class="badge badge--soft badge--muted">Belum</span>
                            @endif
                        </td>
                        <td data-label="Urutan" class="text-center">{{ $item->sort_order }}</td>
                        <td data-label="Tampilkan">
                            <form method="POST" action="{{ route('admin.sekilas-info.toggle', $item) }}">
                                @csrf
                                @method('PATCH')
                                <label class="table-switch">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $item->is_active ? 'checked' : '' }}>
                                    <span class="table-switch__slider"></span>
                                    <span class="table-switch__label">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </label>
                            </form>
                        </td>
                        <td data-label="Aksi" class="table-actions">
                            <button type="button"
                                    class="btn btn--ghost icon-btn js-detail"
                                    data-detail='@json($detailPayload)'
                                    aria-label="Lihat detail {{ $item->title }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button"
                                    class="btn btn--ghost icon-btn js-edit"
                                    data-id="{{ $item->id }}"
                                    data-title="{{ $item->title }}"
                                    data-content="{{ $item->content }}"
                                    data-published="{{ $item->published_at }}"
                                    data-active="{{ $item->is_active ? '1' : '0' }}"
                                    data-sort="{{ $item->sort_order }}"
                                    data-update-url="{{ route('admin.sekilas-info.update', $item) }}">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button type="button"
                                    class="btn btn--danger icon-btn js-delete"
                                    data-delete-url="{{ route('admin.sekilas-info.destroy', $item) }}"
                                    data-delete-title="{{ $item->title }}"
                                    aria-label="Hapus {{ $item->title }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="info-empty">Belum ada sekilas info.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <div class="modal" id="infoModal" aria-hidden="true">
        <div class="modal-overlay" data-close></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Tambah Sekilas Info</h3>
                <button type="button" class="modal-close" data-close><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" id="infoForm" action="{{ route('admin.sekilas-info.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <div class="modal-body">
                    <div class="form-grid">
                        <label class="full">
                            <span>Judul</span>
                            <input type="text" name="title" id="fieldTitle" required maxlength="150">
                        </label>
                        <label class="full">
                            <span>Isi</span>
                            <textarea name="content" id="fieldContent" rows="3" required></textarea>
                            <span class="helper-text">Tampilkan informasi singkat/teaser. Gunakan kalimat pendek.</span>
                        </label>
                        <label>
                            <span>Urutan</span>
                            <input type="number" name="sort_order" id="fieldSort" min="0" step="1" value="0">
                        </label>
                        <label>
                            <span>Tanggal tayang</span>
                            <input type="datetime-local" name="published_at" id="fieldPublished">
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" name="is_active" id="fieldActive" value="1" checked>
                            <span>Tampilkan</span>
                        </label>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn--ghost pill" data-close>Batal</button>
                    <button type="submit" class="btn btn--primary pill shadow">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @include('admin.sekilas-info.delete-modal')

    <div class="modal" id="infoDetailModal" aria-hidden="true">
        <div class="modal-overlay" data-detail-close></div>
        <div class="modal-content info-detail-modal">
            <div class="modal-header">
                <div>
                    <h3 id="detailTitle">Detail Sekilas Info</h3>
                    <p class="helper-text" id="detailStatus"></p>
                </div>
                <button type="button" class="modal-close" data-detail-close><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <p class="helper-text" id="detailPublished"></p>
                <div class="info-detail-body" id="detailContent"></div>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
@endsection
