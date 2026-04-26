@extends('admin.layouts.app')

@section('title', 'Edit Berita')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
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
                <span>Edit Berita</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Edit Berita</h1>
                <p>Perbarui konten atau status untuk berita yang sudah ada.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.news.index') }}" class="news-btn news-btn--back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <button type="submit" form="newsFormEdit" class="news-btn news-btn--primary">
                    <i class="fas fa-check"></i>
                    Perbarui Berita
                </button>
            </div>
        </section>
        <section class="news-editor-section">
        <form id="newsFormEdit" action="{{ route('admin.news.update', $news) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="news-editor-form__body">
                        @include('admin.news.partials.form-fields')
                </div>

            </form>
        </section>
    </div>
    @include('admin.news.partials.dropzone-script')
    @include('admin.news.partials.editor-script')
@endsection
