@php
    $headerUser = $headerUser ?? auth()->user();
    $defaultAvatar = \Illuminate\Support\Facades\Storage::disk('public')->exists('default/user.jpg')
        ? \Illuminate\Support\Facades\Storage::url('default/user.jpg')
        : asset('assets/default/user.jpg');

    $headerAvatar = $headerAvatar ?? ($headerUser?->avatar_url ?: $defaultAvatar);
    $headerName = $headerName ?? ($headerUser?->nama ?? 'Admin Desa');
    $headerEmail = $headerEmail ?? ($headerUser?->email ?? 'admin@tanjungkesuma.id');
    $isSuperAdmin = $headerUser?->is_admin ?? false;
    $headerRole = $headerRole ?? ($isSuperAdmin ? 'Super Admin' : 'Administrator');
@endphp

<div class="header-cluster header-cluster-right">
    <label class="mode-toggle" for="darkModeSwitch">
        <input type="checkbox" id="darkModeSwitch">
        <span class="mode-icon" aria-hidden="true"><i class="fas fa-moon"></i></span>
        <span class="mode-label">Mode Gelap</span>
        <span class="mode-switch" aria-hidden="true"></span>
    </label>

    @include('admin.partials.notification-dropdown')

    <div class="profile-dropdown">
        <button class="header-avatar" id="profileMenuToggle" type="button" aria-haspopup="true"
            aria-expanded="false">
            <img src="{{ $headerAvatar }}" alt="{{ $headerName }}" class="profile-image"
                data-default="{{ $defaultAvatar }}">
            <span class="sr-only">Buka menu profil</span>
        </button>
        <div class="profile-menu" role="menu" aria-hidden="true">
            <div class="profile-menu-header">
                <img src="{{ $headerAvatar }}" alt="{{ $headerName }}" class="profile-image"
                    data-default="{{ $defaultAvatar }}">
                <div>
                    <span class="profile-name">{{ $headerName }}</span>
                    <span class="profile-email">{{ $headerEmail }}</span>
                    <span class="profile-role">{{ $headerRole }}</span>
                </div>
            </div>
            <ul>
                <li>
                    <a href="{{ route('admin.profile.edit') }}" role="menuitem">
                        <i class="fas fa-user"></i><span>Profil</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.help-center') }}" role="menuitem">
                        <i class="fas fa-life-ring"></i><span>Pusat Bantuan</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('logout') }}" role="menuitem" class="logout-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-right-from-bracket"></i><span>Keluar</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
