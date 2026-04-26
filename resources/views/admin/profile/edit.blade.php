@extends('admin.layouts.app')

@section('title', 'Edit Profil')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-profile.css') }}">
@endpush

@section('content')
    <div class="dashboard-container">
        <header class="main-header">
            <div class="header-controls">
                <div class="header-cluster header-cluster-left">
                    <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn"
                        aria-label="Sembunyikan sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="header-icon sidebar-trigger" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <span class="header-title-text">Edit Profil</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Pengaturan</span>
                <i class="fas fa-chevron-right"></i>
                <span>Profil Saya</span>
            </nav>
        </header>

        <section class="page-title">
            <div>
                <h1>Edit Profil</h1>
                <p>Perbarui informasi profil akun dan alamat email Anda.</p>
            </div>
        </section>

        <main class="main-content">
            <div class="content-wrapper">
                <div class="profile-card">
                    <form method="post" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data"
                        class="profile-layout">
                        @csrf
                        @method('patch')

                        <div class="profile-upload">
                            <figure class="profile-upload__figure">
                                <img id="avatarPreview" src="{{ $user->avatar_url }}" alt="Foto profil" class="profile-upload__image">
                                <button type="button" class="profile-upload__add" id="avatarPlusTrigger" aria-label="Unggah foto">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </figure>
                            <div class="profile-upload__meta">
                                <strong>Upload Image</strong>
                                <span>Max file size: 1MB</span>
                            </div>
                            <input id="avatarInput" type="file" name="gambar" accept="image/*" data-preview="avatarPreview" hidden>
                            <input type="hidden" name="remove_gambar" value="0" id="removeGambarInput">
                            <div class="profile-upload__actions">
                                <label class="ghost-btn profile-upload__button" for="avatarInput">
                                    <i class="fas fa-image"></i>
                                    Add Image
                                </label>
                                <button type="button" class="outline-btn profile-upload__button profile-upload__remove" id="removeAvatarBtn">
                                    <i class="fas fa-trash-alt"></i>
                                    Hapus foto
                                </button>
                            </div>
                        </div>

                        <div class="profile-form-col">
                            <div class="profile-form__grid">
                                <label class="form-field">
                                    <span>Nama Lengkap</span>
                                    <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required autofocus
                                        autocomplete="name">
                                    @error('nama')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </label>

                                <label class="form-field">
                                    <span>Email</span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                        autocomplete="username">
                                    @error('email')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </label>

                                <label class="form-field">
                                    <span>Nomor HP</span>
                                    <input type="text" name="nomor_hp" value="{{ old('nomor_hp', $user->nomor_hp) }}">
                                    @error('nomor_hp')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </label>
                            </div>

                            <div class="profile-actions-row">
                                <button type="button" class="outline-btn" onclick="window.history.back()">Cancel</button>
                                <button type="submit" class="primary-btn">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    @php
        $profileToasts = [];
        if (session('status') === 'profile-updated') {
            $profileToasts[] = [
                'title' => 'Profil berhasil diperbarui',
                'message' => 'Perubahan sudah disimpan.',
                'variant' => 'success',
            ];
        }
        if (session('status') && session('status_variant') === 'error') {
            $profileToasts[] = [
                'title' => 'Gagal menyimpan profil',
                'message' => session('status'),
                'variant' => 'error',
            ];
        }
    @endphp

    @if (! empty($profileToasts))
        <div class="toast-stack" id="profileToastStack" role="region" aria-live="polite">
            @foreach ($profileToasts as $toast)
                @php
                    $variant = $toast['variant'] ?? 'neutral';
                    $icons = [
                        'success' => 'fa-check',
                        'warning' => 'fa-triangle-exclamation',
                        'error' => 'fa-circle-xmark',
                        'info' => 'fa-circle-info',
                        'neutral' => 'fa-circle-info',
                    ];
                    $icon = $icons[$variant] ?? $icons['neutral'];
                @endphp
                <article class="toast" data-toast data-variant="{{ $variant }}">
                    <div class="toast__icon" aria-hidden="true">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="toast__content">
                        <strong>{{ $toast['title'] }}</strong>
                        @if (! empty($toast['message']))
                            <p>{{ $toast['message'] }}</p>
                        @endif
                    </div>
                    <button type="button" class="toast__close" data-toast-close aria-label="Tutup notifikasi">
                        <i class="fas fa-times"></i>
                    </button>
                    <span class="toast__progress" aria-hidden="true"></span>
                </article>
            @endforeach
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.querySelector('input[type="file"][data-preview]');
            const preview = document.getElementById('avatarPreview');
            const removeBtn = document.getElementById('removeAvatarBtn');
            const removeInput = document.getElementById('removeGambarInput');
            const plusTrigger = document.getElementById('avatarPlusTrigger');
            const defaultSrc = "{{ asset('assets/default/user.jpg') }}";

            if (fileInput && preview) {
                fileInput.addEventListener('change', () => {
                    const [file] = fileInput.files;
                    if (file) {
                        preview.src = URL.createObjectURL(file);
                        removeInput.value = '0';
                    }
                });
            }

            if (removeBtn && preview) {
                removeBtn.addEventListener('click', () => {
                    fileInput.value = '';
                    preview.src = defaultSrc;
                    removeInput.value = '1';
                });
            }

            plusTrigger?.addEventListener('click', () => {
                fileInput?.click();
            });

            // toast handling (matching penduduk style)
            const toastStack = document.getElementById('profileToastStack');
            if (toastStack) {
                const TOAST_DURATION = 5000;
                const dismissToast = (toast) => {
                    if (!toast || toast.classList.contains('is-leaving')) return;
                    toast.classList.add('is-leaving');
                    window.setTimeout(() => toast.remove(), 250);
                };

                toastStack.querySelectorAll('[data-toast]').forEach((toast) => {
                    const closeButton = toast.querySelector('[data-toast-close]');
                    let timerId;
                    const startTimer = () => {
                        timerId = window.setTimeout(() => dismissToast(toast), TOAST_DURATION);
                        toast.classList.remove('is-paused');
                    };
                    const stopTimer = () => {
                        if (timerId) {
                            window.clearTimeout(timerId);
                            timerId = null;
                        }
                        toast.classList.add('is-paused');
                    };

                    toast.addEventListener('mouseenter', stopTimer);
                    toast.addEventListener('mouseleave', startTimer);
                    closeButton?.addEventListener('click', () => {
                        stopTimer();
                        dismissToast(toast);
                    });

                    requestAnimationFrame(() => toast.classList.add('is-visible'));
                    startTimer();
                });
            }
        });
    </script>
@endsection
