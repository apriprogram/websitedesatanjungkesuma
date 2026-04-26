@extends('admin.layouts.app')

@section('title', 'Halaman Custom')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news-modal.css') }}">
@endpush

@section('content')
    <div class="news-page">
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
                <span class="header-title-text">Manajemen Halaman</span>
                @include('admin.partials.header-controls')
            </div>
                <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <span class="breadcrumb-link">Publikasi</span>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <a href="{{ route('admin.pages.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Halaman</a>
                </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <p class="page-eyebrow">Konten Statis</p>
                <h1>Daftar Halaman</h1>
                <p>Kelola halaman profil, layanan, dan informasi statis lainnya.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.pages.create') }}" class="soft-action-btn soft-action-btn--violet">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Halaman</span>
                </a>
            </div>
        </section>

        <section class="summary-grid summary-grid--wide">
            <article class="stat-card stat-card--accent" data-card="total">
                <div class="stat-card__header">
                    <div class="stat-card__pill">
                        <i class="fas fa-layer-group"></i>
                        <span>Total</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['total']) }}</p>
                    <p class="stat-card__label">Halaman terdaftar.</p>
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
                    <p class="stat-card__value">{{ number_format($stats['published']) }}</p>
                    <p class="stat-card__label">Halaman aktif di publik.</p>
                </div>
            </article>
            <article class="stat-card" data-card="draft">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--warning">
                        <i class="fas fa-file-pen"></i>
                        <span>Draft</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['draft']) }}</p>
                    <p class="stat-card__label">Menunggu publikasi.</p>
                </div>
            </article>
            <article class="stat-card" data-card="views">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--brand">
                        <i class="fas fa-eye"></i>
                        <span>Views</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['views']) }}</p>
                    <p class="stat-card__label">Total views halaman.</p>
                </div>
            </article>
            <article class="stat-card" data-card="image">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--info">
                        <i class="fas fa-image"></i>
                        <span>Feature Image</span>
                    </div>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($stats['with_image']) }}</p>
                    <p class="stat-card__label">Halaman memakai gambar.</p>
                </div>
            </article>
        </section>

        <section class="news-panel">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar Halaman</h2>
                        <p>Kelola konten statis dan halaman informasi desa.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <form action="{{ route('admin.pages.index') }}" method="get" class="panel-filters">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                <select id="per_page" name="per_page" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                    @foreach([10,20,50,100] as $size)
                                        <option value="{{ $size }}" @selected((int)request('per_page', 20) === $size)>{{ $size }} Baris</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="search-input-group">
                            <form action="{{ route('admin.pages.index') }}" method="get" class="search-input-wrapper">
                                @if(request('per_page'))
                                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                                @endif
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="search-input"
                                    name="search"
                                    type="search"
                                    placeholder="Cari halaman..."
                                    value="{{ request('search') }}"
                                    aria-label="Cari halaman"
                                    autocomplete="off"
                                >
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="news-table">
                <table>
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 60px;">#</th>
                            <th style="text-align: left;">Judul Halaman</th>
                            <th style="text-align: left;">Slug / URL</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Views</th>
                            <th style="text-align: center;">Update Terakhir</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            @php $rowNumber = $pages->firstItem() ? $pages->firstItem() + $loop->index : $loop->iteration; @endphp
                            @php $attachments = \Illuminate\Support\Facades\Schema::hasTable('page_attachments') ? ($page->attachments ?? collect()) : collect(); @endphp
                            <tr>
                                <td class="news-table__index" style="text-align: center;"><span>{{ $rowNumber }}</span></td>
                                <td style="text-align: left;">
                                    <strong>{{ $page->title }}</strong>
                                    <span class="row-summary">ID: {{ $page->id }}</span>
                                </td>
                                <td style="text-align: left;"><span class="badge badge--muted">{{ $page->slug }}</span></td>
                                <td style="text-align: center;"><span class="status-pill status-pill--{{ $page->status }}">{{ ucfirst($page->status) }}</span></td>
                                <td style="text-align: center;">{{ number_format($page->views ?? 0) }}</td>
                                <td style="text-align: center;">{{ $page->updated_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                                <td style="text-align: center;">
                                    <details class="news-table__action-dropdown" data-action-menu>
                                        <summary class="action-button action-button--dots" aria-haspopup="menu" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </summary>
                                        <div class="news-table__action-options" role="menu">
                                            @if($page->status === 'published')
                                                <a href="{{ route('pages.show', $page) }}" class="news-table__action-item" role="menuitem" target="_blank">
                                                    <i class="fas fa-eye"></i><span>Lihat</span>
                                                </a>
                                            @endif
                                            <button type="button"
                                                class="news-table__action-item page-detail-btn"
                                                data-page-title="{{ $page->title }}"
                                                data-page-slug="{{ $page->slug }}"
                                                data-page-status="{{ ucfirst($page->status) }}"
                                                data-page-views="{{ number_format($page->views ?? 0) }}"
                                                data-page-updated="{{ optional($page->updated_at)->translatedFormat('d M Y H:i') ?? '-' }}"
                                                data-page-published="{{ optional($page->published_at)->translatedFormat('d M Y H:i') ?? '-' }}"
                                                data-page-meta-title="{{ $page->meta_title ?: '-' }}"
                                                data-page-meta-description="{{ $page->meta_description ?: '-' }}"
                                                data-page-attachments="@json($attachments->map(function ($att) { return ['name' => $att->original_name ?: basename($att->path), 'url' => $att->url, 'type' => $att->type]; })->values())"
                                                data-page-content="{{ \Illuminate\Support\Str::limit(strip_tags($page->content), 300) }}"
                                                data-page-feature="{{ $page->feature_image ? (\Illuminate\Support\Str::startsWith($page->feature_image, ['http://', 'https://']) ? $page->feature_image : asset('storage/' . ltrim($page->feature_image, '/'))) : '' }}"
                                                role="menuitem">
                                                <i class="fas fa-eye"></i><span>Detail</span>
                                            </button>
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="news-table__action-item" role="menuitem">
                                        <i class="fas fa-pen"></i><span>Edit</span>
                                    </a>
                                            <form action="{{ route('admin.pages.destroy', $page) }}" method="post" style="display:inline;" class="page-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="news-table__action-item news-table__action-item--danger delete-trigger" role="menuitem" data-page-title="{{ $page->title }}">
                                                    <i class="fas fa-trash"></i><span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="banner-panel-empty">Belum ada halaman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-summary" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
                <div>Menampilkan {{ $pages->firstItem() ?? 0 }} - {{ $pages->lastItem() ?? 0 }} dari {{ $pages->total() }} halaman</div>
                <div class="table-pagination news-pagination" style="margin-left:auto;">
                    {{ $pages->onEachSide(1)->links('vendor.pagination.bootstrap-4') }}
                </div>
            </div>
        </section>
    </div>
    {{-- Detail Modal --}}
    <div class="news-modal" id="pageDetailModal" aria-hidden="true">
        <div class="news-modal__overlay" data-modal-close></div>
        <div class="news-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="pageDetailTitle">
            <header class="news-modal__header">
                <div>
                    <h3 id="pageDetailTitle">Detail Halaman</h3>
                    <p>Lihat ringkasan data halaman.</p>
                </div>
                <button type="button" class="news-modal__close" data-modal-close aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="news-modal__body">
                <dl class="detail-list">
                    <div><dt>Judul</dt><dd data-detail="title"></dd></div>
                    <div><dt>Slug</dt><dd data-detail="slug"></dd></div>
                    <div><dt>Status</dt><dd data-detail="status"></dd></div>
                    <div><dt>Views</dt><dd data-detail="views"></dd></div>
                    <div><dt>Dipublikasikan</dt><dd data-detail="published"></dd></div>
                    <div><dt>Diperbarui</dt><dd data-detail="updated"></dd></div>
                    <div><dt>Meta Title</dt><dd data-detail="metaTitle"></dd></div>
                    <div><dt>Meta Description</dt><dd data-detail="metaDesc"></dd></div>
                    <div><dt>Lampiran</dt><dd data-detail="attachments" class="detail-attachments"></dd></div>
                    <div><dt>Ringkasan Konten</dt><dd data-detail="content"></dd></div>
                    <div class="detail-image-row" style="margin-top:10px; display:none;" data-detail="feature-wrapper">
                        <dt>Feature Image</dt>
                        <dd><img src="" alt="Feature image" style="max-width: 220px; border-radius: 10px; border:1px solid #e5e7eb;" data-detail="feature"></dd>
                    </div>
                </dl>
            </div>
            <footer class="news-modal__footer">
                <button type="button" class="ghost-btn" data-modal-close>Tutup</button>
            </footer>
        </div>
    </div>

    {{-- Delete Confirm Modal --}}
    <div class="news-modal" id="pageDeleteModal" aria-hidden="true">
        <div class="news-modal__overlay" data-delete-close></div>
        <div class="news-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
            <header class="news-modal__header">
                <div>
                    <h3 id="deleteModalTitle">Hapus Halaman</h3>
                    <p>Konfirmasi penghapusan data halaman.</p>
                </div>
                <button type="button" class="news-modal__close" data-delete-close aria-label="Tutup modal">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="news-modal__body">
                <p id="deleteModalMessage">Anda yakin ingin menghapus halaman ini?</p>
            </div>
            <footer class="news-modal__footer">
                <button type="button" class="ghost-btn" data-delete-close>Batal</button>
                <button type="button" class="primary-btn" id="confirmDeleteBtn">Hapus</button>
            </footer>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('pageDetailModal');
        const detailTargets = {
            title: modal?.querySelector('[data-detail="title"]'),
            slug: modal?.querySelector('[data-detail="slug"]'),
            status: modal?.querySelector('[data-detail="status"]'),
            views: modal?.querySelector('[data-detail="views"]'),
            published: modal?.querySelector('[data-detail="published"]'),
            updated: modal?.querySelector('[data-detail="updated"]'),
            content: modal?.querySelector('[data-detail="content"]'),
            metaTitle: modal?.querySelector('[data-detail="metaTitle"]'),
            metaDesc: modal?.querySelector('[data-detail="metaDesc"]'),
            feature: modal?.querySelector('[data-detail="feature"]'),
            featureWrapper: modal?.querySelector('[data-detail="feature-wrapper"]'),
            attachments: modal?.querySelector('[data-detail="attachments"]'),
        };

        const openModal = () => { if (modal) modal.classList.add('is-visible'); };
        const closeModal = () => { if (modal) modal.classList.remove('is-visible'); };

        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.page-detail-btn');
            if (!btn || !modal) return;
            detailTargets.title.textContent = btn.dataset.pageTitle || '-';
            detailTargets.slug.textContent = btn.dataset.pageSlug || '-';
            detailTargets.status.textContent = btn.dataset.pageStatus || '-';
            detailTargets.views.textContent = btn.dataset.pageViews || '0';
            detailTargets.published.textContent = btn.dataset.pagePublished || '-';
            detailTargets.updated.textContent = btn.dataset.pageUpdated || '-';
            detailTargets.content.textContent = btn.dataset.pageContent || '-';
            detailTargets.metaTitle.textContent = btn.dataset.pageMetaTitle || '-';
            detailTargets.metaDesc.textContent = btn.dataset.pageMetaDescription || '-';

            if (btn.dataset.pageFeature) {
                detailTargets.feature.src = btn.dataset.pageFeature;
                detailTargets.featureWrapper.style.display = 'flex';
            } else {
                detailTargets.featureWrapper.style.display = 'none';
            }

            // Lampiran
            if (detailTargets.attachments) {
                detailTargets.attachments.innerHTML = '';
                let attachments = [];
                try {
                    attachments = btn.dataset.pageAttachments ? JSON.parse(btn.dataset.pageAttachments) : [];
                } catch (err) {
                    attachments = [];
                }

                if (!attachments.length) {
                    detailTargets.attachments.textContent = '-';
                } else {
                    attachments.forEach(att => {
                        const link = document.createElement('a');
                        link.href = att.url || '#';
                        link.target = '_blank';
                        link.rel = 'noopener';
                        link.className = 'detail-chip';
                        link.textContent = att.name || att.url || 'Lampiran';
                        detailTargets.attachments.appendChild(link);
                    });
                }
            }
            openModal();
        });

        // Delete modal
        const deleteModal = document.getElementById('pageDeleteModal');
        const deleteMsg = deleteModal?.querySelector('#deleteModalMessage');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        let deleteFormRef = null;

        const openDelete = (title, form) => {
            deleteFormRef = form;
            if (deleteMsg) {
                deleteMsg.textContent = `Anda yakin ingin menghapus halaman "${title}"?`;
            }
            deleteModal?.classList.add('is-visible');
        };
        const closeDelete = () => {
            deleteModal?.classList.remove('is-visible');
            deleteFormRef = null;
        };

        document.querySelectorAll('.delete-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const form = btn.closest('form');
                openDelete(btn.dataset.pageTitle || 'tanpa judul', form);
            });
        });

        document.querySelectorAll('[data-delete-close]').forEach(el => {
            el.addEventListener('click', closeDelete);
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                if (deleteFormRef) deleteFormRef.submit();
            });
        }
    });
</script>
@endpush
