@php
    use Illuminate\Support\Str;

    $attachments = $page->attachments ?? collect();
    $imageAttachments = $attachments->where('type', 'image')->values();
    $fileAttachments = $attachments->where('type', 'file')->values();

    // Media upload (feature image) tampil di atas konten
    $topImageUrl = $page->feature_image ? image_url($page->feature_image) : null;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->meta_title ?: $page->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <style>
        body {
            background: #fff;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .page-hero {
            background: #1e4fb3;
            color: #fff;
            padding: 88px 0 28px;
        }

        .page-hero .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 28px;
            text-align: left;
        }

        .page-hero .breadcrumb {
            color: #c8d7ff;
            font-size: 0.7rem;
            margin-bottom: 14px;
            line-height: 1.9;
        }

        .page-hero .breadcrumb a {
            color: #c8d7ff;
            text-decoration: none;
        }

        .page-hero .page-title {
            font-size: 1.8rem;
            margin: 0 0 8px;
            line-height: 1.3;
            font-weight: 500;
        }

        .page-hero .page-meta {
            font-size: 0.7rem;
            color: #c8d7ff;
            margin-top: 6px;
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-hero .page-meta .meta-sep {
            color: #c8d7ff;
        }

        .page-hero .page-meta .meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .page-hero .page-meta .meta-item i {
            font-size: 0.7rem;
        }

        .page-body {
            max-width: 1200px;
            margin: 24px auto 40px;
            background: transparent;
            padding: 0 32px;
            flex: 1;
        }

        .page-image {
            text-align: left;
            margin: 0 0 18px;
        }

        .page-image img {
            max-width: 100%;
            border-radius: 12px;
            display: block;
        }

        .page-detail__body {
            line-height: 1.7;
            color: #1f2937;
            font-size: 0.95rem;
        }

        .announcement-attachments {
            margin-top: 22px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .attachment-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .attachment-file {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .attachment-preview img {
            max-width: 100%;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .page-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
                @media (max-width: 768px) {
            .page-hero .page-title {
                font-size: 1.2rem;
                margin: 0 0 8px;
                line-height: 1.3;
                font-weight: 600;
            }

            .page-body p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>

<body>
    @include('frontend.partials.nav', ['navMenus' => $navMenus ?? collect()])

    <!-- Mobile Menu -->
    @include('frontend.partials.mobile-menu', [
        'navMenus' => $navMenus ?? collect(),
        'extraLinks' => [['title' => 'Transparansi Anggaran', 'url' => route('budget.transparency.page')]]
    ])

    <main class="page-main">
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ url('/') }}">Home</a> / <span>Halaman</span> /
                    <span>{{ $page->title }}</span>
                </div>
                <h1 class="page-title">{{ $page->title }}</h1>
                <div class="page-meta">
                    <span class="meta-item"><i
                            class="fas fa-calendar"></i>{{ $page->updated_at?->locale('id')->translatedFormat('l, d M Y') ?? 'Tanggal tidak tersedia' }}</span>
                    <span class="meta-sep">|</span>
                    <span class="meta-item"><i class="fas fa-eye"></i> {{ $page->views ?? 0 }} x dilihat</span>
                </div>
            </div>
        </section>

        <section class="page-body container">
            @if($topImageUrl)
                <div class="page-image">
                    <img src="{{ $topImageUrl }}" alt="{{ $page->title }}" loading="lazy">
                </div>
            @endif

            <div class="page-detail__body">
                {!! $page->content !!}
            </div>

            @if($imageAttachments->count())
                <div class="announcement-attachments" style="margin-top: 20px; margin-bottom: 8px;">
                    @foreach($imageAttachments as $att)
                        @if($att->url)
                            <div class="attachment-card">
                                <div class="attachment-preview">
                                    <img src="{{ $att->url }}" alt="{{ $att->original_name ?? 'Lampiran gambar' }}" loading="lazy">
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($fileAttachments->count())
                <div class="announcement-attachments">
                    @foreach($fileAttachments as $att)
                        @if($att->url)
                            <div class="attachment-card">
                                <div class="attachment-file">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <i class="fas fa-file-alt" style="font-size: 20px; color: #475569;"></i>
                                        <div>
                                            <div style="font-weight:600; color: #0f172a;">
                                                {{ $att->original_name ?? basename($att->path) }}
                                            </div>
                                            <small style="color: #64748b;">Dokumen</small>
                                        </div>
                                    </div>
                                    <a class="news-btn news-btn--ghost" href="{{ $att->url }}" target="_blank" rel="noopener"
                                        style="color: #2563eb; text-decoration: none; padding:8px; display:flex; align-items:center; justify-content:center;"
                                        title="Unduh">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    @include('frontend.partials.footer', [
        'socialLinks' => $socialLinks ?? collect(),
        'publicInfoSetting' => $publicInfoSetting ?? null,
    ])






    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>
</html>
