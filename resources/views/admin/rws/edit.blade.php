@extends('admin.layouts.app')

@section('title', 'Edit RW')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Edit RW</h1>
            <p>Perbarui informasi RW untuk memastikan struktur wilayah tetap sesuai.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.rws.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Detail RW</h2>
                <p>Sesuaikan nomor, kode, atau dusun induk jika terjadi perubahan.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.rws.update', $rw) }}" class="panel-form">
            @csrf
            @method('PUT')
            <div class="settings-form-grid">
                <label class="form-field">
                    <span>Dusun <sup>*</sup></span>
                    <select name="dusun_id" required>
                        <option value="">Pilih dusun</option>
                        @foreach ($dusuns as $dusun)
                            <option value="{{ $dusun->id }}" @selected(old('dusun_id', $rw->dusun_id) == $dusun->id)>{{ $dusun->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>Nomor RW <sup>*</sup></span>
                    <input type="text" name="nomor" value="{{ old('nomor', $rw->nomor) }}" maxlength="10" required>
                </label>
                <label class="form-field">
                    <span>Kode RW</span>
                    <input type="text" name="kode" value="{{ old('kode', $rw->kode) }}" maxlength="20">
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.rws.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan Perubahan</button>
            </div>
        </form>
    </article>
@endsection
