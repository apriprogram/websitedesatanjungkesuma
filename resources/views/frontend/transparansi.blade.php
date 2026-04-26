@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $navMenus = $navMenus ?? collect();
    $socialLinks = $socialLinks ?? collect();
    $publicInfoSetting = $publicInfoSetting ?? new \App\Models\PublicInfoSetting(['is_published' => false]);

    $navTree = $navMenus->where('is_active', true)->whereNull('parent_id')->sortBy('position');

    $resolveUrl = function ($item) {
        if ($item->type === 'custom' && $item->url) {
            return $item->url;
        }
        if ($item->page_slug) {
            return url($item->page_slug);
        }
        return '#';
    };

    $pelaksanaan = $budgetItems['pelaksanaan'] ?? collect();
    $pendapatan = $budgetItems['pendapatan'] ?? collect();
    $pembelanjaan = $budgetItems['pembelanjaan'] ?? collect();
    $allItems = $budgetItems->flatten(1);

    $totalAnggaran = (float) $allItems->sum('anggaran');
    $totalRealisasi = (float) $allItems->sum('realisasi');
    $totalPercent = $totalAnggaran > 0 ? round(($totalRealisasi / $totalAnggaran) * 100) : 0;
    $lastUpdated = optional($allItems->sortByDesc('updated_at')->first())->updated_at;
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transparansi Anggaran Desa Tanjung Kesuma</title>
    <link rel="icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/transparency.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
</head>

<body class="transparansi-body">
    @include('frontend.partials.nav', ['navMenus' => $navMenus])

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <ul>
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

    <main class="transparansi-page">
        <section class="transparansi-head hero transparansi-hero">
            <div class="transparansi-container">
                <div class="transparansi-head__meta">
                    <div class="breadcrumb">Beranda / Transparansi Anggaran</div>
                    <h1>Transparansi Anggaran Desa</h1>
                    <p>Ringkasan APBDes {{ $budgetYear }} beserta realisasi per kategori. Data diperbarui setiap triwulan.</p>
                    <div class="transparansi-meta">
                        <span class="pill pill-primary"><i class="fa-regular fa-calendar"></i> Tahun {{ $budgetYear }}</span>
                        <span class="pill"><i class="fa-regular fa-clock"></i> Update {{ $lastUpdated ? $lastUpdated->format('d M Y') : '–' }}</span>
                        <span class="pill"><i class="fa-solid fa-shield-halved"></i> Akuntabilitas publik</span>
                    </div>
                </div>
                <div class="transparansi-summary">
                    <div class="summary-card">
                        <p class="summary-label">Total Anggaran</p>
                        <h3>Rp {{ number_format($totalAnggaran, 0, ',', '.') }}</h3>
                        <p class="summary-note">Pagu APBDes {{ $budgetYear }}</p>
                    </div>
                    <div class="summary-card">
                        <p class="summary-label">Realisasi</p>
                        <h3 class="text-accent">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</h3>
                        <p class="summary-note">Serapan hingga triwulan berjalan</p>
                        <div class="progress-line"><span style="width: {{ $totalPercent }}%"></span></div>
                        <div class="progress-meta">
                            <span>Total serapan</span>
                            <span>{{ $totalPercent }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="transparansi-section transparansi-section--page" id="ringkasan">
            <div class="transparansi-container">
            <div class="transparansi-header">
                <div class="transparansi-title">
                    <h2>Ringkasan Pelaksanaan</h2>
                    <p class="transparansi-desc">Realisasi dan anggaran per kategori utama.</p>
                    <span class="pill pill-muted" style="margin-top: 10px;">Tahun {{ $budgetYear }}</span>
                </div>
            </div>
            <div class="transparansi-grid">
                <div class="transparansi-row">
                    <!-- Pelaksanaan -->
                    <div class="transparansi-card pelaksanaan-card">
                        <div class="card-header">
                            <i class="fa-regular fa-money-bill-1"></i>
                            <span class="card-title">Pelaksanaan</span>
                        </div>
                        <div class="card-content-3col">
                            @forelse ($pelaksanaan as $item)
                                @php $percent = $item->progress_percent; @endphp
                                <div class="card-col">
                                    <div class="card-subtitle">{{ $item->subcategory ?: $item->description }}</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">Rp. {{ number_format((float) $item->realisasi, 0, ',', '.') }}</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                    <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                    <div class="card-percent">{{ $percent }}%</div>
                                </div>
                                @if(!$loop->last)
                                    <div class="card-divider"></div>
                                @endif
                            @empty
                                <div class="card-col">
                                    <div class="card-subtitle">Data pelaksanaan belum tersedia</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">-</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">-</div>
                                    <div class="card-bar"><span style="width:0%"></span></div>
                                    <div class="card-percent">0%</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="transparansi-row">
                    <!-- Pendapatan -->
                    <div class="transparansi-card pendapatan-card">
                        <div class="card-header">
                            <i class="fa-solid fa-wallet"></i>
                            <span class="card-title">Pendapatan</span>
                        </div>
                        <div class="card-content-3col pendapatan-grid">
                            @forelse ($pendapatan as $item)
                                @php $percent = $item->progress_percent; @endphp
                                <div class="card-col">
                                    <div class="card-subtitle">{{ $item->subcategory ?: $item->description }}</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">Rp. {{ number_format((float) $item->realisasi, 0, ',', '.') }}</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                    <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                    <div class="card-percent">{{ $percent }}%</div>
                                </div>
                            @empty
                                <div class="card-col">
                                    <div class="card-subtitle">Data pendapatan belum tersedia</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">-</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">-</div>
                                    <div class="card-bar"><span style="width:0%"></span></div>
                                    <div class="card-percent">0%</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="transparansi-row">
                    <!-- Pembelanjaan -->
                    <div class="transparansi-card pembelanjaan-card">
                        <div class="card-header">
                            <i class="fa-regular fa-file-lines"></i>
                            <span class="card-title">Pembelanjaan</span>
                        </div>
                        <div class="card-content-3col">
                            @forelse ($pembelanjaan as $item)
                                @php $percent = $item->progress_percent; @endphp
                                <div class="card-col">
                                    <div class="card-subtitle">{{ $item->subcategory ?: $item->description }}</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">Rp. {{ number_format((float) $item->realisasi, 0, ',', '.') }}</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">Rp. {{ number_format((float) $item->anggaran, 0, ',', '.') }}</div>
                                    <div class="card-bar"><span style="width:{{ $percent }}%"></span></div>
                                    <div class="card-percent">{{ $percent }}%</div>
                                </div>

                            @empty
                                <div class="card-col">
                                    <div class="card-subtitle">Data pembelanjaan belum tersedia</div>
                                    <div class="card-label">Realisasi</div>
                                    <div class="card-value highlight">-</div>
                                    <div class="card-label">Anggaran</div>
                                    <div class="card-value">-</div>
                                    <div class="card-bar"><span style="width:0%"></span></div>
                                    <div class="card-percent">0%</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </section>


    </main>

    @include('frontend.partials.footer', ['socialLinks' => $socialLinks, 'publicInfoSetting' => $publicInfoSetting])

    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>

</html>
