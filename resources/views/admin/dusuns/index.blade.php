@extends('admin.layouts.app')

@section('title', 'Data Dusun')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('assets/js/admin-entities.js') }}" defer></script>
    @if ($errors->any() && old('form_context'))
        <script>
            window.__entityModalToOpen = @json(old('form_context'));
        </script>
    @endif
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('exportDropdownToggleHamlets');
            const menu = document.getElementById('exportDropdownMenuHamlets');
            if (!toggle || !menu) return;

            const openMenu = () => {
                menu.hidden = false;
                requestAnimationFrame(() => menu.classList.add('is-visible'));
                toggle.setAttribute('aria-expanded', 'true');
            };
            const closeMenu = () => {
                menu.classList.remove('is-visible');
                menu.hidden = true;
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', (event) => {
                event.stopPropagation();
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                expanded ? closeMenu() : openMenu();
            });
            menu.addEventListener('click', (event) => event.stopPropagation());
            document.addEventListener('click', closeMenu);
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMenu();
            });
        });
    </script>
@endpush

@section('content')
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
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}" class="breadcrumb-link">Dashboard</a>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span class="breadcrumb-link">Wilayah Desa</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <a href="{{ route('admin.dusuns.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Data Dusun</a>
        </nav>
    </header>

    @include('admin.partials.alerts')

    @php
        $statusOptions = [
            'all' => 'Semua Status',
            'populated' => 'Memiliki Penduduk',
            'empty' => 'Belum Ada Penduduk',
        ];
        $entriesOptions = $entriesOptions ?? [10, 12, 25, 50, 100];
        $modalContext = old('form_context');
    @endphp

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Data Dusun</h1>
            <p>Kelola daftar dusun beserta jumlah RW dan penduduknya.</p>
        </div>
        <div class="title-actions">
            <div class="export-dropdown">
                <button
                    type="button"
                    class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                    id="exportDropdownToggleHamlets"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="exportDropdownMenuHamlets"
                >
                    <i class="fas fa-file-export" aria-hidden="true"></i>
                    <span>Ekspor</span>
                    <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                </button>
                <div class="export-dropdown__menu" id="exportDropdownMenuHamlets" role="menu" hidden>
                    <div class="export-dropdown__header">Pilih Ekspor</div>
                    <div class="export-dropdown__grid">
                        <a
                            href="{{ route('admin.dusuns.export.excel') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
                            <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Excel</strong>
                                <small>Unduh format spreadsheet</small>
                            </div>
                        </a>
                        <a
                            href="{{ route('admin.dusuns.export.pdf') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
                            <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download PDF</strong>
                                <small>Siap cetak (F4)</small>
                            </div>
                        </a>
                        <a
                            href="{{ route('admin.dusuns.export.word') }}"
                            class="export-dropdown__item"
                            role="menuitem"
                        >
                            <span class="export-dropdown__icon export-dropdown__icon--blue" aria-hidden="true">
                                <i class="fas fa-file-word"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Word</strong>
                                <small>Format dokumen rapi</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <button type="button" class="primary-btn" data-modal-open="hamletCreateModal">
                <i class="fas fa-plus"></i>
                <span>Tambah Dusun</span>
            </button>
        </div>
    </section>

    <form method="GET" action="{{ route('admin.dusuns.index') }}" class="resident-data-form" id="hamletFilterForm">
        <article class="resident-panel resident-panel--hamlets">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar Dusun</h2>
                        <p>Kelola data kewilayahan tingkat dusun dan pantau statistik penduduknya.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <select name="status" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key || ($status === '' && $key === 'all'))>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <select id="hamletEntries" name="entries" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($entriesOptions as $option)
                                    <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="hamletSearch"
                                    type="search"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Cari nama dusun..."
                                    aria-label="Cari nama dusun"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <section class="resident-table resident-table--hamlets">
            <header class="resident-table__header">
                <div class="resident-table__cell" style="text-align: left;">Nama Dusun</div>
                <div class="resident-table__cell" style="text-align: center;">Kode Wilayah</div>
                <div class="resident-table__cell resident-table__cell--center" style="text-align: center;">Jumlah RW</div>
                <div class="resident-table__cell resident-table__cell--center" style="text-align: center;">Jumlah Penduduk</div>
                <div class="resident-table__cell resident-table__cell--actions" style="text-align: center;">Aksi</div>
            </header>
            <div class="resident-table__body">
                @forelse ($dusuns as $dusun)
                    <article class="resident-table__row">
                        <div class="resident-table__cell" style="text-align: left;">
                            <strong>{{ $dusun->nama }}</strong>
                        </div>
                        <div class="resident-table__cell" style="text-align: center; justify-content: center;">
                            {{ $dusun->kode ?: '—' }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--center" style="text-align: center; justify-content: center;">
                            {{ number_format($dusun->rws_count) }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--center" style="text-align: center; justify-content: center;">
                            {{ number_format($dusun->penduduks_count) }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--actions" style="text-align: center; justify-content: center;">
                            <div class="resident-table__actions">
                                <button
                                    type="button"
                                    class="action-button action-button--neutral"
                                    data-modal-open="hamletDetailModal-{{ $dusun->id }}"
                                    aria-label="Detail dusun {{ $dusun->nama }}"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--primary"
                                    data-modal-open="hamletEditModal-{{ $dusun->id }}"
                                    aria-label="Edit dusun {{ $dusun->nama }}"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--danger"
                                    data-modal-open="hamletDeleteModal-{{ $dusun->id }}"
                                    aria-label="Hapus dusun {{ $dusun->nama }}"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="resident-table__empty">
                        <i class="fas fa-map"></i>
                        <p>Belum ada data dusun yang tercatat.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="resident-panel__footer">
            <span>
                Menampilkan {{ $dusuns->firstItem() ?? 0 }} - {{ $dusuns->lastItem() ?? 0 }} dari {{ $dusuns->total() }} dusun
            </span>
            <div class="resident-pagination__links">
                {{ $dusuns->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>
</form>

    {{-- Modal: Tambah --}}
    <div class="dialog-backdrop" id="hamletCreateModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="hamletCreateTitle">
            <header class="dialog__header">
                <h2 id="hamletCreateTitle">Tambah Dusun</h2>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form tambah dusun">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.dusuns.store') }}" class="dialog__form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                <input type="hidden" name="form_context" value="hamletCreateModal">
                <div class="dialog__body">
                    <div class="settings-form-grid">
                        <label class="form-field @if($modalContext === 'hamletCreateModal' && $errors->has('nama')) form-field--error @endif">
                            <span>Nama Dusun <sup>*</sup></span>
                            <input type="text" name="nama" value="{{ $modalContext === 'hamletCreateModal' ? old('nama') : '' }}" maxlength="100" required>
                            @if ($modalContext === 'hamletCreateModal')
                                @error('nama')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field">
                            <span>Kode Dusun</span>
                            <input type="text" name="kode" value="{{ $modalContext === 'hamletCreateModal' ? old('kode') : '' }}" maxlength="20">
                        </label>
                    </div>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-save"></i>
                        <span>Simpan</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Modal: Detail, Edit, Delete --}}
    @foreach ($dusuns as $dusun)
        @php
            $editContext = $modalContext === 'hamletEditModal-' . $dusun->id;
        @endphp
        <div class="dialog-backdrop" id="hamletDetailModal-{{ $dusun->id }}" aria-hidden="true">
            <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="hamletDetailTitle-{{ $dusun->id }}">
                <header class="dialog__header">
                    <h2 id="hamletDetailTitle-{{ $dusun->id }}">Detail Dusun {{ $dusun->nama }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail dusun {{ $dusun->nama }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Nama Dusun</dt>
                            <dd>{{ $dusun->nama }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Kode Dusun</dt>
                            <dd>{{ $dusun->kode ?: 'Tidak ada kode' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Jumlah RW</dt>
                            <dd>{{ number_format($dusun->rws_count) }} RW</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Jumlah Penduduk</dt>
                            <dd>{{ number_format($dusun->penduduks_count) }} penduduk</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Diperbarui</dt>
                            <dd>{{ $dusun->updated_at?->translatedFormat('d M Y, H:i') ?? 'Belum tersedia' }}</dd>
                        </div>
                    </dl>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Tutup</button>
                </footer>
            </div>
        </div>

        <div class="dialog-backdrop" id="hamletEditModal-{{ $dusun->id }}" aria-hidden="true">
            <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="hamletEditTitle-{{ $dusun->id }}">
                <header class="dialog__header">
                    <h2 id="hamletEditTitle-{{ $dusun->id }}">Edit Dusun {{ $dusun->nama }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form edit dusun {{ $dusun->nama }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.dusuns.update', $dusun) }}" class="dialog__form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="form_context" value="hamletEditModal-{{ $dusun->id }}">
                    <div class="dialog__body">
                        <div class="settings-form-grid">
                            <label class="form-field @if($editContext && $errors->has('nama')) form-field--error @endif">
                                <span>Nama Dusun <sup>*</sup></span>
                                <input type="text" name="nama" value="{{ $editContext ? old('nama', $dusun->nama) : $dusun->nama }}" maxlength="100" required>
                                @if ($editContext)
                                    @error('nama')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field">
                                <span>Kode Dusun</span>
                                <input type="text" name="kode" value="{{ $editContext ? old('kode', $dusun->kode) : $dusun->kode }}" maxlength="20">
                            </label>
                        </div>
                    </div>
                    <footer class="dialog__footer">
                        <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                        <button type="submit" class="primary-btn">
                            <i class="fas fa-save"></i>
                            <span>Perbarui</span>
                        </button>
                    </footer>
                </form>
            </div>
        </div>

        <div class="dialog-backdrop" id="hamletDeleteModal-{{ $dusun->id }}" aria-hidden="true">
            <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="hamletDeleteTitle-{{ $dusun->id }}">
                <header class="dialog__header">
                    <h2 id="hamletDeleteTitle-{{ $dusun->id }}">Hapus Dusun {{ $dusun->nama }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus {{ $dusun->nama }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <p>Anda yakin ingin menghapus dusun <strong>{{ $dusun->nama }}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <footer class="dialog__footer dialog__footer--split">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <form method="POST" action="{{ route('admin.dusuns.destroy', $dusun) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                        <input type="hidden" name="form_context" value="hamletDeleteModal-{{ $dusun->id }}">
                        <button type="submit" class="danger-btn">
                            <i class="fas fa-trash"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </footer>
            </div>
        </div>
    @endforeach
@endsection
