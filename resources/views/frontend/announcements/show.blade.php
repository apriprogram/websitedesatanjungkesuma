@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }} - Pengumuman</title>
    <link rel="icon" href="{{ asset('img/logo/logo_lampung_timur.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            line-height: 1.5;
        }

        .page-hero .breadcrumb a {
            color: #c8d7ff;
            text-decoration: none;
        }

        .page-hero .page-title {
            font-size: 1.9rem;
            margin: 0 0 8px;
            line-height: 1.3;
            font-weight: 600;
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

            .page-hero {
                padding: 55px 0 15px;
            }
        }
    </style>
</head>

<body>
    @include('frontend.partials.nav', ['navMenus' => $navMenus ?? collect()])

    @php
        $attachments = $announcement->attachments ?? collect();
        $imageAttachments = $attachments->where('type', 'image')->values();
        $fileAttachments = $attachments->where('type', 'file')->values();
        $mainImage = $imageAttachments->first();
        $otherImages = $imageAttachments->slice(1);
    @endphp

    <main class="page-main">
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ url('/') }}">Home</a> / <span>Pengumuman</span> /
                    <span>{{ $announcement->title }}</span>
                </div>
                <h1 class="page-title">{{ $announcement->title }}</h1>
                <div class="page-meta">
                    <span class="meta-item"><i
                            class="fas fa-calendar"></i>{{ optional($announcement->published_at ?? $announcement->created_at)->locale('id')->translatedFormat('l, d M Y') }}</span>
                    <span class="meta-sep">|</span>
                    <span class="meta-item"><i
                            class="fas fa-tag"></i>{{ \App\Models\Announcement::categories()[$announcement->category] ?? 'Pengumuman' }}</span>
                    <span class="meta-sep">|</span>
                    <span class="meta-item"><i class="fas fa-eye"></i> {{ $announcement->views }} x dilihat</span>
                </div>
            </div>
        </section>

        <section class="page-body container">
            @if($mainImage && $mainImage->url)
                <div class="page-image">
                    <img src="{{ $mainImage->url }}" alt="{{ $announcement->title }}" loading="lazy">
                </div>
            @endif

            @if($otherImages->count())
                <div class="announcement-attachments" style="margin-top:8px;">
                    @foreach($otherImages as $att)
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

            <div class="page-detail__body">
                {!! $announcement->body !!}
            </div>

            @if($fileAttachments->count())
                <div class="announcement-attachments">
                    @foreach($fileAttachments as $att)
                        @if($att->url)
                            <div class="attachment-card">
                                <div class="attachment-file">
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <i class="fas fa-file-alt"></i>
                                        <div>
                                            <div style="font-weight:600;">{{ $att->original_name ?? basename($att->path) }}</div>
                                            <small>Dokumen</small>
                                        </div>
                                    </div>
                                    <a class="news-btn news-btn--ghost" href="{{ $att->url }}" target="_blank" rel="noopener"
                                        style="padding:10px; width:40px; height:40px; display:flex; align-items:center; justify-content:center; border-radius:50%;"
                                        title="Unduh"><i class="fas fa-download"></i></a>
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
