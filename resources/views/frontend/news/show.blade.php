@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;

    $mediaUrl = function ($path, $default = null) {
        if (empty($path)) return $default;
        if (filter_var($path, FILTER_VALIDATE_URL) || Str::startsWith($path, ['http://', 'https://'])) return $path;
        
        $normalized = ltrim(str_replace(['storage/', 'public/'], '', $path), '/');
        $fullPath = public_path('storage/' . $normalized);
        
        if (file_exists($fullPath)) {
            return asset('storage/' . $normalized);
        }
        return $default;
    };

    $imgSrc = $mediaUrl($news->thumbnail);
    $publishedAt = $news->published_at ?? $news->created_at;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $news->seo_title ?: $news->title }} - Desa Tanjung Kesuma</title>
    <meta name="description" content="{{ $news->seo_description ?: Str::limit(strip_tags($news->content), 150) }}">
    <meta name="keywords" content="{{ $news->seo_keywords ?: 'Berita Desa, Tanjung Kesuma, Lampung Timur' }}">
    <meta name="author" content="{{ $news->author?->name ?? 'Pemerintah Desa Tanjung Kesuma' }}">
    <link rel="canonical" href="{{ route('news.show', $news->slug) }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('news.show', $news->slug) }}">
    <meta property="og:title" content="{{ $news->seo_title ?: $news->title }}">
    <meta property="og:description" content="{{ $news->seo_description ?: Str::limit(strip_tags($news->content), 150) }}">
    @if($imgSrc)
    <meta property="og:image" content="{{ $imgSrc }}">
    @endif
    <meta property="article:published_time" content="{{ $publishedAt->toIso8601String() }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ route('news.show', $news->slug) }}">
    <meta property="twitter:title" content="{{ $news->seo_title ?: $news->title }}">
    <meta property="twitter:description" content="{{ $news->seo_description ?: Str::limit(strip_tags($news->content), 150) }}">
    @if($imgSrc)
    <meta property="twitter:image" content="{{ $imgSrc }}">
    @endif

    <!-- JSON-LD Schema for NewsArticle -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "NewsArticle",
      "headline": "{{ $news->title }}",
      @if($imgSrc)
      "image": [
        "{{ $imgSrc }}"
      ],
      @endif
      "datePublished": "{{ $publishedAt->toIso8601String() }}",
      "dateModified": "{{ $news->updated_at->toIso8601String() }}",
      "author": [{
          "@@type": "Person",
          "name": "{{ $news->author?->name ?? 'Admin Desa' }}"
      }],
      "publisher": {
        "@@type": "GovernmentOrganization",
        "name": "Pemerintah Desa Tanjung Kesuma",
        "logo": {
          "@@type": "ImageObject",
          "url": "{{ asset('img/Logo/logo_lampung_timur.png') }}"
        }
      }
    }
    </script>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
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
            margin: 0 0 16px;
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

        /* Dark mode */
        body.dark-mode {
            background: #0b1220;
            color: #e5e7eb;
        }

        body.dark-mode .page-hero {
            background: #0f1f3d;
            color: #e5e7eb;
        }

        body.dark-mode .page-hero .breadcrumb,
        body.dark-mode .page-hero .breadcrumb a {
            color: #9fb5ff;
        }

        body.dark-mode .page-hero .page-meta,
        body.dark-mode .page-hero .page-meta .meta-sep {
            color: #9fb5ff;
        }

        body.dark-mode .page-detail__body {
            color: #e5e7eb;
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

    <main class="page-main">
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ url('/') }}">Home</a> / <a href="{{ route('news.index') }}">Berita</a> /
                    <span>{{ $news->title }}</span>
                </div>
                <h1 class="page-title">{{ $news->title }}</h1>
                <div class="page-meta">
                    <span class="meta-item"><i
                            class="fas fa-calendar"></i>{{ optional($publishedAt)->locale('id')->translatedFormat('l, d M Y') }}</span>
                    <span class="meta-sep">|</span>
                    <span class="meta-item"><i class="fas fa-user"></i> Admin</span>
                    <span class="meta-sep">|</span>
                    <span class="meta-item"><i class="fas fa-eye"></i> {{ $news->views ?? 0 }} x dilihat</span>
                </div>
            </div>
        </section>

        <section class="page-body container">
            @if($imgSrc)
                <div class="page-image">
                    <img src="{{ $imgSrc }}" alt="{{ $news->title }}" loading="lazy">
                </div>
            @endif

            <div class="page-detail__body">
                {!! $news->content !!}
            </div>
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
