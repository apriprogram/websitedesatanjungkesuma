@php
    $model = optional($announcement);
    $title = old('title', $model->title ?? '');
    $category = old('category', $model->category ?? App\Models\Announcement::CATEGORY_DESA);
    $status = old('status', $model->status ?? App\Models\Announcement::STATUS_DRAFT);
    $excerpt = old('excerpt', $model->excerpt ?? '');
    $body = old('body', $model->body ?? '');
    $publishedAt = old('published_at', $model->published_at
        ? $model->published_at->format('Y-m-d\TH:i')
        : '');
    $attachments = $model?->attachments ?? collect();
@endphp

<div class="news-editor-columns">
    <section class="news-content-card">
        <div class="news-card-heading">
            <h3>Konten</h3>
            <p>Ringkasan dan isi pengumuman.</p>
        </div>
        <div class="news-input-control">
            <label>Judul <span>*</span></label>
            <span class="news-input-control__hint">Rekomendasi 50-60 karakter</span>
            <input id="announcementTitle" name="title" type="text" value="{{ $title }}" placeholder="Judul pengumuman"
                required>
            @error('title')<span class="news-input-control__error">{{ $message }}</span>@enderror
        </div>
        <div class="news-input-control">
            <label>Ringkasan</label>
            <span class="news-input-control__hint">Saran 120-160 karakter</span>
            <textarea id="excerpt" name="excerpt" rows="3" placeholder="Ringkasan singkat">{{ $excerpt }}</textarea>
            @error('excerpt')<span class="news-input-control__error">{{ $message }}</span>@enderror
        </div>
        <div class="news-input-control news-input-control--editor">
            <label>Isi lengkap</label>
            <span class="news-input-control__hint">Gunakan toolbar untuk memformat teks dan menyisipkan media.</span>
            <div class="news-editor-toolbar">
                <div class="news-editor-toolbar-row">
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
                    <button type="button" data-editor-command="insertUnorderedList" aria-label="Bullet list"><i
                            class="fas fa-list-ul"></i></button>
                    <button type="button" data-editor-command="insertOrderedList" aria-label="Number list"><i
                            class="fas fa-list-ol"></i></button>
                    <button type="button" data-editor-command="insertTable" aria-label="Insert table"><i
                            class="fas fa-table"></i></button>
                    <button type="button" data-editor-command="createLink" aria-label="Insert link"><i
                            class="fas fa-link"></i></button>
                    <button type="button" data-editor-command="insertImage" aria-label="Insert image"><i
                            class="fas fa-image"></i></button>
                    <button type="button" data-editor-command="hiliteColor" aria-label="Highlight"
                        data-editor-value="rgba(79, 70, 229, 0.15)"><i class="fas fa-fill-drip"></i></button>
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
                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h1>">Heading
                                    1</button>
                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h2>">Heading
                                    2</button>
                                <button type="button" data-editor-command="formatBlock" data-editor-value="<h3>">Heading
                                    3</button>
                            </div>
                        </div>
                        <div class="news-editor-dropdown" data-editor-dropdown>
                            <button type="button" data-dropdown-toggle>
                                <span data-dropdown-label>Ukuran</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                <button type="button" data-editor-command="fontSize" data-editor-value="1">Extra
                                    Small</button>
                                <button type="button" data-editor-command="fontSize"
                                    data-editor-value="2">Small</button>
                                <button type="button" data-editor-command="fontSize"
                                    data-editor-value="3">Normal</button>
                                <button type="button" data-editor-command="fontSize"
                                    data-editor-value="4">Large</button>
                                <button type="button" data-editor-command="fontSize" data-editor-value="5">Extra
                                    Large</button>
                            </div>
                        </div>
                        <div class="news-editor-dropdown" data-editor-dropdown data-case-dropdown>
                            <button type="button" data-dropdown-toggle>
                                <span data-dropdown-label>Change case</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                <button type="button" data-change-case="uppercase">UPPERCASE</button>
                                <button type="button" data-change-case="lowercase">lowercase</button>
                                <button type="button" data-change-case="capitalize">Title Case</button>
                            </div>
                        </div>
                        <div class="news-editor-dropdown" data-editor-dropdown data-line-spacing-dropdown>
                            <button type="button" data-dropdown-toggle>
                                <span data-dropdown-label>Line spacing</span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="news-editor-dropdown__menu" data-dropdown-open>
                                <button type="button" data-line-spacing="compact" data-line-height="1.25">Tight</button>
                                <button type="button" data-line-spacing="normal"
                                    data-line-height="1.6">Standard</button>
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
                {!! $body !!}
            </div>
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
                            <button type="button" class="news-btn news-btn--ghost"
                                data-editor-input-modal-cancel>Batalkan</button>
                            <button type="submit" class="news-btn news-btn--primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
            <textarea name="body" hidden data-content-textarea>{{ $body }}</textarea>
            @error('body')<span class="news-input-control__error">{{ $message }}</span>@enderror
        </div>
        <div class="news-input-control">
            <label>Lampiran Dokumen (boleh lebih dari 10 file)</label>
            <span class="news-input-control__hint">PDF/DOC/XLS/PPT/ZIP/RAR, bisa pilih banyak sekaligus.</span>
            <input type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt">
            @error('files.*')<span class="news-input-control__error">{{ $message }}</span>@enderror
        </div>
        <div class="news-input-control">
            <label>Lampiran Gambar</label>
            <span class="news-input-control__hint">Pilih beberapa gambar sekaligus (JPG/PNG/WEBP).</span>
            <input type="file" name="images[]" multiple accept="image/*">
            @error('images.*')<span class="news-input-control__error">{{ $message }}</span>@enderror
        </div>

    </section>

    <aside class="news-side-card">
        <div class="news-card-block">
            <div class="news-card-heading">
                <h3>General</h3>
                <p>Nama, status, dan jadwal publikasi.</p>
            </div>
            <div class="news-input-control">
                <label for="publishedAt">Tanggal publikasi</label>
                <input id="publishedAt" name="published_at" type="datetime-local" value="{{ $publishedAt }}">
                @error('published_at')<span class="news-input-control__error">{{ $message }}</span>@enderror
            </div>
            <div class="news-input-control">
                <label for="categorySelect">Kategori</label>
                <select id="categorySelect" name="category">
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('category')<span class="news-input-control__error">{{ $message }}</span>@enderror
            </div>
            <div class="news-input-control">
                <label for="statusSelect">Status</label>
                <select id="statusSelect" name="status">
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('status')<span class="news-input-control__error">{{ $message }}</span>@enderror
            </div>
        </div>
    </aside>
</div>
