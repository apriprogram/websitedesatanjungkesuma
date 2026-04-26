@extends('admin.layouts.app')

@section('title', 'Data RT')

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
            const toggle = document.getElementById('exportDropdownToggleRt');
            const menu = document.getElementById('exportDropdownMenuRt');
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
            <a href="{{ route('admin.rts.index') }}" class="breadcrumb-link breadcrumb-link--active" aria-current="page">Data RT</a>
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
            <h1>Data RT</h1>
            <p>Kelola daftar RT, hubungan RW dan dusun, serta data penduduknya.</p>
        </div>
        <div class="title-actions">
            <div class="export-dropdown">
                <button
                    type="button"
                    class="soft-action-btn soft-action-btn--outline export-dropdown__toggle"
                    id="exportDropdownToggleRt"
                    aria-haspopup="true"
                    aria-expanded="false"
                    aria-controls="exportDropdownMenuRt"
                >
                    <i class="fas fa-file-export" aria-hidden="true"></i>
                    <span>Ekspor</span>
                    <i class="fas fa-chevron-down export-dropdown__chevron" aria-hidden="true"></i>
                </button>
                <div class="export-dropdown__menu" id="exportDropdownMenuRt" role="menu" hidden>
                    <div class="export-dropdown__header">Pilih Ekspor</div>
                    <div class="export-dropdown__grid">
                        <a href="{{ route('admin.rts.export.excel') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--green" aria-hidden="true">
                                <i class="fas fa-file-excel"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download Excel</strong>
                                <small>Unduh format spreadsheet</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.rts.export.pdf') }}" class="export-dropdown__item" role="menuitem">
                            <span class="export-dropdown__icon export-dropdown__icon--rose" aria-hidden="true">
                                <i class="fas fa-file-pdf"></i>
                            </span>
                            <div class="export-dropdown__meta">
                                <strong>Download PDF</strong>
                                <small>Siap cetak (F4)</small>
                            </div>
                        </a>
                        <a href="{{ route('admin.rts.export.word') }}" class="export-dropdown__item" role="menuitem">
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
            <button type="button" class="primary-btn" data-modal-open="rtCreateModal">
                <i class="fas fa-plus"></i>
                <span>Tambah RT</span>
            </button>
        </div>
    </section>

    <form method="GET" action="{{ route('admin.rts.index') }}" class="resident-data-form" id="rtFilterForm">
        <article class="resident-panel resident-panel--rts">
            <header class="panel-header panel-header--table agenda-panel__header">
                <div class="resident-panel__title-row resident-panel__title-row--inline agenda-panel__title-row">
                    <div class="resident-panel__title">
                        <h2>Daftar RT</h2>
                        <p>Kelola data kewilayahan tingkat RT dan pantau statistik penduduknya.</p>
                    </div>
                    <div class="panel-toolbar-inline">
                        <div class="panel-filters">
                            <select name="dusun_id" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                <option value="">Semua Dusun</option>
                                @foreach ($dusuns as $dusun)
                                    <option value="{{ $dusun->id }}" @selected($dusunId === $dusun->id)>{{ $dusun->nama }}</option>
                                @endforeach
                            </select>
                            <select name="rw_id" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                <option value="">Semua RW</option>
                                @foreach ($rws as $rwOption)
                                    <option value="{{ $rwOption->id }}" @selected($rwId === $rwOption->id)>
                                        RW {{ $rwOption->nomor }} ({{ $rwOption->dusun?->nama ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                            <select name="status" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key || ($status === '' && $key === 'all'))>{{ $label }}</option>
                                @endforeach
                            </select>
                            <select id="rtEntries" name="entries" onchange="this.form.submit()" class="form-select-sm panel-filter-select">
                                @foreach ($entriesOptions as $option)
                                    <option value="{{ $option }}" @selected($entries == $option)>{{ $option }} Baris</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="search-input-group">
                            <div class="search-input-wrapper">
                                <i class="fas fa-search search-icon-left"></i>
                                <input
                                    id="rtSearch"
                                    type="search"
                                    name="search"
                                    value="{{ $search }}"
                                    placeholder="Cari nomor RT..."
                                    aria-label="Cari nomor RT"
                                    autocomplete="off"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <section class="resident-table resident-table--rts">
                <header class="resident-table__header">
                    <div class="resident-table__cell" style="text-align: center;">Nomor RT</div>
                    <div class="resident-table__cell" style="text-align: center;">RW Induk</div>
                    <div class="resident-table__cell" style="text-align: left;">Dusun</div>
                    <div class="resident-table__cell resident-table__cell--center" style="text-align: center;">Jumlah Penduduk</div>
                    <div class="resident-table__cell resident-table__cell--actions" style="text-align: center;">Aksi</div>
                </header>
                <div class="resident-table__body">
                    @forelse ($rts as $rt)
                        <article class="resident-table__row">
                        <div class="resident-table__cell" style="text-align: center; justify-content: center;">
                            <strong>RT {{ $rt->nomor }}</strong>
                            <p class="table-cell__meta">Kode {{ $rt->kode ?: '—' }}</p>
                        </div>
                        <div class="resident-table__cell" style="text-align: center; justify-content: center;">
                            RW {{ $rt->rw?->nomor ?? '—' }}
                        </div>
                        <div class="resident-table__cell" style="text-align: left;">
                            {{ $rt->rw?->dusun?->nama ?? '—' }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--center" style="text-align: center; justify-content: center;">
                            {{ number_format($rt->penduduks_count) }}
                        </div>
                        <div class="resident-table__cell resident-table__cell--actions" style="text-align: center; justify-content: center;">
                            <div class="resident-table__actions">
                                <button
                                    type="button"
                                    class="action-button action-button--neutral"
                                    data-modal-open="rtDetailModal-{{ $rt->id }}"
                                    aria-label="Detail RT {{ $rt->nomor }}"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--primary"
                                    data-modal-open="rtEditModal-{{ $rt->id }}"
                                    aria-label="Edit RT {{ $rt->nomor }}"
                                >
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button
                                    type="button"
                                    class="action-button action-button--danger"
                                    data-modal-open="rtDeleteModal-{{ $rt->id }}"
                                    aria-label="Hapus RT {{ $rt->nomor }}"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="resident-table__empty">
                        <i class="fas fa-user-group"></i>
                        <p>Belum ada data RT.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="resident-panel__footer">
            <span>
                Menampilkan {{ $rts->firstItem() ?? 0 }} - {{ $rts->lastItem() ?? 0 }} dari {{ $rts->total() }} RT
            </span>
            <div class="resident-pagination__links">
                {{ $rts->onEachSide(1)->withQueryString()->links('admin.partials.pagination') }}
            </div>
        </div>
    </article>
</form>

    <div class="dialog-backdrop" id="rtCreateModal" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="rtCreateTitle">
            <header class="dialog__header">
                <h2 id="rtCreateTitle">Tambah RT</h2>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form tambah RT">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <form method="POST" action="{{ route('admin.rts.store') }}" class="dialog__form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                <input type="hidden" name="form_context" value="rtCreateModal">
                <div class="dialog__body">
                    <div class="settings-form-grid">
                        <label class="form-field @if($modalContext === 'rtCreateModal' && $errors->has('rw_id')) form-field--error @endif">
                            <span>RW <sup>*</sup></span>
                            <select name="rw_id" required>
                                <option value="">Pilih RW</option>
                                @foreach ($rws as $rwOption)
                                    <option value="{{ $rwOption->id }}" @selected($modalContext === 'rtCreateModal' && old('rw_id') == $rwOption->id)>
                                        Dusun {{ $rwOption->dusun?->nama ?? '-' }} &mdash; RW {{ $rwOption->nomor }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($modalContext === 'rtCreateModal')
                                @error('rw_id')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field @if($modalContext === 'rtCreateModal' && $errors->has('nomor')) form-field--error @endif">
                            <span>Nomor RT <sup>*</sup></span>
                            <input type="text" name="nomor" value="{{ $modalContext === 'rtCreateModal' ? old('nomor') : '' }}" maxlength="10" required>
                            @if ($modalContext === 'rtCreateModal')
                                @error('nomor')
                                    <span class="form-error">{{ $message }}</span>
                                @enderror
                            @endif
                        </label>
                        <label class="form-field">
                            <span>Kode RT</span>
                            <input type="text" name="kode" value="{{ $modalContext === 'rtCreateModal' ? old('kode') : '' }}" maxlength="20">
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
    @foreach ($rts as $rt)
        @php
            $editContext = $modalContext === 'rtEditModal-' . $rt->id;
        @endphp
        <div class="dialog-backdrop" id="rtDetailModal-{{ $rt->id }}" aria-hidden="true">
            <div class="dialog dialog--detail" role="dialog" aria-modal="true" aria-labelledby="rtDetailTitle-{{ $rt->id }}">
                <header class="dialog__header">
                    <h2 id="rtDetailTitle-{{ $rt->id }}">Detail RT {{ $rt->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail RT {{ $rt->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <dl class="detail-list">
                        <div class="detail-list__item">
                            <dt>Nomor RT</dt>
                            <dd>{{ $rt->nomor }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>RW</dt>
                            <dd>RW {{ $rt->rw?->nomor ?? '-' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Dusun</dt>
                            <dd>{{ $rt->rw?->dusun?->nama ?? '-' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Kode RT</dt>
                            <dd>{{ $rt->kode ?: 'Tidak ada kode' }}</dd>
                        </div>
                        <div class="detail-list__item">
                            <dt>Jumlah Penduduk</dt>
                            <dd>{{ number_format($rt->penduduks_count) }} penduduk</dd>
                        </div>
                    </dl>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Tutup</button>
                </footer>
            </div>
        </div>

        <div class="dialog-backdrop" id="rtEditModal-{{ $rt->id }}" aria-hidden="true">
            <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="rtEditTitle-{{ $rt->id }}">
                <header class="dialog__header">
                    <h2 id="rtEditTitle-{{ $rt->id }}">Edit RT {{ $rt->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form edit RT {{ $rt->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <form method="POST" action="{{ route('admin.rts.update', $rt) }}" class="dialog__form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <input type="hidden" name="form_context" value="rtEditModal-{{ $rt->id }}">
                    <div class="dialog__body">
                        <div class="settings-form-grid">
                            <label class="form-field @if($editContext && $errors->has('rw_id')) form-field--error @endif">
                                <span>RW <sup>*</sup></span>
                                <select name="rw_id" required>
                                    <option value="">Pilih RW</option>
                                    @foreach ($rws as $rwOption)
                                        <option value="{{ $rwOption->id }}" @selected(($editContext ? old('rw_id', $rt->rw_id) : $rt->rw_id) == $rwOption->id)>
                                            Dusun {{ $rwOption->dusun?->nama ?? '-' }} &mdash; RW {{ $rwOption->nomor }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($editContext)
                                    @error('rw_id')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field @if($editContext && $errors->has('nomor')) form-field--error @endif">
                                <span>Nomor RT <sup>*</sup></span>
                                <input type="text" name="nomor" value="{{ $editContext ? old('nomor', $rt->nomor) : $rt->nomor }}" maxlength="10" required>
                                @if ($editContext)
                                    @error('nomor')
                                        <span class="form-error">{{ $message }}</span>
                                    @enderror
                                @endif
                            </label>
                            <label class="form-field">
                                <span>Kode RT</span>
                                <input type="text" name="kode" value="{{ $editContext ? old('kode', $rt->kode) : $rt->kode }}" maxlength="20">
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

        <div class="dialog-backdrop" id="rtDeleteModal-{{ $rt->id }}" aria-hidden="true">
            <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="rtDeleteTitle-{{ $rt->id }}">
                <header class="dialog__header">
                    <h2 id="rtDeleteTitle-{{ $rt->id }}">Hapus RT {{ $rt->nomor }}</h2>
                    <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi hapus RT {{ $rt->nomor }}">
                        <i class="fas fa-times"></i>
                    </button>
                </header>
                <div class="dialog__body">
                    <p>Anda yakin ingin menghapus RT <strong>{{ $rt->nomor }}</strong> pada RW {{ $rt->rw?->nomor ?? '-' }}?</p>
                </div>
                <footer class="dialog__footer dialog__footer--split">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-modal-close>Batal</button>
                    <form method="POST" action="{{ route('admin.rts.destroy', $rt) }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                        <input type="hidden" name="form_context" value="rtDeleteModal-{{ $rt->id }}">
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
