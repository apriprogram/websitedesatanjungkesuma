@extends('admin.layouts.app')

@section('title', 'Data RW')

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
            const toggle = document.getElementById('exportDropdownToggleRw');
            const menu = document.getElementById('exportDropdownMenuRw');
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
            <a href="{{ route('admin.rws.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Data RW</a>
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
            <h1>Data RW</h1>
            <p>Kelola daftar RW beserta dusun induk dan data penduduk.</p>
        </div>
        <div class="title-actions">
            <div class="export-dropdown">
                <button
                    type="button"
                    class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                    id="exportDropdownToggleRw"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="exportDropdownMenuRw"
                >
                    <i class="fas fa-file-export" aria-hidden="true"></i>
                    <span>Ekspor</span>
                    <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                </button>
                <div class="export-dropdown__menu" id="exportDropdownMenuRw" role="menu" hidden>
                    <div class="export-dropdown__header">Pilih Ekspor</div>
                    <div class="export-dropdown__grid">
                        <a href="{{ route('admin.rws.export.excel') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Excel</strong>
                                <small>Unduh format spreadsheet</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.rws.export.pdf') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download PDF</strong>
                                <small>Siap cetak (F4)</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.rws.export.word') }}" class="export-dropdown__item" role="menuitem">
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
            <button type="button" class="primary-btn" data-modal-open="rwCreateModal">
                <i class="fas fa-plus"></i>
                <span>Tambah RW</span>
            </button>
        </div>
    </section>

    <form method="GET" action="{{ route('admin.rws.index') }}" class="resident-data-form" id="rwFilterForm">
        <article class="resident-panel resident-panel--rws">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar RW</h2>
                        <p>Kelola data kewilayahan tingkat RW dan pantau statistik penduduknya.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <select name="dusun_id" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                <option value="">Semua Dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected($dusunId === $dusun->id)>{{ $dusun->nama }}</option>
                                @endforeach
                            </select>
                            <select name="status" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key || ($status === '' && $key === 'all'))>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select id="rwEntries" name="entries" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($entriesOptions as $option)
                                    <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="rwSearch"
                                    type="search"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Cari nomor RW..."
                                    aria-label="Cari nomor RW"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <section class="resident-table resident-table--rws">
            <header class="resident-table__header">
                <div class="resident-table__cell" style="text-align: center;">Nomor RW</div>
                <div class="resident-table__cell" style="text-align: left;">Dusun Induk</div>
                <div class="resident-table__cell resident-table__cell--center" style="text-align: center;">Jumlah RT</div>
                <div class="resident-table__cell resident-table__cell--center" style="text-align: center;">Jumlah Penduduk</div>
                <div class="resident-table__cell resident-table__cell--actions" style="text-align: center;">Aksi</div>
            </header>
            <div class="resident-table__body">
                @forelse ($rws as $rw)
                    <article class="resident-table__row">
                        <div class="resident-table__cell" style="text-align: center; justify-content: center;">
                            <strong>RW {{ $rw->nomor }}</strong>
                            <p class="table-cell__meta">Kode {{ $rw->kode ?: '—' }}</p>
                        </div>
                        <div class="resident-table__cell" style="text-align: left;">
                            {{ $rw->dusun?->nama ?? '—' }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--center" style="text-align: center; justify-content: center;">
                            {{ number_format($rw->rts_count) }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--center" style="text-align: center; justify-content: center;">
                            {{ number_format($rw->penduduks_count) }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--actions" style="text-align: center; justify-content: center;">
                            <div class="resident-table__actions">
                                <button
                                    type="button"
                                    class="action-button action-button--neutral"
                                    data-modal-open="rwDetailModal-{{ $rw->id }}"
                                    aria-label="Detail RW {{ $rw->nomor }}"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--primary"
                                    data-modal-open="rwEditModal-{{ $rw->id }}"
                                    aria-label="Edit RW {{ $rw->nomor }}"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--danger"
                                    data-modal-open="rwDeleteModal-{{ $rw->id }}"
                                    aria-label="Hapus RW {{ $rw->nomor }}"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="resident-table__empty">
                        <i class="fas fa-diagram-project"></i>
                        <p>Belum ada data RW.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="resident-panel__footer">
            <span>
                Menampilkan {{ $rws->firstItem() ?? 0 }} - {{ $rws->lastItem() ?? 0 }} dari {{ $rws->total() }} RW
            </span>
            <div class="resident-pagination__links">
                {{ $rws->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>
</form>

    <div class="dialog-backdrop" id="rwCreateModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="rwCreateTitle">
            <header class="dialog__header">
                <h2 id="rwCreateTitle">Tambah RW</h2>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form tambah RW">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.rws.store') }}" class="dialog__form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                <input type="hidden" name="form_context" value="rwCreateModal">
                <div class="dialog__body">
                    <div class="settings-form-grid">
                        <label class="form-field @if($modalContext === 'rwCreateModal' && $errors->has('dusun_id')) form-field--error @endif">
                            <span>Dusun <sup>*</sup></span>
                            <select name="dusun_id" required>
                                <option value="">Pilih dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected($modalContext === 'rwCreateModal' && old('dusun_id') == $dusun->id)>
                                        {{ $dusun->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($modalContext === 'rwCreateModal')
                                @error('dusun_id')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field @if($modalContext === 'rwCreateModal' && $errors->has('nomor')) form-field--error @endif">
                            <span>Nomor RW <sup>*</sup></span>
                            <input type="text" name="nomor" value="{{ $modalContext === 'rwCreateModal' ? old('nomor') : '' }}" maxlength="10" required>
                            @if ($modalContext === 'rwCreateModal')
                                @error('nomor')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field">
                            <span>Kode RW</span>
                            <input type="text" name="kode" value="{{ $modalContext === 'rwCreateModal' ? old('kode') : '' }}" maxlength="20">
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
    @foreach ($rws as $rw)
        @php
            $editContext = $modalContext === 'rwEditModal-' . $rw->id;
        @endphp
        <div class="dialog-backdrop" id="rwDetailModal-{{ $rw->id }}" aria-hidden="true">
            <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="rwDetailTitle-{{ $rw->id }}">
                <header class="dialog__header">
                    <h2 id="rwDetailTitle-{{ $rw->id }}">Detail RW {{ $rw->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail RW {{ $rw->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Nomor RW</dt>
                            <dd>{{ $rw->nomor }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Dusun</dt>
                            <dd>{{ $rw->dusun?->nama ?? '-' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Kode RW</dt>
                            <dd>{{ $rw->kode ?: 'Tidak ada kode' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Jumlah RT</dt>
                            <dd>{{ number_format($rw->rts_count) }} RT</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Jumlah Penduduk</dt>
                            <dd>{{ number_format($rw->penduduks_count) }} penduduk</dd>
                        </div>
                    </dl>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Tutup</button>
                </footer>
            </div>
        </div>

        <div class="dialog-backdrop" id="rwEditModal-{{ $rw->id }}" aria-hidden="true">
            <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="rwEditTitle-{{ $rw->id }}">
                <header class="dialog__header">
                    <h2 id="rwEditTitle-{{ $rw->id }}">Edit RW {{ $rw->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form edit RW {{ $rw->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.rws.update', $rw) }}" class="dialog__form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="form_context" value="rwEditModal-{{ $rw->id }}">
                    <div class="dialog__body">
                        <div class="settings-form-grid">
                            <label class="form-field @if($editContext && $errors->has('dusun_id')) form-field--error @endif">
                                <span>Dusun <sup>*</sup></span>
                                <select name="dusun_id" required>
                                    <option value="">Pilih dusun</option>
                                    @foreach ($dusuns as $dusun)
                                        <option value="{{ $dusun->id }}" @selected(($editContext ? old('dusun_id', $rw->dusun_id) : $rw->dusun_id) == $dusun->id)>
                                            {{ $dusun->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($editContext)
                                    @error('dusun_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field @if($editContext && $errors->has('nomor')) form-field--error @endif">
                                <span>Nomor RW <sup>*</sup></span>
                                <input type="text" name="nomor" value="{{ $editContext ? old('nomor', $rw->nomor) : $rw->nomor }}" maxlength="10" required>
                                @if ($editContext)
                                    @error('nomor')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field">
                                <span>Kode RW</span>
                                <input type="text" name="kode" value="{{ $editContext ? old('kode', $rw->kode) : $rw->kode }}" maxlength="20">
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

        <div class="dialog-backdrop" id="rwDeleteModal-{{ $rw->id }}" aria-hidden="true">
            <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="rwDeleteTitle-{{ $rw->id }}">
                <header class="dialog__header">
                    <h2 id="rwDeleteTitle-{{ $rw->id }}">Hapus RW {{ $rw->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus RW {{ $rw->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <p>Anda yakin ingin menghapus RW <strong>{{ $rw->nomor }}</strong> pada dusun {{ $rw->dusun?->nama ?? '-' }}?</p>
                </div>
                <footer class="dialog__footer dialog__footer--split">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <form method="POST" action="{{ route('admin.rws.destroy', $rw) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                        <input type="hidden" name="form_context" value="rwDeleteModal-{{ $rw->id }}">
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
