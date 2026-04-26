@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    $mediaUrl = function ($path, $default = null) {
        if (empty($path)) {
            return $default;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return $default;
    };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri Video - Desa Tanjung Kesuma</title>
    <link rel="icon" href="{{ asset('img/logo/logo_lampung_timur.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <style>
        body { background: #f8fafc; }
        .video-page { max-width: 1100px; margin: 0 auto 52px; padding: 90px 18px 52px; }
        .video-header { margin-bottom: 16px; }
        .video-header h1 { margin: 0 0 6px; font-size: 1.6rem; color: #111827; font-weight: 600; }
        .video-header p { margin: 0; color: #4b5563; }
        .video-breadcrumb { font-size: 0.9rem; color: #6b7280; margin-bottom: 8px; display: flex; gap: 6px; flex-wrap: wrap; }
        .video-breadcrumb a { color: #2563eb; font-weight: 600; text-decoration: none; }
        .video-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; }
        .video-card {
            background: #fff;
            border: 1px solid #e8eaf6;
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(79, 70, 229, 0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100%;
            transition: transform 0.15s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .video-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 22px 48px rgba(79, 70, 229, 0.12);
            outline: 2px solid #2563eb;
            outline-offset: -2px;
        }
        .video-media {
            position: relative;
            padding: 12px;
        }
        .video-frame {
            position: relative;
            padding-top: 56.25%;
            border-radius: 14px;
            overflow: hidden;
            background: #e5e7eb;
        }
        .video-frame iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .video-body {
            padding: 14px 16px 4px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .video-meta h2 {
            margin: 0;
            font-size: 1.15rem;
            color: #1f2937;
            line-height: 1.35;
            font-weight: 600;
        }
        .video-meta p {
            margin: 4px 0 0;
            color: #4b5563;
            line-height: 1.55;
            font-size: 0.8rem;
        }
        .video-meta .meta-row {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 4px;
        }
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .video-footer {
            margin-top: 12px;
            padding: 12px 16px;
            border-top: 1px solid #edf0f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

        }
        .video-footer a{
            font-size: 0.8rem;
            font-weight: 500;
        }
        .video-link {
            color: #111827;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.95rem;
        }
        .video-action {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #6366f1, #7c3aed);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 12px 26px rgba(99, 102, 241, 0.28);
            text-decoration: none;
            transition: transform 0.12s ease, box-shadow 0.15s ease;
        }
        .video-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 32px rgba(99, 102, 241, 0.32);
        }
        .video-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            z-index: 60;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.22s ease, visibility 0.22s ease;
        }
        .video-modal-overlay.is-visible {
            opacity: 1;
            visibility: visible;
        }
        .video-modal-overlay[hidden] { display: none !important; }
        .video-modal {
            background: #fff;
            border-radius: 18px;
            max-width: 960px;
            width: 100%;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
            transform: translateY(8px) scale(0.98);
            opacity: 0;
            transition: transform 0.24s ease, opacity 0.24s ease;
        }
        .video-modal-overlay.is-visible .video-modal {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .video-modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff;
            color: #111827;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12);
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .video-modal-close:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.16);
        }
        .video-modal-body { padding: 56px 18px 20px; }
        .video-modal-frame {
            position: relative;
            padding-top: 56.25%;
            border-radius: 14px;
            overflow: hidden;
            background: #0f172a;
        }
        .video-modal-frame iframe {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        .video-modal-title {
            margin-top: 14px;
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            text-align: left;
        }
        body.modal-open { overflow: hidden; }
        @media (max-width: 640px) {
            .video-frame { padding-top: 62%; }
            .video-modal-body { padding: 52px 12px 16px; }
            .video-meta h2 {
                font-size: 1rem;
                font-weight: 600;
            }
            .video-meta p {
                font-size: 0.8rem;
                font-weight: 500;
            }
            .meta-row span{
                font-size: 0.8rem;
                font-weight: 500;
                margin-top: 8px;
            }
            .video-header p{
                font-size: 1rem;
                font-weight: 500;
            }
            .video-page {
                padding: 90px 8px 10px;
                margin-bottom: 0px;
            }
        }

        /* Dark Mode Overrides */
        body.dark-mode { background: #0f172a; }
        body.dark-mode .video-header h1 { color: #f1f5f9; }
        body.dark-mode .video-header p { color: #cbd5e1; }
        body.dark-mode .video-card { background: #1e293b; border-color: #334155; }
        body.dark-mode .video-media { background: #0f172a; }
        body.dark-mode .video-meta h2 { color: #f1f5f9; }
        body.dark-mode .video-meta p { color: #94a3b8; }
        body.dark-mode .video-footer { border-top-color: #334155; }
        body.dark-mode .video-link { color: #f1f5f9; }
        body.dark-mode .video-modal { background: #1e293b; }
        body.dark-mode .video-modal-title { color: #f1f5f9; }
        body.dark-mode .video-modal-close { background: #1e293b; color: #f1f5f9; border-color: #334155; }
        body.dark-mode .video-modal-close:hover { background: #334155; }
    </style>
</head>
<body>
    @include('frontend.partials.nav', ['navMenus' => $navMenus ?? collect()])

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
            @php
                $navTree = ($navMenus ?? collect())->where('is_active', true)->whereNull('parent_id')->sortBy('position');
                $resolveUrl = function ($item) {
                    if ($item->type === 'custom' && $item->url) return $item->url;
                    if ($item->page_slug) return url($item->page_slug);
                    return '#';
                };
            @endphp
            @foreach ($navTree as $item)
                @php
                    $children = $item->children->where('is_active', true)->sortBy('position');
                    $menuId = 'nav-' . $loop->index;
                @endphp
                <li>
                    <a href="{{ $resolveUrl($item) }}"
                        @if($children->count()) onclick="toggleMobileSubmenu(event, '{{ $menuId }}')" @endif
                        @if($item->target_blank) target="_blank" rel="noopener" @endif>
                        {{ $item->title }}
                    </a>
                    @if($children->count())
                        <ul class="mobile-submenu" id="{{ $menuId }}-submenu">
                            @foreach ($children as $child)
                                <li>
                                    <a href="{{ $resolveUrl($child) }}" @if($child->target_blank) target="_blank" rel="noopener" @endif>
                                        {{ $child->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
            <li><a href="{{ route('budget.transparency.page') }}">Transparansi Anggaran</a></li>
        </ul>
    </div>

    <div class="video-page">
        <div class="video-breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> / <span>Galeri Video</span>
        </div>
        <div class="video-header">
            <h1>Galeri Video</h1>
            <p>Kumpulan video Desa Tanjung Kesuma dari kanal YouTube resmi.</p>
        </div>

        <div class="video-grid">
            @forelse($videos as $video)
                @php
                    $embedUrl = $video->embed_url ? $video->embed_url . '?controls=1&rel=0&modestbranding=1&playsinline=1' : '';
                    $watchUrl = $video->youtube_url ?: ($video->embed_url ?: '#');
                @endphp
                <article class="video-card"
                    data-embed="{{ $embedUrl ?: $watchUrl }}"
                    data-title="{{ $video->title }}">
                    <div class="video-media">
                        <div class="video-frame">
                            @if($embedUrl)
                                <iframe src="{{ $embedUrl }}" title="{{ $video->title }}" loading="lazy" allowfullscreen></iframe>
                            @else
                                <div class="news-list-thumb--placeholder" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#cbd5e1;font-size:28px;">
                                    <i class="fas fa-video"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="video-body">
                        <div class="video-meta">
                            <h2>{{ $video->title }}</h2>
                            @if($video->description)
                                <p>{{ Str::limit(strip_tags($video->description), 180) }}</p>
                            @endif
                            <div class="meta-row">
                                <span class="meta-chip"><i class="far fa-calendar"></i>{{ optional($video->published_at ?? $video->created_at)->format('d M Y') ?? 'Video' }}</span>
                                <span class="meta-chip"><i class="fab fa-youtube"></i> YouTube</span>
                            </div>
                        </div>
                    </div>
                    <div class="video-footer">
                        <a class="video-link" href="{{ $watchUrl }}" target="_blank" rel="noopener">Buka di YouTube</a>
                        <a class="video-action" href="{{ $watchUrl }}" target="_blank" rel="noopener" aria-label="Lihat video">
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="video-card" style="align-items:center; text-align:center;">
                    <div style="font-size:54px; color:#cbd5e1; margin-bottom:10px;">
                        <i class="fas fa-photo-video" aria-hidden="true"></i>
                    </div>
                    <div class="video-meta">
                        <h2>Belum ada video</h2>
                        <p>Tidak ada video untuk ditampilkan.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:16px;">
            {{ $videos->onEachSide(1)->links('frontend.partials.pagination') }}
        </div>

        <div class="video-modal-overlay" id="videoModal" hidden>
            <div class="video-modal">
                <button class="video-modal-close" type="button" aria-label="Tutup" id="videoModalClose">
                    <i class="fas fa-times"></i>
                </button>
                <div class="video-modal-body">
                    <div class="video-modal-frame">
                        <iframe id="videoModalFrame" src="" allowfullscreen allow="autoplay"></iframe>
                    </div>
                    <div class="video-modal-title" id="videoModalTitle">Video</div>
                </div>
            </div>
        </div>
    </div>

    @include('frontend.partials.footer', [
        'socialLinks' => $socialLinks ?? collect(),
        'publicInfoSetting' => $publicInfoSetting ?? null,
    ])
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
    <script>
        (function() {
            const modal = document.getElementById('videoModal');
            const modalFrame = document.getElementById('videoModalFrame');
            const modalTitle = document.getElementById('videoModalTitle');
            const closeBtn = document.getElementById('videoModalClose');
            const cards = document.querySelectorAll('.video-card');

            const openModal = (embed, title) => {
                if (!embed) return;
                const url = embed.includes('?') ? `${embed}&autoplay=1` : `${embed}?autoplay=1`;
                modalFrame.src = url;
                modalTitle.textContent = title || 'Video';
                modal.hidden = false;
                requestAnimationFrame(() => modal.classList.add('is-visible'));
                document.body.classList.add('modal-open');
            };

            const closeModal = () => {
                modal.classList.remove('is-visible');
                setTimeout(() => {
                    modalFrame.src = '';
                    modal.hidden = true;
                    document.body.classList.remove('modal-open');
                }, 240);
            };

            cards.forEach(card => {
                const embed = card.getAttribute('data-embed');
                const title = card.getAttribute('data-title');

                card.addEventListener('click', () => openModal(embed, title));

                // Keep links working without opening modal
                card.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', e => e.stopPropagation());
                });
            });

            closeBtn?.addEventListener('click', closeModal);
            modal?.addEventListener('click', (e) => {
                if (e.target === modal) closeModal();
            });
            document.addEventListener('keyup', (e) => {
                if (e.key === 'Escape' && !modal.hidden) closeModal();
            });
        })();
    </script>
</body>
</html>
