@extends('admin.layouts.app')

@section('title', 'Edit RT')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Edit RT</h1>
            <p>Sesuaikan informasi RT jika terjadi perubahan pada nomor, kode, atau RW induk.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.rts.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Detail RT</h2>
                <p>Perbarui data RT untuk menjaga struktur wilayah tetap konsisten.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.rts.update', $rt) }}" class="panel-form">
            @csrf
            @method('PUT')
            <div class="settings-form-grid">
                <label class="form-field">
                    <span>RW <sup>*</sup></span>
                    <select name="rw_id" required>
                        <option value="">Pilih RW</option>
                        @foreach ($rws as $rwOption)
                            <option value="{{ $rwOption->id }}" @selected(old('rw_id', $rt->rw_id) == $rwOption->id)>
                                Dusun {{ $rwOption->dusun?->nama }} &mdash; RW {{ $rwOption->nomor }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>Nomor RT <sup>*</sup></span>
                    <input type="text" name="nomor" value="{{ old('nomor', $rt->nomor) }}" maxlength="10" required>
                </label>
                <label class="form-field">
                    <span>Kode RT</span>
                    <input type="text" name="kode" value="{{ old('kode', $rt->kode) }}" maxlength="20">
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.rts.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan Perubahan</button>
            </div>
        </form>
    </article>
@endsection
