@extends('admin.layouts.app')

@section('title', 'Edit Pengumuman Desa')

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
                <span class="header-title-text">Manajemen Pengumuman</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Publikasi</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.announcements.index') }}">Pengumuman</a>
                <i class="fas fa-chevron-right"></i>
                <span>Edit</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Edit Pengumuman</h1>
                <p>Perbarui detail dan status pengumuman desa.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.announcements.index') }}" class="news-btn news-btn--back">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('admin.announcements.index') }}" class="ghost-btn">Batal</a>
                <button type="submit" form="announcementForm" class="primary-btn">Perbarui Pengumuman</button>
            </div>
        </section>

        <section class="news-editor-panel">
            <form id="announcementForm" action="{{ route('admin.announcements.update', $announcement) }}" method="POST"
                enctype="multipart/form-data" class="news-editor-section">
                @csrf
                @method('PUT')
                @include('admin.announcements.partials.form-fields')
            </form>
        </section>

        @if(($announcement->attachments ?? collect())->count())
            <section class="news-editor-panel" style="margin-top:14px;">
                <div class="news-content-card">
                    <div class="news-card-heading">
                        <h3>Lampiran tersimpan</h3>
                        <p>Lihat atau hapus lampiran yang sudah ada.</p>
                    </div>
                    <div class="news-attachment-list" style="display:flex;flex-direction:column;gap:8px;">
                        @foreach($announcement->attachments as $file)
                            <div class="news-attachment-item"
                                style="display:flex;flex-wrap:wrap;align-items:center;gap:10px; justify-content:space-between; border:1px solid #e2e8f0; border-radius:10px; padding:10px;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <div
                                        style="width:48px;height:48px;display:flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;overflow:hidden;">
                                        @if($file->type === 'image' && $file->url)
                                            <img src="{{ $file->url }}" alt="{{ $file->original_name ?? 'Lampiran' }}"
                                                style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <i class="fas fa-file-alt"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div style="font-weight:600;">{{ $file->original_name ?? basename($file->path) }}</div>
                                        <small>{{ $file->type === 'image' ? 'Gambar' : 'Dokumen' }}</small>
                                    </div>
                                </div>
                                <div style="display:flex;gap:8px;align-items:center;">
                                    <a class="news-btn news-btn--ghost" href="{{ $file->url }}" target="_blank" rel="noopener"
                                        style="padding:6px 10px;">Lihat</a>
                                    <form
                                        action="{{ route('admin.announcements.attachments.destroy', [$announcement->id, $file->id]) }}"
                                        method="POST" onsubmit="return confirm('Hapus lampiran ini?');" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="news-btn news-btn--ghost"
                                            style="color:#dc2626; padding:6px 10px;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
    @include('admin.news.partials.editor-script')
@endsection
