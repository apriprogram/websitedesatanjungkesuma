@extends('admin.layouts.app')

@section('title', 'Catat Penduduk Meninggal')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Catat Penduduk Meninggal</h1>
            <p>Isi detail kematian untuk memperbarui status dasar penduduk.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.penduduk-meninggal.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Informasi Kematian</h2>
                <p>Pilih penduduk dan lengkapi penyebab serta lokasi meninggal.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.penduduk-meninggal.store') }}" class="panel-form">
            @csrf
            <div class="settings-form-grid two-columns">
                <label class="form-field">
                    <span>Penduduk <sup>*</sup></span>
                    <select name="penduduk_id" required>
                        <option value="">Pilih penduduk</option>
                        @foreach ($pendudukOptions as $option)
                            <option value="{{ $option->id }}" @selected(old('penduduk_id') == $option->id)>
                                {{ $option->nama }} &mdash; NIK {{ $option->nik }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>Tanggal Meninggal <sup>*</sup></span>
                    <input type="date" name="tanggal_meninggal" value="{{ old('tanggal_meninggal') }}" required>
                </label>
                <label class="form-field">
                    <span>Penyebab</span>
                    <input type="text" name="penyebab" value="{{ old('penyebab') }}" maxlength="150">
                </label>
                <label class="form-field">
                    <span>Tempat Meninggal</span>
                    <input type="text" name="tempat_meninggal" value="{{ old('tempat_meninggal') }}" maxlength="150">
                </label>
                <label class="form-field">
                    <span>No. Akta Kematian</span>
                    <input type="text" name="akta_meninggal_no" value="{{ old('akta_meninggal_no') }}" maxlength="100">
                </label>
                <label class="form-field form-field--full">
                    <span>Keterangan Tambahan</span>
                    <textarea name="keterangan" rows="3">{{ old('keterangan') }}</textarea>
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.penduduk-meninggal.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan</button>
            </div>
        </form>
    </article>
@endsection
