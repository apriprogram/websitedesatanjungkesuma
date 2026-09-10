@extends('admin.layouts.app')

@section('title', 'Tambah Pengumuman Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select.css') }}">
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
                <span class="header-title-text">Manajemen Pengumuman</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Publikasi</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.announcements.index') }}">Pengumuman</a>
                <i class="fas fa-chevron-right"></i>
                <span>Tambah</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Tambah Pengumuman</h1>
                <p>Kelola pengumuman yang tampil di situs desa.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.announcements.index') }}" class="news-btn news-btn--back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('admin.announcements.index') }}" class="ann-btn ann-btn--cancel">Batal</a>
                <button type="submit" form="announcementForm" class="ann-btn ann-btn--submit">Simpan Pengumuman</button>
            </div>
        </section>

        <section class="news-editor-panel">
            <form id="announcementForm" action="{{ route('admin.announcements.store') }}" method="POST"
                enctype="multipart/form-data" class="news-editor-section">
                @csrf
                @include('admin.announcements.partials.form-fields', ['announcement' => null])
            </form>
        </section>
    </div>
    @include('admin.news.partials.editor-script')
    @push('scripts')
        <script src="{{ asset('assets/js/custom-select.js') }}"></script>
    @endpush
@endsection
