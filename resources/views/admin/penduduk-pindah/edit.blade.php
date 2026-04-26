@extends('admin.layouts.app')

@section('title', 'Edit Catatan Penduduk Pindah')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Edit Catatan Penduduk Pindah</h1>
            <p>Perbarui detail kepindahan bila terdapat perubahan informasi.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.penduduk-pindah.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Informasi Kepindahan</h2>
                <p>Sesuaikan data agar status penduduk tetap konsisten.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.penduduk-pindah.update', $record) }}" class="panel-form">
            @csrf
            @method('PUT')
            <div class="settings-form-grid two-columns">
                <label class="form-field">
                    <span>Penduduk <sup>*</sup></span>
                    <select name="penduduk_id" required>
                        <option value="">Pilih penduduk</option>
                        @foreach ($pendudukOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('penduduk_id', $record->penduduk_id) == $option->id)>
                                {{ $option->nama }} &mdash; NIK {{ $option->nik }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>Tanggal Pindah <sup>*</sup></span>
                    <input type="date" name="tanggal_pindah" value="{{ old('tanggal_pindah', optional($record->tanggal_pindah)->toDateString()) }}" required>
                </label>
                <label class="form-field form-field--full">
                    <span>Alasan Pindah</span>
                    <textarea name="alasan_pindah" rows="3">{{ old('alasan_pindah', $record->alasan_pindah) }}</textarea>
                </label>
                <label class="form-field form-field--full">
                    <span>Alamat Tujuan</span>
                    <textarea name="alamat_tujuan" rows="3">{{ old('alamat_tujuan', $record->alamat_tujuan) }}</textarea>
                </label>
                <label class="form-field">
                    <span>Desa Tujuan</span>
                    <input type="text" name="desa_tujuan" value="{{ old('desa_tujuan', $record->desa_tujuan) }}" maxlength="100">
                </label>
                <label class="form-field">
                    <span>Kecamatan Tujuan</span>
                    <input type="text" name="kecamatan_tujuan" value="{{ old('kecamatan_tujuan', $record->kecamatan_tujuan) }}" maxlength="100">
                </label>
                <label class="form-field">
                    <span>Kabupaten Tujuan</span>
                    <input type="text" name="kabupaten_tujuan" value="{{ old('kabupaten_tujuan', $record->kabupaten_tujuan) }}" maxlength="100">
                </label>
                <label class="form-field">
                    <span>Provinsi Tujuan</span>
                    <input type="text" name="provinsi_tujuan" value="{{ old('provinsi_tujuan', $record->provinsi_tujuan) }}" maxlength="100">
                </label>
                <label class="form-field form-field--full">
                    <span>Keterangan Tambahan</span>
                    <textarea name="keterangan" rows="2">{{ old('keterangan', $record->keterangan) }}</textarea>
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.penduduk-pindah.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan Perubahan</button>
            </div>
        </form>
    </article>
@endsection
