@extends('admin.layouts.app')

@section('title', 'Daftar Berita')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
@endpush

@php
    use Illuminate\Support\Str;
@endphp



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
                <span class="header-title-text">Manajemen Berita</span>
                @include('admin.partials.header-controls')
            </div>
                <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <span class="breadcrumb-link">Publikasi</span>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                    <a href="{{ route('admin.news.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Berita</a>
                </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Berita Desa</h1>
                <p>Kelola seluruh konten berita, pantau status, dan publikasikan informasi penting.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.news.create') }}" class="soft-action-btn soft-action-btn--violet">
                    <i class="fas fa-user-plus"></i>
                    <span>Tambah Berita</span>
                </a>
            </div>
        </section>

        <section class="summary-grid summary-grid--wide">
            <article class="stat-card stat-card--accent" data-card="total">
                <div class="stat-card__header">
                    <div class="stat-card__pill">
                        <i class="fas fa-table"></i>
                        <span>Total Berita</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Stabil bulan ini</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($news->total()) }}</p>
                    <p class="stat-card__label">Keseluruhan konten yang tersedia di dashboard.</p>
                </div>
            </article>
            <article class="stat-card" data-card="published">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--success">
                        <i class="fas fa-calendar-check"></i>
                        <span>Terbit</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Naik</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($statusCounts['published'] ?? 0) }}</p>
                    <p class="stat-card__label">Artikel yang sudah dipublikasi.</p>
                </div>
            </article>
            <article class="stat-card" data-card="draft">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--warning">
                        <i class="fas fa-pencil-alt"></i>
                        <span>Draft</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--neutral"><i class="fas fa-minus"></i> Menunggu terbit</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($statusCounts['draft'] ?? 0) }}</p>
                    <p class="stat-card__label">Masih disimpan sebagai rancangan.</p>
                </div>
            </article>
            <article class="stat-card" data-card="archived">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--muted">
                        <i class="fas fa-archive"></i>
                        <span>Arsip</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--down"><i class="fas fa-arrow-down"></i> Dipindahkan</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($statusCounts['archived'] ?? 0) }}</p>
                    <p class="stat-card__label">Konten yang sudah dipindahkan ke arsip.</p>
                </div>
            </article>
            <article class="stat-card" data-card="views">
                <div class="stat-card__header">
                    <div class="stat-card__pill stat-card__pill--brand">
                        <i class="fas fa-chart-line"></i>
                        <span>Total Views</span>
                    </div>
                    <p class="stat-card__trend stat-card__trend--up"><i class="fas fa-arrow-up"></i> Bertambah</p>
                </div>
                <div class="stat-card__body">
                    <p class="stat-card__value">{{ number_format($totalViews) }}</p>
                    <p class="stat-card__label">Kumulatif jumlah pembaca.</p>
                </div>
            </article>
        </section>

            @php
                $tabBaseQuery = request()->except(['page']);
            @endphp

        <section class="tab-bar">
            <div class="tab-bar__items">
                <a href="{{ route('admin.news.index', array_merge($tabBaseQuery, ['category' => null, 'page' => 1])) }}"
                   class="tab-bar__item {{ empty($category) ? 'tab-bar__item--active' : '' }}">
                    Semua Kategori
                </a>
                @foreach ($categories as $categoryItem)
                    <a href="{{ route('admin.news.index', array_merge($tabBaseQuery, ['category' => $categoryItem->id, 'page' => 1])) }}"
                       class="tab-bar__item {{ $categoryItem->id == $category ? 'tab-bar__item--active' : '' }}">
                        {{ $categoryItem->name }}
                    </a>
                @endforeach
                <a href="{{ route('admin.news.categories.index') }}" class="tab-bar__item tab-bar__item--secondary">
                    Kelola kategori
                </a>
            </div>
        </section>

        <form id="news-filter-form" method="GET" action="{{ route('admin.news.index') }}" class="news-filter-form filter-toolbar">
            <div class="form-field">
                <label for="filter-category">Kategori</label>
                <select id="filter-category" name="category">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $categoryItem)
                        <option value="{{ $categoryItem->id }}" {{ $categoryItem->id == $category ? 'selected' : '' }}>
                            {{ $categoryItem->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label for="filter-status">Status</label>
                <select id="filter-status" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $stat)
                        <option value="{{ $stat }}" {{ $stat === $status ? 'selected' : '' }}>
                            {{ ucfirst($stat) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-field">
                <label for="published-from">Terbit sejak</label>
                <input form="news-filter-form" id="published-from" type="date" name="published_from" value="{{ $publishedFrom }}">
            </div>

            <div class="form-field">
                <label for="published-to">Sampai</label>
                <input form="news-filter-form" id="published-to" type="date" name="published_to" value="{{ $publishedTo }}">
            </div>

            <input type="hidden" name="page" value="1">

            <div class="filter-toolbar__actions">
                <button type="submit" class="primary-btn">Terapkan filter</button>
                <a href="{{ route('admin.news.index') }}" class="ghost-btn">Reset</a>
            </div>
        </form>

        <div class="news-layout news-layout--single">
            @php
                $perPageOptions = [6, 12, 18, 24, 36];
            @endphp

            <section class="news-panel">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar Berita</h2>
                        <p>Kelola konten berita dan publikasi informasi desa.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <select name="per_page" form="news-filter-form" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($perPageOptions as $size)
                                    <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }} Baris</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="search-input"
                                    name="search"
                                    type="search"
                                    placeholder="Cari berita..."
                                    value="{{ $search ?? '' }}"
                                    form="news-filter-form"
                                    aria-label="Cari berita"
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
                            <th style="text-align: center; width: 80px;">Thumbnail</th>
                            <th style="text-align: left;">Judul Berita</th>
                            <th style="text-align: center;">Kategori</th>
                            <th style="text-align: center;">Status</th>
                            <th style="text-align: center;">Tgl Publikasi</th>
                            <th style="text-align: center;">Views</th>
                            <th style="text-align: center; width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($news as $item)
                            @php
                                $rowNumber = $news->firstItem() ? $news->firstItem() + $loop->index : $loop->iteration;
                                    $detailPayload = [
                                        'title' => $item->title,
                                        'summary' => $item->summary,
                                        'status' => Str::ucfirst($item->status),
                                        'category' => $item->category->name ?? 'Tanpa kategori',
                                        'published' => $item->published_at?->translatedFormat('d M Y') ?? '-',
                                        'views' => number_format($item->views ?? 0),
                                        'content' => $item->content ?? '',
                                        'slug' => $item->slug,
                                        'thumbnail' => $item->thumbnail ? asset('storage/' . $item->thumbnail) : null,
                                        'author' => $item->author?->name ?? $item->created_by ?? 'Admin',
                                    ];
                            @endphp
                            <tr data-news-detail='@json($detailPayload)'>
                                <td class="news-table__index" style="text-align: center;">
                                    <span>{{ $rowNumber }}</span>
                                </td>
                                <td style="text-align: center; justify-content: center;">
                                    <div class="news-table__thumb-wrapper" style="margin: 0 auto;">
                                        @if ($item->thumbnail)
                                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}" class="news-table__thumb">
                                        @else
                                            <div class="news-table__thumb news-table__thumb--empty">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td style="text-align: left;">
                                    <strong>{{ $item->title }}</strong>
                                    <span class="row-summary">{{ $item->summary }}</span>
                                </td>
                                <td style="text-align: center;">{{ $item->category->name ?? '—' }}</td>
                                <td style="text-align: center;">
                                    <span class="status-pill status-pill--{{ $item->status }}">{{ ucfirst($item->status) }}</span>
                                </td>
                                <td style="text-align: center;">{{ $item->published_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td style="text-align: center;">{{ number_format($item->views ?? 0) }}</td>
                                <td style="text-align: center;">
                                    <details class="news-table__action-dropdown" data-action-menu>
                                        <summary
                                            class="action-button action-button--dots"
                                            aria-haspopup="menu"
                                            aria-expanded="false"
                                            aria-label="Tampilkan opsi untuk {{ $item->title }}"
                                        >
                                            <i class="fas fa-ellipsis-v"></i>
                                        </summary>
                                        <div class="news-table__action-options" role="menu">
                                            <button
                                                type="button"
                                                class="news-table__action-item"
                                                role="menuitem"
                                                data-news-detail-trigger
                                                aria-label="Lihat detail berita {{ $item->title }}"
                                            >
                                                <i class="fas fa-eye"></i>
                                                <span>Lihat</span>
                                            </button>
                                            <a
                                                href="{{ route('admin.news.edit', $item) }}"
                                                class="news-table__action-item"
                                                role="menuitem"
                                            >
                                                <i class="fas fa-pen"></i>
                                                <span>Edit</span>
                                            </a>
                                            <button
                                                type="button"
                                                class="news-table__action-item news-table__action-item--danger"
                                                role="menuitem"
                                                data-news-delete="{{ route('admin.news.destroy', $item) }}"
                                                data-news-title="{{ $item->title }}"
                                            >
                                                <i class="fas fa-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                            <td colspan="8" class="text-center py-6">Belum ada berita yang sesuai filter.</td>
                        </tr>

                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="news-footer">
                    <div class="table-info">
                        Menampilkan {{ $news->count() ? $news->firstItem() : 0 }} - {{ $news->count() ? $news->lastItem() : 0 }} dari {{ $news->total() }} entri
                    </div>
                    <div class="news-pagination news-pagination--center">
                        {{ $news->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
                    </div>
                </div>
            </section>

            <div class="news-delete-modal" id="newsDeleteModal" aria-hidden="true">
                <div class="news-delete-modal__backdrop" data-modal-backdrop></div>
                <div class="news-delete-modal__content" role="dialog" aria-modal="true" aria-labelledby="newsDeleteTitle">
                    <header class="news-delete-modal__header">
                        <h3 id="newsDeleteTitle">Hapus berita?</h3>
                        <button type="button" class="news-delete-modal__close" data-modal-close aria-label="Tutup dialog">
                            <i class="fas fa-times"></i>
                        </button>
                    </header>
                    <p class="news-delete-modal__body-text">
                        Apakah Anda yakin ingin menghapus berita ini? Data yang dihapus tidak dapat dikembalikan.
                    </p>
                    <div class="news-delete-modal__actions">
                        <button type="button" class="news-delete-modal__option" data-modal-close>Batal</button>
                        <button type="button" class="news-delete-modal__option news-delete-modal__option--danger" data-delete-confirm>Hapus</button>
                    </div>
                </div>
            </div>

            <form id="newsDeleteForm" method="POST" class="news-delete-form">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="news-detail-modal" id="newsDetailModal" aria-hidden="true">
        <div class="news-detail-modal__backdrop" data-detail-backdrop></div>
        <div class="news-detail-modal__content" role="dialog" aria-modal="true" aria-labelledby="newsDetailTitle">
            <header class="news-detail-modal__header">
                <h3 id="newsDetailTitle">Detail Berita</h3>
                <button type="button" class="news-detail-modal__close" aria-label="Tutup detail" data-detail-close>
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <figure class="news-detail-modal__thumb">
                <img src="" alt="" data-detail-thumbnail>
            </figure>
            <div class="news-detail-modal__meta-row">
                <span class="news-detail-modal__meta-item"><i class="fas fa-folder-open"></i> <span data-detail-category></span></span>
                <span class="news-detail-modal__meta-item"><i class="fas fa-flag"></i> <span data-detail-status></span></span>
                <span class="news-detail-modal__meta-item"><i class="fas fa-calendar-alt"></i> <span data-detail-published></span></span>
                <span class="news-detail-modal__meta-item"><i class="fas fa-eye"></i> <span data-detail-views></span></span>
                <span class="news-detail-modal__meta-item"><i class="fas fa-user"></i> <span data-detail-author></span></span>
            </div>
            <div class="news-detail-modal__body">
                <h4 data-detail-title></h4>
                <p class="news-detail-modal__summary" data-detail-summary></p>
                <div class="news-detail-modal__text" data-detail-content></div>
            </div>
        </div>
    </div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('newsDeleteModal');
            const deleteForm = document.getElementById('newsDeleteForm');
            const deleteModalTitle = deleteModal.querySelector('#newsDeleteTitle');
            const deleteConfirm = deleteModal.querySelector('[data-delete-confirm]');
            const deleteCloseButtons = deleteModal.querySelectorAll('[data-modal-close], [data-modal-backdrop]');

            const detailModal = document.getElementById('newsDetailModal');
            const detailBackdrop = detailModal.querySelector('[data-detail-backdrop]');
            const detailClose = detailModal.querySelector('[data-detail-close]');
            const detailThumbnail = detailModal.querySelector('[data-detail-thumbnail]');
            const detailTitle = detailModal.querySelector('[data-detail-title]');
            const detailCategory = detailModal.querySelector('[data-detail-category]');
            const detailStatus = detailModal.querySelector('[data-detail-status]');
            const detailPublished = detailModal.querySelector('[data-detail-published]');
            const detailViews = detailModal.querySelector('[data-detail-views]');
            const detailAuthor = detailModal.querySelector('[data-detail-author]');
            const detailSummary = detailModal.querySelector('[data-detail-summary]');
            const detailContent = detailModal.querySelector('[data-detail-content]');
            const defaultThumbnail = @json(asset('assets/default/news-thumb.png'));
            const currentUserName = @json(optional(auth()->user())->name ?: 'Admin');

            function hideDeleteModal() {
                deleteModal.classList.remove('news-delete-modal--visible');
                deleteModal.setAttribute('aria-hidden', 'true');
            }

            function showDeleteModal() {
                deleteModal.setAttribute('aria-hidden', 'false');
                deleteModal.classList.add('news-delete-modal--visible');
            }

            function hideDetailModal() {
                detailModal.classList.remove('news-detail-modal--visible');
            }

            function showDetailModal() {
                detailModal.classList.add('news-detail-modal--visible');
            }

            document.addEventListener('click', function (event) {
                const deleteTrigger = event.target.closest('[data-news-delete]');
                if (deleteTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    deleteForm.action = deleteTrigger.getAttribute('data-news-delete');
                    deleteModalTitle.textContent = deleteTrigger.getAttribute('data-news-title') || 'berita';
                    const actionDropdown = deleteTrigger.closest('details');
                    if (actionDropdown) {
                        actionDropdown.removeAttribute('open');
                    }
                    showDeleteModal();
                    document.querySelectorAll('details[open]').forEach(detail => detail.removeAttribute('open'));
                    return;
                }

                const detailTrigger = event.target.closest('[data-news-detail-trigger]');
                if (detailTrigger) {
                    event.preventDefault();
                    const row = detailTrigger.closest('tr');
                    const payload = row ? row.dataset.newsDetail : null;
                    if (!payload) {
                        return;
                    }
                    const data = JSON.parse(payload);
                    detailTitle.textContent = data.title;
                    detailSummary.textContent = data.summary;
                    detailCategory.textContent = `Kategori: ${data.category}`;
                    detailStatus.textContent = data.status;
                    detailPublished.textContent = data.published;
                    detailViews.textContent = data.views;
                    if (detailAuthor) {
                        detailAuthor.textContent = `Oleh ${data.author || currentUserName}`;
                    }
                    detailContent.innerHTML = data.content || '';
                    detailThumbnail.src = data.thumbnail || defaultThumbnail;
                    detailThumbnail.alt = data.title;
                    showDetailModal();
                    document.querySelectorAll('details[open]').forEach(detail => detail.removeAttribute('open'));
                }
            });

            deleteCloseButtons.forEach(button => {
                button.addEventListener('click', hideDeleteModal);
            });

            deleteConfirm.addEventListener('click', function () {
                deleteForm.submit();
            });

            [detailBackdrop, detailClose].forEach(element => {
                element.addEventListener('click', function () {
                    hideDetailModal();
                });
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('details[open]')) {
                    document.querySelectorAll('details[open]').forEach(detail => detail.removeAttribute('open'));
                }
            });
        });
    </script>
@endpush
@endsection

