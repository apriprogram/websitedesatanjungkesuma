<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal Admin Desa Tanjung Kesuma</title>
    <link rel="icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <meta name="description" content="Masuk untuk mengelola konten digital dan layanan publik Desa Tanjung Kesuma.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
</head>

<body class="login-page login-modern">
    <main class="auth-viewport">
        <section class="auth-card" aria-label="Portal Admin Desa Tanjung Kesuma">
            <div class="auth-card__inner" role="form">
                <div class="auth-card__header">
                    <div class="auth-icon" aria-hidden="true">
                        <img src="{{ asset('img/Logo/logo_lampung_timur.png') }}" alt="Logo Desa Tanjung Kesuma">
                    </div>
                    <h1 class="auth-title">Login Admin</h1>
                    <p class="auth-description">Silakan masukkan alamat email dan kata sandi Anda di bawah ini untuk
                        masuk</p>
                </div>

                @if (session('status'))
                    <div class="login-alert login-alert--success" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="login-alert login-alert--error" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="auth-form" id="adminLoginForm">
                    @csrf
                    <div class="form-field @error('email') has-error @enderror">
                        <label class="field-label" for="email">
                            <span>Email</span>
                            <span class="required" aria-hidden="true">*</span>
                        </label>
                        <div class="input-shell">
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                placeholder="Alamat email" autocomplete="email" required class="form-input modern-input"
                                @error('email') aria-invalid="true" aria-describedby="emailError"
                                data-server-error="true" @enderror>
                        </div>
                        @error('email')
                            <p class="field-feedback" id="emailError" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-field @error('password') has-error @enderror">
                        <label class="field-label" for="password">
                            <span>Password</span>
                            <span class="required" aria-hidden="true">*</span>
                        </label>
                        @php
                            $passwordDescribedBy = 'passwordHelper';
                            if ($errors->has('password')) {
                                $passwordDescribedBy .= ' passwordError';
                            }
                        @endphp
                        <div class="input-shell input-shell--with-action">
                            <input type="password" id="password" name="password" placeholder="Kata sandi"
                                autocomplete="current-password" required aria-describedby="{{ $passwordDescribedBy }}"
                                class="form-input modern-input" @error('password') aria-invalid="true"
                                data-server-error="true" @enderror>
                            <button type="button" class="field-visibility"
                                aria-label="Tampilkan atau sembunyikan password" data-toggle-password="password">
                                <svg class="icon-show" viewBox="0 0 24 24" focusable="false">
                                    <path
                                        d="M12 5c5.05 0 8.93 3.36 10.43 6.68a1 1 0 0 1 0 .84C20.93 15.84 17.05 19 12 19S3.07 15.64 1.57 12.32a1 1 0 0 1 0-.84C3.07 8.16 6.95 5 12 5zm0 12c3.53 0 6.64-2.26 8.06-5-1.42-2.74-4.53-5-8.06-5S5.36 9.26 3.94 12c1.42 2.74 4.53 5 8.06 5zm0-8a3 3 0 1 1 0 6 3 3 0 0 1 0-6z" />
                                </svg>
                                <svg class="icon-hide" viewBox="0 0 24 24" focusable="false">
                                    <path
                                        d="M3.22 2.47a1 1 0 0 1 1.41 0l17 17a1 1 0 0 1-1.41 1.41l-3.21-3.21A11 11 0 0 1 12 19c-5.05 0-8.93-3.36-10.43-6.68a1 1 0 0 1 0-.84 12.91 12.91 0 0 1 3.34-4.27L3.22 3.88a1 1 0 0 1 0-1.41zM12 7a5 5 0 0 1 5 5 4.94 4.94 0 0 1-.38 1.9l-1.53-1.53A3 3 0 0 0 11.63 9l-1.6-1.6A5 5 0 0 1 12 7zm-8.06 5c1.42 2.74 4.53 5 8.06 5a8.9 8.9 0 0 0 3.37-.66l-1.66-1.66a3 3 0 0 1-4.23-4.23L8.3 8.7A11 11 0 0 0 3.94 12z" />
                                </svg>
                            </button>
                        </div>
                        <ul class="password-hints" id="passwordHelper">
                            <li data-rule="length">Minimal 8 karakter</li>
                            <li data-rule="upper-lower">Huruf besar & kecil</li>
                            <li data-rule="number">Memuat angka</li>
                            <li data-rule="symbol">Memuat simbol khusus</li>
                        </ul>
                        @error('password')
                            <p class="field-feedback" id="passwordError" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="login-actions">
                        <label class="remember-wrap" for="remember">
                            <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                            <span class="remember-text">Ingat saya</span>
                        </label>

                        @php
                            $adminWhatsapp = '6281234567890';
                        @endphp
                        <a class="login-link" href="https://wa.me/{{ $adminWhatsapp }}" target="_blank" rel="noopener">
                            Lupa password?
                        </a>
                    </div>

                    <button type="submit" class="auth-submit">Sign in</button>
                </form>
            </div>
        </section>
    </main>
    <script src="{{ asset('assets/js/login.js') }}" defer></script>
</body>

</html>
