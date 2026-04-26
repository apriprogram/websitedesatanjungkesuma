@extends('admin.layouts.app')

@section('title', 'Pusat Bantuan')

@push('head')
    <style>
        :root {
            --help-primary: #2563eb;
            --help-accent: #1d4ed8;
            --help-surface: #ffffff;
            --help-border: #e5e7eb;
            --help-muted: #6b7280;
            --help-bg: #f8fafc;
        }

        .help-hero {
            background: radial-gradient(circle at 10% 20%, #e0edff, transparent 25%), radial-gradient(circle at 80% 0%, #dbeafe, transparent 22%), linear-gradient(135deg, #f8fafc, #eef2ff);
            border: 1px solid #e0e7ff;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 15px 40px rgba(37, 99, 235, 0.12);
            margin-bottom: 24px;
        }

        .help-hero h1 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .help-hero p {
            color: var(--help-muted);
            font-size: 1rem;
            margin: 0;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        .help-card {
            background: var(--help-surface);
            border: 1px solid var(--help-border);
            border-radius: 16px;
            padding: 20px;
        }

        .help-card h2 {
            margin: 0 0 8px;
            font-size: 1.15rem;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .help-card p {
            margin: 0 0 14px;
            color: var(--help-muted);
            font-size: 0.95rem;
        }

        .help-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #e0edff;
            color: var(--help-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .help-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            border-radius: 12px;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: #fff;
            font-weight: 700;
            border: none;
            text-decoration: none;
            box-shadow: 0 12px 30px rgba(22, 196, 107, 0.28);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .help-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 40px rgba(22, 196, 107, 0.32);
        }

        .help-subtext {
            margin-top: 10px;
            color: var(--help-muted);
            font-size: 0.9rem;
        }

        .help-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .help-field label {
            font-weight: 600;
            font-size: 0.95rem;
            color: #0f172a;
            margin-bottom: 6px;
            display: block;
        }

        .help-field input,
        .help-field textarea {
            width: 100%;
            border: 1px solid var(--help-border);
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 0.95rem;
            background: #f9fafb;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .help-field input:focus,
        .help-field textarea:focus {
            outline: none;
            border-color: var(--help-primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .help-field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .help-nav {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .help-nav a {
            color: var(--help-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .help-nav a:hover {
            text-decoration: underline;
        }

        .help-form button {
            align-self: flex-start;
            padding: 11px 16px;
            border-radius: 12px;
            border: 1px solid var(--help-border);
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.16);
        }

        .help-form button:hover {
            background: #111827;
            transform: translateY(-1px);
        }

        .help-wrapper {
            background: var(--help-bg);
            padding: 18px;
            border-radius: 16px;
            border: 1px solid var(--help-border);
            margin-top: 24px;
        }
        .help-alert {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 14px;
            border: 1px solid;
            font-size: 0.95rem;
        }
        .help-alert ul {
            margin: 0;
            padding-left: 18px;
        }
        .help-alert--success {
            background: #ecfdf3;
            border-color: #22c55e;
            color: #166534;
        }
        .help-alert--error {
            background: #fef2f2;
            border-color: #ef4444;
            color: #991b1b;
        }

        .help-hero--logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #034afc;
            color: #fff;
            border-color: #034afc;
        }
        .help-hero--logo .help-hero__brand {
            color: #fff;
        }
        .help-hero__logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            border-radius: 16px;
        }
        .help-hero__brand {
            font-weight: 600;
            font-size: 1.2rem;
            color: #0f172a;
            letter-spacing: 0.02em;
        }

        /* Dark mode overrides */
        body.dark-mode :root {
            --help-primary: #60a5fa;
            --help-accent: #3b82f6;
            --help-surface: #0f172a;
            --help-border: #1f2937;
            --help-muted: #cbd5e1;
            --help-bg: #0b1221;
        }
        body.dark-mode .help-wrapper {
            background: linear-gradient(180deg, #0b1221, #0f172a);
            border-color: #1f2937;
        }
        body.dark-mode .help-hero {
            background: radial-gradient(circle at 10% 20%, rgba(96, 165, 250, 0.16), transparent 25%),
                        radial-gradient(circle at 80% 0%, rgba(59, 130, 246, 0.12), transparent 22%),
                        linear-gradient(135deg, #0b1324, #0e1a30);
            border-color: #1f2937;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
        }
        body.dark-mode .help-hero h1 {
            color: #e5e7eb;
        }
        body.dark-mode .help-hero p {
            color: #cbd5e1;
        }
        body.dark-mode .help-card {
            background: #0f172a;
            border-color: #1f2937;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        body.dark-mode .help-card h2 {
            color: #e5e7eb;
        }
        body.dark-mode .help-card p,
        body.dark-mode .help-subtext {
            color: #cbd5e1;
        }
        body.dark-mode .help-icon {
            background: rgba(96, 165, 250, 0.18);
            color: #bfdbfe;
        }
        body.dark-mode .help-btn {
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.32);
        }
        body.dark-mode .help-hero__brand {
            color: #e5e7eb;
        }
        body.dark-mode .help-field label {
            color: #e5e7eb;
        }
        body.dark-mode .help-field input,
        body.dark-mode .help-field textarea {
            background: #0b1221;
            border-color: #1f2937;
            color: #e5e7eb;
        }
        body.dark-mode .help-field input::placeholder,
        body.dark-mode .help-field textarea::placeholder {
            color: #94a3b8;
        }
        body.dark-mode .help-field input:focus,
        body.dark-mode .help-field textarea:focus {
            background: #0f172a;
            border-color: var(--help-primary);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.28);
        }
        body.dark-mode .help-form button {
            background: #111827;
            border-color: #1f2937;
            color: #e5e7eb;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45);
        }
        body.dark-mode .help-form button:hover {
            background: #0b1221;
        }
        body.dark-mode .help-nav a {
            color: #93c5fd;
        }
        body.dark-mode .help-nav span {
            color: #64748b;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-container">
        <header class="main-header">
            <div class="header-controls">
                <div class="header-cluster header-cluster-left">
                    <button class="header-icon sidebar-collapse-btn" id="sidebarCollapseBtn" aria-label="Sembunyikan sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="header-icon sidebar-trigger" id="sidebarExpandBtn" aria-label="Tampilkan sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <span class="header-title-text">Pusat Bantuan</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Bantuan</span>
                <i class="fas fa-chevron-right"></i>
                <span>Pusat Bantuan</span>
            </nav>
        </header>

        <section class="page-title">
            <div>
                <h1>Pusat Bantuan</h1>
                <p>Kami siap membantu kebutuhan Anda</p>
            </div>
        </section>

        <div class="help-wrapper">
            @if (session('status'))
                <div class="help-alert help-alert--success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="help-alert help-alert--error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="help-hero help-hero--logo" aria-hidden="true">
                <img src="{{ asset('img/Logo/logo_apriprogram.jpg') }}" alt="Logo apriprogram" class="help-hero__logo">
                <span class="help-hero__brand">Apri Program</span>
            </div>

            <div class="help-grid">
                <div class="help-card">
                    <h2><span class="help-icon"><i class="fas fa-headset"></i></span> Hubungi Admin</h2>
                    <p>Butuh respon cepat? Klik tombol di bawah untuk langsung terhubung via WhatsApp.</p>
                    <a class="help-btn" href="https://wa.me/6288706884957" target="_blank" rel="noopener">
                        <i class="fab fa-whatsapp"></i>
                        <span>Chat WhatsApp Admin</span>
                    </a>
                    <div class="help-subtext">Buka chat WhatsApp</div>
                </div>

                <div class="help-card">
                    <h2><span class="help-icon"><i class="fas fa-envelope-open-text"></i></span> Kirim Pesan</h2>
                    <p>Kirimkan detail kebutuhan Anda agar kami bisa menindaklanjuti lebih cepat.</p>
                    <form class="help-form" method="POST" action="{{ route('admin.help-center.send') }}">
                        @csrf
                        <div class="help-field">
                            <label for="help-name">Nama</label>
                            <input id="help-name" type="text" name="name" placeholder="Masukkan nama Anda"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="help-field">
                            <label for="help-email">Email</label>
                            <input id="help-email" type="email" name="email"
                                value="{{ old('email', 'apriyansah74dd@gmail.com') }}" placeholder="nama@email.com"
                                required>
                        </div>
                        <div class="help-field">
                            <label for="help-message">Pesan</label>
                            <textarea id="help-message" name="message" placeholder="Tuliskan kebutuhan atau pertanyaan Anda"
                                required>{{ old('message') }}</textarea>
                        </div>
                        <button type="submit">Kirim Pesan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
