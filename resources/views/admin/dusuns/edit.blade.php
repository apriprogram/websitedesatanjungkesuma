@extends('admin.layouts.app')

@section('title', 'Edit Dusun')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Edit Dusun</h1>
            <p>Perbarui informasi dusun untuk menjaga data wilayah tetap akurat.</p>
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
                <p>Sesuaikan nama atau kode dusun bila terdapat perubahan administrasi.</p>
            </div>
        </header>

        <form method="POST" action="{{ route('admin.dusuns.update', $dusun) }}" class="panel-form">
            @csrf
            @method('PUT')
            <div class="settings-form-grid">
                <label class="form-field">
                    <span>Nama Dusun <sup>*</sup></span>
                    <input type="text" name="nama" value="{{ old('nama', $dusun->nama) }}" maxlength="100" required>
                </label>
                <label class="form-field">
                    <span>Kode Dusun</span>
                    <input type="text" name="kode" value="{{ old('kode', $dusun->kode) }}" maxlength="20">
                </label>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.dusuns.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan Perubahan</button>
            </div>
        </form>
    </article>
@endsection
