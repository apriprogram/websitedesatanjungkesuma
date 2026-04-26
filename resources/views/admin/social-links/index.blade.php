@extends('admin.layouts.app')

@section('title', 'Pengaturan Media Sosial')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-references.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-social.css') }}">
    <style>
        .social-settings__remove { display: flex; justify-content: flex-end; }
        .social-settings__item { position: relative; }
    </style>
@endpush

@section('content')
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
            <span class="header-title-text">Manajemen Media Sosial</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Dashboard</span>
            <i class="fas fa-chevron-right"></i>
            <span>Pengaturan Media Sosial</span>
        </nav>
    </header>

    <section class="page-title">
        <div>
            <h1>Media Sosial Desa</h1>
            <p>Masukkan tautan media sosial yang aktif, sisanya tidak ditampilkan.</p>
        </div>
    </section>

    <div class="card social-settings">
        <form class="social-settings__form" action="{{ route('admin.social-links.update') }}" method="POST" id="socialLinksForm">
            @csrf
            @method('PUT')
            <div class="page-title" style="padding: 0 0 8px 0;">
                <div>
                    <h2 style="margin:0;font-size:1.2rem;">Media Sosial (ikon)</h2>
                    <p style="margin:4px 0 0;">Isi URL untuk ikon media sosial di footer.</p>
                </div>
            </div>
            <div id="socialLinksGrid" class="social-settings__grid">
                @foreach ($iconLinks as $link)
                    <label class="social-settings__item">
                        <div class="social-settings__meta">
                            <span class="social-settings__icon">
                                <i class="{{ $link->icon }}"></i>
                            </span>
                            <div>
                                <h3>{{ $link->name }}</h3>
                                <small>{{ $link->slug }}</small>
                            </div>
                        </div>
                        <input class="social-settings__input" type="url"
                               name="links[{{ $link->slug }}][url]"
                               value="{{ old("links.{$link->slug}.url", $link instanceof \App\Models\SocialLink ? $link->url : '') }}"
                               placeholder="https://">
                    </label>
                @endforeach
            </div>
            <div class="social-settings__actions">
                <button type="submit" class="btn btn--primary">Simpan perubahan</button>
            </div>
        </form>
    </div>

    <div class="card social-settings" style="margin-top:12px;">
        <div class="page-title" style="padding: 0 0 8px 0;">
            <div>
                <h2 style="margin:0;font-size:1.2rem;">Tautan Tambahan (teks, tanpa ikon)</h2>
                <p style="margin:4px 0 0;">Tautan ini akan muncul di bawah ikon media sosial.</p>
            </div>
        </div>
        <form class="social-settings__form" action="{{ route('admin.social-links.update') }}" method="POST" id="textLinksForm">
            @csrf
            @method('PUT')
            <div id="textLinksGrid" class="social-settings__grid">
                @php $oldText = old('text_links', []); @endphp
                @forelse($oldText ?: $textLinks as $idx => $link)
                    @php
                        $name = $oldText ? ($link['name'] ?? '') : $link->name;
                        $url  = $oldText ? ($link['url'] ?? '') : ($link->url ?? '');
                    @endphp
                    <label class="social-settings__item" data-index="{{ $loop->index }}">
                        <div class="social-settings__meta">
                            <span class="social-settings__icon">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <div>
                                <h3>
                                    <input class="social-settings__input" type="text"
                                           name="text_links[{{ $loop->index }}][name]"
                                           value="{{ $name }}"
                                           placeholder="Judul link"
                                           style="padding:6px 10px; width:100%; font-size:0.95rem;">
                                </h3>
                            </div>
                        </div>
                        <input class="social-settings__input" type="url"
                               name="text_links[{{ $loop->index }}][url]"
                               value="{{ $url }}"
                               placeholder="https://">
                        <div class="social-settings__remove">
                            <button type="button" class="btn btn--ghost remove-text-row">Hapus</button>
                        </div>
                    </label>
                @empty
                    <label class="social-settings__item" data-index="0">
                        <div class="social-settings__meta">
                            <span class="social-settings__icon">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <div>
                                <h3>
                                    <input class="social-settings__input" type="text"
                                           name="text_links[0][name]"
                                           value=""
                                           placeholder="Judul link"
                                           style="padding:6px 10px; width:100%; font-size:0.95rem;">
                                </h3>
                            </div>
                        </div>
                        <input class="social-settings__input" type="url"
                               name="text_links[0][url]"
                               value=""
                               placeholder="https://">
                        <div class="social-settings__remove">
                            <button type="button" class="btn btn--ghost remove-text-row">Hapus</button>
                        </div>
                    </label>
                @endforelse
            </div>
            <div class="social-settings__actions">
                <button type="button" class="btn btn--ghost" id="addTextRow" style="margin-right:12px;"><i class="fas fa-plus"></i> Tambah Link</button>
                <button type="submit" class="btn btn--primary" style="margin-left:12px;">Simpan perubahan</button>
            </div>
        </form>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
@push('scripts')
<script>
(function() {
    const grid = document.getElementById('textLinksGrid');
    const addBtn = document.getElementById('addTextRow');
    if (!grid || !addBtn) return;

    const buildRow = (index) => {
        const label = document.createElement('label');
        label.className = 'social-settings__item';
        label.dataset.index = index;
        label.innerHTML = `
            <div class="social-settings__meta">
                <span class="social-settings__icon">
                    <i class="fa-solid fa-link"></i>
                </span>
                <div>
                    <h3>
                        <input class="social-settings__input" type="text"
                            name="text_links[${index}][name]"
                            value=""
                            placeholder="Judul link"
                            style="padding:6px 10px; width:100%; font-size:0.95rem;">
                    </h3>
                </div>
            </div>
            <input class="social-settings__input" type="url"
                name="text_links[${index}][url]"
                value=""
                placeholder="https://">
            <div class="social-settings__remove">
                <button type="button" class="btn btn--ghost remove-text-row">Hapus</button>
            </div>
        `;
        return label;
    };

    const renumber = () => {
        grid.querySelectorAll('.social-settings__item').forEach((row, idx) => {
            row.dataset.index = idx;
            const nameInput = row.querySelector('input[name*=\"text_links\"][type=\"text\"]');
            const urlInput = row.querySelector('input[name*=\"text_links\"][type=\"url\"]');
            if (nameInput) nameInput.name = `text_links[${idx}][name]`;
            if (urlInput) urlInput.name = `text_links[${idx}][url]`;
        });
    };

    addBtn.addEventListener('click', () => {
        const next = grid.querySelectorAll('.social-settings__item').length;
        grid.appendChild(buildRow(next));
    });

    grid.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-text-row')) {
            const row = e.target.closest('.social-settings__item');
            if (row) {
                row.remove();
                renumber();
            }
        }
    });
})();
</script>
@endpush
@endsection
