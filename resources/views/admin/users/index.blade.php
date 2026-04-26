@extends('admin.layouts.app')

@section('title', 'Pengelolaan Admin & Pegawai Desa')

@push('head')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-users.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/profile-images.css') }}">
    <script src="{{ asset('assets/js/image-handler.js') }}" defer></script>
    <style>
        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #f8f9fa;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .profile-image:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }

        .table-avatar {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            position: relative;
        }

        .table-avatar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .table-avatar:hover::after {
            opacity: 1;
        }

        .profile-image.loading {
            opacity: 0.5;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.5;
            }

            50% {
                opacity: 0.8;
            }

            100% {
                opacity: 0.5;
            }
        }
    </style>
@endpush

@php
    $storageDefaultAvatar = asset('default/user.jpg');

    $totalAdmins = $users->count();
    $activeAdmins = $users->where('is_active', true)->count();
    $superAdmins = $users->where('is_admin', true)->count();
    $totalPegawai = $pegawais->count();
    $lastPegawaiUpdated = $pegawais->max('updated_at');

    $statusMessage = session('status');
    $feedbackTone = null;
    if ($statusMessage) {
        $feedbackTone = 'success';
        $normalizedStatus = \Illuminate\Support\Str::lower($statusMessage);
        if (\Illuminate\Support\Str::contains($normalizedStatus, ['hapus', 'dihapus', 'delete'])) {
            $feedbackTone = 'danger';
        } elseif (\Illuminate\Support\Str::contains($normalizedStatus, ['perbarui', 'update', 'ubah'])) {
            $feedbackTone = 'info';
        }
    }
@endphp

