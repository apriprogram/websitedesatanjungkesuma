@php
    $notifications = $notifications ?? ($adminNotifications ?? []);
@endphp

<div class="notification-dropdown">
    <button
        class="header-icon"
        id="notificationToggle"
        type="button"
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="Notifikasi"
    >
        <i class="fas fa-bell"></i>
    </button>
    <div class="notification-menu" role="menu" aria-hidden="true">
        <div class="notification-header">
            <span>Notifikasi</span>
            <small>Riwayat aktivitas terbaru</small>
        </div>
        <ul>
            @forelse ($notifications as $notification)
                <li>
                    <a href="#" class="notification-entry">
                        <span class="notification-icon">
                            <i class="fas {{ $notification['icon'] ?? 'fa-bell' }}"></i>
                        </span>
                        <div>
                            <p class="notification-title">{{ $notification['module'] ?? 'Aktivitas Sistem' }}</p>
                            <span class="notification-message" style="display: block; line-height: 1.4; margin-top: 2px;">
                                {{ $notification['message'] ?? 'Perubahan terbaru terekam.' }}
                            </span>
                            @if (! empty($notification['user']))
                                <span class="notification-meta">Oleh {{ $notification['user'] }}</span>
                            @endif
                            @if (! empty($notification['time']))
                                <time @if(! empty($notification['timestamp'])) datetime="{{ $notification['timestamp'] }}" @endif>
                                    {{ $notification['time'] }}
                                </time>
                            @endif
                        </div>
                    </a>
                </li>
            @empty
                <li>
                    <a href="#" class="notification-entry">
                        <span class="notification-icon">
                            <i class="fas fa-inbox"></i>
                        </span>
                        <div>
                            <p class="notification-title">Belum ada notifikasi</p>
                            <span class="notification-message">Aktivitas terbaru akan muncul di sini.</span>
                            <time>{{ now()->diffForHumans() }}</time>
                        </div>
                    </a>
                </li>
            @endforelse
        </ul>
    </div>
</div>
