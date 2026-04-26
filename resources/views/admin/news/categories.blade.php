@extends('admin.layouts.app')

@section('title', 'Kategori Berita')

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
                <span class="header-title-text">Manajemen Kategori</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Publikasi</span>
                <i class="fas fa-chevron-right"></i>
                <span>Kategori</span>
            </nav>
        </header>

        <section class="page-title page-title--with-actions">
            <div>
                <h1>Kategori Berita</h1>
                <p>Kelola kategori yang digunakan pada halaman berita agar setiap konten berada di klasifikasi yang tepat.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.news.index') }}" class="ghost-btn">Kembali ke Berita</a>
            </div>
        </section>

        <section class="categories-grid">
            <div class="category-form-card">
                <header>
                    <h2>Tambah kategori</h2>
                    <p>Nama kategori ditampilkan pada tab bar dan filter utama.</p>
                </header>
                <form method="POST" action="{{ route('admin.news.categories.store') }}" class="category-form">
                    @csrf
                    <label>
                        <span>Nama kategori</span>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </label>
                    <label>
                        <span>Slug (opsional)</span>
                        <input type="text" name="slug" value="{{ old('slug') }}">
                    </label>
                    <button type="submit" class="primary-btn">Simpan kategori</button>
                </form>
            </div>

            <div class="category-table-card">
                <header>
                    <h2>Daftar kategori</h2>
                    <p>Total {{ $categories->count() }} kategori terdaftar.</p>
                </header>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}
                                        <form id="category-update-{{ $category->id }}" action="{{ route('admin.news.categories.update', $category) }}" method="POST" class="hidden-form">
                                            @csrf
                                            @method('PUT')
                                        </form>
                                    </td>
                                    <td>
                                        <input form="category-update-{{ $category->id }}" type="text" name="name" value="{{ $category->name }}" required>
                                    </td>
                                    <td>
                                        <input form="category-update-{{ $category->id }}" type="text" name="slug" value="{{ $category->slug }}">
                                    </td>
                                    <td class="actions-cell">
                                        <button form="category-update-{{ $category->id }}" type="submit" class="soft-action-btn">Perbarui</button>
                                        <form action="{{ route('admin.news.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-btn">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

