@extends('admin.layouts.app')

@section('title', $page->exists ? 'Edit Halaman' : 'Tambah Halaman')

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
                <p class="page-eyebrow">Konten Statis</p>
                <h1>{{ $page->exists ? 'Edit Halaman' : 'Tambah Halaman' }}</h1>
                <p>Isi detail halaman, slug unik, dan status publikasi.</p>
            </div>
            <div class="title-actions">
                <a href="{{ route('admin.pages.index') }}" class="ghost-btn ghost-btn--back"><i class="fas fa-arrow-left"></i> Kembali</a>
                <button type="submit" form="page-form" class="soft-action-btn soft-action-btn--success soft-action-btn--large">
                    <i class="fas fa-check"></i>
                    <span>{{ $page->exists ? 'Simpan Halaman' : 'Simpan Halaman' }}</span>
                </button>
            </div>
        </section>

        <form id="page-form" class="news-form" method="post" enctype="multipart/form-data"
              action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
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
                            <input type="text" id="pageSlugInput" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="contoh: profil-desa" required>
                        </div>
                        <div class="news-form-grid">
                            <div class="news-input-control">
                                <label for="metaTitle">Meta Title (opsional)</label>
                                <input id="metaTitle" type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}">
                            </div>
                            <div class="news-input-control">
                                <label for="metaDesc">Meta Description (opsional)</label>
                                <input id="metaDesc" type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}">
                            </div>
                        </div>
                        <div class="news-input-control news-input-control--editor">
                            <label>Konten</label>
                            <span class="news-input-control__hint">Gunakan toolbar untuk memformat teks dan menyisipkan media.</span>
                            <div class="news-editor-toolbar">
                                <div class="news-editor-toolbar-row">
                                    <button type="button" data-editor-command="bold" aria-label="Bold"><i class="fas fa-bold"></i></button>
                                    <button type="button" data-editor-command="italic" aria-label="Italic"><i class="fas fa-italic"></i></button>
                                    <button type="button" data-editor-command="underline" aria-label="Underline"><i class="fas fa-underline"></i></button>
                                    <button type="button" data-editor-command="justifyLeft" aria-label="Align left"><i class="fas fa-align-left"></i></button>
                                    <button type="button" data-editor-command="justifyCenter" aria-label="Align center"><i class="fas fa-align-center"></i></button>
                                    <button type="button" data-editor-command="justifyRight" aria-label="Align right"><i class="fas fa-align-right"></i></button>
                                    <button type="button" data-editor-command="justifyFull" aria-label="Justify"><i class="fas fa-align-justify"></i></button>
                                    <button type="button" data-editor-command="outdent" aria-label="Outdent"><i class="fas fa-outdent"></i></button>
                                    <button type="button" data-editor-command="indent" aria-label="Indent"><i class="fas fa-indent"></i></button>
                                    <button type="button" data-editor-command="insertUnorderedList" aria-label="Bullet list"><i class="fas fa-list-ul"></i></button>
                                    <button type="button" data-editor-command="insertOrderedList" aria-label="Number list"><i class="fas fa-list-ol"></i></button>
                                    <button type="button" data-editor-command="insertTable" aria-label="Insert table"><i class="fas fa-table"></i></button>
                                    <button type="button" data-editor-command="createLink" aria-label="Insert link"><i class="fas fa-link"></i></button>
                                    <button type="button" data-editor-command="insertImage" aria-label="Insert image"><i class="fas fa-image"></i></button>
                                    <button type="button" data-editor-command="hiliteColor" aria-label="Highlight" data-editor-value="rgba(79, 70, 229, 0.15)"><i class="fas fa-fill-drip"></i></button>
                                </div>
                                <div class="news-editor-toolbar-row">
                                    <div class="news-editor-toolbar-group">
                                        <div class="news-editor-dropdown" data-editor-dropdown>
                                            <button type="button" data-dropdown-toggle>
                                                <span data-dropdown-label>Paragraph</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                                <button type="button" data-editor-command="formatBlock" data-editor-value="<p>">Paragraph</button>
                                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h1>">Heading 1</button>
                                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h2>">Heading 2</button>
                                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h3>">Heading 3</button>
                                            </div>
                                        </div>
                                        <div class="news-editor-dropdown" data-editor-dropdown>
                                            <button type="button" data-dropdown-toggle>
                                                <span data-dropdown-label>Ukuran</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                                <button type="button" data-editor-command="fontSize" data-editor-value="1">Extra Small</button>
                                                <button type="button" data-editor-command="fontSize" data-editor-value="2">Small</button>
                                                <button type="button" data-editor-command="fontSize" data-editor-value="3">Normal</button>
                                                <button type="button" data-editor-command="fontSize" data-editor-value="4">Large</button>
                                                <button type="button" data-editor-command="fontSize" data-editor-value="5">Extra Large</button>
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
                                                <button type="button" data-change-case="capitalize">Capitalize Each Word</button>
                                            </div>
                                        </div>
                                        <div class="news-editor-dropdown" data-editor-dropdown data-line-spacing-dropdown>
                                            <button type="button" data-dropdown-toggle>
                                                <span data-dropdown-label>Line spacing</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                                <button type="button" data-line-spacing="compact" data-line-height="1.4">Compact</button>
                                                <button type="button" data-line-spacing="normal" data-line-height="1.6">Standard</button>
                                                <button type="button" data-line-spacing="relaxed" data-line-height="2">Relaxed</button>
                                            </div>
                                        </div>
                                        <div class="news-editor-dropdown" data-editor-dropdown data-paragraph-spacing-dropdown>
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
                            <div class="news-editor-input-modal" data-editor-input-modal aria-hidden="true">
                                <div class="news-editor-input-modal__overlay" data-editor-input-modal-close></div>
                                <div class="news-editor-input-modal__dialog">
                                    <header class="news-editor-input-modal__header">
                                        <div>
                                            <h4 data-editor-input-modal-title>Input</h4>
                                            <p data-editor-input-modal-description>Lengkapi form di bawah</p>
                                        </div>
                                        <button type="button" class="news-editor-input-modal__close" data-editor-input-modal-close aria-label="Tutup modal">
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
                            <textarea name="content" hidden data-content-textarea>{{ old('content', $page->content) }}</textarea>
                            @error('content')<span class="news-input-control__error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="news-content-card">
                        <div class="news-card-heading">
                            <h2>Lampiran</h2>
                            <p>Tambahkan gambar atau dokumen pendukung.</p>
                        </div>
                        <div class="news-form-grid">
                            <div class="news-input-control">
                                <div class="news-card-heading" style="margin-top:0.25rem;">
                                    <h4>Lampiran Gambar</h4>
                                    <p>Unggah lebih dari satu gambar (opsional).</p>
                                </div>
                                <label class="upload-btn upload-btn--block" for="attachments_images">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Pilih gambar</span>
                                    <input id="attachments_images" type="file" name="attachments_images[]" accept="image/*" multiple>
                                </label>
                                <small class="news-input-control__hint">Format JPG/PNG, bisa pilih lebih dari satu.</small>
                            </div>
                            <div class="news-input-control">
                                <div class="news-card-heading" style="margin-top:0.25rem;">
                                    <h4>Lampiran File</h4>
                                    <p>Tambahkan dokumen pendukung (PDF/DOC/ZIP).</p>
                                </div>
                                <label class="upload-btn upload-btn--block" for="attachments_files">
                                    <i class="fas fa-file-arrow-up"></i>
                                    <span>Pilih file</span>
                                    <input id="attachments_files" type="file" name="attachments_files[]" multiple>
                                </label>
                                <small class="news-input-control__hint">Maks. 10MB per file. PDF/DOC/ZIP, dll.</small>
                            </div>
                        </div>

                        @if($pageAttachments->count())
                            <div class="news-card-heading" style="margin-top:1rem;">
                                <h4>Lampiran Saat Ini</h4>
                            </div>
                            <ul class="news-attachment-list">
                                @foreach($pageAttachments as $attachment)
                                    <li>
                                        <span class="news-attachment-name">{{ $attachment->original_name ?? basename($attachment->path) }}</span>
                                        <form action="{{ route('admin.pages.attachments.destroy', [$page, $attachment]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="news-table__action-item news-table__action-item--danger" onclick="return confirm('Hapus lampiran ini?')">
                                                <i class="fas fa-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
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
                                <label for="published_at">Tanggal publish</label>
                                <input id="published_at" type="datetime-local" name="published_at" value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\\TH:i')) }}">
                            </div>
                            <div class="news-input-control">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
                                    <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
                                    <option value="archived" @selected(old('status', $page->status) === 'archived')>Archived</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="news-side-card">
                        <div class="news-card-block">
                            <div class="news-card-heading">
                                <h3>Media</h3>
                                <p>Unggah thumbnail yang representatif.</p>
                            </div>
                            <div class="news-upload-card">
                                <div class="news-upload-card__preview @if($page->feature_image) is-visible @endif" id="featurePreview">
                                    @if($page->feature_image)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($page->feature_image) }}" alt="Feature image" id="featurePreviewImg">
                                    @else
                                        <div class="news-upload-card__placeholder" id="featurePlaceholder">
                                            <i class="fas fa-image fa-2x"></i>
                                            <span>No image uploaded</span>
                                        </div>
                                        <img src="" alt="" id="featurePreviewImg" style="opacity:0;max-width:100%;border-radius:12px;">
                                    @endif
                                </div>
                                <p class="news-upload-hint">Rekomendasi 1600×1200, maks. 10MB</p>
                                <label class="upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Pilih gambar</span>
                                    <input id="feature_image" type="file" name="feature_image" accept="image/*" hidden>
                                </label>
                                @if($page->feature_image)
                                    <small>Gambar saat ini: {{ $page->feature_image }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
@endsection

@include('admin.news.partials.editor-script')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.getElementById('pageSlugInput');
        const featureInput = document.getElementById('feature_image');
        const previewImg = document.getElementById('featurePreviewImg');
        const placeholder = document.getElementById('featurePlaceholder');

        if (!titleInput || !slugInput) return;

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

        if (featureInput) {
            featureInput.addEventListener('change', (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (ev) => {
                    if (previewImg) {
                        previewImg.src = ev.target?.result || '';
                        previewImg.style.opacity = 1;
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endpush
