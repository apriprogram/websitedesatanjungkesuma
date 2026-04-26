@extends('admin.layouts.app')

@section('title', 'Kategori Dokumen')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news-modal.css') }}">
    <style>
        .doc-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 220;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }
        .doc-modal.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        .doc-modal__dialog {
            background: #fff;
            border-radius: 20px;
            padding: 1.4rem;
            width: min(520px, 94vw);
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 90px rgba(15, 23, 42, 0.18);
            display: grid;
            gap: 1rem;
            transform: translateY(12px) scale(0.98);
            opacity: 0;
            transition: transform 0.28s cubic-bezier(.2,.75,.4,1), opacity 0.2s ease;
        }
        .doc-modal.is-visible .doc-modal__dialog {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .doc-modal__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
        }
        .doc-modal__title { margin: 0; font-size: 1.2rem; }
        .doc-modal__close {
            border: none;
            background: rgba(15, 23, 42, 0.08);
            border-radius: 999px;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .doc-modal__body { display: grid; gap: 0.8rem; }
        .doc-modal__actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        body.dark-mode .news-table tbody td:first-child {
            color: #ffffff;
            font-weight: 700;
        }
        .cat-action-cell {
            text-align: right !important;
        }
        .cat-actions {
            display: inline-flex;
            justify-content: flex-end;
            gap: 0.4rem;
            width: 100%;
        }
    </style>
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
            <span class="header-title-text">Kategori Dokumen</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('admin.documents.index') }}">Dokumen Desa</a>
            <i class="fas fa-chevron-right"></i>
            <span>Kategori Dokumen</span>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <p class="page-eyebrow">Dokumen & Arsip</p>
            <h1>Kelola Kategori Dokumen</h1>
            <p>Tambahkan atau ubah kategori agar dokumen lebih terstruktur.</p>
        </div>
        <div class="title-actions">
            <button class="soft-action-btn soft-action-btn--violet" type="button" id="openCreateCategory">
                <i class="fas fa-plus"></i>
                <span>Tambah Kategori</span>
            </button>
            <a class="soft-action-btn soft-action-btn--outline" href="{{ route('admin.documents.index') }}">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Dokumen</span>
            </a>
        </div>
    </section>

    <section class="news-panel">
        <div class="news-card-heading">
            <div>
                <h2>Daftar Kategori</h2>
                <p>Gunakan tombol edit/hapus di tiap baris.</p>
            </div>
        </div>
        <div class="news-table category-table">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th class="cat-action-cell" style="width:220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td class="cat-no">{{ $loop->iteration }}</td>
                            <td class="cat-name">{{ $cat->name }}</td>
                            <td class="cat-desc">{{ $cat->description ?: 'Tidak ada deskripsi' }}</td>
                            <td class="cat-actions cat-action-cell">
                                @php
                                    $payload = json_encode([
                                        'id' => $cat->id,
                                        'name' => $cat->name,
                                        'description' => $cat->description,
                                        'update_url' => route('admin.document-categories.update', $cat),
                                        'delete_url' => route('admin.document-categories.destroy', $cat),
                                    ]);
                                @endphp
                                <button class="action-button action-button--neutral" type="button" data-cat-edit='{{ $payload }}' title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="action-button action-button--danger" type="button" data-cat-delete='{{ $payload }}' title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="banner-panel-empty">Belum ada kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Modal: create/edit category --}}
    <div class="doc-modal" id="categoryModal" aria-hidden="true">
        <div class="doc-modal__dialog" role="dialog" aria-modal="true">
            <div class="doc-modal__header">
                <div>
                    <p class="page-eyebrow" style="margin:0;">Kategori</p>
                    <h3 class="doc-modal__title" id="categoryModalTitle">Tambah Kategori</h3>
                </div>
                <button class="doc-modal__close" type="button" data-doc-modal-close><i class="fas fa-times"></i></button>
            </div>
            <form id="categoryForm" class="doc-modal__body" method="post" action="{{ route('admin.document-categories.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="categoryFormMethod">
                <div class="news-form-grid">
                    <div class="news-input-control">
                        <label>Nama Kategori *</label>
                        <input type="text" name="name" id="catName" required>
                    </div>
                    <div class="news-input-control">
                        <label>Deskripsi (opsional)</label>
                        <input type="text" name="description" id="catDesc">
                    </div>
                </div>
                <div class="doc-modal__actions">
                    <button type="button" class="ghost-btn ghost-btn--subtle" data-doc-modal-close><i class="fas fa-arrow-left"></i> Batal</button>
                    <button type="submit" class="soft-action-btn soft-action-btn--success">
                        <i class="fas fa-check"></i>
                        <span id="categorySubmitLabel">Tambah</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: delete category --}}
    <div class="doc-modal" id="deleteCategoryModal" aria-hidden="true">
        <div class="doc-modal__dialog" role="dialog" aria-modal="true">
            <div class="doc-modal__header">
                <div>
                    <p class="page-eyebrow" style="margin:0;">Konfirmasi</p>
                    <h3 class="doc-modal__title">Hapus Kategori?</h3>
                </div>
                <button class="doc-modal__close" type="button" data-doc-modal-close><i class="fas fa-times"></i></button>
            </div>
            <div class="doc-modal__body">
                <p id="deleteCategoryText">Anda yakin ingin menghapus kategori ini?</p>
                <form id="deleteCategoryForm" method="post" action="#">
                    @csrf @method('DELETE')
                    <div class="doc-modal__actions">
                        <button type="button" class="ghost-btn ghost-btn--subtle" data-doc-modal-close><i class="fas fa-arrow-left"></i> Batal</button>
                        <button type="submit" class="soft-action-btn soft-action-btn--danger"><i class="fas fa-trash"></i> Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const catModal = document.getElementById('categoryModal');
    const catDeleteModal = document.getElementById('deleteCategoryModal');
    const catForm = document.getElementById('categoryForm');
    const catMethod = document.getElementById('categoryFormMethod');
    const catTitle = document.getElementById('categoryModalTitle');
    const catSubmitLabel = document.getElementById('categorySubmitLabel');
    const catName = document.getElementById('catName');
    const catDesc = document.getElementById('catDesc');
    const catDeleteText = document.getElementById('deleteCategoryText');
    const catDeleteForm = document.getElementById('deleteCategoryForm');

    const openModal = (modal) => modal?.classList.add('is-visible');
    const closeModal = (modal) => modal?.classList.remove('is-visible');

    document.querySelectorAll('[data-doc-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => closeModal(btn.closest('.doc-modal')));
    });
    [catModal, catDeleteModal].forEach(modal => {
        modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(catModal);
            closeModal(catDeleteModal);
        }
    });

    const resetCatForm = () => {
        catForm.action = "{{ route('admin.document-categories.store') }}";
        catMethod.value = 'POST';
        catName.value = '';
        catDesc.value = '';
        catTitle.textContent = 'Tambah Kategori';
        catSubmitLabel.textContent = 'Tambah';
    };

    document.getElementById('openCreateCategory')?.addEventListener('click', () => {
        resetCatForm();
        openModal(catModal);
    });

    document.querySelectorAll('[data-cat-edit]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const payload = JSON.parse(btn.dataset.catEdit || '{}');
            resetCatForm();
            catTitle.textContent = 'Edit Kategori';
            catSubmitLabel.textContent = 'Simpan';
            catForm.action = payload.update_url || catForm.action;
            catMethod.value = 'PUT';
            catName.value = payload.name || '';
            catDesc.value = payload.description || '';
            openModal(catModal);
        });
    });

    document.querySelectorAll('[data-cat-delete]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const payload = JSON.parse(btn.dataset.catDelete || '{}');
            catDeleteText.innerHTML = payload.name
                ? `Anda yakin ingin menghapus kategori <strong>${payload.name}</strong>?`
                : 'Anda yakin ingin menghapus kategori ini?';
            catDeleteForm.action = payload.delete_url || '#';
            openModal(catDeleteModal);
        });
    });
});
</script>
@endpush
