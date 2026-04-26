@php
    $modalContext = old('form_context');
@endphp

{{-- Create Modal --}}
<div class="dialog-backdrop" id="migrantCreateModal" aria-hidden="true">
    <div class="dialog dialog--form" role="dialog" aria-modal="true" aria-labelledby="migrantCreateTitle">
        <header class="dialog__header">
            <div class="dialog__header-icon">
                <i class="fas fa-route"></i>
            </div>
            <div class="dialog__header-text">
                <h2 id="migrantCreateTitle">Catat Penduduk Pindah</h2>
                <p>Lengkapi data kepindahan penduduk keluar wilayah desa.</p>
            </div>
            <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form pencatatan">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <form method="POST" action="{{ route('admin.penduduk-pindah.store') }}" class="dialog__form">
            @csrf
            <input type="hidden" name="form_context" value="migrantCreateModal">
            <div class="dialog__body">
                <div class="settings-form-grid two-columns">
                    <label class="form-field @if($modalContext === 'migrantCreateModal' && $errors->has('penduduk_id')) form-field--error @endif">
                        <span>Penduduk <sup>*</sup></span>
                        <select name="penduduk_id" required>
                            <option value="">Pilih penduduk</option>
                            @foreach ($pendudukOptions as $option)
                                <option value="{{ $option->id }}" @selected(old('penduduk_id') == $option->id)>
                                    {{ $option->nama }} &mdash; NIK {{ $option->nik }}
                                </option>
                            @endforeach
                        </select>
                        @if ($modalContext === 'migrantCreateModal') @error('penduduk_id') <span class="form-error">{{ $message }}</span> @enderror @endif
                    </label>
                    <label class="form-field @if($modalContext === 'migrantCreateModal' && $errors->has('tanggal_pindah')) form-field--error @endif">
                        <span>Tanggal Pindah <sup>*</sup></span>
                        <input type="date" name="tanggal_pindah" value="{{ old('tanggal_pindah') }}" required>
                        @if ($modalContext === 'migrantCreateModal') @error('tanggal_pindah') <span class="form-error">{{ $message }}</span> @enderror @endif
                    </label>
                    <label class="form-field form-field--full">
                        <span>Alasan Pindah</span>
                        <textarea name="alasan_pindah" rows="3" placeholder="Jelaskan alasan kepindahan...">{{ old('alasan_pindah') }}</textarea>
                    </label>
                    <label class="form-field form-field--full">
                        <span>Alamat Tujuan</span>
                        <textarea name="alamat_tujuan" rows="3" placeholder="Alamat lengkap tujuan pindah...">{{ old('alamat_tujuan') }}</textarea>
                    </label>
                    <label class="form-field">
                        <span>Desa Tujuan</span>
                        <input type="text" name="desa_tujuan" value="{{ old('desa_tujuan') }}" placeholder="Nama desa">
                    </label>
                    <label class="form-field">
                        <span>Kecamatan Tujuan</span>
                        <input type="text" name="kecamatan_tujuan" value="{{ old('kecamatan_tujuan') }}" placeholder="Nama kecamatan">
                    </label>
                    <label class="form-field">
                        <span>Kabupaten Tujuan</span>
                        <input type="text" name="kabupaten_tujuan" value="{{ old('kabupaten_tujuan') }}" placeholder="Nama kabupaten">
                    </label>
                    <label class="form-field">
                        <span>Provinsi Tujuan</span>
                        <input type="text" name="provinsi_tujuan" value="{{ old('provinsi_tujuan') }}" placeholder="Nama provinsi">
                    </label>
                    <label class="form-field form-field--full">
                        <span>Keterangan</span>
                        <textarea name="keterangan" rows="2" placeholder="Catatan tambahan (opsional)...">{{ old('keterangan') }}</textarea>
                    </label>
                </div>
            </div>
            <footer class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                <button type="submit" class="primary-btn">
                    <i class="fas fa-save"></i>
                    <span>Simpan Catatan</span>
                </button>
            </footer>
        </form>
    </div>
</div>

{{-- Detail Modal Template (populated by script) --}}
<div class="dialog-backdrop" id="migrantDetailModal" aria-hidden="true">
    <div class="dialog dialog--detail" role="dialog" aria-modal="true">
        <header class="dialog__header">
            <div class="dialog__header-icon dialog__header-icon--info">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="dialog__header-text">
                <h2>Detail Kepindahan</h2>
                <p>Informasi lengkap data perpindahan penduduk.</p>
            </div>
            <button type="button" class="dialog__close" data-modal-close><i class="fas fa-times"></i></button>
        </header>
        <div class="dialog__body" id="detailContent">
            {{-- Content injected via JS --}}
        </div>
        <footer class="dialog__footer">
            <button type="button" class="ghost-btn" data-modal-close>Tutup</button>
        </footer>
    </div>