@section('content')
    @push('head')
        <script>
            window.defaultImagePaths = {
                user: "{{ asset('default/user.jpg') }}",
                userFallback: [
                    "{{ asset('assets/img/Users/default.jpg') }}",
                    "{{ asset('default/user.jpg') }}",
                    "{{ asset('img/default-user.jpg') }}",
                    "{{ asset('assets/default-avatar.png') }}"
                ]
            };
        </script>
    @endpush

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
            <span class="header-title-text">Admin Panel</span>
            @include('admin.partials.header-controls')
        </div>
        <nav class="breadcrumbs header-breadcrumbs" aria-label="Peta navigasi">
            <span>Pengaturan</span>
            <i class="fas fa-chevron-right"></i>
            <span>Admin &amp; Pegawai</span>
        </nav>
    </header>

    <section class="page-title">
        <div>
            <h1>Pengaturan Admin &amp; Pegawai</h1>
        </div>
        <div class="title-actions">
            <button class="primary-btn js-create-admin" type="button" data-tab-visible="users">
                <i class="fas fa-user-plus"></i>
                Tambah Admin
            </button>
            <button class="primary-btn js-create-pegawai" type="button" data-tab-visible="pegawai" hidden
                aria-hidden="true">
                <i class="fas fa-id-badge"></i>
                Tambah Pegawai
            </button>
        </div>
    </section>

    @php
        $toastNotifications = [];
        if ($statusMessage = session('status')) {
            $variantMap = [
                'success' => 'success',
                'danger' => 'error',
                'info' => 'info',
            ];
            $toastNotifications[] = [
                'variant' => $variantMap[$feedbackTone] ?? 'neutral',
                'title' => $statusMessage,
                'message' => session('status_description') ?? 'Perubahan berhasil disimpan.',
            ];
        }
        if ($errors->any()) {
            $toastNotifications[] = [
                'variant' => 'error',
                'title' => 'Form tidak valid',
                'message' => $errors->first(),
            ];
        }
    @endphp

    @if (!empty($toastNotifications))
        <div class="toast-stack" id="settingsToastStack" role="region" aria-live="polite">
            @foreach ($toastNotifications as $toast)
                @php
                    $variant = $toast['variant'] ?? 'neutral';
                    $iconMap = [
                        'success' => 'fa-check',
                        'warning' => 'fa-triangle-exclamation',
                        'error' => 'fa-circle-xmark',
                        'info' => 'fa-circle-info',
                        'neutral' => 'fa-circle-info',
                    ];
                    $icon = $iconMap[$variant] ?? $iconMap['neutral'];
                @endphp
                <article class="toast" data-toast data-variant="{{ $variant }}">
                    <div class="toast__icon" aria-hidden="true">
                        <i class="fas {{ $icon }}"></i>
                    </div>
                    <div class="toast__content">
                        <strong>{{ $toast['title'] }}</strong>
                        @if (!empty($toast['message']))
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

    <section class="metrics">
        <article class="metric-card">
            <div class="metric-header">
                <span class="metric-icon"><i class="fas fa-user-shield"></i></span>
                <h2>Total Admin</h2>
            </div>
            <p class="metric-value">{{ $totalAdmins }}</p>
            <span class="metric-meta">Aktif: {{ $activeAdmins }} &bull; Super Admin: {{ $superAdmins }}</span>
        </article>
        <article class="metric-card">
            <div class="metric-header">
                <span class="metric-icon"><i class="fas fa-users"></i></span>
                <h2>Data Pegawai</h2>
            </div>
            <p class="metric-value">{{ $totalPegawai }}</p>
            <span class="metric-meta">
                Pembaruan terakhir:
                {{ $lastPegawaiUpdated ? $lastPegawaiUpdated->timezone(config('app.timezone'))->format('d M Y') : 'Belum ada data' }}
            </span>
        </article>
    </section>

    <section class="tab-bar settings-tab-bar">
        <button type="button" class="tab-btn {{ $activeTab === 'users' ? 'is-active' : '' }}" data-settings-tab="users">
            <i class="fas fa-users-gear"></i>
            <span>Data Admin</span>
        </button>
        <button type="button" class="tab-btn {{ $activeTab === 'pegawai' ? 'is-active' : '' }}" data-settings-tab="pegawai">
            <i class="fas fa-people-group"></i>
            <span>Data Pegawai</span>
        </button>
    </section>

    <div class="settings-panels">
        <div class="settings-panel {{ $activeTab === 'users' ? 'is-active' : '' }}" data-settings-panel="users"
            @if($activeTab !== 'users') hidden @endif>
            <section class="management-grid single-column">
                <div class="grid-column">
                    <article class="panel panel--flush">
                        <header class="panel-header panel-header--table panel-header--flush">
                            <div>
                                <h2>Daftar Admin Portal</h2>
                                <p>Lihat dan kelola akun admin beserta status aksesnya.</p>
                            </div>
                            <div class="table-control-group">
                                <div class="table-search">
                                    <i class="fas fa-search"></i>
                                    <input type="search" id="adminSearch" placeholder="Cari Admin" autocomplete="off">
                                </div>
                                <div class="table-actions-group">
                                    <div class="table-filter" data-filter="users">
                                        <button type="button" class="table-filter-btn" aria-label="Filter admin"
                                            data-filter-toggle="users" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-sliders-h"></i>
                                            <span>Filter</span>
                                        </button>
                                        <div class="filter-dropdown" data-filter-menu="users" hidden>
                                            <div class="filter-dropdown__header">
                                                <strong>Filter Admin</strong>
                                                <span>Saring daftar berdasarkan status atau role.</span>
                                            </div>
                                            <div class="filter-dropdown__group">
                                                <span class="filter-dropdown__label">Status</span>
                                                <label class="filter-checkbox">
                                                    <input type="checkbox" value="aktif" data-filter-field="status">
                                                    <span>Aktif</span>
                                                </label>
                                                <label class="filter-checkbox">
                                                    <input type="checkbox" value="nonaktif" data-filter-field="status">
                                                    <span>Nonaktif</span>
                                                </label>
                                            </div>
                                            <div class="filter-dropdown__group">
                                                <span class="filter-dropdown__label">Role</span>
                                                <label class="filter-checkbox">
                                                    <input type="checkbox" value="super admin" data-filter-field="role">
                                                    <span>Super Admin</span>
                                                </label>
                                                <label class="filter-checkbox">
                                                    <input type="checkbox" value="admin" data-filter-field="role">
                                                    <span>Admin</span>
                                                </label>
                                            </div>
                                            <div class="filter-dropdown__actions">
                                                <button type="button" class="filter-reset-btn"
                                                    data-filter-reset="users">Reset</button>
                                                <button type="button" class="filter-apply-btn"
                                                    data-filter-apply="users">Terapkan</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-export" data-export="users">
                                        <button type="button" class="table-export-btn" aria-haspopup="true"
                                            aria-expanded="false" data-export-toggle="users" aria-label="Export data admin">
                                            <i class="fas fa-arrow-up-right-from-square"></i>
                                            <span>Export</span>
                                        </button>
                                        <div class="export-dropdown" data-export-menu="users" hidden>
                                            <header class="export-dropdown__header">
                                                <strong>Ekspor Data Admin</strong>
                                                <span>Total data: {{ $users->count() }}</span>
                                            </header>
                                            <div class="export-dropdown__group">
                                                <button type="button" class="export-dropdown__item" data-export-action="pdf"
                                                    data-export-source="users">
                                                    <i class="fas fa-file-pdf"></i>
                                                    <span>Export ke PDF</span>
                                                </button>
                                                <button type="button" class="export-dropdown__item"
                                                    data-export-action="excel" data-export-source="users">
                                                    <i class="fas fa-file-excel"></i>
                                                    <span>Export ke Excel</span>
                                                </button>
                                                <button type="button" class="export-dropdown__item"
                                                    data-export-action="word" data-export-source="users">
                                                    <i class="fas fa-file-word"></i>
                                                    <span>Export ke Word</span>
                                                </button>
                                            </div>
                                            <footer class="export-dropdown__footer">
                                                <p>Judul tabel: Daftar Admin Portal</p>
                                                <p>Keterangan: Total admin {{ $users->count() }}, aktif {{ $activeAdmins }},
                                                    nonaktif {{ $users->count() - $activeAdmins }}.</p>
                                            </footer>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </header>
                        <section class="content-table settings-table" data-table="users">
                            <header class="table-header">
                                <span class="table-head table-head--small">No</span>
                                <span class="table-head">Admin</span>
                                <span class="table-head">Role</span>
                                <span class="table-head">Super Admin</span>
                                <span class="table-head">Status</span>
                                <span class="table-head">Login Terakhir</span>
                                <span class="table-head align-right">Aksi</span>
                            </header>
                            <div class="table-body">
                                @forelse ($users as $admin)
                                    @php
                                        // Gunakan accessor avatar_url dari model yang sudah menangani validasi path dan versioning
                                        $adminAvatar = $admin->avatar_url;

                                        // Fallback tambahan jika avatar_url kosong
                                        if (empty($adminAvatar)) {
                                            $adminAvatar = $storageDefaultAvatar;
                                        }
                                        $adminLastLogin = $admin->last_login
                                            ? $admin->last_login->timezone(config('app.timezone'))->format('d M Y H:i')
                                            : 'Belum pernah';
                                        $adminLastLoginIp = $admin->last_login_ip ?? 'Tidak diketahui';
                                        $adminLastLoginLocation = $admin->last_login_location
                                            ?? ($admin->last_login_ip ? 'IP: ' . $admin->last_login_ip : 'Lokasi tidak tersedia');
                                        $adminRoleLabel = $admin->is_admin ? 'Super Admin' : 'Admin';
                                        $adminSequence = $loop->iteration;
                                        $adminIdFormatted = '#' . str_pad((string) $admin->id, 6, '0', STR_PAD_LEFT);
                                    @endphp
                                    <article class="table-row" data-status="{{ $admin->is_active ? 'aktif' : 'nonaktif' }}"
                                        data-role="{{ \Illuminate\Support\Str::lower($adminRoleLabel) }}"
                                        data-search="{{ strtolower(trim($admin->nama . ' ' . $admin->email . ' ' . ($admin->nomor_hp ?? '') . ' ' . ($admin->last_login_location ?? '') . ' ' . ($admin->last_login_ip ?? '') . ' ' . $adminIdFormatted)) }}">
                                        <span class="table-cell table-cell--seq">{{ $adminSequence }}</span>
                                        <div class="table-cell table-cell--main">
                                            <figure class="table-avatar">
                                                <img src="{{ $adminAvatar }}" alt="{{ $admin->nama }}" class="profile-image avatar-user"
                                                    loading="lazy" data-id="{{ $admin->id }}"
                                                    onerror="this.onerror=null;this.src='{{ $storageDefaultAvatar }}';">
                                            </figure>
                                            <div class="table-cell__meta">
                                                <strong>{{ $admin->nama }}</strong>
                                                <small>{{ $admin->email }}</small>

                                            </div>
                                        </div>
                                        <span class="table-cell">
                                            <span class="table-chip"><i
                                                    class="fas fa-user-shield"></i>{{ $adminRoleLabel }}</span>
                                        </span>
                                        <span class="table-cell">
                                            <span class="status-pill {{ $admin->is_admin ? 'status-active' : 'status-draft' }}">
                                                <i
                                                    class="fas {{ $admin->is_admin ? 'fa-check-circle' : 'fa-minus-circle' }}"></i>
                                                {{ $admin->is_admin ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </span>
                                        <span class="table-cell">
                                            <span
                                                class="status-pill {{ $admin->is_active ? 'status-active' : 'status-draft' }}">
                                                <i
                                                    class="fas {{ $admin->is_active ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                                {{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </span>
                                        <span class="table-cell table-cell--muted">{{ $adminLastLogin }}</span>
                                        <div class="table-cell table-cell--actions">
                                            <button type="button" class="action-icon action-icon--view js-view-admin"
                                                aria-label="Lihat admin {{ $admin->nama }}" data-avatar="{{ $adminAvatar }}"
                                                data-nama="{{ $admin->nama }}" data-email="{{ $admin->email }}"
                                                data-nomor-hp="{{ $admin->nomor_hp ?? 'Belum diisi' }}"
                                                data-role="{{ $adminRoleLabel }}"
                                                data-is-admin="{{ $admin->is_admin ? '1' : '0' }}"
                                                data-status="{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}"
                                                data-status-raw="{{ $admin->is_active ? 'Aktif' : 'Nonaktif' }}"
                                                data-status-class="{{ $admin->is_active ? 'status-active' : 'status-draft' }}"
                                                data-last-login="{{ $adminLastLogin }}"
                                                data-login-location="{{ $adminLastLoginLocation }}"
                                                data-login-ip="{{ $adminLastLoginIp }}"
                                                data-created="{{ $admin->created_at ? $admin->created_at->timezone(config('app.timezone'))->format('d M Y H:i') : '-' }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="action-icon action-icon--edit js-edit-admin"
                                                aria-label="Edit admin {{ $admin->nama }}"
                                                data-update="{{ route('admin.users.update', $admin) }}"
                                                data-nama="{{ $admin->nama }}" data-email="{{ $admin->email }}"
                                                data-nomor-hp="{{ $admin->nomor_hp }}"
                                                data-is-admin="{{ $admin->is_admin ? '1' : '0' }}"
                                                data-is-active="{{ $admin->is_active ? '1' : '0' }}"
                                                data-avatar="{{ $adminAvatar }}">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <form method="POST" action="{{ route('admin.users.destroy', $admin) }}"
                                                class="js-delete-form"
                                                data-confirm-message="Yakin ingin menghapus admin {{ $admin->nama }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-icon action-icon--delete"
                                                    aria-label="Hapus admin {{ $admin->nama }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </article>
                                @empty
                                    <div class="table-empty" data-empty="users">
                                        <i class="fas fa-user-slash"></i>
                                        <p>Belum ada admin terdaftar. Gunakan tombol <strong>Admin Baru</strong> untuk
                                            menambahkan.</p>
                                    </div>
                                @endforelse
                                @if ($users->isNotEmpty())
                                    <div class="table-empty table-empty--search" data-empty="users" hidden aria-hidden="true">
                                        <i class="fas fa-user-slash"></i>
                                        <p>Tidak ditemukan admin yang sesuai dengan pencarian.</p>
                                    </div>
                                @endif
                            </div>
                            <footer class="table-footer">
                                <div class="table-info" data-entries-info="users" aria-live="polite">Menampilkan 0 data
                                </div>
                                <nav class="table-pagination" data-pagination="users"
                                    aria-label="Navigasi halaman data admin"></nav>
                                <label class="entries-control" for="usersEntries">
                                    <span>Tampilkan</span>
                                    <select id="usersEntries" data-entries-select="users">
                                        <option value="5">5</option>
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <span>data</span>
                                </label>
                            </footer>
                        </section>
                </div>
                </article>
        </div>
        </section>
    </div>
    <div class="settings-panel {{ $activeTab === 'pegawai' ? 'is-active' : '' }}" data-settings-panel="pegawai"
        @if($activeTab !== 'pegawai') hidden @endif>
        <section class="management-grid single-column">
            <div class="grid-column">
                <article class="panel panel--flush">
                    <header class="panel-header panel-header--table panel-header--flush">
                        <div>
                            <h2>Data Aparatur Desa</h2>
                            <p>Kelola informasi pegawai dan struktur aparatur desa.</p>
                            <div class="panel-header__meta-group">
                                <span class="panel-header__stat">
                                    <i class="fas fa-users"></i>
                                    {{ number_format($totalPegawai) }} data pegawai
                                </span>
                                <span class="panel-header__meta">
                                    Pembaruan terakhir:
                                    {{ $lastPegawaiUpdated ? $lastPegawaiUpdated->timezone(config('app.timezone'))->format('d M Y H:i') : 'Belum ada data' }}
                                </span>
                            </div>
                        </div>
                        <div class="table-control-group">
                            <div class="table-search">
                                <i class="fas fa-search"></i>
                                <input type="search" id="pegawaiSearch" placeholder="Cari Pegawai" autocomplete="off">
                            </div>
                            <div class="table-actions-group">
                                <div class="table-filter" data-filter="pegawai">
                                    <button type="button" class="table-filter-btn" aria-label="Filter pegawai"
                                        data-filter-toggle="pegawai" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-sliders-h"></i>
                                        <span>Filter</span>
                                    </button>
                                    <div class="filter-dropdown" data-filter-menu="pegawai" hidden>
                                        <div class="filter-dropdown__header">
                                            <strong>Filter Pegawai</strong>
                                            <span>Pilih status aparatur untuk ditampilkan.</span>
                                        </div>
                                        <div class="filter-dropdown__group">
                                            <span class="filter-dropdown__label">Status</span>
                                            <label class="filter-checkbox">
                                                <input type="checkbox" value="aktif" data-filter-field="status">
                                                <span>Aktif</span>
                                            </label>
                                            <label class="filter-checkbox">
                                                <input type="checkbox" value="cuti" data-filter-field="status">
                                                <span>Cuti</span>
                                            </label>
                                            <label class="filter-checkbox">
                                                <input type="checkbox" value="pensiun" data-filter-field="status">
                                                <span>Purna Tugas</span>
                                            </label>
                                            <label class="filter-checkbox">
                                                <input type="checkbox" value="tidak aktif" data-filter-field="status">
                                                <span>Tidak Aktif</span>
                                            </label>
                                        </div>
                                        <div class="filter-dropdown__actions">
                                            <button type="button" class="filter-reset-btn"
                                                data-filter-reset="pegawai">Reset</button>
                                            <button type="button" class="filter-apply-btn"
                                                data-filter-apply="pegawai">Terapkan</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-export" data-export="pegawai">
                                    <button type="button" class="table-export-btn" aria-haspopup="true"
                                        aria-expanded="false" data-export-toggle="pegawai" aria-label="Export data pegawai">
                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                        <span>Export</span>
                                    </button>
                                    <div class="export-dropdown" data-export-menu="pegawai" hidden>
                                        <header class="export-dropdown__header">
                                            <strong>Ekspor Data Pegawai</strong>
                                            <span>Total data: {{ $pegawais->count() }}</span>
                                        </header>
                                        <div class="export-dropdown__group">
                                            <button type="button" class="export-dropdown__item" data-export-action="pdf"
                                                data-export-source="pegawai">
                                                <i class="fas fa-file-pdf"></i>
                                                <span>Export ke PDF</span>
                                            </button>
                                            <button type="button" class="export-dropdown__item" data-export-action="excel"
                                                data-export-source="pegawai">
                                                <i class="fas fa-file-excel"></i>
                                                <span>Export ke Excel</span>
                                            </button>
                                            <button type="button" class="export-dropdown__item" data-export-action="word"
                                                data-export-source="pegawai">
                                                <i class="fas fa-file-word"></i>
                                                <span>Export ke Word</span>
                                            </button>
                                        </div>
                                        <footer class="export-dropdown__footer">
                                            <p>Judul tabel: Data Aparatur Desa</p>
                                            <p>Keterangan: Total aparatur {{ $pegawais->count() }}, aktif
                                                {{ $pegawais->where('status', 'aktif')->count() }}, lainnya
                                                {{ $pegawais->count() - $pegawais->where('status', 'aktif')->count() }}.
                                            </p>
                                        </footer>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </header>
                    <section class="content-table settings-table" data-table="pegawai">
                        <header class="table-header">
                            <span class="table-head table-head--small">No</span>
                            <span class="table-head">Pegawai</span>
                            <span class="table-head">Jabatan</span>
                            <span class="table-head">Status</span>
                            <span class="table-head">Diperbarui</span>
                            <span class="table-head align-right">Aksi</span>
                        </header>
                        <div class="table-body">
                            @forelse ($pegawais as $pegawai)
                                @php
                                    // Gunakan accessor image_url dari trait HasProfileImage
                                    $pegawaiAvatar = $pegawai->image_url ?? $storageDefaultAvatar;
                                    $pegawaiAvatarVersion = $pegawai->updated_at?->timestamp
                                        ?? $pegawai->created_at?->timestamp
                                        ?? $pegawai->id;

                                    if ($pegawai->gambar && $pegawaiAvatarVersion && !\Illuminate\Support\Str::contains($pegawaiAvatar, 'v=' . $pegawaiAvatarVersion)) {
                                        $separator = \Illuminate\Support\Str::contains($pegawaiAvatar, '?') ? '&' : '?';
                                        $pegawaiAvatar .= $separator . 'v=' . $pegawaiAvatarVersion;
                                    }

                                    $pegawaiStatusValue = trim((string) ($pegawai->status ?? ''));
                                    $pegawaiStatusNormalized = strtolower(str_replace(['_', '-'], ' ', $pegawaiStatusValue));
                                    $pegawaiStatusNormalized = preg_replace('/\s+/', ' ', $pegawaiStatusNormalized);

                                    $pegawaiStatusStored = 'Aktif';
                                    $pegawaiStatusDisplay = 'Masih bekerja';
                                    $pegawaiStatusClass = 'status-active';
                                    $pegawaiStatusIcon = 'fa-circle-check';

                                    if (in_array($pegawaiStatusNormalized, ['cuti'], true)) {
                                        $pegawaiStatusStored = 'Cuti';
                                        $pegawaiStatusDisplay = 'Sedang cuti';
                                        $pegawaiStatusClass = 'status-scheduled';
                                        $pegawaiStatusIcon = 'fa-circle-pause';
                                    } elseif (in_array($pegawaiStatusNormalized, ['pensiun', 'purna tugas', 'purna bakti'], true)) {
                                        $pegawaiStatusStored = 'Pensiun';
                                        $pegawaiStatusDisplay = 'Purna tugas';
                                        $pegawaiStatusClass = 'status-pending';
                                        $pegawaiStatusIcon = 'fa-circle-half-stroke';
                                    } elseif (in_array($pegawaiStatusNormalized, ['tidak aktif', 'non aktif', 'nonaktif', 'sudah tidak bekerja', 'tidak bekerja', 'berhenti', 'berhenti bekerja'], true)) {
                                        $pegawaiStatusStored = 'Tidak Aktif';
                                        $pegawaiStatusDisplay = 'Tidak lagi bertugas';
                                        $pegawaiStatusClass = 'status-draft';
                                        $pegawaiStatusIcon = 'fa-circle-xmark';
                                    }

                                    $pegawaiTanggalLahirInput = $pegawai->tanggal_lahir?->format('Y-m-d') ?? '';
                                    $pegawaiTanggalLahirDisplay = $pegawai->tanggal_lahir?->format('d M Y') ?? '';

                                    $pegawaiMasaMulaiInput = $pegawai->masa_jabatan_mulai?->format('Y-m-d') ?? '';
                                    $pegawaiMasaMulaiDisplay = $pegawai->masa_jabatan_mulai?->format('d M Y') ?? '';
                                    $pegawaiMasaSelesaiInput = $pegawai->masa_jabatan_selesai?->format('Y-m-d') ?? '';
                                    $pegawaiMasaSelesaiDisplay = $pegawai->masa_jabatan_selesai?->format('d M Y') ?? '';

                                    $pegawaiTenureDisplay = ($pegawaiMasaMulaiDisplay || $pegawaiMasaSelesaiDisplay)
                                        ? trim(($pegawaiMasaMulaiDisplay ?: '-') . ' s/d ' . ($pegawaiMasaSelesaiDisplay ?: 'sekarang'))
                                        : 'Belum diatur';

                                    $pegawaiLastLoginInput = $pegawai->last_login
                                        ? $pegawai->last_login->timezone(config('app.timezone'))->format('Y-m-d\TH:i')
                                        : '';
                                    $pegawaiLastLoginDisplay = $pegawai->last_login
                                        ? $pegawai->last_login->timezone(config('app.timezone'))->format('d M Y H:i')
                                        : 'Belum tercatat';

                                    $pegawaiUpdated = $pegawai->updated_at
                                        ? $pegawai->updated_at->timezone(config('app.timezone'))->format('d M Y H:i')
                                        : '-';

                                    $pegawaiCreatedAtDisplay = $pegawai->created_at
                                        ? $pegawai->created_at->timezone(config('app.timezone'))->format('d M Y H:i')
                                        : '-';
                                    $pegawaiUpdatedAtDisplay = $pegawai->updated_at
                                        ? $pegawai->updated_at->timezone(config('app.timezone'))->format('d M Y H:i')
                                        : '-';
                                    $pegawaiDeletedAtDisplay = $pegawai->deleted_at
                                        ? $pegawai->deleted_at->timezone(config('app.timezone'))->format('d M Y H:i')
                                        : '-';

                                    $pegawaiCreatedBy = $pegawai->creator
                                        ? trim(($pegawai->creator->nama ?? '-') . ' (' . ($pegawai->creator->email ?? '-') . ')')
                                        : 'Sistem';
                                    $pegawaiUpdatedBy = $pegawai->updater
                                        ? trim(($pegawai->updater->nama ?? '-') . ' (' . ($pegawai->updater->email ?? '-') . ')')
                                        : 'Belum ada';

                                    $pegawaiFotoKtpUrl = ($pegawai->foto_ktp && file_exists(public_path('storage/' . $pegawai->foto_ktp)))
                                        ? asset('storage/' . $pegawai->foto_ktp)
                                        : '';
                                    $pegawaiFotoKtpName = $pegawai->foto_ktp ? basename($pegawai->foto_ktp) : '';

                                    $pegawaiSkPengangkatanUrl = ($pegawai->sk_pengangkatan && file_exists(public_path('storage/' . $pegawai->sk_pengangkatan)))
                                        ? asset('storage/' . $pegawai->sk_pengangkatan)
                                        : '';
                                    $pegawaiSkPengangkatanName = $pegawai->sk_pengangkatan ? basename($pegawai->sk_pengangkatan) : '';

                                    $pegawaiSkPemberhentianUrl = ($pegawai->sk_pemberhentian && file_exists(public_path('storage/' . $pegawai->sk_pemberhentian)))
                                        ? asset('storage/' . $pegawai->sk_pemberhentian)
                                        : '';
                                    $pegawaiSkPemberhentianName = $pegawai->sk_pemberhentian ? basename($pegawai->sk_pemberhentian) : '';

                                    $pegawaiSequence = $loop->iteration;
                                    $pegawaiNikFormatted = $pegawai->nik
                                        ? '#' . $pegawai->nik
                                        : '#PG' . str_pad((string) $pegawai->id, 4, '0', STR_PAD_LEFT);
                                @endphp

                                <article class="table-row"
                                    data-status="{{ \Illuminate\Support\Str::lower($pegawaiStatusStored) }}"
                                    data-search="{{ strtolower(trim(($pegawai->nama ?? '') . ' ' . ($pegawai->jabatan ?? '') . ' ' . ($pegawai->email ?? '') . ' ' . ($pegawai->nomor_hp ?? '') . ' ' . ($pegawai->nik ?? '') . ' ' . ($pegawai->nip ?? '') . ' ' . $pegawaiStatusStored . ' ' . $pegawaiStatusDisplay)) }}"
                                    data-pegawai-nama="{{ $pegawai->nama ?? '' }}" data-pegawai-nik="{{ $pegawai->nik ?? '' }}"
                                    data-pegawai-ttl="{{ trim(($pegawai->tempat_lahir ?? '') . ', ' . $pegawaiTanggalLahirDisplay) }}"
                                    data-pegawai-gender="{{ $pegawai->jenis_kelamin ?? '' }}"
                                    data-pegawai-agama="{{ $pegawai->agama ?? '' }}"
                                    data-pegawai-alamat="{{ $pegawai->alamat ?? '' }}"
                                    data-pegawai-status="{{ $pegawaiStatusDisplay }}"
                                    data-pegawai-email="{{ $pegawai->email ?? '' }}"
                                    data-pegawai-hp="{{ $pegawai->nomor_hp ?? '' }}"
                                    data-pegawai-jabatan="{{ $pegawai->jabatan ?? '' }}"
                                    data-pegawai-nip="{{ $pegawai->nip ?? '' }}"
                                    data-pegawai-masa-jabatan="{{ $pegawaiTenureDisplay }}"
                                    data-pegawai-sk-angkat="{{ $pegawaiSkPengangkatanName ? 'Ada' : 'Tidak' }}"
                                    data-pegawai-sk-berhenti="{{ $pegawaiSkPemberhentianName ? 'Ada' : 'Tidak' }}"
                                    data-pegawai-univ="{{ $pegawai->universitas ?? '' }}"
                                    data-pegawai-pendidikan="{{ $pegawai->pendidikan_terakhir ?? '' }}"
                                    data-pegawai-tahun-lulus="{{ $pegawai->tahun_lulus ?? '' }}"
                                    data-pegawai-sertifikat="{{ $pegawai->sertifikat_pelatihan ?? '' }}"
                                    data-pegawai-bahasa="{{ $pegawai->bahasa ?? '' }}">
                                    <span class="table-cell table-cell--seq">{{ $pegawaiSequence }}</span>
                                    <div class="table-cell table-cell--main">
                                        @if($pegawaiAvatar)
                                            <figure class="table-avatar">
                                                <img src="{{ $pegawaiAvatar }}" alt="{{ $pegawai->nama ?? 'Pegawai Desa' }}"
                                                    loading="lazy">
                                            </figure>
                                        @endif
                                        <div class="table-cell__meta">
                                            <strong>{{ $pegawai->nama ?? 'Nama belum diatur' }}</strong>
                                            <small>{{ $pegawai->email ?? 'Email belum diisi' }}</small>
                                        </div>
                                    </div>
                                    <span class="table-cell">
                                        <span
                                            class="table-chip table-chip--neutral">{{ $pegawai->jabatan ?? 'Belum ditentukan' }}</span>
                                    </span>
                                    <span class="table-cell">
                                        <span class="status-pill {{ $pegawaiStatusClass }}">
                                            <i class="fas {{ $pegawaiStatusIcon }}"></i>
                                            {{ $pegawaiStatusDisplay }}
                                        </span>
                                    </span>
                                    <span class="table-cell table-cell--muted">{{ $pegawaiUpdated }}</span>
                                    <div class="table-cell table-cell--actions">
                                        <button type="button" class="action-icon action-icon--view js-view-pegawai"
                                            aria-label="Lihat pegawai {{ $pegawai->nama }}" data-avatar="{{ $pegawaiAvatar }}"
                                            data-nama="{{ $pegawai->nama ?? '' }}" data-nik="{{ $pegawai->nik ?? '' }}"
                                            data-nip="{{ $pegawai->nip ?? '' }}" data-jabatan="{{ $pegawai->jabatan ?? '' }}"
                                            data-email="{{ $pegawai->email ?? '' }}"
                                            data-nomor-hp="{{ $pegawai->nomor_hp ?? '' }}"
                                            data-jenis-kelamin="{{ $pegawai->jenis_kelamin ?? '' }}"
                                            data-tempat-lahir="{{ $pegawai->tempat_lahir ?? '' }}"
                                            data-tanggal-lahir="{{ $pegawaiTanggalLahirInput }}"
                                            data-tanggal-lahir-display="{{ $pegawaiTanggalLahirDisplay }}"
                                            data-agama="{{ $pegawai->agama ?? '' }}" data-alamat="{{ $pegawai->alamat ?? '' }}"
                                            data-status="{{ $pegawaiStatusDisplay }}"
                                            data-status-raw="{{ $pegawaiStatusStored }}"
                                            data-status-class="{{ $pegawaiStatusClass }}"
                                            data-masa-jabatan-mulai="{{ $pegawaiMasaMulaiInput }}"
                                            data-masa-jabatan-mulai-display="{{ $pegawaiMasaMulaiDisplay }}"
                                            data-masa-jabatan-selesai="{{ $pegawaiMasaSelesaiInput }}"
                                            data-masa-jabatan-selesai-display="{{ $pegawaiMasaSelesaiDisplay }}"
                                            data-tenure-display="{{ $pegawaiTenureDisplay }}"
                                            data-universitas="{{ $pegawai->universitas ?? '' }}"
                                            data-pendidikan-terakhir="{{ $pegawai->pendidikan_terakhir ?? '' }}"
                                            data-tahun-lulus="{{ $pegawai->tahun_lulus ?? '' }}"
                                            data-sertifikat-pelatihan="{{ $pegawai->sertifikat_pelatihan ?? '' }}"
                                            data-bahasa="{{ $pegawai->bahasa ?? '' }}"
                                            data-foto-ktp-url="{{ $pegawaiFotoKtpUrl }}"
                                            data-foto-ktp-name="{{ $pegawaiFotoKtpName }}"
                                            data-sk-pengangkatan-url="{{ $pegawaiSkPengangkatanUrl }}"
                                            data-sk-pengangkatan-name="{{ $pegawaiSkPengangkatanName }}"
                                            data-sk-pemberhentian-url="{{ $pegawaiSkPemberhentianUrl }}"
                                            data-sk-pemberhentian-name="{{ $pegawaiSkPemberhentianName }}"
                                            data-last-login="{{ $pegawaiLastLoginInput }}"
                                            data-last-login-display="{{ $pegawaiLastLoginDisplay }}"
                                            data-updated="{{ $pegawaiUpdated }}" data-created-by="{{ $pegawaiCreatedBy }}"
                                            data-updated-by="{{ $pegawaiUpdatedBy }}"
                                            data-created-at-display="{{ $pegawaiCreatedAtDisplay }}"
                                            data-updated-at-display="{{ $pegawaiUpdatedAtDisplay }}"
                                            data-deleted-at-display="{{ $pegawaiDeletedAtDisplay }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="action-icon action-icon--edit js-edit-pegawai"
                                            aria-label="Edit pegawai {{ $pegawai->nama }}"
                                            data-update="{{ route('admin.pegawai.update', $pegawai) }}"
                                            data-nama="{{ $pegawai->nama ?? '' }}" data-nik="{{ $pegawai->nik ?? '' }}"
                                            data-nip="{{ $pegawai->nip ?? '' }}" data-jabatan="{{ $pegawai->jabatan ?? '' }}"
                                            data-email="{{ $pegawai->email ?? '' }}"
                                            data-nomor-hp="{{ $pegawai->nomor_hp ?? '' }}"
                                            data-jenis-kelamin="{{ $pegawai->jenis_kelamin ?? '' }}"
                                            data-tempat-lahir="{{ $pegawai->tempat_lahir ?? '' }}"
                                            data-tanggal-lahir="{{ $pegawaiTanggalLahirInput }}"
                                            data-agama="{{ $pegawai->agama ?? '' }}" data-alamat="{{ $pegawai->alamat ?? '' }}"
                                            data-status="{{ $pegawaiStatusStored }}"
                                            data-status-raw="{{ $pegawaiStatusStored }}"
                                            data-masa-jabatan-mulai="{{ $pegawaiMasaMulaiInput }}"
                                            data-masa-jabatan-selesai="{{ $pegawaiMasaSelesaiInput }}"
                                            data-universitas="{{ $pegawai->universitas ?? '' }}"
                                            data-pendidikan-terakhir="{{ $pegawai->pendidikan_terakhir ?? '' }}"
                                            data-tahun-lulus="{{ $pegawai->tahun_lulus ?? '' }}"
                                            data-sertifikat-pelatihan="{{ $pegawai->sertifikat_pelatihan ?? '' }}"
                                            data-bahasa="{{ $pegawai->bahasa ?? '' }}"
                                            data-foto-ktp-url="{{ $pegawaiFotoKtpUrl }}"
                                            data-foto-ktp-name="{{ $pegawaiFotoKtpName }}"
                                            data-sk-pengangkatan-url="{{ $pegawaiSkPengangkatanUrl }}"
                                            data-sk-pengangkatan-name="{{ $pegawaiSkPengangkatanName }}"
                                            data-sk-pemberhentian-url="{{ $pegawaiSkPemberhentianUrl }}"
                                            data-sk-pemberhentian-name="{{ $pegawaiSkPemberhentianName }}"
                                            data-last-login="{{ $pegawaiLastLoginInput }}"
                                            data-created-by="{{ $pegawaiCreatedBy }}" data-updated-by="{{ $pegawaiUpdatedBy }}"
                                            data-created-at-display="{{ $pegawaiCreatedAtDisplay }}"
                                            data-updated-at-display="{{ $pegawaiUpdatedAtDisplay }}"
                                            data-deleted-at-display="{{ $pegawaiDeletedAtDisplay }}"
                                            data-avatar="{{ $pegawaiAvatar }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <form method="POST" action="{{ route('admin.pegawai.destroy', $pegawai) }}"
                                            class="js-delete-form"
                                            data-confirm-message="Hapus data pegawai {{ $pegawai->nama }}?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-icon action-icon--delete"
                                                aria-label="Hapus pegawai {{ $pegawai->nama }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @empty
                                @php
                                    $contohPegawai = collect([
                                        ['nama' => 'Rizki Ananda', 'jabatan' => 'Sekretaris Desa', 'email' => 'rizki.ananda@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5601', 'status' => 'Aktif', 'updated' => now()->subDays(2)->format('d M Y H:i')],
                                        ['nama' => 'Maya Pratiwi', 'jabatan' => 'Kaur Keuangan', 'email' => 'maya.pratiwi@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5602', 'status' => 'Aktif', 'updated' => now()->subDays(1)->format('d M Y H:i')],
                                        ['nama' => 'Bagus Ramdhan', 'jabatan' => 'Kaur Perencanaan', 'email' => 'bagus.ramdhan@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5603', 'status' => 'Aktif', 'updated' => now()->subDays(4)->format('d M Y H:i')],
                                        ['nama' => 'Dewi Kartika', 'jabatan' => 'Kepala Dusun I', 'email' => 'dewi.kartika@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5604', 'status' => 'Aktif', 'updated' => now()->subDays(6)->format('d M Y H:i')],
                                        ['nama' => 'Arman Saputra', 'jabatan' => 'Kepala Dusun II', 'email' => 'arman.saputra@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5605', 'status' => 'Aktif', 'updated' => now()->subDays(3)->format('d M Y H:i')],
                                        ['nama' => 'Laras Salsabila', 'jabatan' => 'Kepala Dusun III', 'email' => 'laras.salsabila@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5606', 'status' => 'Tidak aktif', 'updated' => now()->subDays(8)->format('d M Y H:i')],
                                        ['nama' => 'Farhan Nugraha', 'jabatan' => 'Staf Pelayanan', 'email' => 'farhan.nugraha@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5607', 'status' => 'Aktif', 'updated' => now()->subDays(5)->format('d M Y H:i')],
                                        ['nama' => 'Kirana Ayu', 'jabatan' => 'Staf Administrasi', 'email' => 'kirana.ayu@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5608', 'status' => 'Aktif', 'updated' => now()->subDays(7)->format('d M Y H:i')],
                                        ['nama' => 'Yoga Prakoso', 'jabatan' => 'Operator Data', 'email' => 'yoga.prakoso@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5609', 'status' => 'Aktif', 'updated' => now()->subDays(9)->format('d M Y H:i')],
                                        ['nama' => 'Sinta Rahayu', 'jabatan' => 'Bendahara BUMDes', 'email' => 'sinta.rahayu@tanjungkesuma.id', 'nomor_hp' => '0812-1234-5610', 'status' => 'Aktif', 'updated' => now()->subDays(10)->format('d M Y H:i')],
                                    ]);
                                @endphp
                                @foreach ($contohPegawai as $contoh)
                                    @php
                                        $statusNormalized = strtolower(str_replace(['_', '-'], ' ', $contoh['status']));
                                        $statusNormalized = preg_replace('/\s+/', ' ', $statusNormalized);
                                        $isActive = in_array($statusNormalized, ['aktif', 'active', 'masih bekerja']);
                                        $statusDisplay = $isActive ? 'Masih bekerja' : 'Sudah tidak bekerja';
                                        $statusStored = $isActive ? 'Aktif' : 'Tidak aktif';
                                        $statusClass = $isActive ? 'status-active' : 'status-draft';
                                    @endphp
                                    <article class="table-row is-demo" data-status="{{ strtolower($statusStored) }}"
                                        data-search="{{ strtolower(trim(($contoh['nama'] ?? '') . ' ' . ($contoh['jabatan'] ?? '') . ' ' . ($contoh['email'] ?? '') . ' ' . ($contoh['nomor_hp'] ?? '') . ' ' . $statusStored . ' ' . $statusDisplay)) }}">
                                        <span class="table-cell table-cell--seq">{{ $loop->iteration }}</span>
                                        <div class="table-cell table-cell--main">
                                            <figure class="table-avatar">
                                            </figure>
                                            <div class="table-cell__meta">
                                                <strong>{{ $contoh['nama'] }}</strong>
                                                <small>{{ $contoh['email'] }}</small>
                                            </div>
                                        </div>
                                        <span class="table-cell">
                                            <span class="table-chip table-chip--neutral">{{ $contoh['jabatan'] }}</span>
                                        </span>
                                        <span class="table-cell">
                                            <span class="status-pill {{ $statusClass }}">
                                                <i class="fas {{ $isActive ? 'fa-circle-check' : 'fa-circle-xmark' }}"></i>
                                                {{ $statusDisplay }}
                                            </span>
                                        </span>
                                        <span class="table-cell table-cell--muted">{{ $contoh['updated'] }}</span>
                                        <div class="table-cell table-cell--actions">
                                            <button type="button" class="action-icon action-icon--view js-view-pegawai"
                                                aria-label="Lihat pegawai {{ $contoh['nama'] }}"
                                                data-avatar="{{ $storageDefaultAvatar }}" data-nama="{{ $contoh['nama'] }}"
                                                data-jabatan="{{ $contoh['jabatan'] }}" data-email="{{ $contoh['email'] }}"
                                                data-nomor-hp="{{ $contoh['nomor_hp'] }}" data-status="{{ $statusDisplay }}"
                                                data-status-raw="{{ $statusStored }}" data-status-class="{{ $statusClass }}"
                                                data-updated="{{ $contoh['updated'] }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <span class="demo-chip" aria-hidden="true">Contoh</span>
                                        </div>
                                    </article>
                                @endforeach
                            @endforelse
                            @if ($pegawais->isNotEmpty())
                                <div class="table-empty table-empty--search" data-empty="pegawai" hidden aria-hidden="true">
                                    <i class="fas fa-circle-info"></i>
                                    <p>Tidak ditemukan pegawai sesuai pencarian.</p>
                                </div>
                            @endif
                        </div>
                        <footer class="table-footer">
                            <div class="table-info" data-entries-info="pegawai" aria-live="polite">Menampilkan 0 data</div>
                            <nav class="table-pagination" data-pagination="pegawai"
                                aria-label="Navigasi halaman data pegawai"></nav>
                            <label class="entries-control" for="pegawaiEntries">
                                <span>Tampilkan</span>
                                <select id="pegawaiEntries" data-entries-select="pegawai">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select>
                                <span>data</span>
                            </label>
                        </footer>
                    </section>
            </div>
            </article>
    </div>
    </section>
    </div>
    </div>

    {{-- Modal Form Admin --}}
    <div class="dialog-backdrop" id="adminFormModal" aria-hidden="true">
        <div class="dialog dialog--wide" role="dialog" aria-modal="true" aria-labelledby="userFormTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="userFormTitle">Tambah Admin</h2>
                    <p id="userFormSubtitle">Isi detail admin untuk memberikan akses dashboard.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form admin">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <form id="userForm" class="panel-form modal-form" method="POST" action="{{ route('admin.users.store') }}"
                    enctype="multipart/form-data" data-create-action="{{ route('admin.users.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-method-field>
                    <input type="hidden" name="context_tab" value="users">
                    <input type="hidden" name="remove_gambar" value="0" data-remove-field>

                    <div class="avatar-field">
                        <figure>
                            <img id="userAvatarPreview" src="" alt="Foto admin">
                        </figure>
                        <div class="avatar-field-controls">
                            <label class="ghost-btn">
                                <i class="fas fa-upload"></i>
                                <span>Unggah foto</span>
                                <input type="file" name="gambar" accept="image/*" data-preview="userAvatarPreview" hidden>
                            </label>
                            <button type="button" class="outline-btn" data-action="remove-user-photo">
                                <i class="fas fa-trash"></i>
                                <span>Hapus foto</span>
                            </button>
                        </div>
                    </div>

                    <div class="settings-form-grid two-columns">
                        <label class="form-field">
                            <span>Nama Lengkap</span>
                            <input type="text" name="nama" value="{{ old('nama') }}" required>
                        </label>
                        <label class="form-field">
                            <span>Email</span>
                            <input type="email" name="email" value="{{ old('email') }}" required>
                        </label>
                        <label class="form-field">
                            <span>Password</span>
                            <input type="password" name="password" required>
                        </label>
                        <label class="form-field">
                            <span>Konfirmasi Password</span>
                            <input type="password" name="password_confirmation" required>
                        </label>
                        <label class="form-field">
                            <span>Nomor HP</span>
                            <input type="text" name="nomor_hp" value="{{ old('nomor_hp') }}">
                        </label>
                    </div>

                    <div class="toggle-group">
                        <input type="hidden" name="is_admin" value="0">
                        <label class="toggle-control">
                            <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', true))>
                            <span class="toggle-copy">
                                <strong>Super Admin</strong>
                                <small>Memberikan akses penuh ke seluruh modul pengaturan.</small>
                            </span>
                        </label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="toggle-control">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                            <span class="toggle-copy">
                                <strong>Aktifkan Akun</strong>
                                <small>Nonaktifkan bila akun tidak lagi diberikan akses ke dashboard.</small>
                            </span>
                        </label>
                    </div>

                    <div class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-dismiss="admin">Batalkan</button>
                        <button type="submit" class="primary-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Form Pegawai --}}
    <div class="dialog-backdrop" id="pegawaiFormModal" aria-hidden="true">
        <div class="dialog dialog--wide" role="dialog" aria-modal="true" aria-labelledby="pegawaiFormTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="pegawaiFormTitle">Tambah Pegawai</h2>
                    <p id="pegawaiFormSubtitle">Lengkapi informasi aparatur desa untuk publikasi.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup form pegawai">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <form id="pegawaiForm" class="panel-form modal-form" method="POST"
                    action="{{ route('admin.pegawai.store') }}" enctype="multipart/form-data"
                    data-create-action="{{ route('admin.pegawai.store') }}">
                    @csrf
                    <input type="hidden" name="_method" value="POST" data-method-field>
                    <input type="hidden" name="context_tab" value="pegawai">
                    <input type="hidden" name="remove_gambar" value="0" data-remove-field>

                    <div class="avatar-field">
                        <figure>
                            <img id="pegawaiAvatarPreview" src="" alt="Foto pegawai">
                        </figure>
                        <div class="avatar-field-controls">
                            <label class="ghost-btn">
                                <i class="fas fa-upload"></i>
                                <span>Unggah foto</span>
                                <input type="file" name="gambar" accept="image/*" data-preview="pegawaiAvatarPreview"
                                    hidden>
                            </label>
                            <button type="button" class="outline-btn" data-action="remove-pegawai-photo">
                                <i class="fas fa-trash"></i>
                                <span>Hapus foto</span>
                            </button>
                        </div>
                    </div>

                    @php
                        $pegawaiStatusOld = old('status', 'Aktif');
                        $statusOptions = [
                            ['value' => 'Aktif', 'label' => 'Aktif - Masih bekerja'],
                            ['value' => 'Cuti', 'label' => 'Cuti - Sementara tidak bertugas'],
                            ['value' => 'Pensiun', 'label' => 'Pensiun - Purna tugas'],
                            ['value' => 'Tidak Aktif', 'label' => 'Tidak aktif - Tidak lagi bertugas'],
                        ];
                        $statusDefaultLabel = collect($statusOptions)
                            ->firstWhere('value', $pegawaiStatusOld)['label']
                            ?? $statusOptions[0]['label'];
                        $pegawaiGenderOld = old('jenis_kelamin', 'Laki-laki');
                        $genderOptions = [
                            ['value' => 'Laki-laki', 'label' => 'Laki-laki'],
                            ['value' => 'Perempuan', 'label' => 'Perempuan'],
                        ];
                        $genderDefaultLabel = collect($genderOptions)
                            ->firstWhere('value', $pegawaiGenderOld)['label']
                            ?? $genderOptions[0]['label'];
                    @endphp

                    <div class="form-section">
                        <div class="form-section__header">
                            <h3>Data Pribadi</h3>
                            <p>Identitas utama aparatur desa.</p>
                        </div>
                        <div class="settings-form-grid two-columns">
                            <label class="form-field">
                                <span>NIK</span>
                                <input type="text" name="nik" value="{{ old('nik') }}" maxlength="20" required>
                            </label>
                            <label class="form-field">
                                <span>Nama Lengkap</span>
                                <input type="text" name="nama" value="{{ old('nama') }}" required>
                            </label>
                            <label class="form-field">
                                <span>Email</span>
                                <input type="email" name="email" value="{{ old('email') }}" required>
                            </label>
                            <label class="form-field">
                                <span>Nomor HP</span>
                                <input type="text" name="nomor_hp" value="{{ old('nomor_hp') }}" maxlength="20" required>
                            </label>
                            <label class="form-field">
                                <span>Jabatan</span>
                                <input type="text" name="jabatan" value="{{ old('jabatan') }}" required>
                            </label>
                            <label class="form-field">
                                <span>Jenis Kelamin</span>
                                <div class="select-wrapper" data-fancy-select>
                                    <select name="jenis_kelamin" data-fancy-native hidden>
                                        @foreach ($genderOptions as $option)
                                            <option value="{{ $option['value'] }}" {{ strcasecmp($pegawaiGenderOld, $option['value']) === 0 ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="select-trigger" data-fancy-trigger aria-haspopup="listbox"
                                        aria-expanded="false">
                                        <span data-fancy-label>{{ $genderDefaultLabel }}</span>
                                        <svg data-fancy-arrow viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path fill="currentColor" d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z">
                                            </path>
                                        </svg>
                                    </button>
                                    <div class="select-dropdown" data-fancy-dropdown role="listbox" hidden>
                                        @foreach ($genderOptions as $option)
                                            @php
                                                $isSelected = strcasecmp($pegawaiGenderOld, $option['value']) === 0;
                                            @endphp
                                            <button type="button" class="select-option{{ $isSelected ? ' is-active' : '' }}"
                                                data-fancy-option data-value="{{ $option['value'] }}"
                                                data-label="{{ $option['label'] }}" role="option"
                                                aria-selected="{{ $isSelected ? 'true' : 'false' }}" tabindex="-1">
                                                <span>{{ $option['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </label>
                            <label class="form-field">
                                <span>Tempat Lahir</span>
                                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}">
                            </label>
                            <label class="form-field">
                                <span>Tanggal Lahir</span>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                            </label>
                            <label class="form-field">
                                <span>Agama</span>
                                <input type="text" name="agama" value="{{ old('agama') }}">
                            </label>
                            <label class="form-field form-field--full">
                                <span>Alamat Domisili</span>
                                <textarea name="alamat" rows="3"
                                    placeholder="Contoh: Jl. Melati No. 5, Desa Tanjung Kesuma">{{ old('alamat') }}</textarea>
                            </label>
                            <div class="form-field form-field--full">
                                <span>Foto KTP</span>
                                <div class="upload-card upload-card--file">
                                    <div class="upload-card__dropzone">
                                        <input type="file" name="foto_ktp" accept="image/*" id="pegawaiKtpInput" hidden>
                                        <label for="pegawaiKtpInput" class="upload-card__label">
                                            <span class="upload-card__icon"><i class="fas fa-id-card"></i></span>
                                            <strong>Unggah Foto KTP</strong>
                                            <small>JPG / PNG maksimal 2 MB</small>
                                        </label>
                                    </div>
                                    <div class="upload-card__info" data-current-ktp hidden>
                                        <i class="fas fa-image"></i>
                                        <a href="#" target="_blank" rel="noopener" data-current-ktp-link>lihat foto KTP</a>
                                    </div>
                                    <label class="form-checkbox-inline" data-remove-ktp hidden>
                                        <input type="checkbox" name="remove_foto_ktp" value="1">
                                        <span>Hapus foto KTP yang tersimpan</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section__header">
                            <h3>Data Jabatan &amp; Pekerjaan</h3>
                            <p>Riwayat penugasan aparatur desa.</p>
                        </div>
                        <div class="settings-form-grid two-columns">
                            <label class="form-field">
                                <span>NIP</span>
                                <input type="text" name="nip" value="{{ old('nip') }}" maxlength="20">
                            </label>
                            <label class="form-field">
                                <span>Status Kepegawaian</span>
                                <div class="select-wrapper" data-fancy-select>
                                    <select name="status" data-fancy-native hidden>
                                        @foreach ($statusOptions as $option)
                                            <option value="{{ $option['value'] }}" {{ strcasecmp($pegawaiStatusOld, $option['value']) === 0 ? 'selected' : '' }}>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="select-trigger" data-fancy-trigger aria-haspopup="listbox"
                                        aria-expanded="false">
                                        <span data-fancy-label>{{ $statusDefaultLabel }}</span>
                                        <svg data-fancy-arrow viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path fill="currentColor" d="M7.41 8.59 12 13.17l4.59-4.58L18 10l-6 6-6-6z">
                                            </path>
                                        </svg>
                                    </button>
                                    <div class="select-dropdown" data-fancy-dropdown role="listbox" hidden>
                                        @foreach ($statusOptions as $option)
                                            @php
                                                $isSelected = strcasecmp($pegawaiStatusOld, $option['value']) === 0;
                                            @endphp
                                            <button type="button" class="select-option{{ $isSelected ? ' is-active' : '' }}"
                                                data-fancy-option data-value="{{ $option['value'] }}"
                                                data-label="{{ $option['label'] }}" role="option"
                                                aria-selected="{{ $isSelected ? 'true' : 'false' }}" tabindex="-1">
                                                <span>{{ $option['label'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </label>
                            <label class="form-field">
                                <span>Masa Jabatan Mulai</span>
                                <input type="date" name="masa_jabatan_mulai" value="{{ old('masa_jabatan_mulai') }}">
                            </label>
                            <label class="form-field">
                                <span>Masa Jabatan Selesai</span>
                                <input type="date" name="masa_jabatan_selesai" value="{{ old('masa_jabatan_selesai') }}">
                            </label>
                            <div class="form-field form-field--full">
                                <span>SK Pengangkatan</span>
                                <div class="upload-card upload-card--file">
                                    <div class="upload-card__dropzone">
                                        <input type="file" name="sk_pengangkatan" accept="application/pdf"
                                            id="pegawaiSkPengangkatanInput" hidden>
                                        <label for="pegawaiSkPengangkatanInput" class="upload-card__label">
                                            <span class="upload-card__icon"><i class="fas fa-file-upload"></i></span>
                                            <strong>Unggah SK Pengangkatan</strong>
                                            <small>Format PDF maksimal 4 MB</small>
                                        </label>
                                    </div>
                                    <div class="upload-card__info" data-current-sk-pengangkatan hidden>
                                        <i class="fas fa-file-pdf"></i>
                                        <a href="#" target="_blank" rel="noopener" data-current-sk-pengangkatan-link>lihat
                                            dokumen</a>
                                    </div>
                                    <label class="form-checkbox-inline" data-remove-sk-pengangkatan hidden>
                                        <input type="checkbox" name="remove_sk_pengangkatan" value="1">
                                        <span>Hapus dokumen yang tersimpan</span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-field form-field--full">
                                <span>SK Pemberhentian</span>
                                <div class="upload-card upload-card--file">
                                    <div class="upload-card__dropzone">
                                        <input type="file" name="sk_pemberhentian" accept="application/pdf"
                                            id="pegawaiSkPemberhentianInput" hidden>
                                        <label for="pegawaiSkPemberhentianInput" class="upload-card__label">
                                            <span class="upload-card__icon"><i class="fas fa-file-upload"></i></span>
                                            <strong>Unggah SK Pemberhentian</strong>
                                            <small>Opsional, PDF maksimal 4 MB</small>
                                        </label>
                                    </div>
                                    <div class="upload-card__info" data-current-sk-pemberhentian hidden>
                                        <i class="fas fa-file-pdf"></i>
                                        <a href="#" target="_blank" rel="noopener" data-current-sk-pemberhentian-link>lihat
                                            dokumen</a>
                                    </div>
                                    <label class="form-checkbox-inline" data-remove-sk-pemberhentian hidden>
                                        <input type="checkbox" name="remove_sk_pemberhentian" value="1">
                                        <span>Hapus dokumen yang tersimpan</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section__header">
                            <h3>Data Pendidikan &amp; Keahlian</h3>
                            <p>Riwayat pendidikan terakhir dan kompetensi.</p>
                        </div>
                        <div class="settings-form-grid two-columns">
                            <label class="form-field">
                                <span>Universitas</span>
                                <input type="text" name="universitas" value="{{ old('universitas') }}">
                            </label>
                            <label class="form-field">
                                <span>Pendidikan Terakhir</span>
                                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}">
                            </label>
                            <label class="form-field">
                                <span>Tahun Lulus</span>
                                <input type="text" name="tahun_lulus" value="{{ old('tahun_lulus') }}" maxlength="4"
                                    placeholder="Contoh: 2018">
                            </label>
                            <label class="form-field">
                                <span>Bahasa</span>
                                <input type="text" name="bahasa" value="{{ old('bahasa') }}"
                                    placeholder="Contoh: Indonesia, Inggris">
                            </label>
                            <label class="form-field form-field--full">
                                <span>Sertifikat Pelatihan / Kursus</span>
                                <textarea name="sertifikat_pelatihan" rows="3"
                                    placeholder="Contoh: Pelatihan Tata Naskah Dinas, 2022">{{ old('sertifikat_pelatihan') }}</textarea>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section__header">
                            <h3>Data Sistem &amp; Aktivitas</h3>
                            <p>Parameter aktivitas akun aparatur desa.</p>
                        </div>
                        <div class="settings-form-grid two-columns">
                            <label class="form-field">
                                <span>Last Login</span>
                                <input type="datetime-local" name="last_login" value="{{ old('last_login') }}">
                            </label>
                            <div class="form-field form-field--static">
                                <span>Created By</span>
                                <p data-meta="created-by">-</p>
                            </div>
                            <div class="form-field form-field--static">
                                <span>Updated By</span>
                                <p data-meta="updated-by">-</p>
                            </div>
                            <div class="form-field form-field--static">
                                <span>Created At</span>
                                <p data-meta="created-at">-</p>
                            </div>
                            <div class="form-field form-field--static">
                                <span>Updated At</span>
                                <p data-meta="updated-at">-</p>
                            </div>
                            <div class="form-field form-field--static">
                                <span>Deleted At</span>
                                <p data-meta="deleted-at">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="dialog__footer">
                        <button type="button" class="ghost-btn" data-modal-dismiss="pegawai">Batalkan</button>
                        <button type="submit" class="primary-btn">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Detail Data --}}
    <div class="dialog-backdrop" id="detailModal" aria-hidden="true">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="detailModalTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="detailModalTitle">Detail Data</h2>
                    <p id="detailModalSubtitle">Informasi ringkas data terpilih.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup detail">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body detail-view">
                <figure class="detail-view__avatar">
                    <img id="detailModalAvatar" src="" alt="Avatar akun">
                </figure>
                <div class="detail-view__meta">
                    <h3 id="detailModalName">Nama Lengkap</h3>
                    <span class="detail-status-chip" id="detailModalStatus">Status</span>
                </div>
                <div class="detail-view__content">
                    <dl class="detail-view__list" id="detailSummary">
                        <div>
                            <dt>Email</dt>
                            <dd id="detailModalEmail">-</dd>
                        </div>
                        <div>
                            <dt>Kontak</dt>
                            <dd id="detailModalPhone">-</dd>
                        </div>
                        <div>
                            <dt id="detailModalInfoLabel">Keterangan</dt>
                            <dd id="detailModalInfoValue">-</dd>
                        </div>
                        <div>
                            <dt id="detailModalMetaLabel">Pembaruan</dt>
                            <dd id="detailModalMetaValue">-</dd>
                        </div>
                        <div>
                            <dt>Lokasi Login</dt>
                            <dd id="detailModalLoginLocation">-</dd>
                        </div>
                        <div>
                            <dt>Alamat IP</dt>
                            <dd id="detailModalLoginIp">-</dd>
                        </div>
                    </dl>

                    <div class="detail-sections" data-pegawai-detail hidden>
                        <section class="detail-section">
                            <h4><i class="fas fa-id-card"></i><span>Data Pribadi</span></h4>
                            <dl class="detail-grid">
                                <div>
                                    <dt>NIK</dt>
                                    <dd id="detailNik">-</dd>
                                </div>
                                <div>
                                    <dt>Tempat, Tanggal Lahir</dt>
                                    <dd id="detailBirth">-</dd>
                                </div>
                                <div>
                                    <dt>Jenis Kelamin</dt>
                                    <dd id="detailGender">-</dd>
                                </div>
                                <div>
                                    <dt>Agama</dt>
                                    <dd id="detailReligion">-</dd>
                                </div>
                                <div class="detail-grid__full">
                                    <dt>Alamat</dt>
                                    <dd id="detailAddress">-</dd>
                                </div>
                                <div>
                                    <dt>Foto KTP</dt>
                                    <dd id="detailFotoKtp">
                                        <span data-empty>Belum diunggah</span>
                                        <a href="#" target="_blank" rel="noopener" data-file-link="ktp" hidden>Lihat
                                            dokumen</a>
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section class="detail-section">
                            <h4><i class="fas fa-briefcase"></i><span>Data Jabatan &amp; Pekerjaan</span></h4>
                            <dl class="detail-grid">
                                <div>
                                    <dt>NIP</dt>
                                    <dd id="detailNip">-</dd>
                                </div>
                                <div>
                                    <dt>Jabatan</dt>
                                    <dd id="detailJob">-</dd>
                                </div>
                                <div>
                                    <dt>Status Kepegawaian</dt>
                                    <dd id="detailEmploymentStatus">-</dd>
                                </div>
                                <div>
                                    <dt>Masa Jabatan</dt>
                                    <dd id="detailTenure">-</dd>
                                </div>
                                <div>
                                    <dt>SK Pengangkatan</dt>
                                    <dd id="detailSkPengangkatan">
                                        <span data-empty>Belum diunggah</span>
                                        <a href="#" target="_blank" rel="noopener" data-file-link="sk-pengangkatan"
                                            hidden>Lihat dokumen</a>
                                    </dd>
                                </div>
                                <div>
                                    <dt>SK Pemberhentian</dt>
                                    <dd id="detailSkPemberhentian">
                                        <span data-empty>Belum diunggah</span>
                                        <a href="#" target="_blank" rel="noopener" data-file-link="sk-pemberhentian"
                                            hidden>Lihat dokumen</a>
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section class="detail-section">
                            <h4><i class="fas fa-graduation-cap"></i><span>Data Pendidikan &amp; Keahlian</span></h4>
                            <dl class="detail-grid">
                                <div>
                                    <dt>Universitas</dt>
                                    <dd id="detailUniversity">-</dd>
                                </div>
                                <div>
                                    <dt>Pendidikan Terakhir</dt>
                                    <dd id="detailEducation">-</dd>
                                </div>
                                <div>
                                    <dt>Tahun Lulus</dt>
                                    <dd id="detailGraduationYear">-</dd>
                                </div>
                                <div class="detail-grid__full">
                                    <dt>Sertifikat Pelatihan / Kursus</dt>
                                    <dd id="detailCertifications">-</dd>
                                </div>
                                <div class="detail-grid__full">
                                    <dt>Bahasa</dt>
                                    <dd id="detailLanguages">-</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="detail-section">
                            <h4><i class="fas fa-gear"></i><span>Data Sistem &amp; Aktivitas</span></h4>
                            <dl class="detail-grid">
                                <div>
                                    <dt>Last Login</dt>
                                    <dd id="detailLastLogin">-</dd>
                                </div>
                                <div>
                                    <dt>Created By</dt>
                                    <dd id="detailCreatedBy">-</dd>
                                </div>
                                <div>
                                    <dt>Updated By</dt>
                                    <dd id="detailUpdatedBy">-</dd>
                                </div>
                                <div>
                                    <dt>Created At</dt>
                                    <dd id="detailCreatedAt">-</dd>
                                </div>
                                <div>
                                    <dt>Updated At</dt>
                                    <dd id="detailUpdatedAt">-</dd>
                                </div>
                                <div>
                                    <dt>Deleted At</dt>
                                    <dd id="detailDeletedAt">-</dd>
                                </div>
                            </dl>
                        </section>
                    </div>
                </div>
            </div>
            <div class="dialog__footer">
                <button type="button" class="outline-btn" data-modal-close>Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi --}}
    <div class="dialog-backdrop" id="confirmModal" aria-hidden="true">
        <div class="dialog dialog--confirm" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
            <header class="dialog__header">
                <div>
                    <h2 id="confirmModalTitle">Konfirmasi Aksi</h2>
                    <p>Pastikan tindakan yang akan dilakukan sudah sesuai.</p>
                </div>
                <button type="button" class="dialog__close" data-modal-close aria-label="Tutup konfirmasi">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="dialog__body">
                <p id="confirmModalMessage">Yakin ingin melanjutkan tindakan ini?</p>
            </div>
            <div class="dialog__footer dialog__footer--justify">
                <button type="button" class="ghost-btn" data-confirm-cancel>Batalkan</button>
                <button type="button" class="danger-btn" data-confirm-accept>
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;

            const tabButtons = Array.from(document.querySelectorAll('[data-settings-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
            const actionButtons = Array.from(document.querySelectorAll('[data-tab-visible]'));

            const toggleActionButtons = (target) => {
                actionButtons.forEach((button) => {
                    const isVisible = button.dataset.tabVisible === target;
                    button.hidden = !isVisible;
                    button.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
                });
            };

            const setActiveTab = (target) => {
                if (!target) return;
                tabButtons.forEach((button) => {
                    const isActive = button.dataset.settingsTab === target;
                    button.classList.toggle('is-active', isActive);
                });
                panels.forEach((panel) => {
                    const isActive = panel.dataset.settingsPanel === target;
                    panel.classList.toggle('is-active', isActive);
                    panel.toggleAttribute('hidden', !isActive);
                });
                toggleActionButtons(target);
            };

            tabButtons.forEach((button) => {
                button.addEventListener('click', () => setActiveTab(button.dataset.settingsTab));
            });

            const defaultTab =
                tabButtons.find((button) => button.classList.contains('is-active'))?.dataset.settingsTab ??
                panels[0]?.dataset.settingsPanel;
            if (defaultTab) {
                setActiveTab(defaultTab);
            } else if (panels[0]) {
                toggleActionButtons(panels[0].dataset.settingsPanel);
            }

            const TOAST_DURATION = 5000;
            const dismissToast = (toast) => {
                if (!toast || toast.classList.contains('is-leaving')) return;
                toast.classList.add('is-leaving');
                window.setTimeout(() => toast.remove(), 260);
            };

            document.querySelectorAll('[data-toast]').forEach((toast) => {
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

            const createTableManager = (config) => {
                const rows = Array.from(document.querySelectorAll(config.rowsSelector));
                const searchInput = document.querySelector(config.searchInput);
                const entriesSelect = document.querySelector(config.entriesSelect);
                const infoTarget = document.querySelector(config.infoTarget);
                const paginationTarget = document.querySelector(config.paginationTarget);
                const emptyElements = Array.from(document.querySelectorAll(config.emptySelector));
                const searchEmpty = emptyElements.find((element) => element.classList.contains('table-empty--search'));
                const baseEmpty = emptyElements.find((element) => !element.classList.contains('table-empty--search'));

                const state = {
                    rows,
                    filteredRows: rows.slice(),
                    perPage: parseInt(entriesSelect?.value ?? config.defaultPerPage ?? 10, 10),
                    page: 1,
                    totalPages: 1,
                    activeFilters: {},
                };

                function hideAllRows() {
                    state.rows.forEach((row) => {
                        row.hidden = true;
                        row.classList.add('is-hidden');
                    });
                }

                function updateRows() {
                    hideAllRows();
                    if (state.filteredRows.length === 0) {
                        return;
                    }
                    const startIndex = (state.page - 1) * state.perPage;
                    const endIndex = startIndex + state.perPage;
                    state.filteredRows.slice(startIndex, endIndex).forEach((row) => {
                        row.hidden = false;
                        row.classList.remove('is-hidden');
                    });
                }

                function updateInfo() {
                    if (!infoTarget) return;
                    const total = state.filteredRows.length;
                    if (total === 0) {
                        infoTarget.textContent = 'Menampilkan 0 data';
                        return;
                    }
                    const startIndex = (state.page - 1) * state.perPage + 1;
                    const endIndex = Math.min(startIndex + state.perPage - 1, total);
                    infoTarget.textContent = `Menampilkan ${startIndex}-${endIndex} dari ${total} data`;
                }

                function updateEmptyStates() {
                    const hasRows = state.rows.length > 0;
                    const hasFiltered = state.filteredRows.length > 0;
                    if (!hasRows) {
                        if (searchEmpty) {
                            searchEmpty.hidden = true;
                            searchEmpty.setAttribute('aria-hidden', 'true');
                        }
                        if (baseEmpty) {
                            baseEmpty.hidden = false;
                            baseEmpty.setAttribute('aria-hidden', 'false');
                        }
                        return;
                    }
                    if (!hasFiltered) {
                        if (searchEmpty) {
                            searchEmpty.hidden = false;
                            searchEmpty.setAttribute('aria-hidden', 'false');
                        }
                        if (baseEmpty) {
                            baseEmpty.hidden = true;
                            baseEmpty.setAttribute('aria-hidden', 'true');
                        }
                    } else {
                        if (searchEmpty) {
                            searchEmpty.hidden = true;
                            searchEmpty.setAttribute('aria-hidden', 'true');
                        }
                        if (baseEmpty) {
                            baseEmpty.hidden = true;
                            baseEmpty.setAttribute('aria-hidden', 'true');
                        }
                    }
                }

                function goToPage(pageNumber) {
                    const totalPages = Math.max(1, state.totalPages);
                    const clamped = Math.min(Math.max(pageNumber, 1), totalPages);
                    if (clamped === state.page) return;
                    state.page = clamped;
                    updateRowsAndUI();
                }

                function renderPagination() {
                    if (!paginationTarget) return;
                    const total = state.filteredRows.length;
                    state.totalPages = total === 0 ? 1 : Math.ceil(total / Math.max(state.perPage, 1));
                    paginationTarget.innerHTML = '';
                    if (total <= state.perPage) {
                        paginationTarget.hidden = true;
                        return;
                    }
                    paginationTarget.hidden = false;

                    const createButton = (label, pageNumber, options = {}) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'pagination-btn';
                        if (options.disabled) {
                            button.disabled = true;
                            button.classList.add('is-disabled');
                        }
                        if (options.active) {
                            button.classList.add('is-active');
                            button.setAttribute('aria-current', 'page');
                        }
                        if (options.ariaLabel) {
                            button.setAttribute('aria-label', options.ariaLabel);
                        }
                        button.textContent = label;
                        if (!options.disabled && !options.active) {
                            button.addEventListener('click', () => goToPage(pageNumber));
                        }
                        return button;
                    };

                    const createEllipsis = () => {
                        const span = document.createElement('span');
                        span.className = 'pagination-ellipsis';
                        span.textContent = '...';
                        return span;
                    };

                    paginationTarget.appendChild(
                        createButton('Sebelumnya', state.page - 1, {
                            disabled: state.page <= 1,
                            ariaLabel: 'Halaman sebelumnya',
                        })
                    );

                    const maxButtons = 5;
                    let startPage = Math.max(1, state.page - 2);
                    let endPage = Math.min(state.totalPages, startPage + maxButtons - 1);
                    startPage = Math.max(1, endPage - maxButtons + 1);

                    if (startPage > 1) {
                        paginationTarget.appendChild(createButton('1', 1, { active: state.page === 1 }));
                        if (startPage > 2) {
                            paginationTarget.appendChild(createEllipsis());
                        }
                    }

                    for (let pageNumber = startPage; pageNumber <= endPage; pageNumber += 1) {
                        paginationTarget.appendChild(
                            createButton(String(pageNumber), pageNumber, { active: pageNumber === state.page })
                        );
                    }

                    if (endPage < state.totalPages) {
                        if (endPage < state.totalPages - 1) {
                            paginationTarget.appendChild(createEllipsis());
                        }
                        paginationTarget.appendChild(
                            createButton(String(state.totalPages), state.totalPages, {
                                active: state.page === state.totalPages,
                            })
                        );
                    }

                    paginationTarget.appendChild(
                        createButton('Berikutnya', state.page + 1, {
                            disabled: state.page >= state.totalPages,
                            ariaLabel: 'Halaman berikutnya',
                        })
                    );
                }

                function updateRowsAndUI() {
                    updateRows();
                    updateInfo();
                    updateEmptyStates();
                    renderPagination();
                }

                function matchesActiveFilters(row) {
                    if (typeof config.filterPredicate !== 'function') return true;
                    return config.filterPredicate(row, state.activeFilters || {});
                }

                function applyFiltersAndUpdate() {
                    const term = (searchInput?.value || '').trim().toLowerCase();
                    state.filteredRows = state.rows.filter((row) => {
                        const haystack = (row.dataset.search || '').toLowerCase();
                        const matchesSearch = term === '' || haystack.includes(term);
                        if (!matchesSearch) return false;
                        return matchesActiveFilters(row);
                    });
                    state.page = 1;
                    updateRowsAndUI();
                }

                if (searchInput) {
                    searchInput.addEventListener('input', () => {
                        applyFiltersAndUpdate();
                    });
                }

                if (entriesSelect) {
                    entriesSelect.addEventListener('change', () => {
                        const parsed = parseInt(entriesSelect.value, 10);
                        state.perPage = Number.isFinite(parsed) && parsed > 0
                            ? parsed
                            : config.defaultPerPage ?? 10;
                        state.page = 1;
                        updateRowsAndUI();
                    });
                }

                applyFiltersAndUpdate();

                return {
                    setFilters(newFilters = {}) {
                        state.activeFilters = newFilters;
                        applyFiltersAndUpdate();
                    },
                    resetFilters() {
                        state.activeFilters = {};
                        applyFiltersAndUpdate();
                    },
                    getFilters() {
                        return { ...state.activeFilters };
                    },
                };
            };

            const tableManagers = {};

            tableManagers.users = createTableManager({
                rowsSelector: '[data-table="users"] .table-row',
                searchInput: '#adminSearch',
                entriesSelect: '[data-entries-select="users"]',
                infoTarget: '[data-entries-info="users"]',
                paginationTarget: '[data-pagination="users"]',
                emptySelector: '[data-empty="users"]',
                defaultPerPage: 10,
                filterPredicate: (row, filters = {}) => {
                    const statusFilters = filters.status ?? [];
                    const roleFilters = filters.role ?? [];
                    const rowStatus = (row.dataset.status || '').toLowerCase();
                    const rowRole = (row.dataset.role || '').toLowerCase();
                    if (statusFilters.length > 0 && !statusFilters.includes(rowStatus)) {
                        return false;
                    }
                    if (roleFilters.length > 0 && !roleFilters.includes(rowRole)) {
                        return false;
                    }
                    return true;
                },
            });

            tableManagers.pegawai = createTableManager({
                rowsSelector: '[data-table="pegawai"] .table-row',
                searchInput: '#pegawaiSearch',
                entriesSelect: '[data-entries-select="pegawai"]',
                infoTarget: '[data-entries-info="pegawai"]',
                paginationTarget: '[data-pagination="pegawai"]',
                emptySelector: '[data-empty="pegawai"]',
                defaultPerPage: 10,
                filterPredicate: (row, filters = {}) => {
                    const statusFilters = filters.status ?? [];
                    const rowStatus = (row.dataset.status || '').toLowerCase();
                    if (statusFilters.length > 0 && !statusFilters.includes(rowStatus)) {
                        return false;
                    }
                    return true;
                },
            });

            let openFilterKey = null;

            const closeFilterMenu = (key) => {
                if (!key) return;
                const toggle = document.querySelector(`[data-filter-toggle="${key}"]`);
                const menu = document.querySelector(`[data-filter-menu="${key}"]`);
                if (!toggle || !menu) return;
                menu.hidden = true;
                toggle.classList.remove('is-active');
                toggle.setAttribute('aria-expanded', 'false');
                if (openFilterKey === key) {
                    openFilterKey = null;
                }
            };

            const openFilterMenu = (key) => {
                const toggle = document.querySelector(`[data-filter-toggle="${key}"]`);
                const menu = document.querySelector(`[data-filter-menu="${key}"]`);
                if (!toggle || !menu) return;
                if (openFilterKey && openFilterKey !== key) {
                    closeFilterMenu(openFilterKey);
                }
                menu.hidden = false;
                toggle.classList.add('is-active');
                toggle.setAttribute('aria-expanded', 'true');
                openFilterKey = key;
            };

            const collectFilters = (menu) => {
                const filters = {};
                if (!menu) return filters;
                const inputs = menu.querySelectorAll('input[data-filter-field]');
                inputs.forEach((input) => {
                    const field = input.dataset.filterField;
                    if (!field) return;
                    const value = (input.value || '').toLowerCase();
                    if (!filters[field]) {
                        filters[field] = [];
                    }
                    if (input.checked) {
                        filters[field].push(value);
                    }
                });
                Object.keys(filters).forEach((field) => {
                    if (filters[field].length === 0) {
                        delete filters[field];
                    }
                });
                return filters;
            };

            const filterToggles = document.querySelectorAll('[data-filter-toggle]');
            filterToggles.forEach((toggle) => {
                const key = toggle.dataset.filterToggle;
                const menu = document.querySelector(`[data-filter-menu="${key}"]`);
                if (!key || !menu) return;
                menu.addEventListener('click', (event) => event.stopPropagation());
                toggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    if (openFilterKey === key) {
                        closeFilterMenu(key);
                    } else {
                        openFilterMenu(key);
                    }
                });
            });

            document.querySelectorAll('[data-filter-apply]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    const key = button.dataset.filterApply;
                    if (!key) return;
                    const menu = document.querySelector(`[data-filter-menu="${key}"]`);
                    const manager = tableManagers[key];
                    if (!menu || !manager) return;
                    const filters = collectFilters(menu);
                    manager.setFilters(filters);
                    closeFilterMenu(key);
                });
            });

            document.querySelectorAll('[data-filter-reset]').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.preventDefault();
                    const key = button.dataset.filterReset;
                    if (!key) return;
                    const menu = document.querySelector(`[data-filter-menu="${key}"]`);
                    const manager = tableManagers[key];
                    if (!menu || !manager) return;
                    menu.querySelectorAll('input[data-filter-field]').forEach((input) => {
                        input.checked = false;
                    });
                    manager.resetFilters();
                    closeFilterMenu(key);
                });
            });

            document.addEventListener('click', (event) => {
                if (!openFilterKey) return;
                const menu = document.querySelector(`[data-filter-menu="${openFilterKey}"]`);
                const toggle = document.querySelector(`[data-filter-toggle="${openFilterKey}"]`);
                if (!menu || !toggle) return;
                if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                    closeFilterMenu(openFilterKey);
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && openFilterKey) {
                    closeFilterMenu(openFilterKey);
                }
            });

            const fancySelectControllers = new WeakMap();
            const fancySelectInstances = [];
            let fancySelectListenersBound = false;

            const closeAllFancySelects = (exceptWrapper = null) => {
                fancySelectInstances.forEach(({ wrapper, controller }) => {
                    if (!wrapper || wrapper === exceptWrapper) return;
                    controller.close({ silent: true });
                });
            };

            const setFancySelectValue = (select, value, emitChange = false) => {
                if (!select) return;
                const controller = fancySelectControllers.get(select);
                if (controller) {
                    controller.setValue(value, { emitChange });
                } else {
                    select.value = value;
                    if (emitChange) {
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                }
            };

            const initializeFancySelects = () => {
                const wrappers = document.querySelectorAll('[data-fancy-select]');

                wrappers.forEach((wrapper) => {
                    if (wrapper.dataset.fancyReady === 'true') return;
                    wrapper.dataset.fancyReady = 'true';

                    const select = wrapper.querySelector('[data-fancy-native]');
                    const trigger = wrapper.querySelector('[data-fancy-trigger]');
                    const label = wrapper.querySelector('[data-fancy-label]');
                    const dropdown = wrapper.querySelector('[data-fancy-dropdown]');
                    const options = Array.from(wrapper.querySelectorAll('[data-fancy-option]'));

                    if (!select || !trigger || !label || !dropdown || options.length === 0) return;

                    const setOptionTabbing = (isOpen) => {
                        options.forEach((option) => {
                            option.tabIndex = isOpen ? 0 : -1;
                        });
                    };

                    const applyValue = (value, emitChange = false) => {
                        const option = options.find((item) => (item.dataset.value ?? item.value) === value) ?? options[0];
                        const resolvedValue = option?.dataset.value ?? option?.value ?? value;
                        const resolvedLabel =
                            option?.dataset.label ?? option?.textContent?.trim() ??
                            select.options[select.selectedIndex]?.text ?? resolvedValue;

                        options.forEach((item) => {
                            const isActive = (item.dataset.value ?? item.value) === resolvedValue;
                            item.classList.toggle('is-active', isActive);
                            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        });

                        label.textContent = resolvedLabel;
                        if (select.value !== resolvedValue) {
                            select.value = resolvedValue;
                        }

                        if (emitChange) {
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    };

                    const controller = {
                        open() {
                            if (wrapper.classList.contains('is-open')) return;
                            closeAllFancySelects(wrapper);
                            wrapper.classList.add('is-open');
                            trigger.setAttribute('aria-expanded', 'true');
                            dropdown.hidden = false;
                            setOptionTabbing(true);
                            const activeOption = options.find((item) => item.classList.contains('is-active')) ?? options[0];
                            window.requestAnimationFrame(() => activeOption?.focus({ preventScroll: true }));
                        },
                        close({ silent = false } = {}) {
                            if (!wrapper.classList.contains('is-open')) return;
                            wrapper.classList.remove('is-open');
                            trigger.setAttribute('aria-expanded', 'false');
                            dropdown.hidden = true;
                            setOptionTabbing(false);
                            if (!silent) {
                                trigger.focus({ preventScroll: true });
                            }
                        },
                        setValue(value, { emitChange = false } = {}) {
                            applyValue(value, emitChange);
                        },
                    };

                    fancySelectControllers.set(select, controller);
                    fancySelectInstances.push({ wrapper, controller, select });

                    trigger.addEventListener('click', (event) => {
                        event.preventDefault();
                        if (wrapper.classList.contains('is-open')) {
                            controller.close({ silent: true });
                        } else {
                            controller.open();
                        }
                    });

                    trigger.addEventListener('keydown', (event) => {
                        if (event.key === ' ' || event.key === 'Enter' || event.key === 'Spacebar') {
                            event.preventDefault();
                            if (wrapper.classList.contains('is-open')) {
                                controller.close({ silent: true });
                            } else {
                                controller.open();
                            }
                        }
                        if (event.key === 'ArrowDown' && !wrapper.classList.contains('is-open')) {
                            event.preventDefault();
                            controller.open();
                        }
                    });

                    options.forEach((option) => {
                        option.addEventListener('click', (event) => {
                            event.preventDefault();
                            const value = option.dataset.value ?? option.value ?? '';
                            controller.setValue(value, { emitChange: true });
                            controller.close();
                        });
                    });

                    options.forEach((option, index) => {
                        option.addEventListener('keydown', (event) => {
                            const key = event.key;
                            if (key === 'Enter' || key === ' ' || key === 'Spacebar') {
                                event.preventDefault();
                                const value = option.dataset.value ?? option.value ?? '';
                                controller.setValue(value, { emitChange: true });
                                controller.close();
                                return;
                            }
                            if (key === 'ArrowDown') {
                                event.preventDefault();
                                const next = options[(index + 1) % options.length];
                                next?.focus({ preventScroll: true });
                                return;
                            }
                            if (key === 'ArrowUp') {
                                event.preventDefault();
                                const previous = options[(index - 1 + options.length) % options.length];
                                previous?.focus({ preventScroll: true });
                                return;
                            }
                            if (key === 'Escape') {
                                event.preventDefault();
                                controller.close();
                            }
                        });
                    });

                    select.addEventListener('change', () => {
                        applyValue(select.value, false);
                    });

                    dropdown.hidden = true;
                    setOptionTabbing(false);
                    applyValue(select.value || options[0]?.dataset.value || '', false);
                });

                if (!fancySelectListenersBound) {
                    fancySelectListenersBound = true;
                    document.addEventListener('click', (event) => {
                        const wrapper = event.target.closest('[data-fancy-select]');
                        closeAllFancySelects(wrapper);
                    });
                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            closeAllFancySelects();
                        }
                    });
                }
            };

            initializeFancySelects();

            const modalStack = [];

            const openModal = (modal) => {
                if (!modal || modal.classList.contains('is-visible')) return;
                modal.classList.remove('is-closing');
                modal.setAttribute('aria-hidden', 'false');
                modal.classList.add('is-visible');
                modalStack.push(modal);
                body.classList.add('modal-open');
            };

            const closeModal = (modal) => {
                if (!modal || !modal.classList.contains('is-visible')) return;
                modal.classList.add('is-closing');

                const finalize = () => {
                    modal.classList.remove('is-visible', 'is-closing');
                    modal.setAttribute('aria-hidden', 'true');
                    closeAllFancySelects();
                    const index = modalStack.lastIndexOf(modal);
                    if (index !== -1) {
                        modalStack.splice(index, 1);
                    }
                    if (modalStack.length === 0) {
                        body.classList.remove('modal-open');
                    }
                };

                const handleTransitionEnd = (event) => {
                    if (event.target !== modal) return;
                    modal.removeEventListener('transitionend', handleTransitionEnd);
                    modal.removeEventListener('animationend', handleTransitionEnd);
                    finalize();
                };

                modal.addEventListener('transitionend', handleTransitionEnd);
                modal.addEventListener('animationend', handleTransitionEnd);
                window.setTimeout(handleTransitionEnd, 340, { target: modal });
            };

            const confirmModal = document.getElementById('confirmModal');
            const confirmMessage = document.getElementById('confirmModalMessage');
            const confirmAccept = confirmModal?.querySelector('[data-confirm-accept]');
            const confirmCancel = confirmModal?.querySelector('[data-confirm-cancel]');
            let pendingDeleteForm = null;

            const closeConfirmModal = () => {
                pendingDeleteForm = null;
                if (confirmModal) {
                    closeModal(confirmModal);
                }
            };

            confirmCancel?.addEventListener('click', closeConfirmModal);
            confirmModal?.querySelector('[data-modal-close]')?.addEventListener('click', closeConfirmModal);

            confirmAccept?.addEventListener('click', () => {
                if (!pendingDeleteForm) {
                    closeConfirmModal();
                    return;
                }
                pendingDeleteForm.dataset.confirmed = 'true';
                if (typeof pendingDeleteForm.requestSubmit === 'function') {
                    pendingDeleteForm.requestSubmit();
                } else {
                    pendingDeleteForm.submit();
                }
                pendingDeleteForm = null;
                closeConfirmModal();
            });

            document.querySelectorAll('.js-delete-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    if (form.dataset.confirmed === 'true') {
                        form.dataset.confirmed = '';
                        return;
                    }
                    event.preventDefault();
                    pendingDeleteForm = form;
                    if (confirmMessage) {
                        confirmMessage.textContent =
                            form.dataset.confirmMessage || 'Yakin ingin melanjutkan tindakan ini?';
                    }
                    openModal(confirmModal);
                    confirmAccept?.focus();
                });
            });

            const setupAvatarInput = (form, previewId, removeSelector) => {
                if (!form) return null;
                const preview = document.getElementById(previewId);
                const fileInput = form.querySelector(`input[type="file"][data-preview="${previewId}"]`);
                const removeField = form.querySelector('[data-remove-field]');
                const removeButton = form.querySelector(removeSelector);
                const defaultSrc = preview?.dataset.default || preview?.src || '';

                if (fileInput && preview) {
                    fileInput.addEventListener('change', () => {
                        const [file] = fileInput.files;
                        if (!file) return;
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            preview.src = e.target?.result ?? defaultSrc;
                            if (removeField) {
                                removeField.value = '0';
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                }

                if (removeButton && preview) {
                    removeButton.addEventListener('click', () => {
                        if (fileInput) {
                            fileInput.value = '';
                        }
                        preview.src = defaultSrc;
                        if (removeField) {
                            removeField.value = '1';
                        }
                    });
                }

                return {
                    reset() {
                        if (preview) {
                            preview.src = defaultSrc;
                        }
                        if (fileInput) {
                            fileInput.value = '';
                        }
                        if (removeField) {
                            removeField.value = '0';
                        }
                    },
                    set(src) {
                        if (preview) {
                            preview.src = src || defaultSrc;
                        }
                        if (removeField) {
                            removeField.value = '0';
                        }
                        if (fileInput) {
                            fileInput.value = '';
                        }
                    },
                };
            };

            const applyFileInfo = (wrapper, link, removeWrapper, checkbox, url, name, fallbackLabel) => {
                if (!wrapper) return;
                const hasFile = Boolean(url);
                wrapper.hidden = !hasFile;
                if (link) {
                    if (hasFile) {
                        link.hidden = false;
                        link.href = url;
                        link.textContent = name || fallbackLabel;
                    } else {
                        link.hidden = true;
                        link.removeAttribute('href');
                    }
                }
                if (removeWrapper) {
                    removeWrapper.hidden = !hasFile;
                }
                if (checkbox) {
                    checkbox.checked = false;
                }
            };

            const setPegawaiMetadata = ({ createdBy, updatedBy, createdAt, updatedAt, deletedAt } = {}) => {
                const autoNote = 'Diisi otomatis setelah tersimpan';
                if (pegawaiMetaCreatedBy) pegawaiMetaCreatedBy.textContent = createdBy || autoNote;
                if (pegawaiMetaUpdatedBy) pegawaiMetaUpdatedBy.textContent = updatedBy || autoNote;
                if (pegawaiMetaCreatedAt) pegawaiMetaCreatedAt.textContent = createdAt || autoNote;
                if (pegawaiMetaUpdatedAt) pegawaiMetaUpdatedAt.textContent = updatedAt || autoNote;
                if (pegawaiMetaDeletedAt) pegawaiMetaDeletedAt.textContent = deletedAt || '-';
            };

            const setDetailLink = (wrapper, link, url, name) => {
                if (!wrapper) return;
                const empty = wrapper.querySelector('[data-empty]');
                const hasFile = Boolean(url);
                if (empty) {
                    empty.hidden = hasFile;
                }
                if (link) {
                    if (hasFile) {
                        link.hidden = false;
                        link.href = url;
                        link.textContent = name || link.textContent || 'Lihat dokumen';
                    } else {
                        link.hidden = true;
                        link.removeAttribute('href');
                    }
                }
            };

            const handleModalClose = (modal) => {
                if (!modal) return;
                if (modal.id === 'confirmModal') {
                    closeConfirmModal();
                    return;
                }
                if (modal.id === 'adminFormModal') {
                    resetAdminForm();
                }
                if (modal.id === 'pegawaiFormModal') {
                    resetPegawaiForm();
                }
                closeModal(modal);
            };

            document.querySelectorAll('.dialog-backdrop').forEach((modal) => {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        if (modal.id === 'pegawaiFormModal' || modal.id === 'adminFormModal') {
                            return; // Mencegah tutup modal saat klik backdrop untuk form
                        }
                        handleModalClose(modal);
                    }
                });
            });

            document.querySelectorAll('[data-modal-close]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('.dialog-backdrop');
                    handleModalClose(modal);
                });
            });

            document.querySelectorAll('[data-modal-dismiss]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('.dialog-backdrop');
                    handleModalClose(modal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modalStack.length > 0) {
                    const modal = modalStack[modalStack.length - 1];
                    if (modal.id === 'pegawaiFormModal' || modal.id === 'adminFormModal') {
                        return; // Mencegah tutup modal saat tekan Escape untuk form
                    }
                    handleModalClose(modal);
                }
            });

            const adminModal = document.getElementById('adminFormModal');
            const adminForm = document.getElementById('userForm');
            const adminMethodField = adminForm?.querySelector('[data-method-field]');
            const adminContextField = adminForm?.querySelector('input[name="context_tab"]');
            const adminRemoveField = adminForm?.querySelector('[data-remove-field]');
            const adminNameField = adminForm?.querySelector('input[name="nama"]');
            const adminEmailField = adminForm?.querySelector('input[name="email"]');
            const adminPhoneField = adminForm?.querySelector('input[name="nomor_hp"]');
            const adminPasswordField = adminForm?.querySelector('input[name="password"]');
            const adminPasswordConfirmField = adminForm?.querySelector('input[name="password_confirmation"]');
            const adminIsAdminField = adminForm?.querySelector('input[name="is_admin"]');
            const adminIsActiveField = adminForm?.querySelector('input[name="is_active"]');
            const adminTitle = document.getElementById('userFormTitle');
            const adminSubtitle = document.getElementById('userFormSubtitle');
            const adminAvatarHelpers = setupAvatarInput(adminForm, 'userAvatarPreview', '[data-action="remove-user-photo"]');
            const adminCreateAction = adminForm?.dataset.createAction;

            function resetAdminForm() {
                if (!adminForm) return;
                adminForm.reset();
                adminForm.action = adminCreateAction || adminForm.action;
                if (adminMethodField) adminMethodField.value = 'POST';
                if (adminContextField) adminContextField.value = 'users';
                if (adminRemoveField) adminRemoveField.value = '0';
                if (adminIsAdminField) adminIsAdminField.checked = true;
                if (adminIsActiveField) adminIsActiveField.checked = true;
                if (adminPasswordField) {
                    adminPasswordField.required = true;
                    adminPasswordField.value = '';
                }
                if (adminPasswordConfirmField) {
                    adminPasswordConfirmField.required = true;
                    adminPasswordConfirmField.value = '';
                }
                if (adminTitle) adminTitle.textContent = 'Tambah Admin';
                if (adminSubtitle) adminSubtitle.textContent = 'Isi detail admin untuk memberikan akses dashboard.';
                if (adminAvatarHelpers) adminAvatarHelpers.reset();
                adminForm.dataset.mode = 'create';
            }

            function openAdminForm(mode, payload = {}) {
                if (!adminForm || !adminModal) return;
                setActiveTab('users');
                if (adminContextField) adminContextField.value = 'users';

                if (mode === 'edit') {
                    adminForm.dataset.mode = 'edit';
                    adminForm.action = payload.update || adminCreateAction || adminForm.action;
                    if (adminMethodField) adminMethodField.value = 'PUT';
                    if (adminTitle) adminTitle.textContent = 'Perbarui Admin';
                    if (adminSubtitle) adminSubtitle.textContent = 'Sesuaikan data admin dan status akses.';
                    if (adminNameField) adminNameField.value = payload.nama ?? '';
                    if (adminEmailField) adminEmailField.value = payload.email ?? '';
                    if (adminPhoneField) adminPhoneField.value = payload.nomorHp ?? '';
                    if (adminPasswordField) {
                        adminPasswordField.required = false;
                        adminPasswordField.value = '';
                    }
                    if (adminPasswordConfirmField) {
                        adminPasswordConfirmField.required = false;
                        adminPasswordConfirmField.value = '';
                    }
                    if (adminIsAdminField) adminIsAdminField.checked = payload.isAdmin === '1';
                    if (adminIsActiveField) adminIsActiveField.checked = payload.isActive === '1';
                    if (adminRemoveField) adminRemoveField.value = '0';
                    if (adminAvatarHelpers) adminAvatarHelpers.set(payload.avatar);
                } else {
                    resetAdminForm();
                }

                openModal(adminModal);
                window.requestAnimationFrame(() => {
                    if (adminNameField) {
                        adminNameField.focus();
                    }
                });
            }

            const pegawaiModal = document.getElementById('pegawaiFormModal');
            const pegawaiForm = document.getElementById('pegawaiForm');
            const pegawaiMethodField = pegawaiForm?.querySelector('[data-method-field]');
            const pegawaiContextField = pegawaiForm?.querySelector('input[name="context_tab"]');
            const pegawaiRemoveField = pegawaiForm?.querySelector('[data-remove-field]');
            const pegawaiNameField = pegawaiForm?.querySelector('input[name="nama"]');
            const pegawaiJabatanField = pegawaiForm?.querySelector('input[name="jabatan"]');
            const pegawaiEmailField = pegawaiForm?.querySelector('input[name="email"]');
            const pegawaiPhoneField = pegawaiForm?.querySelector('input[name="nomor_hp"]');
            const pegawaiStatusField = pegawaiForm?.querySelector('select[name="status"]');
            const pegawaiNikField = pegawaiForm?.querySelector('input[name="nik"]');
            const pegawaiNipField = pegawaiForm?.querySelector('input[name="nip"]');
            const pegawaiGenderField = pegawaiForm?.querySelector('select[name="jenis_kelamin"]');
            const pegawaiTempatLahirField = pegawaiForm?.querySelector('input[name="tempat_lahir"]');
            const pegawaiTanggalLahirField = pegawaiForm?.querySelector('input[name="tanggal_lahir"]');
            const pegawaiAgamaField = pegawaiForm?.querySelector('input[name="agama"]');
            const pegawaiAlamatField = pegawaiForm?.querySelector('textarea[name="alamat"]');
            const pegawaiMasaMulaiField = pegawaiForm?.querySelector('input[name="masa_jabatan_mulai"]');
            const pegawaiMasaSelesaiField = pegawaiForm?.querySelector('input[name="masa_jabatan_selesai"]');
            const pegawaiUniversitasField = pegawaiForm?.querySelector('input[name="universitas"]');
            const pegawaiPendidikanField = pegawaiForm?.querySelector('input[name="pendidikan_terakhir"]');
            const pegawaiTahunLulusField = pegawaiForm?.querySelector('input[name="tahun_lulus"]');
            const pegawaiBahasaField = pegawaiForm?.querySelector('input[name="bahasa"]');
            const pegawaiSertifikatField = pegawaiForm?.querySelector('textarea[name="sertifikat_pelatihan"]');
            const pegawaiLastLoginField = pegawaiForm?.querySelector('input[name="last_login"]');
            const pegawaiMetaCreatedBy = pegawaiForm?.querySelector('[data-meta="created-by"]');
            const pegawaiMetaUpdatedBy = pegawaiForm?.querySelector('[data-meta="updated-by"]');
            const pegawaiMetaCreatedAt = pegawaiForm?.querySelector('[data-meta="created-at"]');
            const pegawaiMetaUpdatedAt = pegawaiForm?.querySelector('[data-meta="updated-at"]');
            const pegawaiMetaDeletedAt = pegawaiForm?.querySelector('[data-meta="deleted-at"]');
            const pegawaiCurrentKtp = pegawaiForm?.querySelector('[data-current-ktp]');
            const pegawaiCurrentKtpLink = pegawaiCurrentKtp?.querySelector('[data-current-ktp-link]');
            const pegawaiRemoveKtpWrapper = pegawaiForm?.querySelector('[data-remove-ktp]');
            const pegawaiRemoveKtpCheckbox = pegawaiRemoveKtpWrapper?.querySelector('input[name="remove_foto_ktp"]');
            const pegawaiCurrentSkPengangkatan = pegawaiForm?.querySelector('[data-current-sk-pengangkatan]');
            const pegawaiCurrentSkPengangkatanLink =
                pegawaiCurrentSkPengangkatan?.querySelector('[data-current-sk-pengangkatan-link]');
            const pegawaiRemoveSkPengangkatanWrapper = pegawaiForm?.querySelector('[data-remove-sk-pengangkatan]');
            const pegawaiRemoveSkPengangkatanCheckbox =
                pegawaiRemoveSkPengangkatanWrapper?.querySelector('input[name="remove_sk_pengangkatan"]');
            const pegawaiCurrentSkPemberhentian = pegawaiForm?.querySelector('[data-current-sk-pemberhentian]');
            const pegawaiCurrentSkPemberhentianLink =
                pegawaiCurrentSkPemberhentian?.querySelector('[data-current-sk-pemberhentian-link]');
            const pegawaiRemoveSkPemberhentianWrapper = pegawaiForm?.querySelector('[data-remove-sk-pemberhentian]');
            const pegawaiRemoveSkPemberhentianCheckbox =
                pegawaiRemoveSkPemberhentianWrapper?.querySelector('input[name="remove_sk_pemberhentian"]');
            const pegawaiTitle = document.getElementById('pegawaiFormTitle');
            const pegawaiSubtitle = document.getElementById('pegawaiFormSubtitle');
            const pegawaiAvatarHelpers = setupAvatarInput(pegawaiForm, 'pegawaiAvatarPreview', '[data-action="remove-pegawai-photo"]');
            window.pegawaiCreateAction = pegawaiForm?.dataset.createAction; // Make it globally accessible

            function resetPegawaiForm() {
                if (!pegawaiForm) return;
                pegawaiForm.reset();
                pegawaiForm.action = window.pegawaiCreateAction || pegawaiForm.action;
                if (pegawaiMethodField) pegawaiMethodField.value = 'POST';
                if (pegawaiContextField) pegawaiContextField.value = 'pegawai';
                if (pegawaiRemoveField) pegawaiRemoveField.value = '0';
                if (pegawaiStatusField) setFancySelectValue(pegawaiStatusField, 'Aktif');
                if (pegawaiGenderField) setFancySelectValue(pegawaiGenderField, 'Laki-laki');
                if (pegawaiNikField) pegawaiNikField.value = '';
                if (pegawaiNipField) pegawaiNipField.value = '';
                if (pegawaiTempatLahirField) pegawaiTempatLahirField.value = '';
                if (pegawaiTanggalLahirField) pegawaiTanggalLahirField.value = '';
                if (pegawaiAgamaField) pegawaiAgamaField.value = '';
                if (pegawaiAlamatField) pegawaiAlamatField.value = '';
                if (pegawaiMasaMulaiField) pegawaiMasaMulaiField.value = '';
                if (pegawaiMasaSelesaiField) pegawaiMasaSelesaiField.value = '';
                if (pegawaiUniversitasField) pegawaiUniversitasField.value = '';
                if (pegawaiPendidikanField) pegawaiPendidikanField.value = '';
                if (pegawaiTahunLulusField) pegawaiTahunLulusField.value = '';
                if (pegawaiBahasaField) pegawaiBahasaField.value = '';
                if (pegawaiSertifikatField) pegawaiSertifikatField.value = '';
                if (pegawaiLastLoginField) pegawaiLastLoginField.value = '';
                setPegawaiMetadata();
                applyFileInfo(
                    pegawaiCurrentKtp,
                    pegawaiCurrentKtpLink,
                    pegawaiRemoveKtpWrapper,
                    pegawaiRemoveKtpCheckbox,
                    '',
                    '',
                    'Lihat foto KTP'
                );
                applyFileInfo(
                    pegawaiCurrentSkPengangkatan,
                    pegawaiCurrentSkPengangkatanLink,
                    pegawaiRemoveSkPengangkatanWrapper,
                    pegawaiRemoveSkPengangkatanCheckbox,
                    '',
                    '',
                    'Lihat dokumen'
                );
                applyFileInfo(
                    pegawaiCurrentSkPemberhentian,
                    pegawaiCurrentSkPemberhentianLink,
                    pegawaiRemoveSkPemberhentianWrapper,
                    pegawaiRemoveSkPemberhentianCheckbox,
                    '',
                    '',
                    'Lihat dokumen'
                );
                if (pegawaiTitle) pegawaiTitle.textContent = 'Tambah Pegawai';
                if (pegawaiSubtitle) pegawaiSubtitle.textContent = 'Lengkapi informasi aparatur desa untuk publikasi.';
                if (pegawaiAvatarHelpers) pegawaiAvatarHelpers.reset();
                pegawaiForm.dataset.mode = 'create';
            }

            function openPegawaiForm(mode, payload = {}) {
                if (!pegawaiForm || !pegawaiModal) return;
                setActiveTab('pegawai');
                if (pegawaiContextField) pegawaiContextField.value = 'pegawai';

                if (mode === 'edit') {
                    pegawaiForm.dataset.mode = 'edit';
                    pegawaiForm.action = payload.update || window.pegawaiCreateAction || pegawaiForm.action;
                    if (pegawaiMethodField) pegawaiMethodField.value = 'PUT';
                    if (pegawaiTitle) pegawaiTitle.textContent = 'Perbarui Pegawai';
                    if (pegawaiSubtitle) pegawaiSubtitle.textContent = 'Sesuaikan data aparatur desa.';
                    if (pegawaiNikField) pegawaiNikField.value = payload.nik ?? '';
                    if (pegawaiNameField) pegawaiNameField.value = payload.nama ?? '';
                    if (pegawaiNipField) pegawaiNipField.value = payload.nip ?? '';
                    if (pegawaiJabatanField) pegawaiJabatanField.value = payload.jabatan ?? '';
                    if (pegawaiEmailField) pegawaiEmailField.value = payload.email ?? '';
                    if (pegawaiPhoneField) pegawaiPhoneField.value = payload.nomorHp ?? '';
                    if (pegawaiGenderField) setFancySelectValue(pegawaiGenderField, payload.jenisKelamin ?? 'Laki-laki');
                    if (pegawaiTempatLahirField) pegawaiTempatLahirField.value = payload.tempatLahir ?? '';
                    if (pegawaiTanggalLahirField) pegawaiTanggalLahirField.value = payload.tanggalLahir ?? '';
                    if (pegawaiAgamaField) pegawaiAgamaField.value = payload.agama ?? '';
                    if (pegawaiAlamatField) pegawaiAlamatField.value = payload.alamat ?? '';
                    if (pegawaiStatusField) setFancySelectValue(pegawaiStatusField, payload.statusRaw ?? payload.status ?? 'Aktif');
                    if (pegawaiMasaMulaiField) pegawaiMasaMulaiField.value = payload.masaJabatanMulai ?? '';
                    if (pegawaiMasaSelesaiField) pegawaiMasaSelesaiField.value = payload.masaJabatanSelesai ?? '';
                    if (pegawaiUniversitasField) pegawaiUniversitasField.value = payload.universitas ?? '';
                    if (pegawaiPendidikanField) pegawaiPendidikanField.value = payload.pendidikanTerakhir ?? '';
                    if (pegawaiTahunLulusField) pegawaiTahunLulusField.value = payload.tahunLulus ?? '';
                    if (pegawaiBahasaField) pegawaiBahasaField.value = payload.bahasa ?? '';
                    if (pegawaiSertifikatField) pegawaiSertifikatField.value = payload.sertifikatPelatihan ?? '';
                    if (pegawaiLastLoginField) pegawaiLastLoginField.value = payload.lastLogin ?? '';
                    if (pegawaiRemoveField) pegawaiRemoveField.value = '0';
                    if (pegawaiAvatarHelpers) pegawaiAvatarHelpers.set(payload.avatar);
                    setPegawaiMetadata({
                        createdBy: payload.createdBy,
                        updatedBy: payload.updatedBy,
                        createdAt: payload.createdAtDisplay,
                        updatedAt: payload.updatedAtDisplay,
                        deletedAt: payload.deletedAtDisplay,
                    });
                    applyFileInfo(
                        pegawaiCurrentKtp,
                        pegawaiCurrentKtpLink,
                        pegawaiRemoveKtpWrapper,
                        pegawaiRemoveKtpCheckbox,
                        payload.fotoKtpUrl,
                        payload.fotoKtpName,
                        'Lihat foto KTP'
                    );
                    applyFileInfo(
                        pegawaiCurrentSkPengangkatan,
                        pegawaiCurrentSkPengangkatanLink,
                        pegawaiRemoveSkPengangkatanWrapper,
                        pegawaiRemoveSkPengangkatanCheckbox,
                        payload.skPengangkatanUrl,
                        payload.skPengangkatanName,
                        'Lihat dokumen'
                    );
                    applyFileInfo(
                        pegawaiCurrentSkPemberhentian,
                        pegawaiCurrentSkPemberhentianLink,
                        pegawaiRemoveSkPemberhentianWrapper,
                        pegawaiRemoveSkPemberhentianCheckbox,
                        payload.skPemberhentianUrl,
                        payload.skPemberhentianName,
                        'Lihat dokumen'
                    );
                } else {
                    resetPegawaiForm();
                }

                openModal(pegawaiModal);
                window.requestAnimationFrame(() => {
                    if (pegawaiNameField) {
                        pegawaiNameField.focus();
                    }
                });
            }

            document.querySelector('.js-create-admin')?.addEventListener('click', () => openAdminForm('create'));
            document.querySelectorAll('.js-edit-admin').forEach((button) => {
                button.addEventListener('click', () => openAdminForm('edit', button.dataset));
            });

            document.querySelector('.js-create-pegawai')?.addEventListener('click', () => openPegawaiForm('create'));
            document.querySelectorAll('.js-edit-pegawai').forEach((button) => {
                button.addEventListener('click', () => openPegawaiForm('edit', button.dataset));
            });

            const detailModal = document.getElementById('detailModal');
            const detailTitle = document.getElementById('detailModalTitle');
            const detailSubtitle = document.getElementById('detailModalSubtitle');
            const detailAvatar = document.getElementById('detailModalAvatar');
            const detailName = document.getElementById('detailModalName');
            const detailStatus = document.getElementById('detailModalStatus');
            const detailEmail = document.getElementById('detailModalEmail');
            const detailPhone = document.getElementById('detailModalPhone');
            const detailInfoLabel = document.getElementById('detailModalInfoLabel');
            const detailInfoValue = document.getElementById('detailModalInfoValue');
            const detailMetaLabel = document.getElementById('detailModalMetaLabel');
            const detailMetaValue = document.getElementById('detailModalMetaValue');
            const detailSections = detailModal?.querySelector('[data-pegawai-detail]');
            const detailNik = document.getElementById('detailNik');
            const detailBirth = document.getElementById('detailBirth');
            const detailGender = document.getElementById('detailGender');
            const detailReligion = document.getElementById('detailReligion');
            const detailAddress = document.getElementById('detailAddress');
            const detailFotoKtp = document.getElementById('detailFotoKtp');
            const detailFotoKtpLink = detailFotoKtp?.querySelector('[data-file-link="ktp"]');
            const detailNip = document.getElementById('detailNip');
            const detailJob = document.getElementById('detailJob');
            const detailEmploymentStatus = document.getElementById('detailEmploymentStatus');
            const detailTenure = document.getElementById('detailTenure');
            const detailSkPengangkatan = document.getElementById('detailSkPengangkatan');
            const detailSkPengangkatanLink = detailSkPengangkatan?.querySelector('[data-file-link="sk-pengangkatan"]');
            const detailSkPemberhentian = document.getElementById('detailSkPemberhentian');
            const detailSkPemberhentianLink = detailSkPemberhentian?.querySelector('[data-file-link="sk-pemberhentian"]');
            const detailUniversity = document.getElementById('detailUniversity');
            const detailEducation = document.getElementById('detailEducation');
            const detailGraduationYear = document.getElementById('detailGraduationYear');
            const detailCertifications = document.getElementById('detailCertifications');
            const detailLanguages = document.getElementById('detailLanguages');
            const detailLastLogin = document.getElementById('detailLastLogin');
            const detailCreatedBy = document.getElementById('detailCreatedBy');
            const detailUpdatedBy = document.getElementById('detailUpdatedBy');
            const detailCreatedAt = document.getElementById('detailCreatedAt');
            const detailUpdatedAt = document.getElementById('detailUpdatedAt');
            const detailDeletedAt = document.getElementById('detailDeletedAt');

            const applyStatusClass = (element, statusClass) => {
                if (!element) return;
                element.classList.remove('status-active', 'status-draft', 'status-scheduled', 'status-pending');
                if (statusClass) {
                    element.classList.add(statusClass);
                }
            };

            const openDetailModal = (type, payload = {}) => {
                if (!detailModal) return;
                const isPegawai = type === 'pegawai';
                if (detailSections) {
                    detailSections.hidden = !isPegawai;
                }
                if (detailTitle) {
                    detailTitle.textContent = type === 'admin' ? 'Detail Admin' : 'Detail Pegawai';
                }
                if (detailSubtitle) {
                    detailSubtitle.textContent =
                        type === 'admin'
                            ? 'Tinjau status akses admin terpilih.'
                            : 'Tinjau profil aparatur desa terpilih.';
                }
                if (detailAvatar) {
                    detailAvatar.src = payload.avatar || detailAvatar.dataset.default || detailAvatar.src;
                    detailAvatar.alt = payload.nama ? `Foto ${payload.nama}` : 'Avatar akun';
                }
                if (detailName) {
                    detailName.textContent = payload.nama ?? '-';
                }
                if (detailStatus) {
                    const rawStatus = payload.statusRaw ?? payload.status ?? '-';
                    detailStatus.textContent = payload.status ?? '-';
                    applyStatusClass(detailStatus, payload.statusClass);
                    if (rawStatus && rawStatus !== '-') {
                        detailStatus.setAttribute('title', rawStatus);
                    } else {
                        detailStatus.removeAttribute('title');
                    }
                }
                if (detailEmail) {
                    detailEmail.textContent = payload.email ?? '-';
                }
                if (detailPhone) {
                    detailPhone.textContent = payload.nomorHp ?? '-';
                }
                if (detailInfoLabel) {
                    detailInfoLabel.textContent = type === 'admin' ? 'Role' : 'Jabatan';
                }
                if (detailInfoValue) {
                    detailInfoValue.textContent = type === 'admin' ? payload.role ?? '-' : payload.jabatan ?? '-';
                }
                if (detailMetaLabel) {
                    detailMetaLabel.textContent = type === 'admin' ? 'Login Terakhir' : 'Pembaruan Data';
                }
                if (detailMetaValue) {
                    if (type === 'admin') {
                        const created = payload.created && payload.created !== '-' ? ` - Dibuat ${payload.created}` : '';
                        detailMetaValue.textContent = (payload.lastLogin ?? '-') + created;
                    } else {
                        detailMetaValue.textContent = payload.updated ?? '-';
                    }
                }
                if (isPegawai) {
                    const valueOrDash = (value, fallback = '-') => {
                        if (value === undefined || value === null) return fallback;
                        const text = String(value).trim();
                        return text !== '' ? text : fallback;
                    };
                    if (detailNik) detailNik.textContent = valueOrDash(payload.nik);
                    if (detailBirth) {
                        const parts = [];
                        if (valueOrDash(payload.tempatLahir, '')) parts.push(valueOrDash(payload.tempatLahir, ''));
                        if (valueOrDash(payload.tanggalLahirDisplay, '')) parts.push(valueOrDash(payload.tanggalLahirDisplay, ''));
                        detailBirth.textContent = parts.length > 0 ? parts.join(', ') : '-';
                    }
                    if (detailGender) detailGender.textContent = valueOrDash(payload.jenisKelamin);
                    if (detailReligion) detailReligion.textContent = valueOrDash(payload.agama);
                    if (detailAddress) detailAddress.textContent = valueOrDash(payload.alamat);
                    setDetailLink(detailFotoKtp, detailFotoKtpLink, payload.fotoKtpUrl, payload.fotoKtpName || 'Lihat foto KTP');
                    if (detailNip) detailNip.textContent = valueOrDash(payload.nip);
                    if (detailJob) detailJob.textContent = valueOrDash(payload.jabatan);
                    if (detailEmploymentStatus) {
                        detailEmploymentStatus.textContent = valueOrDash(payload.statusRaw ?? payload.status);
                    }
                    if (detailTenure) detailTenure.textContent = valueOrDash(payload.tenureDisplay);
                    setDetailLink(
                        detailSkPengangkatan,
                        detailSkPengangkatanLink,
                        payload.skPengangkatanUrl,
                        payload.skPengangkatanName || 'Lihat dokumen'
                    );
                    setDetailLink(
                        detailSkPemberhentian,
                        detailSkPemberhentianLink,
                        payload.skPemberhentianUrl,
                        payload.skPemberhentianName || 'Lihat dokumen'
                    );
                    if (detailUniversity) detailUniversity.textContent = valueOrDash(payload.universitas);
                    if (detailEducation) detailEducation.textContent = valueOrDash(payload.pendidikanTerakhir);
                    if (detailGraduationYear) detailGraduationYear.textContent = valueOrDash(payload.tahunLulus);
                    if (detailCertifications) detailCertifications.textContent = valueOrDash(payload.sertifikatPelatihan);
                    if (detailLanguages) detailLanguages.textContent = valueOrDash(payload.bahasa);
                    if (detailLastLogin) detailLastLogin.textContent = valueOrDash(payload.lastLoginDisplay);
                    if (detailCreatedBy) detailCreatedBy.textContent = valueOrDash(payload.createdBy);
                    if (detailUpdatedBy) detailUpdatedBy.textContent = valueOrDash(payload.updatedBy);
                    if (detailCreatedAt) detailCreatedAt.textContent = valueOrDash(payload.createdAtDisplay);
                    if (detailUpdatedAt) detailUpdatedAt.textContent = valueOrDash(payload.updatedAtDisplay);
                    if (detailDeletedAt) detailDeletedAt.textContent = valueOrDash(payload.deletedAtDisplay, '-');
                }
                openModal(detailModal);
            };


            document.querySelectorAll('.js-view-admin').forEach((button) => {
                button.addEventListener('click', () => {
                    openDetailModal('admin', {
                        avatar: button.dataset.avatar,
                        nama: button.dataset.nama,
                        email: button.dataset.email,
                        nomorHp: button.dataset.nomorHp,
                        role: button.dataset.role,
                        status: button.dataset.status,
                        statusRaw: button.dataset.statusRaw || button.dataset.status,
                        statusClass: button.dataset.statusClass,
                        lastLogin: button.dataset.lastLogin,
                        created: button.dataset.created,
                    });
                });
            });

            document.querySelectorAll('.js-view-pegawai').forEach((button) => {
                button.addEventListener('click', () => {
                    openDetailModal('pegawai', {
                        avatar: button.dataset.avatar,
                        nama: button.dataset.nama,
                        nik: button.dataset.nik,
                        nip: button.dataset.nip,
                        jabatan: button.dataset.jabatan,
                        email: button.dataset.email,
                        nomorHp: button.dataset.nomorHp,
                        jenisKelamin: button.dataset.jenisKelamin,
                        tempatLahir: button.dataset.tempatLahir,
                        tanggalLahir: button.dataset.tanggalLahir,
                        tanggalLahirDisplay: button.dataset.tanggalLahirDisplay,
                        agama: button.dataset.agama,
                        alamat: button.dataset.alamat,
                        status: button.dataset.status,
                        statusRaw: button.dataset.statusRaw || button.dataset.status,
                        statusClass: button.dataset.statusClass,
                        masaJabatanMulai: button.dataset.masaJabatanMulai,
                        masaJabatanMulaiDisplay: button.dataset.masaJabatanMulaiDisplay,
                        masaJabatanSelesai: button.dataset.masaJabatanSelesai,
                        masaJabatanSelesaiDisplay: button.dataset.masaJabatanSelesaiDisplay,
                        tenureDisplay: button.dataset.tenureDisplay,
                        universitas: button.dataset.universitas,
                        pendidikanTerakhir: button.dataset.pendidikanTerakhir,
                        tahunLulus: button.dataset.tahunLulus,
                        sertifikatPelatihan: button.dataset.sertifikatPelatihan,
                        bahasa: button.dataset.bahasa,
                        fotoKtpUrl: button.dataset.fotoKtpUrl,
                        fotoKtpName: button.dataset.fotoKtpName,
                        skPengangkatanUrl: button.dataset.skPengangkatanUrl,
                        skPengangkatanName: button.dataset.skPengangkatanName,
                        skPemberhentianUrl: button.dataset.skPemberhentianUrl,
                        skPemberhentianName: button.dataset.skPemberhentianName,
                        lastLogin: button.dataset.lastLogin,
                        lastLoginDisplay: button.dataset.lastLoginDisplay,
                        createdBy: button.dataset.createdBy,
                        updatedBy: button.dataset.updatedBy,
                        createdAtDisplay: button.dataset.createdAtDisplay,
                        updatedAtDisplay: button.dataset.updatedAtDisplay,
                        deletedAtDisplay: button.dataset.deletedAtDisplay,
                        updated: button.dataset.updated,
                    });
                });
            });

            resetAdminForm();
            resetPegawaiForm();
        });
    </script>
    <script src="{{ asset('assets/js/admin-forms.js') }}" defer></script>
    <script src="{{ asset('assets/js/admin-users.js') }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all profile images
            ImageHandler.defaultUserImage = window.defaultImagePaths.user;
            ImageHandler.fallbackImages = window.defaultImagePaths.userFallback;
            ImageHandler.initializeProfileImages();

            // Re-initialize profile images when the table content changes
            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        ImageHandler.initializeProfileImages();
                    }
                });
            });

            // Watch for table content changes
            const tableBody = document.querySelector('.table-body');
            if (tableBody) {
                observer.observe(tableBody, { childList: true, subtree: true });
            }

            // Watch for modal content changes
            const modalContainer = document.querySelector('#modal');
            if (modalContainer) {
                observer.observe(modalContainer, { childList: true, subtree: true });
            }

            // Initialize form handlers
            window.resetAdminForm = resetAdminForm;
            window.resetPegawaiForm = resetPegawaiForm;
        });
    </script>
@endpush
