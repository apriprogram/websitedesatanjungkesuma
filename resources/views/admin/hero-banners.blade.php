@extends('admin.layouts.app')

@section('title', 'Pengaturan Banner Publik')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-banner.css') }}">
@endpush

@section('content')
    <?php
    use Illuminate\Support\Facades\Storage;

    $activeCount = $slides->where('status', 'active')->count();
    $draftCount = $slides->where('status', 'draft')->count();
    $archivedCount = $slides->where('status', 'archived')->count();

    $mainStats = [
        'total' => $slides->count(),
        'active' => $slides->where('status', 'active')->count(),
        'draft' => $slides->where('status', 'draft')->count(),
        'archived' => $slides->where('status', 'archived')->count(),
    ];

    $infoStats = [
        'total' => $infoBanners->count(),
        'active' => $infoBanners->where('status', 'active')->count(),
        'draft' => $infoBanners->where('status', 'draft')->count(),
    ];

    $infographicStats = [
        'total' => $infographicBanners->count(),
        'active' => $infographicBanners->where('status', 'active')->count(),
        'draft' => $infographicBanners->where('status', 'draft')->count(),
    ];
        ?>

    <div class="hero-banner-page">
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
                <span class="header-title-text">Pengaturan Banner</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
                <span class="breadcrumb-link breadcrumb-link--active">Pengaturan Banner</span>
            </nav>
        </header>

        @include('admin.partials.alerts')

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Pengaturan Banner</h1>
                <p>Kelola hero-slide, status, dan layout banner agar konsisten dengan halaman referensi.</p>
            </div>
            <div class="title-actions">
                <button class="primary-btn hero-banners-action" id="newBannerBtn">
                    <i class="fas fa-plus"></i>
                    Tambah Banner
                </button>
            </div>
        </section>

        <section class="summary-grid">
            <article class="stat-card stat-card--accent">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Banner aktif</span>
                        <p class="stat-card__value" id="summaryActiveValue"><?php echo e($activeCount); ?></p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--primary"><i class="fas fa-bullhorn"></i></span>
                </div>
                <p class="stat-card__label">Banner yang dipangkas di halaman depan.</p>
            </article>
            <article class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Total banner</span>
                        <p class="stat-card__value" id="summaryTotalValue"><?php echo e($slides->count()); ?></p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--primary"><i class="fas fa-layer-group"></i></span>
                </div>
                <p class="stat-card__label">Jumlah semua banner di tab aktif.</p>
            </article>
            <article class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Banner draft</span>
                        <p class="stat-card__value" id="summaryDraftValue"><?php echo e($draftCount); ?></p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--muted"><i class="fas fa-file-alt"></i></span>
                </div>
                <p class="stat-card__label">Banner yang perlu revisi atau approval.</p>
            </article>
            <article class="stat-card">
                <div class="stat-card__header">
                    <div>
                        <span class="stat-card__eyebrow">Banner arsip</span>
                        <p class="stat-card__value" id="summaryArchivedValue"><?php echo e($archivedCount); ?></p>
                    </div>
                    <span class="stat-card__icon stat-card__icon--muted"><i class="fas fa-archive"></i></span>
                </div>
                <p class="stat-card__label">Banner yang sudah dimatikan.</p>
            </article>
        </section>

        <section class="tab-bar">
            <div class="tab-bar__items">
                <a class="tab-bar__item tab-bar__item--active" data-tab-panel="panel-utama">Banner Utama</a>
                <a class="tab-bar__item" data-tab-panel="panel-info">Banner Info</a>
                <a class="tab-bar__item" data-tab-panel="panel-infografis">Infografis</a>
            </div>
        </section>

        <section class="panels">
            <article class="hero-banner-card tab-panel is-active" id="panel-utama">
                <div class="panel-heading">
                    <div>
                        <h3>Daftar Banner Publik</h3>
                        <p>Upload gambar, ubah status, dan preview banner sebelum disimpan ke database.</p>
                    </div>
                </div>

                <?php if (session('success')): ?>
                <div class="alert alert-success" role="status">
                    <?php    echo e(session('success')); ?>

                </div>
                <?php endif; ?>

                <div class="table-controls">
                    <input type="search" id="bannerSearch" placeholder="Cari banner...">
                    <div class="table-filter">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="draft">Draft</option>
                            <option value="archived">Arsip</option>
                        </select>
                    </div>
                    <select id="sortFilter">
                        <option value="recent">Urutkan terbaru</option>
                        <option value="label">Judul A-Z</option>
                    </select>
                </div>

                <div class="table-responsive">
                    <table class="hero-banner-table" id="bannerTable">
                        <thead>
                            <tr>
                                <th>Label</th>
                                <th>Status</th>
                                <th>Terakhir diubah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $slides;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $slide):
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                            <tr data-id="<?php    echo e($slide->id); ?>"
                                data-update-url="<?php    echo e(route('admin.hero-banners.update', $slide)); ?>"
                                data-delete-url="<?php    echo e(route('admin.hero-banners.destroy', $slide)); ?>"
                                data-status="<?php    echo e($slide->status); ?>"
                                data-sort-order="<?php    echo e($slide->sort_order); ?>"
                                data-label="<?php    echo e($slide->title); ?>"
                                data-subtitle="<?php    echo e($slide->subtitle); ?>"
                                data-description="<?php    echo e($slide->description); ?>"
                                data-button-label="<?php    echo e($slide->button_label); ?>"
                                data-button-url="<?php    echo e($slide->button_url); ?>"
                                data-is-profile="<?php    echo e($slide->is_profile_slide ? '1' : '0'); ?>"
                                data-updated="<?php    echo e($slide->updated_at->timestamp); ?>"
                                data-updated-human="<?php    echo e($slide->updated_at->format('d M Y H:i')); ?>"
                                data-created-human="<?php    echo e($slide->created_at->format('d M Y H:i')); ?>"
                                data-image-url="<?php    echo e($slide->background_url ? asset('storage/' . ltrim($slide->background_url, '/')) : ''); ?>">
                                <td>
                                    <div class="table-label">
                                        <?php    if ($slide->background_url): ?>
                                        <img src="<?php        echo e(asset('storage/' . ltrim($slide->background_url, '/'))); ?>"
                                            alt="<?php        echo e($slide->title); ?>">
                                        <?php    else: ?>
                                        <span class="table-label__placeholder"><i class="fas fa-image"></i></span>
                                        <?php    endif; ?>
                                        <div>
                                            <strong><?php    echo e($slide->title); ?></strong>
                                            <?php    if ($slide->subtitle): ?>
                                            <small><?php        echo e($slide->subtitle); ?></small>
                                            <?php    endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="hero-banner-tags <?php    echo e($slide->status); ?>">
                                        <i class="fas fa-circle"></i>
                                        <?php    echo e(ucfirst($slide->status)); ?>

                                    </span>
                                </td>
                                <td><?php    echo e($slide->updated_at->format('d M Y')); ?></td>
                                <td class="table-actions">
                                    <div class="action-menu">
                                        <button class="action-menu__trigger" type="button" aria-label="Aksi banner"
                                            data-action-menu>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-menu__list">
                                            <button type="button" class="action-menu__item" data-action="preview">
                                                <i class="fas fa-eye"></i><span>Lihat</span>
                                            </button>
                                            <button type="button" class="action-menu__item" data-action="edit">
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            </button>
                                            <button type="button" class="action-menu__item action-menu__item--danger"
                                                data-action="delete">
                                                <i class="fas fa-trash"></i><span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach;
    $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-summary">
                    <div id="tableInfo">Menampilkan <?php echo e($slides->count()); ?> banner</div>
                    <div class="pagination-line">
                        <button class="soft-action-btn">&lsaquo;&lsaquo;</button>
                        <span>1</span>
                        <button class="soft-action-btn">&rsaquo;&rsaquo;</button>
                    </div>
                    <div class="entries-control">
                        <label>Menampilkan</label>
                        <select id="entriesSelect">
                            <option>10</option>
                            <option>25</option>
                        </select>
                        <span>entri</span>
                    </div>
                </div>
            </article>

            <article class="hero-banner-card tab-panel" id="panel-info">
                <div class="panel-heading">
                    <div>
                        <h3>Banner Info</h3>
                        <p class="banner-panel__description">Kelola gambar banner informasi yang ditampilkan pada bagian
                            info-media.</p>
                    </div>
                </div>
                <div class="table-controls">
                    <input type="search" id="infoSearch" placeholder="Cari banner info...">
                    <div class="table-filter">
                        <label for="infoStatusFilter">Status</label>
                        <select id="infoStatusFilter">
                            <option value="">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <select id="infoSort">
                        <option value="recent">Urutkan terbaru</option>
                        <option value="label">Judul A-Z</option>
                    </select>
                </div>
                <div class="banner-table-wrapper">
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true;
    $__currentLoopData = $infoBanners;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $index => $info):
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop();
        $__empty_1 = false; ?>
                            <tr data-update-url="<?php    echo e(route('admin.hero-banners.info.update', $info)); ?>"
                                data-delete-url="<?php    echo e(route('admin.hero-banners.info.destroy', $info)); ?>"
                                data-title="<?php    echo e($info->title); ?>"
                                data-subtitle="<?php    echo e($info->subtitle); ?>"
                                data-description="<?php    echo e($info->description); ?>"
                                data-status="<?php    echo e($info->status); ?>"
                                data-sort-order="<?php    echo e($info->sort_order); ?>"
                                data-image-url="<?php    echo e(asset('storage/' . ltrim($info->image_url, '/'))); ?>"
                                data-created="<?php    echo e(optional($info->created_at)->format('d M Y H:i')); ?>"
                                data-updated="<?php    echo e(optional($info->updated_at)->format('d M Y H:i')); ?>">
                                <td><img src="<?php    echo e(asset('storage/' . ltrim($info->image_url, '/'))); ?>"
                                        alt="<?php    echo e($info->title); ?>" loading="lazy"></td>
                                <td>
                                    <strong><?php    echo e($info->title ?: 'Tanpa judul'); ?></strong>
                                    <p><?php    echo e($info->subtitle); ?></p>
                                </td>
                                <td><span
                                        class="badge badge--<?php    echo e($info->status); ?>"><?php    echo e(ucfirst($info->status)); ?></span>
                                </td>
                                <td>
                                    <div class="action-menu">
                                        <button class="action-menu__trigger" type="button" aria-label="Aksi banner info"
                                            data-action-menu>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-menu__list">
                                            <button type="button" class="action-menu__item" data-info-detail>
                                                <i class="fas fa-eye"></i><span>Lihat</span>
                                            </button>
                                            <button type="button" class="action-menu__item" data-info-edit>
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            </button>
                                            <button type="button" class="action-menu__item action-menu__item--danger"
                                                data-info-delete>
                                                <i class="fas fa-trash"></i><span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach;
    $__env->popLoop();
    $loop = $__env->getLastLoop();
    if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="banner-panel-empty">Belum ada banner info.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-summary">
                    <div id="infoTableInfo">Menampilkan <?php echo e($infoBanners->count()); ?> banner info</div>
                    <div class="pagination-line">
                        <button class="soft-action-btn" id="infoPrev" type="button">&lsaquo;</button>
                        <span id="infoPageInfo">Hal 1</span>
                        <button class="soft-action-btn" id="infoNext" type="button">&rsaquo;</button>
                    </div>
                </div>
            </article>
            <article class="hero-banner-card tab-panel" id="panel-infografis">
                <div class="panel-heading">
                    <div>
                        <h3>Infografis</h3>
                        <p class="banner-panel__description">Kelola gambar infografis untuk tampil di bagian Infografis.</p>
                    </div>
                </div>
                <div class="table-controls">
                    <input type="search" id="infoGrafisSearch" placeholder="Cari infografis...">
                    <div class="table-filter">
                        <label for="infografisStatusFilter">Status</label>
                        <select id="infografisStatusFilter">
                            <option value="">Semua status</option>
                            <option value="active">Aktif</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <select id="infografisSort">
                        <option value="recent">Urutkan terbaru</option>
                        <option value="label">Judul A-Z</option>
                    </select>
                </div>
                <div class="banner-table-wrapper">
                    <table class="info-table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true;
    $__currentLoopData = $infographicBanners;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $graphic):
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop();
        $__empty_1 = false; ?>
                            <tr data-type="infografis"
                                data-update-url="<?php    echo e(route('admin.hero-banners.infografis.update', $graphic)); ?>"
                                data-delete-url="<?php    echo e(route('admin.hero-banners.infografis.destroy', $graphic)); ?>"
                                data-title="<?php    echo e($graphic->title); ?>"
                                data-subtitle="<?php    echo e($graphic->subtitle); ?>"
                                data-description="<?php    echo e($graphic->description); ?>"
                                data-status="<?php    echo e($graphic->status); ?>"
                                data-sort-order="<?php    echo e($graphic->sort_order); ?>"
                                data-image-url="<?php    echo e(asset('storage/' . ltrim($graphic->image_url, '/'))); ?>"
                                data-created="<?php    echo e(optional($graphic->created_at)->format('d M Y H:i')); ?>"
                                data-updated="<?php    echo e(optional($graphic->updated_at)->format('d M Y H:i')); ?>">
                                <td><img src="<?php    echo e(asset('storage/' . ltrim($graphic->image_url, '/'))); ?>"
                                        alt="<?php    echo e($graphic->title); ?>" loading="lazy"></td>
                                <td>
                                    <strong><?php    echo e($graphic->title ?: 'Tanpa judul'); ?></strong>
                                    <p><?php    echo e($graphic->subtitle); ?></p>
                                </td>
                                <td><span
                                        class="badge badge--<?php    echo e($graphic->status); ?>"><?php    echo e(ucfirst($graphic->status)); ?></span>
                                </td>
                                <td class="table-actions">
                                    <div class="action-menu">
                                        <button class="action-menu__trigger" type="button" aria-label="Aksi infografis"
                                            data-action-menu>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="action-menu__list">
                                            <button type="button" class="action-menu__item" data-info-detail>
                                                <i class="fas fa-eye"></i><span>Lihat</span>
                                            </button>
                                            <button type="button" class="action-menu__item" data-info-edit>
                                                <i class="fas fa-pen"></i><span>Edit</span>
                                            </button>
                                            <button type="button" class="action-menu__item action-menu__item--danger"
                                                data-info-delete>
                                                <i class="fas fa-trash"></i><span>Hapus</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach;
    $__env->popLoop();
    $loop = $__env->getLastLoop();
    if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="banner-panel-empty">Belum ada infografis.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <div class="modal" id="bannerModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content">
                <h3 id="modalTitle">Tambah Banner Utama</h3>
                <form id="bannerForm" class="modal-form" method="post"
                    action="<?php echo e(route('admin.hero-banners.store')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="bannerFormMethod" value="POST">

                    <div class="modal-form-scroll">
                        <div class="banner-upload-box">
                            <div class="banner-upload-preview" id="bannerImagePreviewWrapper">
                                <img src="" alt="Preview gambar banner" id="bannerImagePreview">
                                <div class="banner-upload-placeholder" id="bannerImagePlaceholder">
                                    <i class="fas fa-image"></i>
                                    <span>Tidak ada gambar</span>
                                </div>
                            </div>
                            <div class="banner-upload-info">
                                <span>Gambar (upload JPEG/PNG, Max 4MB)</span>
                                <span class="banner-upload-hint" id="bannerImagePreviewLabel">Preview gambar saat ini</span>
                            </div>
                            <input type="file" id="bannerImage" name="image" accept="image/*">
                        </div>

                        <div class="modal-form-grid">
                            <div>
                                <label for="bannerTitle">Judul Banner</label>
                                <input type="text" id="bannerTitle" name="title" required>

                                <label for="bannerSubtitle">Subjudul</label>
                                <input type="text" id="bannerSubtitle" name="subtitle">
                            </div>

                            <div>
                                <label for="bannerButtonLabel">Label tombol</label>
                                <input type="text" id="bannerButtonLabel" name="button_label">

                                <label for="bannerButtonUrl">URL tombol</label>
                                <input type="url" id="bannerButtonUrl" name="button_url">
                            </div>
                        </div>

                        <div class="modal-form-grid modal-form-grid--half">
                            <div>
                                <label for="bannerStatus">Status</label>
                                <select id="bannerStatus" name="status">
                                    <option value="active">Aktif</option>
                                    <option value="draft">Draft</option>
                                    <option value="archived">Arsip</option>
                                </select>
                            </div>
                            <div>
                                <label for="bannerSort">Urutan</label>
                                <input type="number" id="bannerSort" name="sort_order" value="0" min="0">
                            </div>
                        </div>

                        <div class="modal-form-grid modal-form-span-2">
                            <div class="modal-form-span-2">
                                <label for="bannerDescription">Deskripsi</label>
                                <textarea id="bannerDescription" name="description" rows="4"></textarea>
                            </div>
                        </div>

                        <label for="bannerProfileSlide" class="checkbox-field toggle-switch">
                            <input type="hidden" name="is_profile_slide" value="0">
                            <input type="checkbox" id="bannerProfileSlide" name="is_profile_slide" value="1">
                            <span class="toggle-switch__slider" aria-hidden="true"></span>
                            <span>Gunakan sebagai slide profil desa</span>
                        </label>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="soft-action-btn" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal" id="previewModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content preview-modal-content">
                <h3>Preview Banner</h3>
                <div class="modal-form-scroll">
                    <div class="hero-banner-preview" id="bannerPreview">
                        <img src="" alt="Preview Banner" id="previewImage">
                        <div class="preview-text">
                            <p class="preview-subtitle" id="previewSubtitle"></p>
                            <p class="preview-description" id="previewDescription"></p>
                            <a href="#" class="primary-btn" id="previewButton">Kunjungi</a>
                        </div>
                    </div>
                    <table class="preview-data">
                        <tbody>
                            <tr>
                                <th>Judul</th>
                                <td id="previewTableTitle"></td>
                            </tr>
                            <tr>
                                <th>Subjudul</th>
                                <td id="previewTableSubtitle"></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td id="previewTableDescription"></td>
                            </tr>
                            <tr>
                                <th>Label Tombol</th>
                                <td id="previewTableButtonLabel"></td>
                            </tr>
                            <tr>
                                <th>URL Tombol</th>
                                <td id="previewTableButtonUrl"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="previewTableStatus"></td>
                            </tr>
                            <tr>
                                <th>Urutan</th>
                                <td id="previewTableSort"></td>
                            </tr>
                            <tr>
                                <th>Slide Profil</th>
                                <td id="previewTableProfile"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-actions">
                    <button type="button" class="soft-action-btn" data-modal-close>Tutup</button>
                </div>
            </div>
        </div>

        <div class="modal" id="detailModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content preview-modal-content">
                <div class="modal-header">
                    <div>
                        <h3 id="detailTitle">Detail Banner</h3>
                        <p class="helper-text" id="detailSubtitle">Informasi lengkap banner terpilih.</p>
                    </div>
                    <button class="modal-close" type="button" data-modal-close aria-label="Tutup detail"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="modal-form-scroll">
                    <div class="hero-banner-preview" id="detailPreview">
                        <img src="" alt="Preview Banner" id="detailImage">
                        <div class="preview-text">
                            <p class="preview-subtitle" id="detailDesc"></p>
                            <p class="preview-description" id="detailButton"></p>
                        </div>
                    </div>
                    <table class="preview-data">
                        <tbody>
                            <tr>
                                <th>Judul</th>
                                <td id="detailTableTitle"></td>
                            </tr>
                            <tr>
                                <th>Subjudul</th>
                                <td id="detailTableSubtitle"></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td id="detailTableDescription"></td>
                            </tr>
                            <tr>
                                <th>Label Tombol</th>
                                <td id="detailTableButtonLabel"></td>
                            </tr>
                            <tr>
                                <th>URL Tombol</th>
                                <td id="detailTableButtonUrl"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="detailTableStatus"></td>
                            </tr>
                            <tr>
                                <th>Urutan</th>
                                <td id="detailTableSort"></td>
                            </tr>
                            <tr>
                                <th>Slide Profil</th>
                                <td id="detailTableProfile"></td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td id="detailTableCreated"></td>
                            </tr>
                            <tr>
                                <th>Terakhir Diubah</th>
                                <td id="detailTableUpdated"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-actions">
                    <button type="button" class="soft-action-btn" data-modal-close>Tutup</button>
                </div>
            </div>
        </div>

        <div class="modal" id="infoDetailModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content preview-modal-content">
                <div class="modal-header modal-header--between">
                    <div>
                        <h3>Detail Banner Info</h3>
                        <p class="helper-text">Informasi banner info terpilih.</p>
                    </div>
                    <button class="modal-close" type="button" data-modal-close aria-label="Tutup detail"><i
                            class="fas fa-times"></i></button>
                </div>
                <div class="modal-form-scroll">
                    <div class="hero-banner-preview">
                        <img src="" alt="Preview Banner Info" id="infoDetailImage">
                    </div>
                    <table class="preview-data">
                        <tbody>
                            <tr>
                                <th>Judul</th>
                                <td id="infoDetailTitle"></td>
                            </tr>
                            <tr>
                                <th>Subjudul</th>
                                <td id="infoDetailSubtitle"></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td id="infoDetailDescription"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="infoDetailStatus"></td>
                            </tr>
                            <tr>
                                <th>Urutan</th>
                                <td id="infoDetailSort"></td>
                            </tr>
                            <tr>
                                <th>Dibuat</th>
                                <td id="infoDetailCreated"></td>
                            </tr>
                            <tr>
                                <th>Diubah</th>
                                <td id="infoDetailUpdated"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-actions">
                    <button type="button" class="soft-action-btn" data-modal-close>Tutup</button>
                </div>
            </div>
        </div>

        <div class="modal" id="infoEditModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content">
                <div class="modal-header modal-header--between">
                    <div>
                        <h3>Edit Banner Info</h3>
                        <p class="helper-text">Perbarui data banner info.</p>
                    </div>
                    <button class="modal-close" type="button" data-modal-close aria-label="Tutup edit"><i
                            class="fas fa-times"></i></button>
                </div>
                <form id="infoEditForm" class="modal-form" method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" value="PUT">
                    <div class="modal-form-scroll">
                        <div class="banner-upload-box">
                            <div class="banner-upload-preview" id="infoEditPreviewWrapper">
                                <img src="" alt="Preview gambar banner info" id="infoEditPreview">
                                <div class="banner-upload-placeholder" id="infoEditPlaceholder">
                                    <i class="fas fa-image"></i>
                                    <span>Tidak ada gambar</span>
                                </div>
                            </div>
                            <div class="banner-upload-info">
                                <span>Gambar (upload JPEG/PNG, Max 4MB)</span>
                                <span class="banner-upload-hint" id="infoEditPreviewLabel">Preview gambar saat ini</span>
                            </div>
                            <input type="file" name="image" id="infoEditImage" accept="image/*">
                        </div>

                        <div class="modal-form-grid">
                            <div>
                                <label>Judul</label>
                                <input type="text" name="title" id="infoEditTitle">
                            </div>
                            <div>
                                <label>Subjudul</label>
                                <input type="text" name="subtitle" id="infoEditSubtitle">
                            </div>
                        </div>

                        <div class="modal-form-grid modal-form-span-2">
                            <div class="modal-form-span-2">
                                <label>Deskripsi</label>
                                <textarea name="description" id="infoEditDescription" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="modal-form-grid modal-form-grid--half">
                            <div>
                                <label>Status</label>
                                <select name="status" id="infoEditStatus">
                                    <option value="active">Aktif</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                            <div>
                                <label>Urutan</label>
                                <input type="number" name="sort_order" id="infoEditSort" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="soft-action-btn" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <form id="infoDeleteForm" method="post" style="display:none;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
        </form>

        <div class="modal" id="deleteModal" aria-hidden="true">
            <div class="modal-backdrop" data-modal-close></div>
            <div class="modal-content">
                <h3>Konfirmasi Hapus</h3>
                <p>Yakin ingin menghapus banner <strong id="deleteModalLabel"></strong>?</p>
                <div class="modal-actions">
                    <button type="button" class="soft-action-btn" data-modal-close>Batal</button>
                    <button type="button" class="primary-btn" id="deleteModalConfirm">Hapus</button>
                </div>
            </div>
        </div>

        <form id="bannerDeleteForm" method="post" style="display: none;">
            <?php echo csrf_field(); ?>
            <?php echo method_field('DELETE'); ?>
        </form>
        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" hidden>
            <?php echo csrf_field(); ?>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-bar__item');
            const primaryAddButton = document.getElementById('newBannerBtn');
            const summaryActiveValue = document.getElementById('summaryActiveValue');
            const summaryDraftValue = document.getElementById('summaryDraftValue');
            const summaryArchivedValue = document.getElementById('summaryArchivedValue');
            const summaryTotalValue = document.getElementById('summaryTotalValue');
            let activeTabId = 'panel-utama';
            const summaryStats = {
                'panel-utama': { active: <?php echo e($mainStats['active']); ?>, draft: <?php echo e($mainStats['draft']); ?>, archived: <?php echo e($mainStats['archived']); ?>, total: <?php echo e($mainStats['total']); ?> },
                'panel-info': { active: <?php echo e($infoStats['active']); ?>, draft: <?php echo e($infoStats['draft']); ?>, archived: 0, total: <?php echo e($infoStats['total']); ?> },
                'panel-infografis': { active: <?php echo e($infographicStats['active']); ?>, draft: <?php echo e($infographicStats['draft']); ?>, archived: 0, total: <?php echo e($infographicStats['total']); ?> },
            };

            const updateSummaryStats = (panelId) => {
                const stats = summaryStats[panelId] || summaryStats['panel-utama'];
                summaryActiveValue.textContent = stats.active ?? 0;
                summaryDraftValue.textContent = stats.draft ?? 0;
                summaryArchivedValue.textContent = stats.archived ?? 0;
                summaryTotalValue.textContent = stats.total ?? 0;
            };

            const updatePrimaryButton = (panelId) => {
                activeTabId = panelId;
                const primaryAddButton = document.getElementById('newBannerBtn');
                if (!primaryAddButton) return;
                const labelMap = {
                    'panel-utama': 'Tambah Banner Utama',
                    'panel-info': 'Tambah Banner Info',
                    'panel-infografis': 'Tambah Infografis',
                };
                primaryAddButton.innerHTML = `<i class="fas fa-plus"></i> ${labelMap[panelId] || 'Tambah Banner'}`;
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', event => {
                    event.preventDefault();
                    tabs.forEach(tabItem => tabItem.classList.remove('tab-bar__item--active'));
                    tab.classList.add('tab-bar__item--active');
                    document.querySelectorAll('.tab-panel').forEach(panel => {
                        panel.classList.toggle('is-active', panel.id === tab.dataset.tabPanel);
                    });
                    updateSummaryStats(tab.dataset.tabPanel);
                    updatePrimaryButton(tab.dataset.tabPanel);
                });
            });
            const initialPanel = document.querySelector('.tab-panel.is-active');
            const initialId = initialPanel ? initialPanel.id : 'panel-utama';
            updateSummaryStats(initialId);
            updatePrimaryButton(initialId);

            const bannerTable = document.getElementById('bannerTable');
            const rows = Array.from(bannerTable.querySelectorAll('tbody tr'));
            const tableInfo = document.getElementById('tableInfo');
            const statusFilter = document.getElementById('statusFilter');
            const searchInput = document.getElementById('bannerSearch');
            const sortFilter = document.getElementById('sortFilter');
            const previewModal = document.getElementById('previewModal');
            const bannerModal = document.getElementById('bannerModal');
            const deleteModal = document.getElementById('deleteModal');
            const previewImage = document.getElementById('previewImage');
            const previewSubtitle = document.getElementById('previewSubtitle');
            const previewDescription = document.getElementById('previewDescription');
            const previewButton = document.getElementById('previewButton');
            const previewTableTitle = document.getElementById('previewTableTitle');
            const previewTableSubtitle = document.getElementById('previewTableSubtitle');
            const previewTableDescription = document.getElementById('previewTableDescription');
            const previewTableButtonLabel = document.getElementById('previewTableButtonLabel');
            const previewTableButtonUrl = document.getElementById('previewTableButtonUrl');
            const previewTableStatus = document.getElementById('previewTableStatus');
            const previewTableSort = document.getElementById('previewTableSort');
            const previewTableProfile = document.getElementById('previewTableProfile');
            const modalTitle = document.getElementById('modalTitle');
            const bannerForm = document.getElementById('bannerForm');
            const bannerFormMethod = document.getElementById('bannerFormMethod');
            const bannerTitleInput = document.getElementById('bannerTitle');
            const bannerSubtitleInput = document.getElementById('bannerSubtitle');
            const bannerDescriptionInput = document.getElementById('bannerDescription');
            const bannerButtonLabelInput = document.getElementById('bannerButtonLabel');
            const bannerButtonUrlInput = document.getElementById('bannerButtonUrl');
            const bannerStatusInput = document.getElementById('bannerStatus');
            const bannerSortInput = document.getElementById('bannerSort');
            const bannerProfileInput = document.getElementById('bannerProfileSlide');
            const bannerImageInput = document.getElementById('bannerImage');
            const bannerImagePreview = document.getElementById('bannerImagePreview');
            const bannerImagePreviewWrapper = document.getElementById('bannerImagePreviewWrapper');
            const bannerImagePlaceholder = document.getElementById('bannerImagePlaceholder');
            const deleteForm = document.getElementById('bannerDeleteForm');
            const deleteModalLabel = document.getElementById('deleteModalLabel');
            const deleteModalConfirm = document.getElementById('deleteModalConfirm');
            const storeRoute = "<?php echo e(route('admin.hero-banners.store')); ?>";
            const detailModal = document.getElementById('detailModal');
            const detailImage = document.getElementById('detailImage');
            const detailDesc = document.getElementById('detailDesc');
            const detailButton = document.getElementById('detailButton');
            const detailTableTitle = document.getElementById('detailTableTitle');
            const infoForm = document.getElementById('infoBannerForm');
            const infoMethod = document.getElementById('infoBannerMethod');
            const infoTitle = document.getElementById('infoTitle');
            const infoSubtitle = document.getElementById('infoSubtitle');
            const infoDescription = document.getElementById('infoDescription');
            const infoStatus = document.getElementById('infoStatus');
            const infoSort = document.getElementById('infoSort');
            const infoImage = document.getElementById('infoImage');
            const infoSubmitBtn = document.getElementById('infoSubmitBtn');
            const infoResetBtn = document.getElementById('resetInfoForm');
            const infoDetailModal = document.getElementById('infoDetailModal');
            const infoDetailImage = document.getElementById('infoDetailImage');
            const infoDetailTitle = document.getElementById('infoDetailTitle');
            const infoDetailSubtitle = document.getElementById('infoDetailSubtitle');
            const infoDetailDescription = document.getElementById('infoDetailDescription');
            const infoDetailStatus = document.getElementById('infoDetailStatus');
            const infoDetailSort = document.getElementById('infoDetailSort');
            const infoDetailCreated = document.getElementById('infoDetailCreated');
            const infoDetailUpdated = document.getElementById('infoDetailUpdated');
            const detailTableSubtitle = document.getElementById('detailTableSubtitle');
            const detailTableDescription = document.getElementById('detailTableDescription');
            const detailTableButtonLabel = document.getElementById('detailTableButtonLabel');
            const detailTableButtonUrl = document.getElementById('detailTableButtonUrl');
            const detailTableStatus = document.getElementById('detailTableStatus');
            const detailTableSort = document.getElementById('detailTableSort');
            const detailTableProfile = document.getElementById('detailTableProfile');
            const detailTableCreated = document.getElementById('detailTableCreated');
            const detailTableUpdated = document.getElementById('detailTableUpdated');
            const infoEditModal = document.getElementById('infoEditModal');
            const infoEditForm = document.getElementById('infoEditForm');
            const infoEditTitle = document.getElementById('infoEditTitle');
            const infoEditSubtitle = document.getElementById('infoEditSubtitle');
            const infoEditDescription = document.getElementById('infoEditDescription');
            const infoEditStatus = document.getElementById('infoEditStatus');
            const infoEditSort = document.getElementById('infoEditSort');
            const infoEditImage = document.getElementById('infoEditImage');
            const infoEditPreview = document.getElementById('infoEditPreview');
            const infoEditPreviewWrapper = document.getElementById('infoEditPreviewWrapper');
            const infoEditPreviewLabel = document.getElementById('infoEditPreviewLabel');
            const infoDeleteForm = document.getElementById('infoDeleteForm');
            const infoEditMethod = infoEditForm ? infoEditForm.querySelector('input[name=\"_method\"]') : null;
            const infoEditModalTitle = infoEditModal ? infoEditModal.querySelector('.modal-header h3') : null;
            const infoEditModalHelper = infoEditModal ? infoEditModal.querySelector('.modal-header .helper-text') : null;
            const infoEditPlaceholder = document.getElementById('infoEditPlaceholder');
            const infoStoreRoute = "<?php echo e(route('admin.hero-banners.info.store')); ?>";
            const infografisStoreRoute = "<?php echo e(route('admin.hero-banners.infografis.store')); ?>";

            const filterRows = () => {
                const searchTerm = searchInput.value.trim().toLowerCase();
                const statusValue = statusFilter.value;
                let visible = 0;
                rows.forEach(row => {
                    const matchesStatus = !statusValue || row.dataset.status === statusValue;
                    const matchesSearch = row.dataset.label.toLowerCase().includes(searchTerm);
                    const visibleRow = matchesStatus && matchesSearch;
                    row.classList.toggle('is-hidden', !visibleRow);
                    if (visibleRow) visible++;
                });
                tableInfo.textContent = `Menampilkan ${visible} dari ${rows.length} banner`;
            };

            const sortRows = () => {
                const tbody = bannerTable.tBodies[0];
                const sorted = [...rows].sort((a, b) => {
                    if (sortFilter.value === 'label') {
                        return a.dataset.label.localeCompare(b.dataset.label);
                    }
                    return b.dataset.updated - a.dataset.updated;
                });
                sorted.forEach(row => tbody.appendChild(row));
            };

            const closeModal = (modal) => {
                modal.classList.remove('modal--visible');
                modal.setAttribute('aria-hidden', 'true');
            };

            const openModal = (modal) => {
                modal.classList.add('modal--visible');
                modal.setAttribute('aria-hidden', 'false');
            };

            document.querySelectorAll('[data-modal-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    closeModal(btn.closest('.modal'));
                });
            });

            searchInput.addEventListener('input', filterRows);
            statusFilter.addEventListener('change', filterRows);
            sortFilter.addEventListener('change', () => {
                sortRows();
                filterRows();
            });

            bannerImageInput.addEventListener('change', event => {
                const file = event.target.files[0];
                if (!file) {
                    bannerImagePreviewWrapper.classList.remove('is-visible');
                    bannerImagePreview.src = '';
                    bannerImagePlaceholder?.classList.remove('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = ({ target }) => {
                    bannerImagePreview.src = target.result;
                    bannerImagePreviewWrapper.classList.add('is-visible');
                    bannerImagePlaceholder?.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            });

            primaryAddButton?.addEventListener('click', () => {
                if (activeTabId === 'panel-utama') {
                    bannerForm.reset();
                    bannerForm.action = storeRoute;
                    bannerFormMethod.value = 'POST';
                    modalTitle.textContent = 'Tambah Banner Utama';
                    bannerStatusInput.value = 'active';
                    bannerSortInput.value = '0';
                    bannerProfileInput.checked = false;
                    bannerImagePreviewWrapper.classList.remove('is-visible');
                    bannerImagePlaceholder?.classList.remove('hidden');
                    openModal(bannerModal);
                    return;
                }

                if (!infoEditForm) return;
                infoEditForm.reset();
                infoEditPreviewWrapper.classList.remove('is-visible');
                infoEditPreview.src = '';
                infoEditPreviewLabel.textContent = 'Preview gambar saat ini';

                if (activeTabId === 'panel-info') {
                    infoEditForm.action = infoStoreRoute;
                    infoEditMethod && (infoEditMethod.value = 'POST');
                    infoEditStatus.value = 'active';
                    infoEditSort.value = '0';
                    if (infoEditModalTitle) infoEditModalTitle.textContent = 'Tambah Banner Info';
                    if (infoEditModalHelper) infoEditModalHelper.textContent = 'Tambahkan banner info baru.';
                    if (infoEditImage) infoEditImage.required = true;
                    infoEditTitle && (infoEditTitle.value = '');
                    infoEditSubtitle && (infoEditSubtitle.value = '');
                    infoEditDescription && (infoEditDescription.value = '');
                    infoEditPreviewWrapper.classList.remove('is-visible');
                    infoEditPlaceholder?.classList.remove('hidden');
                    infoEditPreviewLabel.textContent = 'Preview gambar saat ini';
                    openModal(infoEditModal);
                    return;
                }

                if (activeTabId === 'panel-infografis') {
                    infoEditForm.action = infografisStoreRoute;
                    infoEditMethod && (infoEditMethod.value = 'POST');
                    infoEditStatus.value = 'active';
                    infoEditSort.value = '0';
                    if (infoEditModalTitle) infoEditModalTitle.textContent = 'Tambah Infografis';
                    if (infoEditModalHelper) infoEditModalHelper.textContent = 'Tambahkan infografis baru.';
                    if (infoEditImage) infoEditImage.required = true;
                    infoEditTitle && (infoEditTitle.value = '');
                    infoEditSubtitle && (infoEditSubtitle.value = '');
                    infoEditDescription && (infoEditDescription.value = '');
                    infoEditPreviewWrapper.classList.remove('is-visible');
                    infoEditPlaceholder?.classList.remove('hidden');
                    infoEditPreviewLabel.textContent = 'Preview gambar saat ini';
                    openModal(infoEditModal);
                }
            });

            // action menu toggle
            document.addEventListener('click', (e) => {
                document.querySelectorAll('.action-menu__list.is-open').forEach(list => {
                    if (!list.contains(e.target) && !list.previousElementSibling?.contains(e.target)) {
                        list.classList.remove('is-open');
                        list.closest('tr')?.classList.remove('active-menu');
                    }
                });
                if (e.target.closest('[data-action-menu]')) {
                    const menu = e.target.closest('.action-menu');
                    const list = menu?.querySelector('.action-menu__list');
                    if (list) {
                        const isOpen = list.classList.toggle('is-open');
                        menu.closest('tr')?.classList.toggle('active-menu', isOpen);
                    }
                }
            });

            bannerTable.addEventListener('click', event => {
                const presetRow = event.target.closest('tr');
                if (!presetRow) {
                    return;
                }

                if (event.target.closest('[data-action="preview"]')) {
                    const imageUrl = presetRow.dataset.imageUrl;
                    previewImage.src = imageUrl || '';
                    previewSubtitle.textContent = presetRow.dataset.subtitle || '-';
                    previewDescription.textContent = presetRow.dataset.description || '-';
                    previewTableTitle.textContent = presetRow.dataset.label || '-';
                    previewTableSubtitle.textContent = presetRow.dataset.subtitle || '-';
                    previewTableDescription.textContent = presetRow.dataset.description || '-';
                    previewTableButtonLabel.textContent = presetRow.dataset.buttonLabel || '-';
                    previewTableButtonUrl.textContent = presetRow.dataset.buttonUrl || '-';
                    previewTableStatus.textContent = presetRow.dataset.status ? presetRow.dataset.status.charAt(0).toUpperCase() + presetRow.dataset.status.slice(1) : '-';
                    previewTableSort.textContent = presetRow.dataset.sortOrder || '0';
                    previewTableProfile.textContent = presetRow.dataset.isProfile === '1' ? 'Ya' : 'Tidak';
                    previewButton.textContent = presetRow.dataset.buttonLabel || 'Kunjungi';
                    previewButton.href = presetRow.dataset.buttonUrl || '#';
                    openModal(previewModal);
                    return;
                }

                if (event.target.closest('[data-action="detail"]')) {
                    detailImage.src = presetRow.dataset.imageUrl || '';
                    detailDesc.textContent = presetRow.dataset.subtitle || '-';
                    detailButton.textContent = presetRow.dataset.buttonLabel ? `Tombol: ${presetRow.dataset.buttonLabel}` : 'Tidak ada tombol';
                    detailTableTitle.textContent = presetRow.dataset.label || '-';
                    detailTableSubtitle.textContent = presetRow.dataset.subtitle || '-';
                    detailTableDescription.textContent = presetRow.dataset.description || '-';
                    detailTableButtonLabel.textContent = presetRow.dataset.buttonLabel || '-';
                    detailTableButtonUrl.textContent = presetRow.dataset.buttonUrl || '-';
                    detailTableStatus.textContent = presetRow.dataset.status ? presetRow.dataset.status.charAt(0).toUpperCase() + presetRow.dataset.status.slice(1) : '-';
                    detailTableSort.textContent = presetRow.dataset.sortOrder || '0';
                    detailTableProfile.textContent = presetRow.dataset.isProfile === '1' ? 'Ya' : 'Tidak';
                    detailTableCreated.textContent = presetRow.dataset.createdHuman || '-';
                    detailTableUpdated.textContent = presetRow.dataset.updatedHuman || '-';
                    openModal(detailModal);
                    return;
                }

                if (event.target.closest('[data-action="edit"]')) {
                    bannerForm.action = presetRow.dataset.updateUrl;
                    bannerFormMethod.value = 'PUT';
                    modalTitle.textContent = 'Edit Banner';
                    bannerTitleInput.value = presetRow.dataset.label;
                    bannerSubtitleInput.value = presetRow.dataset.subtitle;
                    bannerDescriptionInput.value = presetRow.dataset.description;
                    bannerButtonLabelInput.value = presetRow.dataset.buttonLabel;
                    bannerButtonUrlInput.value = presetRow.dataset.buttonUrl;
                    bannerStatusInput.value = presetRow.dataset.status;
                    bannerSortInput.value = presetRow.dataset.sortOrder || '0';
                    bannerProfileInput.checked = presetRow.dataset.isProfile === '1';
                    if (presetRow.dataset.imageUrl) {
                        bannerImagePreview.src = presetRow.dataset.imageUrl;
                        bannerImagePreviewWrapper.classList.add('is-visible');
                        bannerImagePlaceholder?.classList.add('hidden');
                    } else {
                        bannerImagePreviewWrapper.classList.remove('is-visible');
                        bannerImagePreview.src = '';
                        bannerImagePlaceholder?.classList.remove('hidden');
                    }
                    openModal(bannerModal);
                    return;
                }

                if (event.target.closest('[data-action="delete"]')) {
                    deleteForm.action = presetRow.dataset.deleteUrl;
                    deleteModalLabel.textContent = presetRow.dataset.label || 'banner';
                    currentDeleteForm = deleteForm;
                    openModal(deleteModal);
                }
            });

            let currentDeleteForm = deleteForm;
            deleteModalConfirm.addEventListener('click', () => currentDeleteForm.submit());

            sortRows();
            filterRows();

            // Info Banner handlers
            if (infoForm) {
                const resetInfoFormHandler = () => {
                    infoForm.action = "<?php echo e(route('admin.hero-banners.info.store')); ?>";
                    if (infoMethod) infoMethod.value = 'POST';
                    if (infoTitle) infoTitle.value = '';
                    if (infoSubtitle) infoSubtitle.value = '';
                    if (infoDescription) infoDescription.value = '';
                    if (infoStatus) infoStatus.value = 'active';
                    if (infoSort) infoSort.value = '0';
                    if (infoImage) infoImage.value = '';
                    if (infoSubmitBtn) infoSubmitBtn.textContent = 'Unggah Banner Info';
                };

                infoResetBtn?.addEventListener('click', resetInfoFormHandler);
                resetInfoFormHandler();
            }

            document.querySelectorAll('#panel-info tbody tr, #panel-infografis tbody tr').forEach(row => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('form')) return;

                    if (event.target.closest('[data-info-edit]')) {
                        infoEditForm.action = row.dataset.updateUrl;
                        infoEditTitle.value = row.dataset.title || '';
                        infoEditSubtitle.value = row.dataset.subtitle || '';
                        infoEditDescription.value = row.dataset.description || '';
                        infoEditStatus.value = row.dataset.status || 'active';
                        infoEditSort.value = row.dataset.sortOrder || '0';
                        infoEditImage.value = '';
                        infoEditMethod && (infoEditMethod.value = 'PUT');
                        if (infoEditImage) infoEditImage.required = false;
                        if (row.dataset.type === 'infografis') {
                            if (infoEditModalTitle) infoEditModalTitle.textContent = 'Edit Infografis';
                            if (infoEditModalHelper) infoEditModalHelper.textContent = 'Perbarui data infografis.';
                        } else {
                            if (infoEditModalTitle) infoEditModalTitle.textContent = 'Edit Banner Info';
                            if (infoEditModalHelper) infoEditModalHelper.textContent = 'Perbarui data banner info.';
                        }
                        if (row.dataset.imageUrl) {
                            infoEditPreview.src = row.dataset.imageUrl;
                            infoEditPreviewWrapper.classList.add('is-visible');
                            infoEditPreviewLabel.textContent = 'Preview gambar saat ini';
                            infoEditPlaceholder?.classList.add('hidden');
                        } else {
                            infoEditPreviewWrapper.classList.remove('is-visible');
                            infoEditPreview.src = '';
                            infoEditPreviewLabel.textContent = 'Belum ada gambar';
                            infoEditPlaceholder?.classList.remove('hidden');
                        }
                        openModal(infoEditModal);
                        return;
                    }

                    if (event.target.closest('[data-info-detail]')) {
                        infoDetailImage.src = row.dataset.imageUrl || '';
                        infoDetailTitle.textContent = row.dataset.title || '-';
                        infoDetailSubtitle.textContent = row.dataset.subtitle || '-';
                        infoDetailDescription.textContent = row.dataset.description || '-';
                        infoDetailStatus.textContent = row.dataset.status ? row.dataset.status.charAt(0).toUpperCase() + row.dataset.status.slice(1) : '-';
                        infoDetailSort.textContent = row.dataset.sortOrder || '0';
                        infoDetailCreated.textContent = row.dataset.created || '-';
                        infoDetailUpdated.textContent = row.dataset.updated || '-';
                        openModal(infoDetailModal);
                        return;
                    }

                    if (event.target.closest('[data-info-delete]')) {
                        infoDeleteForm.action = row.dataset.deleteUrl;
                        deleteModalLabel.textContent = row.dataset.title || 'banner info';
                        currentDeleteForm = infoDeleteForm;
                        openModal(deleteModal);
                        return;
                    }
                });
            });

            infoEditImage?.addEventListener('change', (event) => {
                const file = event.target.files?.[0];
                if (!file) {
                    infoEditPreviewWrapper.classList.remove('is-visible');
                    infoEditPreview.src = '';
                    infoEditPreviewLabel.textContent = 'Belum ada gambar';
                    infoEditPlaceholder?.classList.remove('hidden');
                    return;
                }
                const reader = new FileReader();
                reader.onload = ({ target }) => {
                    infoEditPreview.src = target.result;
                    infoEditPreviewWrapper.classList.add('is-visible');
                    infoEditPreviewLabel.textContent = 'Preview gambar baru';
                    infoEditPlaceholder?.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            });

        });
    </script>
@endpush
