@extends('admin.layouts.app')

@section('title', 'Tambah Kartu Keluarga')

@section('content')
    @include('admin.partials.alerts')

    <section class="page-title">
        <div>
            <h1>Tambah Kartu Keluarga</h1>
            <p>Rekam nomor KK baru beserta kepala keluarga dan informasi wilayah.</p>
        </div>
        <div class="title-actions">
            <a href="{{ route('admin.keluargas.index') }}" class="outline-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </section>

    <article class="panel">
        <header class="panel-header">
            <div>
                <h2>Informasi Keluarga</h2>
                <p>Isikan nomor KK dan data kepala keluarga sesuai dokumen resmi.</p>
            </div>
        </header>
        <form method="POST" action="{{ route('admin.keluargas.store') }}" class="panel-form">
            @csrf
            <div class="settings-form-grid two-columns">
                <label class="form-field">
                    <span>Nomor KK <sup>*</sup></span>
                    <input type="text" name="no_kk" value="{{ old('no_kk') }}" maxlength="30" required>
                </label>
                <label class="form-field">
                    <span>NIK Kepala Keluarga</span>
                    <input type="text" name="kepala_nik" value="{{ old('kepala_nik') }}" maxlength="20">
                </label>
                <label class="form-field form-field--full">
                    <span>Alamat</span>
                    <textarea name="alamat" rows="3">{{ old('alamat') }}</textarea>
                </label>
                <label class="form-field">
                    <span>Dusun</span>
                    <select name="dusun_id">
                        <option value="">Pilih dusun</option>
                        @foreach ($dusunOptions as $dusun)
                            <option value="{{ $dusun->id }}" @selected(old('dusun_id') == $dusun->id)>{{ $dusun->nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>RW</span>
                    <select name="rw_id">
                        <option value="">Pilih RW</option>
                        @foreach ($rwOptions as $rw)
                            <option value="{{ $rw->id }}" @selected(old('rw_id') == $rw->id)>
                                Dusun {{ $rw->dusun?->nama }} &mdash; RW {{ $rw->nomor }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="form-field">
                    <span>RT</span>
                    <select name="rt_id">
                        <option value="">Pilih RT</option>
                        @foreach ($rtOptions as $rt)
                            <option value="{{ $rt->id }}" @selected(old('rt_id') == $rt->id)>
                                RW {{ $rt->rw?->nomor }} &mdash; RT {{ $rt->nomor }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.keluargas.index') }}" class="outline-btn">Batal</a>
                <button type="submit" class="primary-btn">Simpan</button>
            </div>
        </form>
    </article>
@endsection
