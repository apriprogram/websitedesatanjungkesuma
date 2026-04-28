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

    $pegawaiList = $pegawaiList ?? collect();
    $statuses = $statuses ?? collect();

    $resolvePegawaiFoto = function ($pegawai, $default = 'img/Users/user2.png') {
        $foto = $pegawai->image_url ?? $pegawai->gambar ?? null;
        return image_url($foto, asset($default));
    };
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pegawai Desa - Tanjung Kesuma</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
</head>

<body class="pegawai-body-v2">
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

    <main class="pegawai-page-v2">
        <div class="pegawai-hero-v2">
            <div class="pegawai-breadcrumb-v2">
                <a href="{{ route('home') }}">Beranda</a> / <span>Pegawai Desa</span>
            </div>
            <div class="pegawai-hero-top-v2">
                <div class="pegawai-header-v2">
                    <h1>Pegawai Desa</h1>
                    <p class="pegawai-hero-desc-v2">Daftar perangkat desa lengkap dengan informasi jabatan dan status
                        kepegawaian.</p>
                </div>
                <a class="pegawai-back-btn-v2" href="{{ route('home') }}">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>

        <div class="pegawai-toolbar-v2">
            <div class="pegawai-total-v2">
                <i class="fas fa-users"></i> Total Pegawai: {{ $pegawaiList->count() }}
            </div>
            <div class="pegawai-filters-v2">
                <div class="pegawai-search-v2">
                    <i class="fas fa-search" style="color: #64748b;"></i>
                    <input type="text" id="pegawaiSearch" placeholder="Cari nama atau NIP...">
                </div>
                <select id="pegawaiStatus" class="pegawai-select-v2">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ Str::lower($status) }}">{{ $status }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <section class="pegawai-content-v2">
            @if($pegawaiList->isEmpty())
                <div class="pegawai-empty" id="pegawaiEmpty">Data pegawai belum tersedia.</div>
            @else
                @php
                    $kepalaDesa = $pegawaiList->first(fn($p) => Str::contains(Str::lower($p->jabatan ?? ''), 'kepala desa'));
                    $others = $kepalaDesa ? $pegawaiList->filter(fn($p) => $p->id !== $kepalaDesa->id) : $pegawaiList;

                    $staffLain = $others->sortBy(function ($p) {
                        $jabatan = Str::lower($p->jabatan ?? '');
                        if (Str::contains($jabatan, 'sekretaris')) return 1;
                        if (Str::contains($jabatan, 'bendahara')) return 2;
                        if (Str::contains($jabatan, 'kepala') || Str::contains($jabatan, 'kasi') || Str::contains($jabatan, 'kaur') || Str::contains($jabatan, 'kadus')) return 3;
                        if (Str::contains($jabatan, 'staf')) return 4;
                        if (Str::contains($jabatan, 'honorer')) return 5;
                        return 10;
                    });
                @endphp

                <!-- Kepala Desa Section -->
                @if($kepalaDesa)
                    @php
                        $fotoKD = $resolvePegawaiFoto($kepalaDesa);
                        $nipKD = $kepalaDesa->nip ?? 'Tidak tersedia';
                        $statusKD = $kepalaDesa->status_kepegawaian ?? $kepalaDesa->status ?? '-';
                    @endphp
                    <div class="pegawai-leader-section-v2">
                        <span class="leader-label-v2">Kepala Desa</span>
                        <div style="display: flex; justify-content: center; width: 100%;">
                            <div class="pegawai-card"
                                 data-filterable="true"
                                 data-name="{{ Str::lower($kepalaDesa->nama) }}"
                                 data-jabatan="kepala desa"
                                 data-nip="{{ Str::lower($nipKD) }}"
                                 data-status="{{ Str::lower($statusKD) }}">
                                 
                                <div class="pegawai-card__image">
                                    <img src="{{ $fotoKD }}" alt="{{ $kepalaDesa->nama }}">
                                </div>
                                <div class="pegawai-card-info">
                                    <div class="pegawai-name">{{ $kepalaDesa->nama }}</div>
                                    <div class="pegawai-role">Kepala Desa</div>
                                    <div class="pegawai-status-badge {{ Str::lower($statusKD) === 'aktif' ? 'aktif' : '' }}">{{ $statusKD }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Staff Grid -->
                <div class="pegawai-staff-section-v2">
                    <span class="leader-label-v2">Perangkat Desa</span>
                    <div class="pegawai-grid" id="pegawaiGrid">
                    @foreach($staffLain as $pegawai)
                        @php
                            $foto = $resolvePegawaiFoto($pegawai);
                            $nip = $pegawai->nip ?? $pegawai->nik ?? 'Tidak tersedia';
                            $status = $pegawai->status_kepegawaian ?? $pegawai->status ?? 'Tidak diketahui';
                            $jabatan = $pegawai->jabatan ?? 'Perangkat Desa';
                        @endphp
                        <div class="pegawai-card"
                            data-filterable="true"
                            data-name="{{ Str::lower($pegawai->nama ?? '') }}"
                            data-jabatan="{{ Str::lower($jabatan) }}"
                            data-nip="{{ Str::lower($nip) }}"
                            data-status="{{ Str::lower($status) }}">
                            <div class="pegawai-card__image">
                                <img src="{{ $foto }}" alt="{{ $pegawai->nama }}" loading="lazy">
                            </div>
                            <div class="pegawai-card-info">
                                <div class="pegawai-name">{{ $pegawai->nama }}</div>
                                <div class="pegawai-role">{{ $jabatan }}</div>
                                <div class="pegawai-status-badge {{ Str::lower($status) === 'aktif' ? 'aktif' : '' }}">{{ $status }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="pegawai-empty" id="pegawaiEmptyState" style="display:none; margin-top: 20px;">Tidak ada pegawai
                    sesuai filter.</div>
            @endif
        </section>
    </main>

    @include('frontend.partials.footer', ['socialLinks' => $socialLinks, 'publicInfoSetting' => $publicInfoSetting])

    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
    <script>
        (function () {
            const searchInput = document.getElementById('pegawaiSearch');
            const statusSelect = document.getElementById('pegawaiStatus');
            const cards = Array.from(document.querySelectorAll('[data-filterable="true"]'));
            const emptyState = document.getElementById('pegawaiEmptyState');

            function filterCards() {
                const query = (searchInput?.value || '').toLowerCase();
                const status = (statusSelect?.value || '').toLowerCase();
                let visible = 0;

                cards.forEach(card => {
                    const name = card.dataset.name || '';
                    const jabatan = card.dataset.jabatan || '';
                    const nip = card.dataset.nip || '';
                    const st = card.dataset.status || '';

                    const matchQuery = !query || name.includes(query) || jabatan.includes(query) || nip.includes(query);
                    const matchStatus = !status || st === status;

                    const show = matchQuery && matchStatus;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                if (emptyState) emptyState.style.display = visible ? 'none' : 'block';

                // Handle leader section visibility if empty
                const leaderSection = document.querySelector('.pegawai-leader-section-v2');
                if (leaderSection) {
                    const leaderCard = leaderSection.querySelector('.leader-card-v2');
                    leaderSection.style.display = (leaderCard && leaderCard.style.display === 'none') ? 'none' : '';
                }
            }

            if (searchInput) searchInput.addEventListener('input', filterCards);
            if (statusSelect) statusSelect.addEventListener('change', filterCards);

            filterCards();
        })();
    </script>
</body>

</html>
