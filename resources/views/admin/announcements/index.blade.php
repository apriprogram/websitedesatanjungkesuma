@extends('admin.layouts.app')

@section('title', 'Pengumuman Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
@endpush



@section('content')
    <div class="news-page announcements-page">
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
                <span class="header-title-text">Pengumuman Desa</span>
                @include('admin.partials.header-controls')
            </div>
                <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <span class="breadcrumb-link">Publikasi</span>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <a href="{{ route('admin.announcements.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Pengumuman</a>
                </nav>
        </header>

        @php
            $perPageOptions = [6, 12, 18, 24, 36];
            $currentPerPage = (int) request('per_page', $announcements->perPage());
        @endphp

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Pengumuman Desa</h1>
                <p>Kelola pengumuman yang tampil di website desa.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.announcements.create') }}" class="soft-action-btn soft-action-btn--violet">
                    <i class="fas fa-bullhorn"></i>
                    <span>Tambah Pengumuman</span>
                </a>
            </div>
        </section>

        <section class="summary-grid summary-grid--wide">
            <article class="stat-card stat-card--accent">
                <div class="stat-card__header">
                    <div class="stat-card__pill">
                        <i class="fas fa-bullhorn"></i>
                        <span>Total Pengumuman</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Semua status</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($totalAnnouncements) }}</p>
                    <p class="stat-card__label">Total entri pengumuman yang tersimpan.</p>
                </div>
            </article>

            <article class="stat-card" data-card="published">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--success">
                        <i class="fas fa-calendar-check"></i>
                        <span>Terbit</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Aktif</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($publishedCount) }}</p>
                    <p class="stat-card__label">Pengumuman yang sudah dipublikasikan.</p>
                </div>
            </article>

            <article class="stat-card" data-card="draft">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--warning">
                        <i class="fas fa-pencil-alt"></i>
                        <span>Draft</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Perlu terbit</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($draftCount) }}</p>
                    <p class="stat-card__label">Siap ditinjau sebelum dipublikasikan.</p>
                </div>
            </article>

            <article class="stat-card" data-card="month">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--brand">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Terbit Bulan Ini</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Bulan berjalan</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($publishedThisMonth) }}</p>
                    <p class="stat-card__label">Pengumuman yang dipublikasikan bulan ini.</p>
                </div>
            </article>

            <article class="stat-card" data-card="categories">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--muted">
                        <i class="fas fa-layer-group"></i>
                        <span>Kategori</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Struktur</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($categoryCount) }}</p>
                    <p class="stat-card__label">Grup kategori yang tersedia.</p>
                </div>
            </article>
        </section>

        <form action="{{ route('admin.announcements.index') }}" method="GET" id="announcementFilterForm"
            class="news-filter-form filter-toolbar">
            <div class="form-field" style="flex-grow: 1;">
                <label for="search-announcement">Pencarian</label>
                <div style="display: flex; gap: 0.5rem; align-items: stretch; position: relative; z-index: 5;">
                    <input type="search" id="search-announcement" name="q" value="{{ request('q') }}"
                        placeholder="Cari judul pengumuman..."
                        style="flex: 1; min-width: 0; position: relative; z-index: 5;">
                    <button type="submit"
                        style="background: rgba(148, 163, 184, 0.15); color: #475569; border: 1px solid rgba(148, 163, 184, 0.2); padding: 0 1rem; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: grid; place-items: center; min-width: 44px; position: relative; z-index: 10; pointer-events: auto;">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="form-field">
                <label for="filter-category">Kategori</label>
                <select id="filter-category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status">
                    <option value="">Semua status</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Terbit</option>
                </select>
            </div>

            <div class="form-field">
                <label for="filter-per-page">Per halaman</label>
                <select id="filter-per-page" name="per_page" onchange="this.form.submit()">
                    @foreach($perPageOptions as $size)
                        <option value="{{ $size }}" {{ $currentPerPage === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-toolbar__actions">
                <button type="submit" class="primary-btn">Terapkan filter</button>
                <a href="{{ route('admin.announcements.index') }}" class="ghost-btn">Reset</a>
            </div>
        </form>

        <section class="news-panel">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar Pengumuman</h2>
                        <p>Kelola pengumuman desa dan pantau status publikasinya.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <select name="per_page" form="announcementFilterForm" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($perPageOptions as $size)
                                    <option value="{{ $size }}" {{ $currentPerPage === $size ? 'selected' : '' }}>{{ $size }} Baris</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="search-announcement"
                                    name="q"
                                    type="search"
                                    placeholder="Cari pengumuman..."
                                    value="{{ request('q') }}"
                                    form="announcementFilterForm"
                                    aria-label="Cari pengumuman"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="news-table">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 60px;">#</th>
                            <th style="text-align: left;">Judul Pengumuman</th>
                            <th style="text-align: center;">Kategori</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Tgl Publikasi</th>
                            <th style="text-align: center;">Views</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            @php
                                $detailPayload = [
                                    'title' => $announcement->title,
                                    'excerpt' => $announcement->excerpt,
                                    'category' => $categories[$announcement->category] ?? ucfirst($announcement->category),
                                    'status' => $announcement->status === 'published' ? 'Terbit' : 'Draft',
                                    'published' => $announcement->published_at?->translatedFormat('d M Y H:i') ?? $announcement->created_at?->translatedFormat('d M Y H:i'),
                                    'body' => $announcement->body,
                                    'views' => $announcement->views ?? 0,
                                    'attachments' => $announcement->attachments->map(fn($att) => [
                                        'type' => $att->type,
                                        'url' => asset('storage/' . ltrim($att->path, '/')),
                                        'name' => $att->original_name
                                    ]),
                                ];
                            @endphp
                            <tr>
                                <td class="news-table__index" style="text-align: center;">
                                    <span>{{ $announcements->firstItem() ? $announcements->firstItem() + $loop->index : $loop->iteration }}</span>
                                </td>
                                <td style="text-align: left;">
                                    <div style="display:flex;align-items:flex-start;gap:12px;">
                                        @if($thumb = $announcement->imageAttachments->first())
                                            <img src="{{ asset('storage/' . ltrim($thumb->path, '/')) }}" alt=""
                                                style="width:56px;height:56px;border-radius:6px;object-fit:cover;border:1px solid #e2e8f0;flex-shrink:0;">
                                        @endif
                                        <div>
                                            <strong>{{ $announcement->title }}</strong>
                                            <span class="row-summary">{{ $announcement->excerpt }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="table-chip" style="margin: 0 auto;">
                                        <i class="fas fa-tag"></i>
                                        {{ $categories[$announcement->category] ?? ucfirst($announcement->category) }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <span
                                        class="status-pill {{ $announcement->status === 'published' ? 'status-active' : 'status-draft' }}" style="margin: 0 auto;">
                                        <i
                                            class="fas {{ $announcement->status === 'published' ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                        {{ $announcement->status === 'published' ? 'Terbit' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="table-cell--muted" style="text-align: center;">
                                    {{ $announcement->published_at?->translatedFormat('d M Y H:i') ?? $announcement->created_at?->translatedFormat('d M Y H:i') }}
                                </td>
                                <td style="text-align: center;">
                                    <span style="color:#64748b;font-size:0.9rem;display:flex;align-items:center;gap:6px;justify-content:center;">
                                        <i class="fas fa-eye"></i> {{ number_format($announcement->views ?? 0) }}
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <details class="news-table__action-dropdown" data-action-menu>
                                        <summary class="action-button action-button--dots" aria-haspopup="menu"
                                            aria-expanded="false" aria-label="Tampilkan opsi untuk {{ $announcement->title }}">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </summary>
                                        <div class="news-table__action-options" role="menu">
                                            <button type="button" class="news-table__action-item" role="menuitem"
                                                data-ann-detail='@json($detailPayload)'
                                                aria-label="Lihat detail pengumuman {{ $announcement->title }}">
                                                <i class="fas fa-eye"></i>
                                                <span>Lihat</span>
                                            </button>
                                            <a href="{{ route('admin.announcements.edit', $announcement) }}"
                                                class="news-table__action-item" role="menuitem">
                                                <i class="fas fa-pen"></i>
                                                <span>Edit</span>
                                            </a>
                                            <button type="button"
                                                class="news-table__action-item news-table__action-item--danger" role="menuitem"
                                                data-ann-delete="{{ route('admin.announcements.destroy', $announcement) }}"
                                                data-ann-title="{{ $announcement->title }}">
                                                <i class="fas fa-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-6">Belum ada pengumuman untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="news-footer">
                <div class="table-info">
                    Menampilkan {{ $announcements->count() ? $announcements->firstItem() : 0 }} -
                    {{ $announcements->count() ? $announcements->lastItem() : 0 }} dari {{ $announcements->total() }} entri
                </div>
                <div class="news-pagination">
                    {{ $announcements->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
                </div>
            </div>
        </section>

        <div class="news-detail-modal" id="announcementDetailModal" aria-hidden="true">
            <div class="news-detail-modal__backdrop" data-ann-detail-backdrop></div>
            <div class="news-detail-modal__content" role="dialog" aria-modal="true" aria-labelledby="annDetailTitle">
                <header class="news-detail-modal__header">
                    <div>
                        <small
                            style="text-transform:uppercase;font-size:0.75rem;color:#64748b;font-weight:600;letter-spacing:0.5px;">Lihat
                            Detail Pengumuman</small>
                        <h3 id="annDetailTitle" style="margin-top:4px;">Detail Pengumuman</h3>
                        <p class="news-detail-modal__summary" data-ann-detail-excerpt></p>
                    </div>
                    <button type="button" class="news-detail-modal__close" aria-label="Tutup detail" data-ann-detail-close>
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="news-detail-modal__meta-row">
                    <span class="news-detail-modal__meta-item"><i class="fas fa-folder-open"></i> <span
                            data-ann-detail-category></span></span>
                    <span class="news-detail-modal__meta-item"><i class="fas fa-flag"></i> <span
                            data-ann-detail-status></span></span>
                    <span class="news-detail-modal__meta-item"><i class="fas fa-calendar-alt"></i> <span
                            data-ann-detail-published></span></span>
                    <span class="news-detail-modal__meta-item"><i class="fas fa-eye"></i> <span
                            data-ann-detail-views></span></span>
                </div>
                <div class="news-detail-modal__body">
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;" data-ann-detail-attachments></div>
                    <div class="news-detail-modal__text" data-ann-detail-body></div>
                </div>
            </div>
        </div>

        <div class="news-delete-modal" id="annDeleteModal" aria-hidden="true">
            <div class="news-delete-modal__backdrop" data-ann-delete-backdrop></div>
            <div class="news-delete-modal__content" role="dialog" aria-modal="true" aria-labelledby="annDeleteTitle">
                <header class="news-delete-modal__header">
                    <div>
                        <h3 id="annDeleteTitle">Hapus pengumuman?</h3>
                        <p class="news-delete-modal__body-text">Tindakan ini akan menghapus data pengumuman secara permanen.
                        </p>
                    </div>
                    <button type="button" class="news-delete-modal__close" data-ann-delete-close
                        aria-label="Tutup konfirmasi">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <p class="news-delete-modal__body-text" data-ann-delete-target></p>
                <div class="news-delete-modal__actions">
                    <button type="button" class="news-delete-modal__option" data-ann-delete-close>Batal</button>
                    <button type="button" class="news-delete-modal__option news-delete-modal__option--danger"
                        data-ann-delete-confirm>Hapus</button>
                </div>
            </div>
        </div>

        <form id="annDeleteForm" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('announcementDetailModal');
                if (!modal) return;

                const backdrop = modal.querySelector('[data-ann-detail-backdrop]');
                const closeBtn = modal.querySelector('[data-ann-detail-close]');
                const titleEl = modal.querySelector('#annDetailTitle');
                const excerptEl = modal.querySelector('[data-ann-detail-excerpt]');
                const categoryEl = modal.querySelector('[data-ann-detail-category]');
                const statusEl = modal.querySelector('[data-ann-detail-status]');
                const publishedEl = modal.querySelector('[data-ann-detail-published]');
                const viewsEl = modal.querySelector('[data-ann-detail-views]');
                const bodyEl = modal.querySelector('[data-ann-detail-body]');
                const attachmentsEl = modal.querySelector('[data-ann-detail-attachments]');

                const openModal = (payload = {}) => {
                    if (titleEl) titleEl.textContent = payload.title || 'Detail Pengumuman';
                    if (excerptEl) excerptEl.textContent = payload.excerpt || '-';
                    if (categoryEl) categoryEl.textContent = payload.category || '-';
                    if (statusEl) statusEl.textContent = payload.status || '-';
                    if (publishedEl) publishedEl.textContent = payload.published || '-';
                    if (viewsEl) viewsEl.textContent = (payload.views || '0') + ' x dilihat';
                    if (bodyEl) bodyEl.innerHTML = payload.body || '';

                    if (attachmentsEl) {
                        attachmentsEl.innerHTML = '';
                        const attachments = payload.attachments || [];
                        attachments.forEach(att => {
                            if (att.type === 'image') {
                                const img = document.createElement('img');
                                img.src = att.url;
                                img.alt = att.name;
                                img.style.cssText = 'height:80px;border-radius:6px;border:1px solid #e2e8f0;cursor:pointer;object-fit:cover;';
                                img.onclick = () => window.open(att.url, '_blank');
                                attachmentsEl.appendChild(img);
                            } else {
                                const link = document.createElement('a');
                                link.href = att.url;
                                link.target = '_blank';
                                link.style.cssText = 'display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:#f1f5f9;border-radius:6px;font-size:0.85rem;color:#475569;text-decoration:none;border:1px solid #e2e8f0;';
                                link.innerHTML = `<i class="fas fa-file-alt"></i> ${att.name}`;
                                attachmentsEl.appendChild(link);
                            }
                        });
                    }

                    modal.classList.add('news-detail-modal--visible');
                    modal.setAttribute('aria-hidden', 'false');
                };

                const closeModal = () => {
                    modal.classList.remove('news-detail-modal--visible');
                    modal.setAttribute('aria-hidden', 'true');
                };

                document.querySelectorAll('[data-ann-detail]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const payload = button.dataset.annDetail ? JSON.parse(button.dataset.annDetail) : {};
                        openModal(payload);
                    });
                });

                backdrop?.addEventListener('click', closeModal);
                closeBtn?.addEventListener('click', closeModal);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal.classList.contains('news-detail-modal--visible')) {
                        closeModal();
                    }
                });
            });

            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('annDeleteModal');
                const form = document.getElementById('annDeleteForm');
                if (!modal || !form) return;

                const backdrop = modal.querySelector('[data-ann-delete-backdrop]');
                const closeButtons = modal.querySelectorAll('[data-ann-delete-close]');
                const confirmButton = modal.querySelector('[data-ann-delete-confirm]');
                const targetText = modal.querySelector('[data-ann-delete-target]');

                let currentAction = null;
                const openModal = ({ title = '' } = {}, action = null) => {
                    currentAction = action;
                    if (targetText) targetText.textContent = title ? `Anda akan menghapus "${title}".` : 'Anda akan menghapus pengumuman ini.';
                    modal.classList.add('news-delete-modal--visible');
                    modal.setAttribute('aria-hidden', 'false');
                };

                const closeModal = () => {
                    modal.classList.remove('news-delete-modal--visible');
                    modal.setAttribute('aria-hidden', 'true');
                    currentAction = null;
                };

                document.querySelectorAll('[data-ann-delete]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.dataset.annDelete || null;
                        const title = button.dataset.annTitle || '';
                        openModal({ title }, action);
                    });
                });

                confirmButton?.addEventListener('click', () => {
                    if (!currentAction) return;
                    form.action = currentAction;
                    form.submit();
                });

                closeButtons.forEach((btn) => btn.addEventListener('click', closeModal));
                backdrop?.addEventListener('click', closeModal);
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal.classList.contains('news-delete-modal--visible')) {
                        closeModal();
                    }
                });
            });
        </script>
    @endpush
@endsection
