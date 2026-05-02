@extends('admin.layouts.app')

@section('title', $page->exists ? 'Edit Halaman' : 'Tambah Halaman')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-news.css') }}">
    <style>
        /* Drag and Drop Redesign */
        .file-drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 3rem 2rem;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s ease;
            margin-bottom: 1.5rem;
        }

        .file-drop-zone.drag-over {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .drop-icon {
            font-size: 3rem;
            color: #94a3b8;
            margin-bottom: 1rem;
        }

        .file-drop-zone h4 {
            font-size: 1.1rem;
            color: #0f172a;
            margin: 0 0 0.5rem;
            font-weight: 600;
        }

        .file-drop-zone p {
            color: #64748b;
            font-size: 0.9rem;
            margin: 0 0 1.25rem;
        }

        .btn-browse {
            display: inline-block;
            padding: 0.4rem 1.2rem;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .btn-browse:hover {
            border-color: #94a3b8;
            background: #f1f5f9;
        }

        .btn-remove-preview {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: #ef4444;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            z-index: 10;
            display: none;
        }

        .btn-remove-preview:hover {
            background: #ef4444;
            color: #fff;
            transform: scale(1.1);
        }

        .news-upload-card__preview.is-visible .btn-remove-preview {
            display: flex;
        }

        .attachments-list-styled {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .attachment-file-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.75rem 1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #334155;
            font-weight: 500;
            font-size: 0.95rem;
            word-break: break-all;
        }

        .file-info i {
            color: #64748b;
        }

        .file-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-size {
            color: #64748b;
            font-size: 0.85rem;
        }

        .btn-remove,
        .btn-download {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.25rem;
            border-radius: 4px;
        }

        .btn-remove:hover {
            color: #ef4444;
            background: #fef2f2;
        }

        .btn-download:hover {
            color: #3b82f6;
            background: #eff6ff;
        }

        .attachments-gallery-styled {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .attachment-image-card {
            position: relative;
            width: 140px;
            height: 100px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .attachment-image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .attachment-size {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            backdrop-filter: blur(4px);
        }

        .btn-remove-img {
            position: absolute;
            top: 4px;
            left: 4px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            color: #ef4444;
            display: grid;
            place-items: center;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            font-size: 0.8rem;
        }

        .btn-remove-img:hover {
            background: #ef4444;
            color: #fff;
        }

        .attachment-file-card,
        .attachment-image-card {
            cursor: grab;
        }

        .attachment-file-card:active,
        .attachment-image-card:active {
            cursor: grabbing;
        }

        .sortable-ghost {
            opacity: 0.4;
            background: #e2e8f0;
        }

        /* Custom Confirmation Modal */
        .confirm-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .confirm-modal.is-visible {
            opacity: 1;
            visibility: visible;
        }

        .confirm-modal__overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
        }

        .confirm-modal__container {
            position: relative;
            background: #fff;
            width: 90%;
            max-width: 400px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            padding: 2rem;
            transform: scale(0.9) translateY(30px);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
            text-align: center;
        }

        .confirm-modal.is-visible .confirm-modal__container {
            transform: scale(1) translateY(0);
        }

        .confirm-modal__icon {
            width: 64px;
            height: 64px;
            background: #fef2f2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1.5rem;
        }

        .confirm-modal h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }

        .confirm-modal p {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .confirm-modal__actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .confirm-modal__btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            flex: 1;
        }

        .confirm-modal__btn--cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .confirm-modal__btn--cancel:hover {
            background: #e2e8f0;
        }

        .confirm-modal__btn--confirm {
            background: #ef4444;
            color: #fff;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }

        .confirm-modal__btn--confirm:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
        }

        .soft-action-btn--success {
            background: #22c55e !important;
            box-shadow: none !important;
            transition: all 0.3s ease !important;
        }

        .soft-action-btn--success:hover {
            background: #15803d !important;
            box-shadow: none !important;
            transform: translateY(0) !important;
        }

        .ghost-btn {
            transition: all 0.3s ease !important;
        }

        /* Confirm Modal Dark Mode */
        .dark-mode .confirm-modal__container {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dark-mode .confirm-modal h3 {
            color: #f8fafc;
        }

        .dark-mode .confirm-modal p {
            color: #94a3b8;
        }

        .dark-mode .confirm-modal__btn--cancel {
            background: #334155;
            color: #f1f5f9;
        }

        .dark-mode .confirm-modal__btn--cancel:hover {
            background: #475569;
        }

        .dark-mode .confirm-modal__icon {
            background: rgba(239, 68, 68, 0.15);
        }
    </style>
@endpush

@section('content')
<div class="news-page">
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
            <span class="header-title-text">Manajemen Halaman</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Publikasi</span>
            <i class="fas fa-chevron-right"></i>
            <span>Halaman</span>
            <i class="fas fa-chevron-right"></i>
            <span>{{ $page->exists ? 'Edit' : 'Tambah' }}</span>
        </nav>
    </header>

    <section class="page-title page-title--with-actions">
        <div>
            <h1>{{ $page->exists ? 'Edit Halaman' : 'Tambah Halaman' }}</h1>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.pages.index') }}" class="ghost-btn ghost-btn--back"
                style="text-decoration: none;"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" form="page-form"
                class="soft-action-btn soft-action-btn--success soft-action-btn--large">
                <i class="fas fa-check"></i>
                <span>{{ $page->exists ? 'Simpan Halaman' : 'Simpan Halaman' }}</span>
            </button>
        </div>
    </section>

    <form id="page-form" class="news-form" method="post" enctype="multipart/form-data"
        action="{{ $page->exists ? route('admin.pages.update', ['page' => $page->id]) : route('admin.pages.store') }}">
        @csrf
        @if($page->exists)
            @method('PUT')
        @endif
        <div class="news-editor-columns">
            <div class="news-editor-main">
                @php($pageAttachments = \Illuminate\Support\Facades\Schema::hasTable('page_attachments') ? $page->attachments : collect())

                <div class="news-content-card">
                    <header class="news-card-heading">
                        <h2>Konten</h2>
                        <p>Ringkasan dan isi halaman.</p>
                    </header>
                    <div class="news-input-control">
                        <label for="title">Judul *</label>
                        <small class="news-input-control__hint">Rekomendasi 50-60 karakter</small>
                        <input id="title" type="text" name="title" value="{{ old('title', $page->title) }}" required>
                    </div>
                    <div class="news-input-control">
                        <label for="pageSlugInput">Slug (alamat URL)</label>
                        <small class="news-input-control__hint">Gunakan huruf kecil dan tanda hubung</small>
                        <input type="text" id="pageSlugInput" name="slug" value="{{ old('slug', $page->slug) }}"
                            placeholder="contoh: profil-desa" required>
                    </div>

                    <div class="news-input-control news-input-control--editor">
                        <label>Konten</label>
                        <span class="news-input-control__hint">Gunakan toolbar untuk memformat teks dan menyisipkan
                            media.</span>
                        <div class="news-editor-toolbar">
                            <div class="news-editor-toolbar-row">
                                <button type="button" data-editor-command="undo" title="Undo (Ctrl+Z)"><i
                                        class="fas fa-undo"></i></button>
                                <button type="button" data-editor-command="redo" title="Redo (Ctrl+Y)"><i
                                        class="fas fa-redo"></i></button>
                                <span
                                    style="width:1px;height:20px;background:#e2e8f0;margin:0 8px;display:inline-block;"></span>
                                <button type="button" data-editor-command="bold" aria-label="Bold"><i
                                        class="fas fa-bold"></i></button>
                                <button type="button" data-editor-command="italic" aria-label="Italic"><i
                                        class="fas fa-italic"></i></button>
                                <button type="button" data-editor-command="underline" aria-label="Underline"><i
                                        class="fas fa-underline"></i></button>
                                <button type="button" data-editor-command="justifyLeft" aria-label="Align left"><i
                                        class="fas fa-align-left"></i></button>
                                <button type="button" data-editor-command="justifyCenter" aria-label="Align center"><i
                                        class="fas fa-align-center"></i></button>
                                <button type="button" data-editor-command="justifyRight" aria-label="Align right"><i
                                        class="fas fa-align-right"></i></button>
                                <button type="button" data-editor-command="justifyFull" aria-label="Justify"><i
                                        class="fas fa-align-justify"></i></button>
                                <button type="button" data-editor-command="outdent" aria-label="Outdent"><i
                                        class="fas fa-outdent"></i></button>
                                <button type="button" data-editor-command="indent" aria-label="Indent"><i
                                        class="fas fa-indent"></i></button>
                                <button type="button" data-editor-command="insertUnorderedList"
                                    aria-label="Bullet list"><i class="fas fa-list-ul"></i></button>
                                <button type="button" data-editor-command="insertOrderedList"
                                    aria-label="Number list"><i class="fas fa-list-ol"></i></button>
                                <button type="button" data-editor-command="insertTable" aria-label="Insert table"><i
                                        class="fas fa-table"></i></button>
                                <button type="button" data-editor-command="createLink" aria-label="Insert link"><i
                                        class="fas fa-link"></i></button>
                                <button type="button" data-editor-command="insertImage" aria-label="Insert image"><i
                                        class="fas fa-image"></i></button>
                                <button type="button" data-editor-command="hiliteColor" aria-label="Highlight"
                                    data-editor-value="rgba(79, 70, 229, 0.15)"><i
                                        class="fas fa-fill-drip"></i></button>
                            </div>
                            <div class="news-editor-toolbar-row">
                                <div class="news-editor-toolbar-group">
                                    <div class="news-editor-dropdown" data-editor-dropdown>
                                        <button type="button" data-dropdown-toggle>
                                            <span data-dropdown-label>Paragraph</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                                            <button type="button" data-editor-command="formatBlock"
                                                data-editor-value="<p>">Paragraph</button>
                                            <button type="button" data-editor-command="formatBlock"
                                                data-editor-value="<h1>">Heading 1</button>
                                            <button type="button" data-editor-command="formatBlock"
                                                data-editor-value="<h2>">Heading 2</button>
                                            <button type="button" data-editor-command="formatBlock"
                                                data-editor-value="<h3>">Heading 3</button>
                                        </div>
                                    </div>
                                    <div class="news-editor-dropdown" data-editor-dropdown>
                                        <button type="button" data-dropdown-toggle>
                                            <span data-dropdown-label>Ukuran</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                                            <button type="button" data-editor-command="fontSize"
                                                data-editor-value="1">Extra Small</button>
                                            <button type="button" data-editor-command="fontSize"
                                                data-editor-value="2">Small</button>
                                            <button type="button" data-editor-command="fontSize"
                                                data-editor-value="3">Normal</button>
                                            <button type="button" data-editor-command="fontSize"
                                                data-editor-value="4">Large</button>
                                            <button type="button" data-editor-command="fontSize"
                                                data-editor-value="5">Extra Large</button>
                                        </div>
                                    </div>
                                    <div class="news-editor-dropdown" data-editor-dropdown data-case-dropdown>
                                        <button type="button" data-dropdown-toggle>
                                            <span data-dropdown-label>Change case</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                                            <button type="button" data-change-case="lowercase">lowercase</button>
                                            <button type="button" data-change-case="uppercase">UPPERCASE</button>
                                            <button type="button" data-change-case="capitalize">Capitalize Each
                                                Word</button>
                                        </div>
                                    </div>
                                    <div class="news-editor-dropdown" data-editor-dropdown data-line-spacing-dropdown>
                                        <button type="button" data-dropdown-toggle>
                                            <span data-dropdown-label>Line spacing</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                                            <button type="button" data-line-spacing="compact"
                                                data-line-height="1.4">Compact</button>
                                            <button type="button" data-line-spacing="normal"
                                                data-line-height="1.6">Standard</button>
                                            <button type="button" data-line-spacing="relaxed"
                                                data-line-height="2">Relaxed</button>
                                        </div>
                                    </div>
                                    <div class="news-editor-dropdown" data-editor-dropdown
                                        data-paragraph-spacing-dropdown>
                                        <button type="button" data-dropdown-toggle>
                                            <span data-dropdown-label>Paragraph spacing</span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                                            <button type="button" data-paragraph-spacing="compact">Compact</button>
                                            <button type="button" data-paragraph-spacing="standard">Standard</button>
                                            <button type="button" data-paragraph-spacing="relaxed">Relaxed</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="news-editor-area" contenteditable="true" data-content-editor>
                            {!! old('content', $page->content) !!}
                        </div>

                        <textarea name="content" hidden
                            data-content-textarea>{{ old('content', $page->content) }}</textarea>
                        @error('content')<span class="news-input-control__error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="news-content-card">
                    <div class="news-card-heading">
                        <h2>Unggah Dokumen & Media</h2>
                        <p>Tarik dan lepas dokumen untuk menambahkan lampiran.</p>
                    </div>

                    <div class="file-drop-zone" id="dropZone">
                        <div class="drop-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>Pilih file atau tarik & lepas ke sini.</h4>
                        <p>txt, docx, pdf, jpeg, png, xlsx - Maks. 10MB</p>
                        <label class="btn-browse">
                            Telusuri file
                            <input id="unified_attachments" type="file" name="unified_attachments[]" multiple hidden>
                        </label>
                    </div>

                    <div id="unifiedPreviewContainer">
                        <div class="attachments-list-styled" id="filesPreviewUnified"></div>
                        <div class="attachments-gallery-styled" id="imagesPreviewUnified"></div>
                    </div>

                    <input type="hidden" name="attachment_order" id="attachmentOrderInput" value="[]">

                    @if($pageAttachments->count())
                        <div class="news-card-heading" style="margin-top:2rem; margin-bottom:1rem;">
                            <h4>Lampiran Saat Ini:</h4>
                        </div>

                        <div class="attachments-list-styled" id="sortableFileList">
                            @foreach($pageAttachments->where('type', '!=', 'image') as $attachment)
                                <div class="attachment-file-card" data-attachment-id="{{ $attachment->id }}">
                                    <div class="file-info">
                                        <i class="fas fa-paperclip"></i>
                                        <span
                                            class="file-name">{{ $attachment->original_name ?? basename($attachment->path) }}</span>
                                    </div>
                                    <div class="file-actions">
                                        <span class="file-size">{{ round($attachment->size / 1024) }} KB</span>
                                        <a href="{{ asset('storage/' . ltrim($attachment->path, '/')) }}" target="_blank"
                                            class="btn-download" title="Download"><i class="fas fa-download"></i></a>
                                        <button type="button" class="btn-remove"
                                            onclick="if(confirm('Hapus lampiran ini?')) document.getElementById('delete-form-{{ $attachment->id }}').submit();"><i
                                                class="fas fa-times"></i></button>
                                        <form id="delete-form-{{ $attachment->id }}"
                                            action="{{ route('admin.pages.attachments.destroy', [$page, $attachment]) }}"
                                            method="POST" hidden>
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="attachments-gallery-styled" id="sortableImageList">
                            @foreach($pageAttachments->where('type', 'image') as $attachment)
                                <div class="attachment-image-card" data-attachment-id="{{ $attachment->id }}">
                                    <img src="{{ asset('storage/' . ltrim($attachment->path, '/')) }}"
                                        alt="{{ $attachment->original_name }}">
                                    <span class="attachment-size">{{ round($attachment->size / 1024) }} KB</span>
                                    <button type="button" class="btn-remove-img"
                                        onclick="if(confirm('Hapus gambar ini?')) document.getElementById('delete-form-{{ $attachment->id }}').submit();"><i
                                            class="fas fa-times"></i></button>
                                    <form id="delete-form-{{ $attachment->id }}"
                                        action="{{ route('admin.pages.attachments.destroy', [$page, $attachment]) }}"
                                        method="POST" hidden>
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <aside class="news-editor-side">
                <div class="news-side-card">
                    <div class="news-card-block">
                        <div class="news-card-heading">
                            <h3>General</h3>
                            <p>Nama, status, dan jadwal publish.</p>
                        </div>
                        <div class="news-input-control">
                            <label for="published_at">Tanggal publish (opsional)</label>
                            <input id="published_at" type="datetime-local" name="published_at"
                                value="{{ old('published_at', $page->published_at ? $page->published_at->format('Y-m-d\TH:i') : '') }}">
                        </div>
                        <div class="news-input-control">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
                                <option value="published" @selected(old('status', $page->status) === 'published')>
                                    Published</option>
                                <option value="archived" @selected(old('status', $page->status) === 'archived')>Archived
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="news-side-card">
                    <div class="news-card-block">
                        <div class="news-card-heading">
                            <h3>Cover</h3>
                            <p>Unggah thumbnail yang representatif.</p>
                        </div>
                        <div class="news-upload-card">
                            <div class="news-upload-card__preview @if($page->feature_image) is-visible @endif"
                                id="featurePreview">
                                <button type="button" class="btn-remove-preview" id="btnRemoveFeature"
                                    title="Hapus gambar">
                                    <i class="fas fa-times"></i>
                                </button>
                                @if($page->feature_image)
                                    <img src="{{ asset('storage/' . ltrim($page->feature_image, '/')) }}"
                                        alt="Feature image" id="featurePreviewImg">
                                @else
                                    <div class="news-upload-card__placeholder" id="featurePlaceholder">
                                        <i class="fas fa-cloud-upload-alt fa-2x" style="color: #94a3b8; opacity: 0.6;"></i>
                                        <span style="font-size: 0.85rem; color: #64748b; margin-top: 8px;">Belum ada
                                            gambar</span>
                                    </div>
                                    <img src="" alt="" id="featurePreviewImg"
                                        style="opacity:0;max-width:100%;border-radius:12px;">
                                @endif
                            </div>
                            <p class="news-upload-hint">Rekomendasi 1600×1200, maks. 10MB</p>
                            <div style="text-align: center;">
                                <label class="btn-browse">
                                    Telusuri file
                                    <input id="feature_image" type="file" name="feature_image" accept="image/*" hidden>
                                </label>
                            </div>
                            <input type="hidden" name="remove_feature_image" id="remove_feature_image" value="0">
                            @if($page->feature_image)
                                <div id="currentImageText">
                                    <small
                                        style="display: block; word-break: break-all; line-height: 1.4; margin-top: 10px;">Gambar
                                        saat ini: {{ $page->feature_image }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<!-- Confirmation Modal for Cover Removal -->
<div id="confirmCoverModal" class="confirm-modal">
    <div class="confirm-modal__overlay"></div>
    <div class="confirm-modal__container">
        <div class="confirm-modal__icon">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3>Hapus Gambar Cover?</h3>
        <p>Apakah Anda yakin ingin menghapus gambar cover ini? Perubahan akan disimpan secara permanen setelah Anda
            menekan tombol Simpan Halaman.</p>
        <div class="confirm-modal__actions">
            <button type="button" class="confirm-modal__btn confirm-modal__btn--cancel"
                id="btnCancelDelete">Batal</button>
            <button type="button" class="confirm-modal__btn confirm-modal__btn--confirm" id="btnConfirmDelete">Ya,
                Hapus</button>
        </div>
    </div>
</div>



<!-- Move editor input modal outside of page-form to prevent nested form bugs -->
<div class="news-editor-input-modal" data-editor-input-modal aria-hidden="true">
    <div class="news-editor-input-modal__overlay" data-editor-input-modal-close></div>
    <div class="news-editor-input-modal__dialog">
        <header class="news-editor-input-modal__header">
            <div>
                <h4 data-editor-input-modal-title>Input</h4>
                <p data-editor-input-modal-description>Lengkapi form di bawah</p>
            </div>
            <button type="button" class="news-editor-input-modal__close" data-editor-input-modal-close
                aria-label="Tutup modal">
                <i class="fas fa-xmark"></i>
            </button>
        </header>
        <form data-editor-input-modal-form>
            <div class="news-editor-input-modal__fields" data-editor-input-modal-fields></div>
            <div class="news-editor-input-modal__actions">
                <button type="button" class="news-btn news-btn--ghost" data-editor-input-modal-cancel>Batalkan</button>
                <button type="submit" class="news-btn news-btn--primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('admin.news.partials.editor-script')

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const titleInput = document.querySelector('input[name="title"]');
            const slugInput = document.getElementById('pageSlugInput');
            const featureInput = document.getElementById('feature_image');
            const previewImg = document.getElementById('featurePreviewImg');
            const placeholder = document.getElementById('featurePlaceholder');

            if (titleInput && slugInput) {
                const slugify = (text) => text.toString().toLowerCase()
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9\-]/g, '')
                    .replace(/\-+/g, '-');

                titleInput.addEventListener('input', () => {
                    if (slugInput.dataset.touched === 'true') return;
                    slugInput.value = slugify(titleInput.value);
                });

                slugInput.addEventListener('input', () => {
                    slugInput.dataset.touched = 'true';
                });
            }

            if (featureInput) {
                const btnRemoveFeature = document.getElementById('btnRemoveFeature');
                const removeFeatureInput = document.getElementById('remove_feature_image');
                const featurePreview = document.getElementById('featurePreview');
                const currentImageText = document.getElementById('currentImageText');

                featureInput.addEventListener('change', (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        if (previewImg) {
                            previewImg.src = ev.target?.result || '';
                            previewImg.style.opacity = 1;
                            if (featurePreview) featurePreview.classList.add('is-visible');
                        }
                        if (placeholder) {
                            placeholder.style.display = 'none';
                        }
                        if (removeFeatureInput) removeFeatureInput.value = '0';
                    };
                    reader.readAsDataURL(file);
                });

                if (btnRemoveFeature) {
                    const modal = document.getElementById('confirmCoverModal');
                    const btnCancel = document.getElementById('btnCancelDelete');
                    const btnConfirm = document.getElementById('btnConfirmDelete');

                    btnRemoveFeature.addEventListener('click', (e) => {
                        e.preventDefault();
                        if (modal) modal.classList.add('is-visible');
                    });

                    if (btnCancel) {
                        btnCancel.addEventListener('click', () => {
                            if (modal) modal.classList.remove('is-visible');
                        });
                    }

                    if (btnConfirm) {
                        btnConfirm.addEventListener('click', () => {
                            if (featureInput) featureInput.value = '';
                            if (previewImg) {
                                previewImg.src = '';
                                previewImg.style.opacity = 0;
                            }
                            if (placeholder) {
                                placeholder.style.display = 'flex';
                            }
                            if (featurePreview) {
                                featurePreview.classList.remove('is-visible');
                            }
                            if (removeFeatureInput) {
                                removeFeatureInput.value = '1';
                            }
                            if (currentImageText) {
                                currentImageText.style.display = 'none';
                            }
                            if (modal) modal.classList.remove('is-visible');
                        });
                    }
                }
            }


            const unifiedInput = document.getElementById('unified_attachments');
            const dropZone = document.getElementById('dropZone');
            const filesPreviewUnified = document.getElementById('filesPreviewUnified');
            const imagesPreviewUnified = document.getElementById('imagesPreviewUnified');

            if (unifiedInput && dropZone) {
                let dt = new DataTransfer();

                function renderPreviews() {
                    filesPreviewUnified.innerHTML = '';
                    imagesPreviewUnified.innerHTML = '';
                    Array.from(dt.files).forEach((file, index) => {
                        const sizeKB = Math.round(file.size / 1024);
                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                const card = document.createElement('div');
                                card.className = 'attachment-image-card new-upload';
                                card.innerHTML = `
                                    <img src="${ev.target.result}" alt="${file.name}">
                                    <span class="attachment-size">${sizeKB} KB</span>
                                    <button type="button" class="btn-remove-img" data-index="${index}"><i class="fas fa-times"></i></button>
                                `;
                                imagesPreviewUnified.appendChild(card);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            const card = document.createElement('div');
                            card.className = 'attachment-file-card new-upload';
                            card.innerHTML = `
                                <div class="file-info">
                                    <i class="fas fa-paperclip"></i>
                                    <span class="file-name">${file.name}</span>
                                </div>
                                <div class="file-actions">
                                    <span class="file-size">${sizeKB} KB</span>
                                    <button type="button" class="btn-remove" data-index="${index}"><i class="fas fa-times"></i></button>
                                </div>
                            `;
                            filesPreviewUnified.appendChild(card);
                        }
                    });

                    setTimeout(() => {
                        document.querySelectorAll('.new-upload .btn-remove, .new-upload .btn-remove-img').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                const idx = parseInt(btn.dataset.index);
                                const newDt = new DataTransfer();
                                Array.from(dt.files).forEach((f, i) => {
                                    if (i !== idx) newDt.items.add(f);
                                });
                                dt = newDt;
                                unifiedInput.files = dt.files;
                                renderPreviews();
                            });
                        });
                    }, 100);
                }

                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, preventDefaults, false);
                });
                function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
                });
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
                });

                dropZone.addEventListener('drop', (e) => {
                    Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
                    unifiedInput.files = dt.files;
                    renderPreviews();
                });

                unifiedInput.addEventListener('change', (e) => {
                    Array.from(e.target.files).forEach(f => dt.items.add(f));
                    unifiedInput.files = dt.files;
                    renderPreviews();
                });
            }

            // Initialize SortableJS for existing attachments
            const sortableFileList = document.getElementById('sortableFileList');
            const sortableImageList = document.getElementById('sortableImageList');
            const orderInput = document.getElementById('attachmentOrderInput');

            function updateSortOrder() {
                const order = [];
                document.querySelectorAll('[data-attachment-id]').forEach(el => {
                    order.push(el.dataset.attachmentId);
                });
                if (orderInput) orderInput.value = JSON.stringify(order);
            }

            if (typeof Sortable !== 'undefined') {
                if (sortableFileList) {
                    new Sortable(sortableFileList, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: updateSortOrder
                    });
                }
                if (sortableImageList) {
                    new Sortable(sortableImageList, {
                        animation: 150,
                        ghostClass: 'sortable-ghost',
                        onEnd: updateSortOrder
                    });
                }
            }

            // Prevent 404 errors caused by exceeding post_max_size
            const pageForm = document.getElementById('page-form');
            if (pageForm) {
                pageForm.addEventListener('submit', (e) => {
                    const MAX_POST_SIZE = 5 * 1024 * 1024; // Lower to 5MB to be safe
                    let totalSize = 0;

                    const fInput = document.getElementById('feature_image');
                    const uInput = document.getElementById('unified_attachments');

                    if (fInput && fInput.files && fInput.files.length > 0) {
                        totalSize += fInput.files[0].size;
                    }

                    if (uInput && uInput.files && uInput.files.length > 0) {
                        for (let i = 0; i < uInput.files.length; i++) {
                            totalSize += uInput.files[i].size;
                        }
                    }

                    if (totalSize > MAX_POST_SIZE) {
                        e.preventDefault();
                        const sizeMB = (totalSize / 1024 / 1024).toFixed(2);
                        alert(`Gagal menyimpan! Total ukuran file (${sizeMB} MB) terlalu besar (maks. 7 MB).`);
                    }
                });
            }
        });
    </script>
@endpush