</div>

@foreach ($records as $record)
    {{-- Edit Modal --}}
    <div class="dialog-backdrop" id="migrantEditModal-{{ $record->id }}" aria-hidden="true">
        <div class="dialog dialog--form" role="dialog" aria-modal="true">
            <header class="dialog__header">
                <div class="dialog__header-icon dialog__header-icon--warning">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <div class="dialog__header-text">
                    <h2>Edit Catatan Kepindahan</h2>
                    <p>Perbarui data perpindahan untuk <strong>{{ $record->penduduk?->nama }}</strong>.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close><i class="fas fa-times"></i></button>
            </header>
            <form method="POST" action="{{ route('admin.penduduk-pindah.update', $record->id) }}" class="dialog__form">
                @csrf
                @method('PUT')
                <input type="hidden" name="form_context" value="migrantEditModal-{{ $record->id }}">
                <div class="dialog__body">
                    <div class="settings-form-grid two-columns">
                        <label class="form-field">
                            <span>Penduduk</span>
                            <input type="text" value="{{ $record->penduduk?->nama }}" disabled>
                            <input type="hidden" name="penduduk_id" value="{{ $record->penduduk_id }}">
                        </label>
                        <label class="form-field">
                            <span>Tanggal Pindah <sup>*</sup></span>
                            <input type="date" name="tanggal_pindah" value="{{ old('tanggal_pindah', optional($record->tanggal_pindah)->toDateString()) }}" required>
                        </label>
                        <label class="form-field form-field--full">
                            <span>Alasan Pindah</span>
                            <textarea name="alasan_pindah" rows="3" placeholder="Jelaskan alasan kepindahan...">{{ old('alasan_pindah', $record->alasan_pindah) }}</textarea>
                        </label>
                        <label class="form-field form-field--full">
                            <span>Alamat Tujuan</span>
                            <textarea name="alamat_tujuan" rows="3" placeholder="Alamat lengkap tujuan pindah...">{{ old('alamat_tujuan', $record->alamat_tujuan) }}</textarea>
                        </label>
                        <label class="form-field">
                            <span>Desa Tujuan</span>
                            <input type="text" name="desa_tujuan" value="{{ old('desa_tujuan', $record->desa_tujuan) }}" placeholder="Nama desa">
                        </label>
                        <label class="form-field">
                            <span>Kecamatan Tujuan</span>
                            <input type="text" name="kecamatan_tujuan" value="{{ old('kecamatan_tujuan', $record->kecamatan_tujuan) }}" placeholder="Nama kecamatan">
                        </label>
                        <label class="form-field">
                            <span>Kabupaten Tujuan</span>
                            <input type="text" name="kabupaten_tujuan" value="{{ old('kabupaten_tujuan', $record->kabupaten_tujuan) }}" placeholder="Nama kabupaten">
                        </label>
                        <label class="form-field">
                            <span>Provinsi Tujuan</span>
                            <input type="text" name="provinsi_tujuan" value="{{ old('provinsi_tujuan', $record->provinsi_tujuan) }}" placeholder="Nama provinsi">
                        </label>
                        <label class="form-field form-field--full">
                            <span>Keterangan</span>
                            <textarea name="keterangan" rows="2" placeholder="Catatan tambahan (opsional)...">{{ old('keterangan', $record->keterangan) }}</textarea>
                        </label>
                    </div>
                </div>
                <footer class="dialog__footer">
                    <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                    <button type="submit" class="primary-btn">
                        <i class="fas fa-save"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </footer>
            </form>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div class="dialog-backdrop" id="migrantDeleteModal-{{ $record->id }}" aria-hidden="true">
        <div class="dialog dialog--confirm" role="dialog" aria-modal="true">
            <header class="dialog__header">
                <div class="dialog__header-icon dialog__header-icon--danger">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="dialog__header-text">
                    <h2>Konfirmasi Hapus</h2>
                    <p>Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close><i class="fas fa-times"></i></button>
            </header>
            <div class="dialog__body">
                <p>Apakah Anda yakin ingin menghapus catatan kepindahan untuk <strong>{{ $record->penduduk?->nama }}</strong>?</p>
            </div>
            <footer class="dialog__footer">
                <button type="button" class="ghost-btn" data-modal-close>Batal</button>
                <form method="POST" action="{{ route('admin.penduduk-pindah.destroy', $record->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="danger-btn">
                        <i class="fas fa-trash"></i>
                        <span>Hapus Permanen</span>
                    </button>
                </form>
            </footer>
        </div>
    </div>
@endforeach
