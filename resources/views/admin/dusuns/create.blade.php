@extends('admin.layouts.app')

@section('title', 'Tambah Dusun')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Tambah Dusun</h1>
            <p>Lengkapi informasi dusun baru untuk wilayah administrasi desa.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.dusuns.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Detail Dusun</h2>
                <p>Pastikan penamaan dan kode dusun sesuai dokumen resmi.</p>
            </div>
        </header>

        <form method="POST" action="{{ route('admin.dusuns.store') }}" class="panel-form">
            @csrf
            <div class="settings-form-grid">
                <label class="form-field">
                    <span>Nama Dusun <sup>*</sup></span>
                    <input type="text" name="nama" value="{{ old('nama') }}" maxlength="100" required>
                </label>
                <label class="form-field">
                    <span>Kode Dusun</span>
                    <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20">
                </label>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.dusuns.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan</button>
            </div>
        </form>
    </article>
@endsection
