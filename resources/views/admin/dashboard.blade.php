@extends('admin.layouts.app')

@section('title', 'Dashboard Admin Desa Tanjung Kesuma')

@push('head')
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --bg-card: #ffffff;
            --bg-page: #f3f4f6;
            --border-color: #e5e7eb;
        }

        .dashboard-container {
            padding: 0;
            background-color: transparent;
            min-height: 100vh;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--text-secondary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            flex-direction: column;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.blue {
            background-color: #eff6ff;
            color: #3b82f6;
        }

        .stat-icon.green {
            background-color: #ecfdf5;
            color: #10b981;
        }

        .stat-icon.yellow {
            background-color: #fffbeb;
            color: #f59e0b;
        }

        .stat-icon.purple {
            background-color: #f5f3ff;
            color: #8b5cf6;
        }

        .stat-icon.red {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .stat-meta {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .content-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            height: 100%;
            cursor: pointer;
        }

        .content-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(35, 61, 128, 0.14);
            border-color: #4f46e5;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .detail-card {
            background: var(--bg-card);
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            border: 1px solid var(--border-color);
        }

        .detail-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .detail-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .detail-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 0.25rem;
        }

        .pill {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #eef2ff;
            color: #4338ca;
            border: 1px solid #e0e7ff;
        }

        .detail-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
        }

        .detail-metric {
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .detail-metric span {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }

        .detail-metric strong {
            font-size: 1rem;
            color: var(--text-primary);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: space-between;
        }

        .section-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.9rem;
            color: #1d4ed8;
            text-decoration: none;
            font-weight: 600;
        }

        .section-link i {
            font-size: 0.9rem;
        }

        .section-link:hover {
            color: #0f172a;
        }

        .section-kependudukan {
            margin-top: 1.5rem;
        }

        /* Metric cards (new layout) */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: #ffffff;
            border: 1.5px solid #dfe7ff;
            border-radius: 22px;
            padding: 1.25rem 1.4rem;
            box-shadow: none;
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
            cursor: pointer;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 40px rgba(35, 61, 128, 0.18);
            border-color: #4f46e5;
        }

        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.35rem;
        }

        .metric-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            height: 52px;
            border-radius: 16px;
            font-size: 1.35rem;
            font-weight: 700;
            color: #1f3a8a;
            background: linear-gradient(135deg, #e5edff, #d7e5ff);
            border: 1px solid #d1ddff;
        }

        .metric-icon.green {
            color: #0f5132;
            background: linear-gradient(135deg, #e6f9ef, #d2f4e3);
            border-color: #bdebd3;
        }

        .metric-icon.yellow {
            color: #92400e;
            background: linear-gradient(135deg, #fff7e6, #ffefcc);
            border-color: #ffe3ad;
        }

        .metric-icon.red {
            color: #991b1b;
            background: linear-gradient(135deg, #ffecec, #ffdede);
            border-color: #ffc9c9;
        }

        .metric-icon.purple {
            color: #5b21b6;
            background: linear-gradient(135deg, #f3e8ff, #e7ddff);
            border-color: #dec7ff;
        }

        .metric-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .metric-value {
            font-size: 2.25rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        .metric-desc {
            font-size: 0.95rem;
            color: #4b5563;
        }

        .metric-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: #e8f5e9;
            color: #1b5e20;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .metric-badge.neutral {
            background: #eef2ff;
            color: #312e81;
        }

        .metric-progress {
            margin-top: 0.35rem;
        }

        .progress-line {
            width: 100%;
            height: 12px;
            border-radius: 12px;
            background: #e5e7eb;
            overflow: hidden;
            position: relative;
        }

        .progress-line span {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 0%;
            border-radius: 12px;
            background: linear-gradient(90deg, #22c55e, #16a34a);
            transition: width 0.4s ease;
        }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #4b5563;
            margin-bottom: 0.35rem;
        }

        .gender-progress {
            margin-top: 0.5rem;
        }

        .gender-progress .progress-line span.male {
            background: linear-gradient(90deg, #38bdf8, #0ea5e9);
        }

        .gender-progress .progress-line span.female {
            background: linear-gradient(90deg, #f472b6, #ec4899);
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .list-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-content {
            flex: 1;
            margin-left: 1rem;
        }

        .list-title {
            font-weight: 500;
            color: var(--text-primary);
            display: block;
        }

        .list-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .progress-bar-container {
            margin-top: 0.5rem;
            background-color: #e5e7eb;
            border-radius: 9999px;
            height: 0.5rem;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.5s ease-in-out;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        /* Dark mode overrides */
        body.dark-mode {
            --text-primary: #e5e7eb;
            --text-secondary: #cbd5e1;
            --bg-card: rgba(15, 23, 42, 0.92);
            --bg-page: #0b1224;
            --border-color: rgba(148, 163, 184, 0.25);
        }

        body.dark-mode .section-title {
            color: var(--text-primary);
        }

        body.dark-mode .section-title i {
            color: #94a3b8;
        }

        body.dark-mode .section-link {
            color: #a5b4fc;
        }

        body.dark-mode .section-link:hover {
            color: #c7d2fe;
        }

        body.dark-mode .metric-card,
        body.dark-mode .detail-card,
        body.dark-mode .content-card {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.94), rgba(8, 15, 30, 0.92));
            border: 1px solid rgba(99, 102, 241, 0.35);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.55);
        }

        body.dark-mode .metric-title,
        body.dark-mode .detail-title,
        body.dark-mode .card-title,
        body.dark-mode .list-title {
            color: var(--text-primary);
        }

        body.dark-mode .metric-value,
        body.dark-mode .detail-total {
            color: #f8fafc;
        }

        body.dark-mode .metric-desc,
        body.dark-mode .detail-metric span,
        body.dark-mode .stat-label,
        body.dark-mode .list-subtitle {
            color: var(--text-secondary);
        }

        body.dark-mode .metric-icon {
            background: linear-gradient(140deg, rgba(99, 102, 241, 0.18), rgba(59, 130, 246, 0.1));
            color: #e0e7ff;
            border: 1px solid rgba(99, 102, 241, 0.4);
        }

        body.dark-mode .metric-icon.green {
            background: linear-gradient(140deg, rgba(16, 185, 129, 0.2), rgba(34, 197, 94, 0.12));
            color: #bbf7d0;
            border-color: rgba(52, 211, 153, 0.4);
        }

        body.dark-mode .metric-icon.yellow {
            background: linear-gradient(140deg, rgba(234, 179, 8, 0.2), rgba(249, 115, 22, 0.12));
            color: #fde68a;
            border-color: rgba(251, 191, 36, 0.4);
        }

        body.dark-mode .metric-icon.red {
            background: linear-gradient(140deg, rgba(239, 68, 68, 0.22), rgba(248, 113, 113, 0.12));
            color: #fecdd3;
            border-color: rgba(248, 113, 113, 0.38);
        }

        body.dark-mode .metric-icon.purple {
            background: linear-gradient(140deg, rgba(109, 40, 217, 0.18), rgba(147, 51, 234, 0.12));
            color: #e9d5ff;
            border-color: rgba(167, 139, 250, 0.42);
        }

        body.dark-mode .metric-badge {
            background: rgba(148, 163, 184, 0.18);
            color: #e2e8f0;
        }

        body.dark-mode .metric-badge.neutral {
            background: rgba(99, 102, 241, 0.18);
            color: #c7d2fe;
        }

        body.dark-mode .detail-metric {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.28);
        }

        body.dark-mode .progress-line {
            background: rgba(148, 163, 184, 0.25);
        }

        body.dark-mode .pill {
            background: rgba(99, 102, 241, 0.15);
            color: #e2e8f0;
            border-color: rgba(99, 102, 241, 0.25);
        }

        body.dark-mode .list-item {
            border-color: rgba(148, 163, 184, 0.2);
        }

        body.dark-mode .badge-success {
            background-color: rgba(34, 197, 94, 0.18);
            color: #bbf7d0;
        }

        body.dark-mode .badge-warning {
            background-color: rgba(234, 179, 8, 0.2);
            color: #fde68a;
        }

        body.dark-mode .badge-danger {
            background-color: rgba(248, 113, 113, 0.22);
            color: #fecdd3;
        }

        body.dark-mode .badge-info {
            background-color: rgba(59, 130, 246, 0.2);
            color: #bfdbfe;
        }

        body.dark-mode .progress-bar-container {
            background-color: rgba(148, 163, 184, 0.25);
        }

        /* Mobile specific refinements */
        @media (max-width: 640px) {
            .metric-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 0.8rem;
            }

            .metric-card {
                padding: 1.1rem 1rem;
                border-radius: 18px;
                gap: 0.35rem;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 140px;
            }

            .metric-card.full-width {
                grid-column: span 2 !important;
                min-height: auto;
            }

            .metric-header {
                margin-bottom: 0.25rem;
                gap: 0.5rem;
            }

            .metric-icon {
                min-width: 38px;
                height: 38px;
                font-size: 1rem;
                border-radius: 10px;
            }

            .metric-badge {
                padding: 0.2rem 0.5rem;
                font-size: 0.72rem;
                white-space: nowrap;
            }

            .metric-title {
                font-size: 0.88rem;
                line-height: 1.25;
                letter-spacing: -0.01em;
                margin-bottom: 0.15rem;
            }

            .metric-value {
                font-size: 1.5rem;
                line-height: 1.1;
                margin-bottom: 0.15rem;
            }

            .metric-desc {
                font-size: 0.78rem;
                line-height: 1.3;
                margin-top: auto;
            }

            /* Ensure kependudukan first card works with full-width class */
            .section-kependudukan-grid .metric-card:first-child {
                grid-column: span 2 !important;
            }
        }
    </style>
@endpush

@php
    use Illuminate\Support\Facades\Storage;
    $user = auth()->user();
    $avatar = $user->avatar_url;
@endphp

@section('content')
    <div class="dashboard-container">
        <!-- Header -->
        <!-- Header -->
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
                <span class="header-title-text">Admin Panel</span>
                @include('admin.partials.header-controls')
            </div>
            <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
                <span>Dashboard</span>
                <i class="fas fa-chevron-right"></i>
                <span>Ringkasan</span>
            </nav>
        </header>

        <section class="page-title">
            <div>
                <h1>Dashboard Overview</h1>
                <p>Selamat datang kembali, {{ $user->nama }}!</p>
            </div>
        </section>

        <!-- Kependudukan Stats -->
        <div class="section-title section-kependudukan">
            <span><i class="fas fa-users"></i> Statistik Kependudukan</span>
            <a class="section-link" href="{{ route('admin.penduduks.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid section-kependudukan-grid">
            <div class="metric-card full-width">
                <div class="metric-header">
                    <div class="metric-icon"><i class="fas fa-users"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Total Penduduk Terdaftar</div>
                <div class="metric-value">{{ number_format($summary['kependudukan']['total_penduduk']) }}</div>
                <div class="metric-desc">
                    <i class="fas fa-male"></i> {{ number_format($summary['kependudukan']['laki']) }} Laki-laki &nbsp;·&nbsp;
                    <i class="fas fa-female"></i> {{ number_format($summary['kependudukan']['perempuan']) }} Perempuan
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-home"></i></div>
                    <span class="metric-badge"><i class="fas fa-user-friends"></i> KK</span>
                </div>
                <div class="metric-title">Kepala Keluarga</div>
                <div class="metric-value">{{ number_format($summary['kependudukan']['kk']) }}</div>
                <div class="metric-desc">
                    Rata-rata {{ $summary['kependudukan']['kk'] > 0 ? round($summary['kependudukan']['total_penduduk'] / $summary['kependudukan']['kk'], 1) : 0 }} jiwa/KK
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-walking"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-exchange-alt"></i> Mutasi</span>
                </div>
                <div class="metric-title">Penduduk Pindah</div>
                <div class="metric-value">{{ number_format($summary['kependudukan']['pindah']) }}</div>
                <div class="metric-desc">Data mutasi keluar</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-briefcase"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-user-clock"></i> Bekerja</span>
                </div>
                <div class="metric-title">Belum Bekerja</div>
                <div class="metric-value">{{ number_format($summary['kependudukan_detail']['belum_bekerja'] ?? 0) }}</div>
                <div class="metric-desc">Penduduk belum/tidak bekerja</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-briefcase"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Aktif</span>
                </div>
                <div class="metric-title">Sudah Bekerja</div>
                <div class="metric-value">{{ number_format($summary['kependudukan_detail']['sudah_bekerja'] ?? 0) }}</div>
                <div class="metric-desc">Penduduk bekerja</div>
            </div>

            @php
                $pendTop = $summary['kependudukan_detail']['pendidikan_top'] ?? ['label' => '-', 'total' => 0];
                $pekTop = $summary['kependudukan_detail']['pekerjaan_top'] ?? ['label' => '-', 'total' => 0];
                $kawinTop = $summary['kependudukan_detail']['status_kawin_top'] ?? ['label' => '-', 'total' => 0];
                $agamaTop = $summary['kependudukan_detail']['agama_top'] ?? ['label' => '-', 'total' => 0];
                $sukuTop = $summary['kependudukan_detail']['suku_top'] ?? ['label' => '-', 'total' => 0];
                $goldarTop = $summary['kependudukan_detail']['golongan_darah_top'] ?? ['label' => '-', 'total' => 0];
                $usiaTop = $summary['kependudukan_detail']['usia_top'] ?? ['label' => '-', 'total' => 0];
                $statusTop = $summary['kependudukan_detail']['status_top'] ?? ['label' => '-', 'total' => 0];
            @endphp

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-graduation-cap"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-book"></i> Pendidikan</span>
                </div>
                <div class="metric-title">Pendidikan</div>
                <div class="metric-value">{{ number_format($pendTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $pendTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-briefcase"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-users"></i> Pekerjaan</span>
                </div>
                <div class="metric-title">Pekerjaan</div>
                <div class="metric-value">{{ number_format($pekTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $pekTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-heart"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-ring"></i> Perkawinan</span>
                </div>
                <div class="metric-title">Status Perkawinan</div>
                <div class="metric-value">{{ number_format($kawinTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $kawinTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-praying-hands"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-star"></i> Agama</span>
                </div>
                <div class="metric-title">Agama</div>
                <div class="metric-value">{{ number_format($agamaTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $agamaTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon blue"><i class="fas fa-globe"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-users"></i> Suku</span>
                </div>
                <div class="metric-title">Suku</div>
                <div class="metric-value">{{ number_format($sukuTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $sukuTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-tint"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-heartbeat"></i> Golongan Darah</span>
                </div>
                <div class="metric-title">Golongan Darah</div>
                <div class="metric-value">{{ number_format($goldarTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $goldarTop['label'] ?? '-' }}</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-hourglass-half"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-chart-bar"></i> Usia</span>
                </div>
                <div class="metric-title">Usia</div>
                <div class="metric-value">{{ number_format($usiaTop['total'] ?? 0) }}</div>
                <div class="metric-desc">Terbanyak: {{ $usiaTop['label'] ?? '-' }}</div>
            </div>
        </div>

        <!-- Status Umur -->
        @php
            $age = $summary['umur'] ?? ['groups' => []];
            $ageGroups = $age['groups'] ?? [];
            $genderTotal = ($age['laki'] ?? 0) + ($age['perempuan'] ?? 0);
            $malePercent = $genderTotal > 0 ? round((($age['laki'] ?? 0) / $genderTotal) * 100, 1) : 0;
            $femalePercent = $genderTotal > 0 ? round((($age['perempuan'] ?? 0) / $genderTotal) * 100, 1) : 0;
        @endphp
        <div class="section-title">
            <span><i class="fas fa-birthday-cake"></i> Status Umur</span>
            <a class="section-link" href="{{ route('admin.penduduks.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-heart"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Aktif</span>
                </div>
                <div class="metric-title">Hidup</div>
                <div class="metric-value">{{ number_format($age['hidup'] ?? 0) }}</div>
                <div class="metric-desc">Penduduk hidup/aktif</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-heart-broken"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-exclamation"></i> Vital</span>
                </div>
                <div class="metric-title">Meninggal</div>
                <div class="metric-value">{{ number_format($age['mati'] ?? 0) }}</div>
                <div class="metric-desc">Data kematian tercatat</div>
            </div>
            <div class="metric-card full-width">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-male"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-user"></i> Demografi</span>
                </div>
                <div class="metric-title">Laki-laki</div>
                <div class="metric-value">{{ number_format($age['laki'] ?? 0) }}</div>
                <div class="metric-progress gender-progress">
                    <div class="progress-labels">
                        <span>Proporsi</span>
                        <span>{{ $malePercent }}%</span>
                    </div>
                    <div class="progress-line">
                        <span class="male" style="width: {{ $malePercent }}%"></span>
                    </div>
                </div>
                <div class="metric-desc">Penduduk laki-laki</div>
            </div>
            <div class="metric-card full-width">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-female"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-user"></i> Demografi</span>
                </div>
                <div class="metric-title">Perempuan</div>
                <div class="metric-value">{{ number_format($age['perempuan'] ?? 0) }}</div>
                <div class="metric-progress gender-progress">
                    <div class="progress-labels">
                        <span>Proporsi</span>
                        <span>{{ $femalePercent }}%</span>
                    </div>
                    <div class="progress-line">
                        <span class="female" style="width: {{ $femalePercent }}%"></span>
                    </div>
                </div>
                <div class="metric-desc">Penduduk perempuan</div>
            </div>

            @foreach ([
                'bayi' => 'Bayi (0-1 tahun)',
                'balita' => 'Balita (1-5 tahun)',
                'anak' => 'Anak-anak (5-12 tahun)',
                'remaja' => 'Remaja (12-21 tahun)',
                'dewasa_muda' => 'Dewasa Muda (20-an - awal 30-an)',
                'dewasa' => 'Dewasa (30-an - 50-an)',
                'paruh_baya' => 'Paruh Baya (45-65 tahun)',
                'lansia' => 'Lansia (Di atas 60/65 tahun)',
            ] as $key => $label)
                @php
                    $val = $ageGroups[$key]['total'] ?? 0;
                @endphp
                <div class="metric-card">
                    <div class="metric-header">
                        <div class="metric-icon neutral"><i class="fas fa-user-clock"></i></div>
                        <span class="metric-badge neutral"><i class="fas fa-list"></i> Usia</span>
                    </div>
                    <div class="metric-title">{{ $label }}</div>
                    <div class="metric-value">{{ number_format($val) }}</div>
                    <div class="metric-desc">Kelompok usia</div>
                </div>
            @endforeach
        </div>

        <!-- Wilayah -->
        <div class="section-title">
            <span><i class="fas fa-map-marked-alt"></i> Wilayah</span>
            <a class="section-link" href="{{ route('admin.dusuns.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon"><i class="fas fa-map"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Jumlah Dusun</div>
                <div class="metric-value">{{ number_format($summary['wilayah']['dusun']) }}</div>
                <div class="metric-desc">Dusun terdaftar</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-route"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Jumlah RW</div>
                <div class="metric-value">{{ number_format($summary['wilayah']['rw']) }}</div>
                <div class="metric-desc">RW aktif</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-stream"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Jumlah RT</div>
                <div class="metric-value">{{ number_format($summary['wilayah']['rt']) }}</div>
                <div class="metric-desc">RT aktif</div>
            </div>
        </div>

        <!-- Berita -->
        <div class="section-title">
            <span><i class="fas fa-newspaper"></i> Berita</span>
            <a class="section-link" href="{{ route('admin.news.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon"><i class="fas fa-th-large"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil bulan ini</span>
                </div>
                <div class="metric-title">Total Berita</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['berita']['total'] ?? 0) }}</div>
                <div class="metric-desc">Keseluruhan konten</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-calendar-check"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Terbit</span>
                </div>
                <div class="metric-title">Terbit</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['berita']['published'] ?? 0) }}</div>
                <div class="metric-desc">Artikel dipublikasikan</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-pencil-alt"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-pen"></i> Draft</span>
                </div>
                <div class="metric-title">Draft</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['berita']['draft'] ?? 0) }}</div>
                <div class="metric-desc">Menunggu terbit</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-archive"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-box"></i> Arsip</span>
                </div>
                <div class="metric-title">Arsip</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['berita']['archived'] ?? 0) }}</div>
                <div class="metric-desc">Berita diarsipkan</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-eye"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-chart-line"></i> Trafik</span>
                </div>
                <div class="metric-title">Total Views</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['berita']['views'] ?? 0) }}</div>
                <div class="metric-desc">Akumulasi tayangan</div>
            </div>
        </div>

        <!-- Halaman -->
        <div class="section-title">
            <span><i class="fas fa-file-alt"></i> Halaman</span>
            <a class="section-link" href="{{ route('admin.pages.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon"><i class="fas fa-copy"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Total Halaman</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['halaman']['total'] ?? 0) }}</div>
                <div class="metric-desc">Konten halaman</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-check-circle"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Publish</span>
                </div>
                <div class="metric-title">Publish</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['halaman']['published'] ?? 0) }}</div>
                <div class="metric-desc">Halaman aktif</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-pencil-alt"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-pen"></i> Draft</span>
                </div>
                <div class="metric-title">Draft</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['halaman']['draft'] ?? 0) }}</div>
                <div class="metric-desc">Menunggu publish</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-eye"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-chart-line"></i> Trafik</span>
                </div>
                <div class="metric-title">Views</div>
                <div class="metric-value">{{ number_format($summary['publikasi_detail']['halaman']['views'] ?? 0) }}</div>
                <div class="metric-desc">Total tayangan</div>
            </div>
        </div>

        <!-- Transparansi Anggaran -->
        @php
            $trans = $summary['publikasi_detail']['transparansi'] ?? [];
            $anggaranTotal = $trans['total_anggaran'] ?? 0;
            $realisasiTotal = $trans['total_realisasi'] ?? 0;
            $progress = $anggaranTotal > 0 ? min(100, round(($realisasiTotal / $anggaranTotal) * 100, 1)) : 0;
        @endphp
        <div class="section-title">
            <span><i class="fas fa-wallet"></i> Transparansi Anggaran</span>
            <a class="section-link" href="{{ route('admin.budget-items.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-list"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Publish</span>
                </div>
                <div class="metric-title">Banyak Anggaran</div>
                <div class="metric-value">{{ number_format($trans['total_items'] ?? 0) }}</div>
                <div class="metric-desc">Total entri anggaran</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon blue"><i class="fas fa-check-circle"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-bullseye"></i> Terbit</span>
                </div>
                <div class="metric-title">Publish</div>
                <div class="metric-value">{{ number_format($trans['published_items'] ?? 0) }}</div>
                <div class="metric-desc">Item dipublikasikan</div>
            </div>
            <div class="metric-card full-width">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-coins"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-wallet"></i> Anggaran</span>
                </div>
                <div class="metric-title">Total Anggaran</div>
                <div class="metric-value" style="font-size: 1.6rem;">{{ number_format($anggaranTotal ?? 0) }}</div>
                <div class="metric-desc">Nilai anggaran disetujui</div>
            </div>
            <div class="metric-card full-width">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-hand-holding-usd"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Progres</span>
                </div>
                <div class="metric-title">Total Realisasi</div>
                <div class="metric-value" style="font-size: 1.6rem;">{{ number_format($realisasiTotal ?? 0) }}</div>
                <div class="metric-progress">
                    <div class="progress-labels">
                        <span>Realisasi</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="progress-line">
                        <span style="width: {{ $progress }}%"></span>
                    </div>
                </div>
                <div class="metric-desc">Realisasi dibanding total anggaran</div>
            </div>
        </div>

        <!-- Data Pegawai -->
        <div class="section-title">
            <span><i class="fas fa-user-tie"></i> Data Pegawai</span>
            <a class="section-link" href="{{ route('admin.users.index') }}"><span>Lihat</span><i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon purple"><i class="fas fa-users"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-arrow-up"></i> Stabil</span>
                </div>
                <div class="metric-title">Total Pegawai</div>
                <div class="metric-value">{{ number_format($summary['pegawai_detail']['total'] ?? 0) }}</div>
                <div class="metric-desc">Data staf & perangkat</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon green"><i class="fas fa-check-circle"></i></div>
                    <span class="metric-badge"><i class="fas fa-check"></i> Aktif</span>
                </div>
                <div class="metric-title">Status Aktif</div>
                <div class="metric-value">{{ number_format($summary['pegawai_detail']['aktif'] ?? 0) }}</div>
                <div class="metric-desc">Sedang bertugas</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon yellow"><i class="fas fa-plane-departure"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-umbrella-beach"></i> Cuti</span>
                </div>
                <div class="metric-title">Cuti</div>
                <div class="metric-value">{{ number_format($summary['pegawai_detail']['cuti'] ?? 0) }}</div>
                <div class="metric-desc">Sedang cuti</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon red"><i class="fas fa-user-slash"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-ban"></i> Tidak Aktif</span>
                </div>
                <div class="metric-title">Tidak Aktif</div>
                <div class="metric-value">{{ number_format($summary['pegawai_detail']['tidak_aktif'] ?? 0) }}</div>
                <div class="metric-desc">Nonaktif/berhenti</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <div class="metric-icon blue"><i class="fas fa-award"></i></div>
                    <span class="metric-badge neutral"><i class="fas fa-flag"></i> Pensiun</span>
                </div>
                <div class="metric-title">Pensiun</div>
                <div class="metric-value">{{ number_format($summary['pegawai_detail']['pensiun'] ?? 0) }}</div>
                <div class="metric-desc">Pegawai pensiun</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- ROW 1: 3 Columns (Agenda, Pekerjaan, Pendidikan) -->
            <div class="grid-cols-3">
                
                <!-- 1. Agenda Prioritas -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Agenda Prioritas</h3>
                        <a href="{{ route('admin.agendas.index') }}" style="color: var(--primary-color); font-size: 0.875rem;">Lihat Semua</a>
                    </div>
                    @forelse ($agendas as $agenda)
                        <div class="list-item">
                            <div style="width: 4px; height: 40px; background-color: {{ $agenda->priority === 'high' ? 'var(--danger-color)' : ($agenda->priority === 'medium' ? 'var(--warning-color)' : 'var(--secondary-color)') }}; border-radius: 2px; margin-right: 1rem;">
                            </div>
                            <div class="list-content">
                                <span class="list-title">{{ $agenda->title }}</span>
                                <span class="list-subtitle">{{ $agenda->due_date?->translatedFormat('d M Y') ?? 'Tanpa tenggat' }}</span>
                            </div>
                            <span class="badge {{ $agenda->is_completed ? 'badge-success' : 'badge-warning' }}">
                                {{ $agenda->is_completed ? 'Selesai' : 'Berjalan' }}
                            </span>
                        </div>
                    @empty
                        <p style="color: var(--text-secondary); text-align: center; padding: 1rem;">Tidak ada agenda aktif.</p>
                    @endforelse
                </div>

                <!-- 2. Distribusi Pekerjaan -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Distribusi Pekerjaan</h3>
                    </div>
                    @if(isset($statistics['pekerjaan']))
                        @foreach($statistics['pekerjaan']->take(5) as $stat)
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.875rem; color: var(--text-primary);">{{ $stat->label }}</span>
                                    <span style="font-size: 0.875rem; font-weight: 600;">{{ number_format($stat->value) }}</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar"
                                        style="width: {{ ($stat->value / max($summary['kependudukan']['total_penduduk'], 1)) * 100 }}%; background-color: var(--primary-color);">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- 3. Pendidikan -->
                <div class="content-card">
                    <div class="card-header">
                        <h3 class="card-title">Pendidikan</h3>
                    </div>
                    @if(isset($statistics['pendidikan']))
                        @foreach($statistics['pendidikan']->take(5) as $stat)
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.875rem; color: var(--text-primary);">{{ $stat->label }}</span>
                                    <span style="font-size: 0.875rem; font-weight: 600;">{{ number_format($stat->value) }}</span>
                                </div>
                                <div class="progress-bar-container">
                                    <div class="progress-bar"
                                        style="width: {{ ($stat->value / max($summary['kependudukan']['total_penduduk'], 1)) * 100 }}%; background-color: var(--info-color);">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- ROW 2: Activity Log (Full Width) -->
            <div class="content-card">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Terbaru</h3>
                </div>
                @forelse ($activities as $activity)
                    @php
                        // Calculate chronological index: Total - (Offset + Current Index)
                        // This makes the newest item have the highest number (Total), and oldest item have number 1.
                        $chronoIndex = $activities->total() - ($activities->currentPage() - 1) * $activities->perPage() - $loop->index;
                    @endphp
                    <div class="list-item">
                        <div style="font-weight: 600; font-size: 0.9rem; color: var(--text-secondary); width: 30px; text-align: center; margin-right: 0.5rem;">
                            {{ $chronoIndex }}.
                        </div>
                        <div style="width: 32px; height: 32px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                            <i class="fas fa-history" style="color: var(--text-secondary); font-size: 0.875rem;"></i>
                        </div>
                        <div class="list-content">
                            <span class="list-title">{{ $activity->description ?? $activity->action }}</span>
                            <span class="list-subtitle">{{ $activity->user?->nama ?? 'Sistem' }} • {{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <p style="color: var(--text-secondary); text-align: center; padding: 1rem;">Belum ada aktivitas.</p>
                @endforelse
                
                <!-- Pagination -->
                @if($activities->hasPages())
                    <div class="card-footer" style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        {{ $activities->links('admin.partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
        </div>
    </div>

@endsection
