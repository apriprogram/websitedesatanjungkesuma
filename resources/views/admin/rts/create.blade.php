@extends('admin.layouts.app')

@section('title', 'Tambah RT')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Tambah RT</h1>
            <p>Lengkapi data RT sesuai RW dan dusun tempat wilayah tersebut berada.</p>
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
                <p>Pilih RW induk dan masukkan nomor serta kode RT.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.rts.store') }}" class="panel-form">
            @csrf
            <div class="settings-form-grid">
                <label class="form-field">
                    <span>RW <sup>*</sup></span>
                    <select name="rw_id" required>
                        <option value="">Pilih RW</option>
                        @foreach ($rws as $rw)
                            <option value="{{ $rw->id }}" @selected(old('rw_id') == $rw->id)>
                                Dusun {{ $rw->dusun?->nama }} &mdash; RW {{ $rw->nomor }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>Nomor RT <sup>*</sup></span>
                    <input type="text" name="nomor" value="{{ old('nomor') }}" maxlength="10" required>
                </label>
                <label class="form-field">
                    <span>Kode RT</span>
                    <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20">
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.rts.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan</button>
            </div>
        </form>
    </article>
@endsection
