@extends('admin.layouts.app')

@section('title', 'Tambah Data Penduduk')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-residents.css') }}">
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
            <span class="header-title-text">Manajemen Penduduk</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Penduduk</span>
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
            <span>Tambah Data</span>
        </nav>
    </header>

    @include('admin.partials.alerts', ['errors' => $errors ?? new \Illuminate\Support\ViewErrorBag()])

    <section class="page-title page-title--with-actions">
        <div>
            <h1>Tambah Data Penduduk</h1>
            <p>Lengkapi informasi identitas, dokumen, dan alamat penduduk.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.penduduks.index') }}" class="ghost-btn ghost-btn--subtle">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.penduduks.store') }}" class="resident-form" enctype="multipart/form-data">
        @csrf

        <div class="resident-form__grid">
            <section class="form-section">
                <div class="form-section__header">
                    <h2>Identitas Penduduk</h2>
                    <p>Isi data dasar sesuai KTP dan kartu keluarga resmi.</p>
                </div>
                <div class="form-section__body">
                    <div class="settings-form-grid two-columns">
                        <div class="form-field kk-dropdown" data-kk-picker>
                            <span>No. KK <sup>*</sup></span>
                            <input type="hidden" name="no_kk" value="{{ old('no_kk') }}" data-kk-value>
                        <div class="kk-dropdown__field" data-kk-display-wrapper>
                            <input type="text" class="kk-dropdown__display" data-kk-display readonly required placeholder="Pilih No. KK" value="{{ old('no_kk') }}">
                            <i class="fas fa-chevron-down kk-dropdown__caret" aria-hidden="true"></i>
                        </div>
                        <div class="kk-dropdown__panel" data-kk-panel>
                            <div class="kk-dropdown__search">
                                <i class="fas fa-search"></i>
                                <input type="text" data-kk-search placeholder="Cari No. KK">
                            </div>
                            <div class="kk-dropdown__list">
                                @foreach ($keluargaOptions as $keluarga)
                                    <button type="button" class="kk-dropdown__option" data-kk-option data-value="{{ $keluarga->no_kk }}">
                                        {{ $keluarga->no_kk }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                        <label class="form-field">
                            <span>NIK <sup>*</sup></span>
                            <input type="text" name="nik" value="{{ old('nik') }}" maxlength="20" required>
                        </label>
                        <label class="form-field">
                            <span>Nama Lengkap <sup>*</sup></span>
                            <input type="text" name="nama" value="{{ old('nama') }}" maxlength="100" required>
                        </label>
                        <label class="form-field">
                            <span>Jenis Kelamin <sup>*</sup></span>
                            <select name="jenis_kelamin_id" required>
                                <option value="">Pilih jenis kelamin</option>
                                @foreach ($jenisKelaminOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('jenis_kelamin_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Tempat Lahir</span>
                            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Tanggal Lahir</span>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                        </label>
                        <label class="form-field">
                            <span>Agama</span>
                            <select name="agama_id">
                                <option value="">Pilih agama</option>
                                @foreach ($agamaOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('agama_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Pendidikan (dalam KK)</span>
                            <select name="pendidikan_kk_id">
                                <option value="">Pilih pendidikan</option>
                                @foreach ($pendidikanOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('pendidikan_kk_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Pendidikan Sedang Ditempuh</span>
                            <select name="pendidikan_sedang_id">
                                <option value="">Pilih pendidikan</option>
                                @foreach ($pendidikanOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('pendidikan_sedang_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Pekerjaan</span>
                            <select name="pekerjaan_id">
                                <option value="">Pilih pekerjaan</option>
                                @foreach ($pekerjaanOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('pekerjaan_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>No. HP / WhatsApp</span>
                            <input type="text" name="nomor_hp" value="{{ old('nomor_hp') }}" maxlength="20" placeholder="08...">
                        </label>
                        <label class="form-field">
                            <span>Email Aktif</span>
                            <input type="email" name="email" value="{{ old('email') }}" maxlength="100" placeholder="contoh@email.com">
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-section form-section--accent">
                <div class="form-section__header">
                    <h2>Foto Penduduk</h2>
                    <p>Unggah foto profil serta dokumentasi KTP dan KK (opsional).</p>
                </div>
                <div class="form-section__body">
                    <div class="settings-form-grid three-columns">
                        <div class="form-field">
                            <span>Foto Profil</span>
                            <input type="file" name="foto_profil" accept="image/*">
                            <div class="media-preview" data-photo-preview="foto_profil">
                                <span class="media-preview__placeholder">Belum ada foto.</span>
                            </div>
                            <label class="media-preview__remove">
                                <input type="checkbox" name="remove_foto_profil" value="1" @checked(old('remove_foto_profil'))>
                                <span>Hapus foto profil saat ini</span>
                            </label>
                            <small class="form-field__hint">Biarkan kosong jika tidak ingin menambahkan foto profil. Maksimal 5 MB.</small>
                        </div>
                        <div class="form-field">
                            <span>Foto KTP</span>
                            <input type="file" name="foto_ktp" accept="image/*">
                            <div class="media-preview" data-photo-preview="foto_ktp">
                                <span class="media-preview__placeholder">Belum ada foto.</span>
                            </div>
                            <label class="media-preview__remove">
                                <input type="checkbox" name="remove_foto_ktp" value="1" @checked(old('remove_foto_ktp'))>
                                <span>Hapus foto KTP saat ini</span>
                            </label>
                            <small class="form-field__hint">Unggah scan KTP dengan teks terbaca.</small>
                        </div>
                        <div class="form-field">
                            <span>Foto KK</span>
                            <input type="file" name="foto_kk" accept="image/*">
                            <div class="media-preview" data-photo-preview="foto_kk">
                                <span class="media-preview__placeholder">Belum ada foto.</span>
                            </div>
                            <label class="media-preview__remove">
                                <input type="checkbox" name="remove_foto_kk" value="1" @checked(old('remove_foto_kk'))>
                                <span>Hapus foto KK saat ini</span>
                            </label>
                            <small class="form-field__hint">Gunakan foto KK terbaru jika tersedia.</small>
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section__header">
                    <h2>Relasi Keluarga</h2>
                    <p>Lengkapi hubungan keluarga dan kewarganegaraan penduduk.</p>
                </div>
                <div class="form-section__body">
                    <div class="settings-form-grid two-columns">
                        <label class="form-field">
                            <span>Status Perkawinan</span>
                            <select name="status_kawin_id">
                                <option value="">Pilih status perkawinan</option>
                                @foreach ($statusKawinOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('status_kawin_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Hubungan dalam KK</span>
                            <select name="kk_level_id">
                                <option value="">Pilih hubungan</option>
                                @foreach ($hubunganKkOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('kk_level_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Kewarganegaraan</span>
                            <select name="warganegara_id">
                                <option value="">Pilih kewarganegaraan</option>
                                @foreach ($warganegaraOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('warganegara_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Nama Ayah</span>
                            <input type="text" name="nama_ayah" value="{{ old('nama_ayah') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>NIK Ayah</span>
                            <input type="text" name="ayah_nik" value="{{ old('ayah_nik') }}" maxlength="20">
                        </label>
                        <label class="form-field">
                            <span>Nama Ibu</span>
                            <input type="text" name="nama_ibu" value="{{ old('nama_ibu') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>NIK Ibu</span>
                            <input type="text" name="ibu_nik" value="{{ old('ibu_nik') }}" maxlength="20">
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-section form-section--accent">
                <div class="form-section__header">
                    <h2>Dokumen &amp; Status Administrasi</h2>
                    <p>Catat dokumen penting serta status kependudukan terbaru.</p>
                </div>
                <div class="form-section__body">
                    <div class="settings-form-grid two-columns">
                        <label class="form-field">
                            <span>Golongan Darah</span>
                            <select name="golongan_darah_id">
                                <option value="">Pilih golongan darah</option>
                                @foreach ($golonganDarahOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('golongan_darah_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Akta Lahir</span>
                            <input type="text" name="akta_lahir" value="{{ old('akta_lahir') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Dokumen Paspor</span>
                            <input type="text" name="dokumen_pasport" value="{{ old('dokumen_pasport') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Tanggal Akhir Paspor</span>
                            <input type="date" name="tanggal_akhir_paspor" value="{{ old('tanggal_akhir_paspor') }}">
                        </label>
                        <label class="form-field">
                            <span>Dokumen KITAS</span>
                            <input type="text" name="dokumen_kitas" value="{{ old('dokumen_kitas') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Akta Perkawinan</span>
                            <input type="text" name="akta_perkawinan" value="{{ old('akta_perkawinan') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Tanggal Perkawinan</span>
                            <input type="date" name="tanggal_perkawinan" value="{{ old('tanggal_perkawinan') }}">
                        </label>
                        <label class="form-field">
                            <span>Akta Perceraian</span>
                            <input type="text" name="akta_perceraian" value="{{ old('akta_perceraian') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>Tanggal Perceraian</span>
                            <input type="date" name="tanggal_perceraian" value="{{ old('tanggal_perceraian') }}">
                        </label>
                        <label class="form-field">
                            <span>Jenis Cacat</span>
                            <select name="cacat_id">
                                <option value="">Tidak ada</option>
                                @foreach ($cacatOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('cacat_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Cara KB</span>
                            <select name="cara_kb_id">
                                <option value="">Tidak menggunakan</option>
                                @foreach ($caraKbOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('cara_kb_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Status Rekam KTP</span>
                            <select name="status_rekam_id">
                                <option value="">Belum ditentukan</option>
                                @foreach ($statusRekamOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('status_rekam_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Status Dasar <sup>*</sup></span>
                            <select name="status_dasar_id" required>
                                <option value="">Pilih status</option>
                                @foreach ($statusDasarOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('status_dasar_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Suku</span>
                            <select name="suku_id">
                                <option value="">Tidak diketahui</option>
                                @foreach ($sukuOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('suku_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Sedang Hamil?</span>
                            <select name="hamil">
                                <option value="" @selected($hamilValue === '')>Tidak diketahui</option>
                                <option value="1" @selected($hamilValue === '1')>Ya</option>
                                <option value="0" @selected($hamilValue === '0')>Tidak</option>
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Memiliki KTP-el</span>
                            <select name="ktp_el">
                                <option value="0" @selected($ktpElValue === '0')>Tidak</option>
                                <option value="1" @selected($ktpElValue === '1')>Ya</option>
                            </select>
                        </label>
                        <label class="form-field">
                            <span>Tag ID Card</span>
                            <input type="text" name="tag_id_card" value="{{ old('tag_id_card') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>ID Asuransi</span>
                            <input type="text" name="id_asuransi" value="{{ old('id_asuransi') }}" maxlength="100">
                        </label>
                        <label class="form-field">
                            <span>No. Asuransi</span>
                            <input type="text" name="no_asuransi" value="{{ old('no_asuransi') }}" maxlength="100">
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="form-section__header">
                    <h2>Alamat &amp; Wilayah</h2>
                    <p>Perbarui domisili sesuai KK dan alamat terkini.</p>
                </div>
                <div class="form-section__body">
                    <div class="settings-form-grid two-columns">
                        <label class="form-field form-field--full">
                            <span>Alamat Domisili (sesuai KK)</span>
                            <textarea name="alamat" rows="2">{{ old('alamat') }}</textarea>
                        </label>
                        <label class="form-field form-field--full">
                            <span>Alamat Saat Ini</span>
                            <textarea name="alamat_sekarang" rows="2">{{ old('alamat_sekarang') }}</textarea>
                        </label>
                        <label class="form-field">
                            <span>Dusun</span>
                            <select name="dusun_id">
                                <option value="">Pilih dusun</option>
                                @foreach ($dusunOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('dusun_id') == $option->id)>{{ $option->nama }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>RW</span>
                            <select name="rw_id">
                                <option value="">Pilih RW</option>
                                @foreach ($rwOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('rw_id') == $option->id)>
                                        Dusun {{ $option->dusun?->nama }} &mdash; RW {{ $option->nomor }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span>RT</span>
                            <select name="rt_id">
                                <option value="">Pilih RT</option>
                                @foreach ($rtOptions as $option)
                                    <option value="{{ $option->id }}" @selected(old('rt_id') == $option->id)>
                                        RW {{ $option->rw?->nomor }} &mdash; RT {{ $option->nomor }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>
        </div>

        <div class="form-actions form-actions--sticky">
            <a href="{{ route('admin.penduduks.index') }}" class="ghost-btn ghost-btn--subtle">
                Batal
            </a>
            <button type="submit" class="primary-btn">
                Simpan Data
            </button>
        </div>
    </form>
    @include('admin.penduduks.partials.kk-picker-script')
@endsection
