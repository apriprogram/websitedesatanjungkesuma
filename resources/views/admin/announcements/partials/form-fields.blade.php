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
                {{-- ═══ Baris 1: Icon Buttons ═══ --}}
                <div class="news-editor-toolbar-row news-editor-toolbar-row--icons">
                    <button type="button" data-editor-command="bold" aria-label="Bold"><i class="fas fa-bold"></i></button>
                    <button type="button" data-editor-command="italic" aria-label="Italic"><i class="fas fa-italic"></i></button>
                    <button type="button" data-editor-command="underline" aria-label="Underline"><i class="fas fa-underline"></i></button>
                    <span class="news-editor-toolbar-sep"></span>
                    <button type="button" data-editor-command="justifyLeft" aria-label="Align left"><i class="fas fa-align-left"></i></button>
                    <button type="button" data-editor-command="justifyCenter" aria-label="Align center"><i class="fas fa-align-center"></i></button>
                    <button type="button" data-editor-command="justifyRight" aria-label="Align right"><i class="fas fa-align-right"></i></button>
                    <button type="button" data-editor-command="justifyFull" aria-label="Justify"><i class="fas fa-align-justify"></i></button>
                    <span class="news-editor-toolbar-sep"></span>
                    <button type="button" data-editor-command="outdent" aria-label="Outdent"><i class="fas fa-outdent"></i></button>
                    <button type="button" data-editor-command="indent" aria-label="Indent"><i class="fas fa-indent"></i></button>
                    <button type="button" data-editor-command="insertUnorderedList" aria-label="Bullet list"><i class="fas fa-list-ul"></i></button>
                    <button type="button" data-editor-command="insertOrderedList" aria-label="Number list"><i class="fas fa-list-ol"></i></button>
                    <span class="news-editor-toolbar-sep"></span>
                    <button type="button" data-editor-command="insertTable" aria-label="Insert table"><i class="fas fa-table"></i></button>
                    <button type="button" data-editor-command="createLink" aria-label="Insert link"><i class="fas fa-link"></i></button>
                    <button type="button" data-editor-command="insertImage" aria-label="Insert image"><i class="fas fa-image"></i></button>
                    <span class="news-editor-toolbar-sep"></span>
                    {{-- Highlight Color --}}
                    <div class="news-editor-color-picker" id="highlightPicker" data-color-picker="hiliteColor">
                        <button type="button" class="news-editor-color-trigger" id="highlightTrigger" title="Warna Latar Teks" aria-label="Warna Latar" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-fill-drip"></i>
                            <span class="color-bar" id="highlightBar" style="background:#6366f1;"></span>
                        </button>
                        <div class="news-editor-color-panel" id="highlightPanel" style="display:none;"></div>
                    </div>
                    {{-- Text Color --}}
                    <div class="news-editor-color-picker" id="textColorPicker" data-color-picker="foreColor">
                        <button type="button" class="news-editor-color-trigger" id="textColorTrigger" title="Warna Teks" aria-label="Warna Teks" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-font"></i>
                            <span class="color-bar" id="textColorBar" style="background:#ef4444;"></span>
                        </button>
                        <div class="news-editor-color-panel" id="textColorPanel" style="display:none;"></div>
                    </div>
                </div>

                {{-- ═══ Baris 2: Dropdown Controls ═══ --}}
                <div class="news-editor-toolbar-row news-editor-toolbar-row--dropdowns">
                    <div class="news-editor-dropdown" data-editor-dropdown>
                        <button type="button" data-dropdown-toggle>
                            <span data-dropdown-label>Paragraph</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                            <button type="button" data-editor-command="formatBlock" data-editor-value="<p>" style="font-size: 1rem; font-weight: normal;">Paragraph</button>
                            <button type="button" data-editor-command="formatBlock" data-editor-value="<h1>" style="font-size: 1.5rem; font-weight: 700;">Heading 1</button>
                            <button type="button" data-editor-command="formatBlock" data-editor-value="<h2>" style="font-size: 1.25rem; font-weight: 600;">Heading 2</button>
                            <button type="button" data-editor-command="formatBlock" data-editor-value="<h3>" style="font-size: 1.1rem; font-weight: 600;">Heading 3</button>
                        </div>
                    </div>
                    <div class="news-editor-dropdown" data-editor-dropdown>
                        <button type="button" data-dropdown-toggle>
                            <span data-dropdown-label>Ukuran</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                            <button type="button" data-editor-command="fontSize" data-editor-value="1" style="font-size: 0.7rem;">Extra Small</button>
                            <button type="button" data-editor-command="fontSize" data-editor-value="2" style="font-size: 0.85rem;">Small</button>
                            <button type="button" data-editor-command="fontSize" data-editor-value="3" style="font-size: 1rem;">Normal</button>
                            <button type="button" data-editor-command="fontSize" data-editor-value="4" style="font-size: 1.25rem;">Large</button>
                            <button type="button" data-editor-command="fontSize" data-editor-value="5" style="font-size: 1.5rem;">Extra Large</button>
                        </div>
                    </div>
                    <div class="news-editor-dropdown" data-editor-dropdown data-case-dropdown>
                        <button type="button" data-dropdown-toggle>
                            <span data-dropdown-label>Change case</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                            <button type="button" data-change-case="uppercase" style="text-transform: uppercase;">UPPERCASE</button>
                            <button type="button" data-change-case="lowercase" style="text-transform: lowercase;">lowercase</button>
                            <button type="button" data-change-case="capitalize" style="text-transform: capitalize;">Title Case</button>
                        </div>
                    </div>
                    <div class="news-editor-dropdown" data-editor-dropdown data-line-spacing-dropdown>
                        <button type="button" data-dropdown-toggle>
                            <span data-dropdown-label>Line spacing</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                            <button type="button" data-line-spacing="compact" data-line-height="1.25" style="line-height: 1.25; padding-top: 0.25rem; padding-bottom: 0.25rem;">Tight</button>
                            <button type="button" data-line-spacing="normal" data-line-height="1.6" style="line-height: 1.6; padding-top: 0.5rem; padding-bottom: 0.5rem;">Standard</button>
                            <button type="button" data-line-spacing="relaxed" data-line-height="2" style="line-height: 2; padding-top: 0.75rem; padding-bottom: 0.75rem;">Relaxed</button>
                        </div>
                    </div>
                    <div class="news-editor-dropdown" data-editor-dropdown data-paragraph-spacing-dropdown>
                        <button type="button" data-dropdown-toggle>
                            <span data-dropdown-label>Paragraph spacing</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="news-editor-dropdown__menu" data-dropdown-open>
                            <button type="button" data-paragraph-spacing="compact" style="margin-bottom: 2px;">Compact</button>
                            <button type="button" data-paragraph-spacing="standard" style="margin-bottom: 8px;">Standard</button>
                            <button type="button" data-paragraph-spacing="relaxed" style="margin-bottom: 16px;">Relaxed</button>
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
                        <button type="button" class="news-editor-input-modal__close" data-editor-input-modal-close aria-label="Tutup modal">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </header>
                    <div data-editor-input-modal-form>
                        <div class="news-editor-input-modal__fields" data-editor-input-modal-fields></div>
                        <div class="news-editor-input-modal__actions">
                            <button type="button" class="ann-btn ann-btn--cancel" data-editor-input-modal-cancel>Batalkan</button>
                            <button type="button" class="ann-btn ann-btn--submit" data-editor-input-modal-submit>Simpan</button>
                        </div>
                    </div>
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
                <div class="cs-wrapper" data-cs-label="Pilih kategori">
                    <select id="categorySelect" name="category" class="cs-native">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('category')<span class="news-input-control__error">{{ $message }}</span>@enderror
            </div>
            <div class="news-input-control">
                <label for="statusSelect">Status</label>
                <div class="cs-wrapper" data-cs-label="Pilih status">
                    <select id="statusSelect" name="status" class="cs-native">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('status')<span class="news-input-control__error">{{ $message }}</span>@enderror
            </div>
        </div>
    </aside>
</div>
