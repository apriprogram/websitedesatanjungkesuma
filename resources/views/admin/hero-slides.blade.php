@extends('admin.layouts.app')

@section('title', 'Pengaturan Hero Slide')

@push('head')
    <style>
        .hero-slides-page {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .hero-slides-panel {
            background: #fff;
            padding: 1.5rem;
            border-radius: 20px;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .hero-slides-panel h3 {
            margin: 0 0 0.75rem;
            font-weight: 600;
        }

        .hero-slide-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
        }

        .hero-slide-form .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .hero-slide-form input,
        .hero-slide-form select,
        .hero-slide-form textarea {
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0.8rem;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .hero-slide-form textarea {
            resize: vertical;
            min-height: 90px;
        }

        .tab-panel {
            display: none;
            flex-direction: column;
            gap: 1rem;
        }

        .tab-panel.is-active {
            display: flex;
        }

        .table-filter,
        .table-search {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .table-filter select,
        .table-search input {
            border-radius: 12px;
            border: 1px solid #d1d5db;
            padding: 0.6rem 0.9rem;
            font-size: 0.9rem;
            background: #f8fafc;
        }

        .table-info {
            font-size: 0.9rem;
            color: #475569;
        }

        .entries-control {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.9rem;
        }

        .hero-slide-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .hero-slide-table th,
        .hero-slide-table td {
            padding: 0.9rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        .hero-slide-table th {
            font-weight: 600;
            background: #f8fafc;
        }

        .hero-slide-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            font-size: 0.8rem;
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
        }

        .panel-actions {
            margin-top: 1rem;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        .panel-actions .primary-btn,
        .panel-actions .soft-action-btn {
            min-width: 140px;
        }
    </style>
@endpush

@section('content')
    <div class="hero-slides-page">
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
                <span class="header-title-text">Pengelolaan Hero Slide &amp; Banner</span>
                @include('admin.partials.header-controls')
            </div>
            <section class="page-title page-title--with-actions">
                <div>
                    <h1>Pengelolaan Hero Slide &amp; Banner</h1>
                    <p>Sesuaikan tampilan profil Desa dan banner publikasi yang muncul di halaman utama.</p>
                </div>
                <div class="title-actions">
                    <button class="primary-btn">Simpan Perubahan</button>
                </div>
            </section>
        </header>

        <section class="summary-grid">
            <article class="stat-card stat-card--accent" data-card="hero">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Slide aktif</span>
                        <p class="stat-card__value">{{ count($slides) }}</p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--primary"><i class="fas fa-image"></i></span>
                </div>
                <p class="stat-card__label">Jumlah hero slide yang siap ditayangkan.</p>
            </article>
            <article class="stat-card" data-card="banner">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Banner aktif</span>
                        <p class="stat-card__value">{{ collect($bannerMenus)->where('state', true)->count() }}</p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--success"><i class="fas fa-bullhorn"></i></span>
                </div>
                <p class="stat-card__label">Menu banner profil desa dapat diaktifkan disini.</p>
            </article>
        </section>

        <section class="tab-bar">
            <div class="tab-bar__items">
                <a href="#" class="tab-bar__item tab-bar__item--active" data-tab-target="slides">Hero Slides</a>
                <a href="#" class="tab-bar__item" data-tab-target="profil">Profil Desa</a>
                <a href="#" class="tab-bar__item" data-tab-target="banner">Banner Publik</a>
            </div>
        </section>

        <section class="panels">
            <article class="hero-slides-panel tab-panel is-active" id="panel-slides">
                <h3>Daftar Hero Slide</h3>
                <div class="table-search">
                    <input type="search" placeholder="Cari judul slide...">
                    <div class="table-filter">
                        <select>
                            <option value="all">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="draft">Draft</option>
                        </select>
                        <select>
                            <option value="newest">Terbaru</option>
                            <option value="updated">Terakhir update</option>
                        </select>
                    </div>
                </div>
                <div class="table-info">Menampilkan {{ count($slides) }} slide</div>
                <table class="hero-slide-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Subjudul</th>
                            <th>Status</th>
                            <th>Background</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slides as $slide)
                            <tr>
                                <td>{{ $slide['title'] }}</td>
                                <td>{{ $slide['subtitle'] }}</td>
                                <td>
                                    <span class="hero-slide-badge">
                                        <i class="fas fa-circle"></i>
                                        {{ $slide['status'] }}
                                    </span>
                                </td>
                                <td>{{ $slide['background'] }}</td>
                                <td>
                                    <button class="soft-action-btn soft-action-btn--violet">Edit</button>
                                    <button class="soft-action-btn soft-action-btn--danger">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="table-info">Menampilkan 1-{{ count($slides) }} dari {{ count($slides) }} slide</div>
                <div class="paginations">
                    <button class="soft-action-btn">«</button>
                    <button class="soft-action-btn">1</button>
                    <button class="soft-action-btn">»</button>
                </div>
            </article>

            <article class="hero-slides-panel tab-panel" id="panel-profil">
                <h3>Slide Profil Desa</h3>
                <form class="hero-slide-form">
                    <div class="form-group">
                        <label>Judul Profil</label>
                        <input type="text" value="Profil Desa Tanjung Kesuma">
                    </div>
                    <div class="form-group">
                        <label>Subtitle</label>
                        <input type="text" value="Desa yang harmonis dan mandiri">
                    </div>
                    <div class="form-group">
                        <label>Konten Ringkasan</label>
                        <textarea>Teknologi informasi kami hadir untuk mempermudah pelayanan publik dan transparansi kegiatan.</textarea>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select>
                            <option>Aktif</option>
                            <option>Arsip</option>
                        </select>
                    </div>
                </form>
            </article>

            <article class="hero-slides-panel tab-panel" id="panel-banner">
                <h3>Menu Banner Publik</h3>
                <div class="hero-slide-form">
                    @foreach($bannerMenus as $menu)
                        <div class="form-group">
                            <label>{{ $menu['name'] }}</label>
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <input type="checkbox" {{ $menu['state'] ? 'checked' : '' }}>
                                <span>{{ $menu['label'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="panels">
            <article class="hero-slides-panel">
                <div class="table-summary">
                    <div class="entries-control">
                        <label>Menampilkan</label>
                        <select>
                            <option>10</option>
                            <option>20</option>
                            <option>50</option>
                        </select>
                    </div>
                    <div class="table-info">1-3 dari 3 entri</div>
                </div>
            </article>
        </section>

        <div class="panel-actions">
            <button class="soft-action-btn">Batalkan</button>
            <button class="primary-btn">Terapkan</button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.tab-bar__item');
            tabs.forEach(tab => tab.addEventListener('click', function (event) {
                event.preventDefault();
                tabs.forEach(t => t.classList.remove('tab-bar__item--active'));
                this.classList.add('tab-bar__item--active');
                document.querySelectorAll('.tab-panel').forEach(panel => {
                    panel.classList.toggle('is-active', panel.id === `panel-${this.dataset.tabTarget}`);
                });
            }));
        });
    </script>
@endpush
