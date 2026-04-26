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

    $categoryOptions = $categoryOptions ?? [];
    $budgetItemsFlat = $budgetItems->flatten(1)->values();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Anggaran - Tanjung Kesuma</title>
    <link rel="icon" href="{{ asset('img/logo/logo_lampung_timur.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/budget-page.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
</head>

<body class="stats-body">
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
        </ul>
    </div>

    <main class="stats-page">
        <section class="stats-hero">
            <div class="stats-hero__copy">
                <div class="breadcrumb">Beranda / Statistik Anggaran</div>
                <h1>Statistik Anggaran</h1>
                <p>Distribusi anggaran dan realisasi per kategori dan subkategori.</p>
                <div class="meta-pills">
                    <span class="pill pill-primary"><i class="fa-regular fa-calendar"></i> Tahun {{ $budgetYear }}</span>
                    <span class="pill"><i class="fa-solid fa-layer-group"></i> {{ $selectedCategory ? ($categoryOptions[$selectedCategory] ?? 'Kategori') : 'Semua Kategori' }}</span>
                </div>
            </div>
            <div class="stats-hero__cards">
                <div class="summary-card">
                    <p class="summary-label">Total Anggaran</p>
                    <h3>Rp {{ number_format((float) data_get($budgetTotals, 'anggaran', 0), 0, ',', '.') }}</h3>
                    <p class="summary-note">Pagu APBDes</p>
                </div>
                <div class="summary-card">
                    <p class="summary-label">Realisasi</p>
                    <h3>Rp {{ number_format((float) data_get($budgetTotals, 'realisasi', 0), 0, ',', '.') }}</h3>
                    <p class="summary-note">{{ data_get($budgetTotals, 'percent', 0) }}% serapan</p>
                </div>
            </div>
        </section>

        <section class="filter-bar">
            <form method="GET" action="{{ route('statistics.budget.page') }}" class="filter-form" id="budgetFilterForm">
                <div class="filter-group">
                    <label for="filterYearBudget">Tahun</label>
                    <select name="year" id="filterYearBudget" onchange="document.getElementById('budgetFilterForm').submit()">
                        @foreach ($budgetYears as $year)
                            <option value="{{ $year }}" @selected((int) $year === (int) $budgetYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterCategoryBudget">Kategori Anggaran</label>
                    <select name="category" id="filterCategoryBudget" onchange="document.getElementById('budgetFilterForm').submit()">
                        <option value="">Semua</option>
                        @foreach ($categoryOptions as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected($selectedCategory === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </section>

        <section class="stats-grid">
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Distribusi Anggaran</h3>
                    <p>Perbandingan Anggaran vs Realisasi per kategori.</p>
                    <div class="card-menu">
                        <button class="menu-trigger" data-canvas="budgetBarChart"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('budgetBarChart','jpg')"><i class="fa-regular fa-image"></i> Export JPG</button>
                            <button onclick="exportChart('budgetBarChart','pdf')"><i class="fa-regular fa-file-pdf"></i> Export PDF</button>
                        </div>
                    </div>
                </div>
                <canvas id="budgetBarChart" height="200"></canvas>
            </div>
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Serapan per Kategori</h3>
                    <p>Persentase serapan anggaran.</p>
                    <div class="card-menu">
                        <button class="menu-trigger" data-canvas="budgetPieChart"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('budgetPieChart','jpg')"><i class="fa-regular fa-image"></i> Export JPG</button>
                            <button onclick="exportChart('budgetPieChart','pdf')"><i class="fa-regular fa-file-pdf"></i> Export PDF</button>
                        </div>
                    </div>
                </div>
                <canvas id="budgetPieChart" height="200"></canvas>
            </div>
        </section>

        <section class="stats-panel stats-table">
            <div class="stats-panel__header">
                <h3>Tabel Anggaran</h3>
                <p>Detail per subkategori sesuai filter.</p>
            </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Subkategori</th>
                            <th>Realisasi</th>
                            <th>Anggaran</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($budgetItemsFlat as $item)
                            @php
                                $pct = $item->anggaran > 0 ? round(($item->realisasi / $item->anggaran) * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ ucfirst($item->category) }}</td>
                                <td>{{ $item->subcategory ?: $item->description }}</td>
                                <td>Rp {{ number_format((float) $item->realisasi, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $item->anggaran, 0, ',', '.') }}</td>
                                <td>
                                    <div class="table-progress" data-progress="{{ $pct }}">
                                        <span style="width:0%"></span>
                                        <em>{{ $pct }}%</em>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;">Belum ada data anggaran untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    @include('frontend.partials.footer', ['socialLinks' => $socialLinks, 'publicInfoSetting' => $publicInfoSetting])

    <script src="{{ asset('assets/vendor/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
    <script>
        const budgetSummary = @json($budgetSummary ?? []);
        const budgetTotals = @json($budgetTotals ?? []);

        const labelsMap = {
            pelaksanaan: 'Pelaksanaan',
            pendapatan: 'Pendapatan',
            pembelanjaan: 'Pembelanjaan',
        };

        const chartColors = ['#2162e2', '#4dabf7', '#9b59b6', '#27ae60', '#f39c12', '#e74c3c'];

        function renderBudgetBar() {
            const ctx = document.getElementById('budgetBarChart');
            if (!ctx) return;

            const labels = Object.keys(budgetSummary).map(k => labelsMap[k] || k);
            const anggaran = Object.values(budgetSummary).map(v => Number(v.anggaran || 0));
            const realisasi = Object.values(budgetSummary).map(v => Number(v.realisasi || 0));

            if (window.budgetBarInst) window.budgetBarInst.destroy();
            window.budgetBarInst = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Anggaran', data: anggaran, backgroundColor: '#cddcff' },
                        { label: 'Realisasi', data: realisasi, backgroundColor: '#2162e2' }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        datalabels: { anchor: 'end', align: 'end', formatter: (v) => v.toLocaleString('id-ID') }
                    },
                    scales: { x: { stacked: false }, y: { beginAtZero: true } }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderBudgetPie() {
            const ctx = document.getElementById('budgetPieChart');
            if (!ctx) return;
            const labels = Object.keys(budgetSummary).map(k => labelsMap[k] || k);
            const data = Object.values(budgetSummary).map(v => Number(v.realisasi || 0));
            if (window.budgetPieInst) window.budgetPieInst.destroy();
            window.budgetPieInst = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: chartColors
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            formatter: (value) => {
                                const total = data.reduce((a, b) => a + b, 0);
                                return total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                            }
                        }
                    },
                    cutout: '65%'
                },
                plugins: [ChartDataLabels]
            });
        }

        function animateTableBars() {
            document.querySelectorAll('.table-progress span').forEach(bar => {
                const target = bar.parentElement?.getAttribute('data-progress') || '0';
                requestAnimationFrame(() => { bar.style.width = target + '%'; });
            });
        }

        function exportChart(canvasId, type = 'jpg') {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            const dataUrl = canvas.toDataURL('image/png', 1.0);
            if (type === 'jpg') {
                const offscreen = document.createElement('canvas');
                const ctx = offscreen.getContext('2d');
                const img = new Image();
                img.onload = () => {
                    const padding = 24;
                    offscreen.width = img.width + padding * 2;
                    offscreen.height = img.height + padding * 2;
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, offscreen.width, offscreen.height);
                    ctx.drawImage(img, padding, padding);
                    const finalUrl = offscreen.toDataURL('image/jpeg', 1.0);
                    const link = document.createElement('a');
                    link.href = finalUrl;
                    link.download = canvasId + '.jpg';
                    link.click();
                };
                img.src = dataUrl;
            } else if (type === 'pdf') {
                const win = window.open('', '_blank');
                if (!win) return;
                win.document.write(`
                    <html>
                    <head><title>${canvasId}</title></head>
                    <body style="margin:24px; background:#fff; display:flex; justify-content:center; align-items:center;">
                        <img id="chart-export-img" src="${dataUrl}" style="max-width:100%; width:100%; height:auto; object-fit:contain; border:1px solid #f0f0f0; padding:12px; background:#fff;">
                    </body>
                    </html>`);
                win.document.close();
                const img = win.document.getElementById('chart-export-img');
                if (img) {
                    img.onload = () => {
                        win.focus();
                        win.print();
                    };
                } else {
                    win.focus();
                    win.print();
                }
            }
        }

        renderBudgetBar();
        renderBudgetPie();
        animateTableBars();

        // Dropdown export menu
        const triggers = document.querySelectorAll('.menu-trigger');
        const closeMenus = () => document.querySelectorAll('.menu-dropdown').forEach(m => m.classList.remove('open'));
        triggers.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                closeMenus();
                const menu = btn.nextElementSibling;
                if (menu) menu.classList.toggle('open');
            });
        });
        document.addEventListener('click', () => closeMenus());
    </script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>

</html>
