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

    $budgetYears = $budgetYears ?? collect();
    $selectedCategory = $selectedCategory ?: '';
    $selectedDusun = $selectedDusun ?: '';
    $dusunOptions = $dusunOptions ?? collect();

    $displayCategory = [
        \App\Models\BudgetItem::CATEGORY_PELAKSANAAN => 'Pelaksanaan',
        \App\Models\BudgetItem::CATEGORY_PENDAPATAN => 'Pendapatan',
        \App\Models\BudgetItem::CATEGORY_PEMBELANJAAN => 'Pembelanjaan',
    ][$selectedCategory] ?? 'Semua Kategori';

    $categoryOptions = [
        \App\Models\BudgetItem::CATEGORY_PELAKSANAAN => 'Pelaksanaan',
        \App\Models\BudgetItem::CATEGORY_PENDAPATAN => 'Pendapatan',
        \App\Models\BudgetItem::CATEGORY_PEMBELANJAAN => 'Pembelanjaan',
    ];

    $budgetItemsFlat = $budgetItems->flatten(1)->values();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Desa - Tanjung Kesuma</title>
    <link rel="icon" type="image/png" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <style>
        @media (max-width: 768px) {
            /* Resize Hero Title */
            .stats-hero h1 {
                font-size: 1.5rem !important;
                margin-bottom: 10px;
                line-height: 1.3;
            }

            /* Resize Breadcrumb */
            .stats-hero .breadcrumb {
                font-size: 0.75rem !important;
                margin-bottom: 5px;
            }

            /* Resize Meta Pills */
            .meta-pills {
                gap: 5px;
            }
            .meta-pills .pill {
                font-size: 0.75rem !important;
                padding: 4px 10px !important;
            }

            /* Resize Summary Cards */
            .summary-card {
                padding: 15px !important;
            }
            .summary-card h3 {
                font-size: 1.75rem !important;
                margin: 5px 0;
            }
            .summary-card .summary-label {
                font-size: 0.9rem !important;
            }
            .summary-card .summary-note {
                font-size: 0.8rem !important;
            }

            /* Widget Icons */
            .mini-card__icon {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }
            .mini-card__body h3 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body class="stats-body">
    @include('frontend.partials.nav', ['navMenus' => $navMenus])

    <!-- Mobile Menu -->
    @include('frontend.partials.mobile-menu', [
        'navMenus' => $navMenus ?? collect(),
        'extraLinks' => [['title' => 'Transparansi Anggaran', 'url' => route('budget.transparency.page')]]
    ])

    <main class="stats-page">
        <section class="stats-hero hero">
            <div class="stats-hero__copy">
                <div class="breadcrumb">Beranda / Statistik Desa</div>
                <h1>Statistik Kependudukan & Anggaran</h1>
                <p>Dashboard interaktif untuk melihat data penduduk, gender, usia, dan anggaran desa secara lengkap.</p>
                <div class="meta-pills">
                    <span class="pill pill-primary"><i class="fa-regular fa-calendar"></i> Tahun {{ $budgetYear }}</span>
                    <span class="pill"><i class="fa-solid fa-layer-group"></i> {{ $displayCategory }}</span>
                    <span class="pill"><i class="fa-regular fa-compass"></i> Dusun: {{ $selectedDusun ? ($dusunOptions->firstWhere('id', $selectedDusun)->nama ?? 'Dipilih') : 'Semua' }}</span>
                </div>
            </div>
            <div class="stats-hero__cards">
                <div class="summary-card">
                    <p class="summary-label">Total Penduduk</p>
                    <h3>{{ number_format((int) data_get($populationStats, 'total', 0), 0, ',', '.') }}</h3>
                    <p class="summary-note">Laki-laki {{ data_get($populationStats, 'male_percent', 0) }}% | Perempuan {{ data_get($populationStats, 'female_percent', 0) }}%</p>
                </div>
                <div class="summary-card">
                    <p class="summary-label">Total Anggaran</p>
                    <h3>Rp {{ number_format((float) data_get($budgetTotals, 'anggaran', 0), 0, ',', '.') }}</h3>
                    <p class="summary-note">Realisasi Rp {{ number_format((float) data_get($budgetTotals, 'realisasi', 0), 0, ',', '.') }} ({{ data_get($budgetTotals, 'percent', 0) }}%)</p>
                </div>
            </div>
        </section>

        <section class="filter-bar">
            <form method="GET" action="{{ route('statistics.page') }}" class="filter-form" id="statsFilterForm">
                <div class="filter-group">
                    <label for="filterYear">Tahun</label>
                    <select name="year" id="filterYear" onchange="document.getElementById('statsFilterForm').submit()">
                        @foreach ($budgetYears as $year)
                            <option value="{{ $year }}" @selected((int) $year === (int) $budgetYear)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterDusun">Dusun</label>
                    <select name="dusun" id="filterDusun" onchange="document.getElementById('statsFilterForm').submit()">
                        <option value="">Semua Dusun</option>
                        @foreach ($dusunOptions as $dusun)
                            <option value="{{ $dusun->id }}" @selected($selectedDusun == $dusun->id)>{{ $dusun->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterCategory">Kategori Anggaran</label>
                    <select name="category" id="filterCategory" onchange="document.getElementById('statsFilterForm').submit()">
                        <option value="">Semua</option>
                        @foreach ($categoryOptions as $catKey => $catLabel)
                            <option value="{{ $catKey }}" @selected($selectedCategory === $catKey)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </section>

        <section class="stats-widgets">
            <div class="mini-card">
                <div class="mini-card__icon primary"><i class="fa-solid fa-wallet"></i></div>
                <div class="mini-card__body">
                    <p>Total Anggaran</p>
                    <h3>Rp {{ number_format((float) data_get($budgetTotals, 'anggaran', 0), 0, ',', '.') }}</h3>
                    <span class="badge muted">Tahun {{ $budgetYear }}</span>
                </div>
            </div>
            <div class="mini-card">
                <div class="mini-card__icon success"><i class="fa-solid fa-check-circle"></i></div>
                <div class="mini-card__body">
                    <p>Realisasi</p>
                    <h3>Rp {{ number_format((float) data_get($budgetTotals, 'realisasi', 0), 0, ',', '.') }}</h3>
                    <span class="badge success">{{ data_get($budgetTotals, 'percent', 0) }}% serapan</span>
                </div>
            </div>
            <div class="mini-card">
                <div class="mini-card__icon info"><i class="fa-solid fa-people-group"></i></div>
                <div class="mini-card__body">
                    <p>Total Penduduk</p>
                    <h3>{{ number_format((int) data_get($populationStats, 'total', 0), 0, ',', '.') }}</h3>
                    <span class="badge muted">{{ data_get($populationStats, 'male_percent', 0) }}% L / {{ data_get($populationStats, 'female_percent', 0) }}% P</span>
                </div>
            </div>
            <div class="mini-card">
                <div class="mini-card__icon warning"><i class="fa-solid fa-layer-group"></i></div>
                <div class="mini-card__body">
                    <p>Kategori Aktif</p>
                    <h3>{{ $selectedCategory ? ($categoryOptions[$selectedCategory] ?? 'Kategori') : 'Semua' }}</h3>
                    <span class="badge muted">Filter dusun: {{ $selectedDusun ? ($dusunOptions->firstWhere('id', $selectedDusun)->nama ?? 'Dipilih') : 'Semua' }}</span>
                </div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Distribusi Anggaran</h3>
                    <p>Perbandingan Anggaran vs Realisasi per kategori.</p>
                    <div class="chart-actions">
                        <span class="chip">Bar</span>
                        <span class="chip muted">Per Kategori</span>
                    </div>
                </div>
                <canvas id="budgetBarChart" height="200"></canvas>
            </div>
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Distribusi Gender</h3>
                    <p>Komposisi penduduk berdasarkan gender.</p>
                    <div class="chart-actions">
                        <span class="chip">Pie</span>
                        <span class="chip muted">Penduduk</span>
                    </div>
                </div>
                <canvas id="genderPieChart" height="200"></canvas>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Distribusi Usia</h3>
                    <p>Kelompok usia penduduk</p>
                    <div class="chart-actions">
                        <span class="chip">Bar</span>
                        <span class="chip muted">Usia</span>
                    </div>
                </div>
                <canvas id="ageBarChart" height="200"></canvas>
            </div>
            <div class="stats-panel">
                <div class="stats-panel__header">
                    <h3>Pekerjaan (Top)</h3>
                    <p>Pekerjaan terbanyak</p>
                    <div class="chart-actions">
                        <span class="chip">Horizontal</span>
                        <span class="chip muted">Top 6</span>
                    </div>
                </div>
                <canvas id="jobBarChart" height="200"></canvas>
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
        const statData = @json($statistikData ?? []);
        const population = @json($populationStats ?? []);

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
                        {
                            label: 'Anggaran',
                            data: anggaran,
                            backgroundColor: '#cddcff'
                        },
                        {
                            label: 'Realisasi',
                            data: realisasi,
                            backgroundColor: '#2162e2'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        datalabels: { anchor: 'end', align: 'end', formatter: (v) => v.toLocaleString('id-ID') }
                    },
                    scales: {
                        x: { stacked: false },
                        y: { beginAtZero: true }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderGenderPie() {
            const ctx = document.getElementById('genderPieChart');
            if (!ctx) return;
            const male = Number(population.male || 0);
            const female = Number(population.female || 0);
            const total = male + female;

            if (window.genderPieInst) window.genderPieInst.destroy();
            window.genderPieInst = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [male, female],
                        backgroundColor: ['#2162e2', '#f39c12']
                    }]
                },
                options: {
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: {
                            color: '#fff',
                            formatter: (value) => {
                                const pct = total > 0 ? (value / total * 100).toFixed(1) + '%' : '0%';
                                return pct;
                            }
                        }
                    },
                    cutout: '65%'
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderAgeBar() {
            const ctx = document.getElementById('ageBarChart');
            if (!ctx) return;
            const labels = statData.usia?.labels || [];
            const data = (statData.usia?.data || []).map(Number);

            if (window.ageBarInst) window.ageBarInst.destroy();
            window.ageBarInst = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Penduduk', data, backgroundColor: '#27ae60' }] },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'end' }
                    },
                    scales: { y: { beginAtZero: true } }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderJobBar() {
            const ctx = document.getElementById('jobBarChart');
            if (!ctx) return;
            const labels = (statData.pekerjaan?.labels || []).slice(0, 6);
            const data = (statData.pekerjaan?.data || []).slice(0, 6).map(Number);

            if (window.jobBarInst) window.jobBarInst.destroy();
            window.jobBarInst = new Chart(ctx, {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Penduduk', data, backgroundColor: '#9b59b6' }] },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'right' }
                    },
                    scales: { x: { beginAtZero: true } }
                },
                plugins: [ChartDataLabels]
            });
        }

        renderBudgetBar();
        renderGenderPie();
        renderAgeBar();
        renderJobBar();

        // Animate table progress bars
        (function animateTableBars() {
            const bars = document.querySelectorAll('.table-progress span');
            bars.forEach(bar => {
                const parent = bar.parentElement;
                const target = parent?.getAttribute('data-progress') || '0';
                requestAnimationFrame(() => {
                    bar.style.width = target + '%';
                });
            });
        })();
    </script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>

</html>
