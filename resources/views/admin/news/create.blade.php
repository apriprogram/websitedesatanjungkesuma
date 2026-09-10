@extends('admin.layouts.app')

@section('title', 'Tambah Berita')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom-select.css') }}">
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
                <span class="header-title-text">Manajemen Berita</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Publikasi</span>
                <i class="fas fa-chevron-right"></i>
                <span>Tambah Berita</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Tambah Berita</h1>
                <p>Isi form berikut untuk menambahkan berita desa baru.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.news.index') }}" class="news-btn news-btn--back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('admin.news.index') }}" class="ann-btn ann-btn--cancel">Batal</a>
                <button type="submit" form="newsForm" class="ann-btn ann-btn--submit">
                    Simpan Berita
                </button>
            </div>
        </section>
        <section class="news-editor-section">
        <form id="newsForm" action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
                <div class="news-editor-form__body">
                    @include('admin.news.partials.form-fields')
                </div>
            </form>
        </section>
    </div>
    @include('admin.news.partials.dropzone-script')
    @include('admin.news.partials.editor-script')
    
    <script src="{{ asset('assets/js/custom-select.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            CustomSelect.init();
        });
    </script>
@endsection
