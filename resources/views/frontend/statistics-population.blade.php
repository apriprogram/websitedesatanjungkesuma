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

    $selectedDusun = $selectedDusun ?: '';
    $dusunOptions = $dusunOptions ?? collect();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik Penduduk - Tanjung Kesuma</title>
    <link rel="icon" href="{{ asset('img/Logo/logo_lampung_timur.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/search-fix.css') }}?v={{ time() }}">
    <link rel="stylesheet" href="{{ asset('assets/css/statistics-revamp.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome/all.min.css') }}">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
            /* Prevent horizontal scroll from 100vw */
        }

        .page-hero {
            background: #1e4fb3;
            color: #fff;
            width: 100vw;
            min-height: auto;
            margin-left: calc(50% - 50vw);
            padding: 110px 0 100px;
            position: relative;
            display: flex;
            align-items: flex-start;
        }

        .page-hero .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
            text-align: left;
            width: 100%;
        }

        .page-hero .breadcrumb {
            color: #c8d7ff;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .page-hero .breadcrumb a {
            color: #c8d7ff;
            text-decoration: none;
        }

        .page-hero .page-title {
            font-size: 2.8rem;
            /* Balanced size */
            margin: 0 0 15px;
            line-height: 1.2;
            font-weight: 600;
        }

                @media (max-width: 768px) {
            /* Resize Hero Title */
            
            .page-hero {
                padding: 85px 0 90px;
            }
            .page-hero h1 {
                font-size: 1.5rem !important;
                margin-bottom: 10px;
                line-height: 1.3;
            }

            /* Resize Breadcrumb */
            .page-hero .breadcrumb {
                font-size: 0.75rem !important;
                margin-bottom: 20px;
            }

            .page-hero span {
                font-size: 0.75rem !important;
            }
        }

        .page-hero .page-meta {
            font-size: 1.1rem;
            color: #c8d7ff;
            margin-top: 15px;
            display: flex;
            gap: 25px;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Adjustments for stats page content */
        .stats-content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px 40px;
            margin-top: -75px;
            /* Adjust overlap */
            position: relative;
            z-index: 10;
        }

        .stats-page {
            width: 100%;
            padding: 0;
        }
    </style>
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
            <li><a href="{{ route('budget.transparency.page') }}">Transparansi Anggaran</a></li>
        </ul>
    </div>

    <main class="stats-page">
        <!-- Standardized Hero Section -->
        <section class="page-hero">
            <div class="container">
                <div class="breadcrumb">
                    <a href="{{ url('/') }}">Beranda</a> / <span>Statistik Penduduk</span>
                </div>
                <h1 class="page-title">Statistik Penduduk</h1>
                <div class="page-meta">
                    <span>
                        <i class="fa-regular fa-compass"></i> Dusun:
                        {{ $selectedDusun ? ($dusunOptions->firstWhere('id', $selectedDusun)->nama ?? 'Dipilih') : 'Semua' }}
                    </span>
                    <span>
                        <i class="fa-solid fa-users"></i> Total Penduduk:
                        {{ number_format((int) data_get($populationStats, 'total', 0), 0, ',', '.') }} Jiwa
                    </span>
                </div>
            </div>
        </section>

        <!-- Main Content Wrapper -->
        <div class="stats-content-wrapper">

            <!-- Summary Cards (Moved to top) -->
            <section class="summary-grid">
                <div class="summary-tile">
                    <div class="summary-icon male">
                        <i class="fa-solid fa-mars"></i>
                    </div>
                    <div class="summary-content">
                        <div>
                            <h4>Laki-laki</h4>
                            <p class="count">
                                {{ number_format((int) data_get($populationStats, 'male', 0), 0, ',', '.') }}
                                <span style="font-size: 0.5em; color: #666;">Jiwa</span>
                            </p>
                        </div>
                        <p class="note"><i class="fa-solid fa-arrow-up"></i>
                            {{ data_get($populationStats, 'male_percent', 0) }}%</p>
                    </div>
                </div>
                <div class="summary-tile">
                    <div class="summary-icon female">
                        <i class="fa-solid fa-venus"></i>
                    </div>
                    <div class="summary-content">
                        <div>
                            <h4>Perempuan</h4>
                            <p class="count">
                                {{ number_format((int) data_get($populationStats, 'female', 0), 0, ',', '.') }}
                                <span style="font-size: 0.5em; color: #666;">Jiwa</span>
                            </p>
                        </div>
                        <p class="note"><i class="fa-solid fa-arrow-up"></i>
                            {{ data_get($populationStats, 'female_percent', 0) }}%</p>
                    </div>
                </div>
            </section>

            <!-- Filter Bar -->
            <section class="filter-bar">
                <form method="GET" action="{{ route('statistics.population.page') }}" class="filter-form"
                    id="popFilterForm">
                    <div class="filter-group">
                        <label for="filterDusunPop"><i class="fa-solid fa-filter"></i> Filter Data:</label>
                        <select name="dusun" id="filterDusunPop"
                            onchange="document.getElementById('popFilterForm').submit()">
                            <option value="">Semua Dusun</option>
                            @foreach ($dusunOptions as $dusun)
                                <option value="{{ $dusun->id }}" @selected($selectedDusun == $dusun->id)>{{ $dusun->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </section>

            <!-- Charts Grid -->
            <section class="stats-grid">
                <!-- Gender Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-venus-mars"></i> Gender</h3>
                        <button class="menu-trigger" data-canvas="genderPieChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('genderPieChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('genderPieChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <!-- Canvas container for responsive aspect ratio -->
                    <div style="position: relative; height: 300px;">
                        <canvas id="genderPieChart"></canvas>
                    </div>
                </div>

                <!-- Age Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-chart-column"></i> Usia</h3>
                        <button class="menu-trigger" data-canvas="ageBarChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('ageBarChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('ageBarChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="ageBarChart"></canvas>
                    </div>
                </div>

                <!-- Job Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-briefcase"></i> Pekerjaan (Top 6)</h3>
                        <button class="menu-trigger" data-canvas="jobBarChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('jobBarChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('jobBarChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="jobBarChart"></canvas>
                    </div>
                </div>

                <!-- Education Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-graduation-cap"></i> Pendidikan</h3>
                        <button class="menu-trigger" data-canvas="eduBarChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('eduBarChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('eduBarChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="eduBarChart"></canvas>
                    </div>
                </div>

                <!-- Religion Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-hands-praying"></i> Agama</h3>
                        <button class="menu-trigger" data-canvas="religionPieChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('religionPieChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('religionPieChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="religionPieChart"></canvas>
                    </div>
                </div>

                <!-- Blood Type Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-heart-pulse"></i> Golongan Darah</h3>
                        <button class="menu-trigger" data-canvas="bloodPieChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('bloodPieChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('bloodPieChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="bloodPieChart"></canvas>
                    </div>
                </div>

                <!-- Marriage Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-ring"></i> Status Kawin</h3>
                        <button class="menu-trigger" data-canvas="marriagePieChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('marriagePieChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('marriagePieChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="marriagePieChart"></canvas>
                    </div>
                </div>

                <!-- Wilayah Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-map-location-dot"></i> Wilayah</h3>
                        <button class="menu-trigger" data-canvas="wilayahBarChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('wilayahBarChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('wilayahBarChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="wilayahBarChart"></canvas>
                    </div>
                </div>

                <!-- Ethnic Chart -->
                <div class="stats-card">
                    <div class="stats-card__header">
                        <h3><i class="fa-solid fa-people-arrows"></i> Suku</h3>
                        <button class="menu-trigger" data-canvas="tribeBarChart"><i
                                class="fa-solid fa-ellipsis"></i></button>
                        <div class="menu-dropdown">
                            <button onclick="exportChart('tribeBarChart','jpg')"><i class="fa-regular fa-image"></i>
                                JPG</button>
                            <button onclick="exportChart('tribeBarChart','png')"><i class="fa-regular fa-image"></i>
                                PNG</button>
                        </div>
                    </div>
                    <div style="position: relative; height: 300px;">
                        <canvas id="tribeBarChart"></canvas>
                    </div>
                </div>
            </section>
        </div> <!-- End stats-content-wrapper -->
    </main>

    @include('frontend.partials.footer', ['socialLinks' => $socialLinks, 'publicInfoSetting' => $publicInfoSetting])

    <script src="{{ asset('assets/vendor/chartjs/chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chartjs/chartjs-plugin-datalabels.min.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
    <script>
        // Set Default Font
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '#6B7280';
        Chart.defaults.scale.grid.color = '#F3F4F6';
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        Chart.defaults.plugins.tooltip.padding = 10;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;

        const statData = @json($statistikData ?? []);
        const population = @json($populationStats ?? []);
        const wilayahData = @json($wilayahData ?? []);

        // Common Colors
        const colors = {
            blue: '#4F46E5',
            green: '#10B981',
            yellow: '#F59E0B',
            red: '#EF4444',
            purple: '#8B5CF6',
            cyan: '#06B6D4',
            pink: '#EC4899',
            slate: '#64748B'
        };

        const palette = Object.values(colors);

        function renderGenderPie() {
            const ctx = document.getElementById('genderPieChart');
            if (!ctx) return;
            const male = Number(population.male || 0);
            const female = Number(population.female || 0);
            const total = male + female;

            if (window.genderPiePop) window.genderPiePop.destroy();
            window.genderPiePop = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Laki-laki', 'Perempuan'],
                    datasets: [{
                        data: [male, female],
                        backgroundColor: [colors.blue, colors.pink],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                        datalabels: {
                            color: '#fff',
                            textAlign: 'center',
                            font: {
                                size: 14,
                                weight: 'normal'
                            },
                            formatter: (value, ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? (value / total * 100).toFixed(0) + '%' : '0%';
                                return value + '\n(' + percentage + ')';
                            }
                        }
                    },
                    cutout: '60%'
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderAgeBar() {
            const ctx = document.getElementById('ageBarChart');
            if (!ctx) return;
            const labels = statData.usia?.labels || [];
            const data = (statData.usia?.data || []).map(Number);

            if (window.ageBarPop) window.ageBarPop.destroy();
            window.ageBarPop = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Penduduk',
                        data,
                        backgroundColor: colors.green,
                        borderRadius: 4,
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false } // Hide labels on bars for clean look
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function renderJobBar() {
            const ctx = document.getElementById('jobBarChart');
            if (!ctx) return;
            // TOP 6 Jobs
            let jobs = (statData.pekerjaan?.labels || []).map((l, i) => ({ label: l, val: statData.pekerjaan?.data[i] || 0 }));
            // Sort Descending? Usually backend already sorts. Assuming sorted.
            const labels = jobs.slice(0, 6).map(j => j.label);
            const data = jobs.slice(0, 6).map(j => Number(j.val));

            if (window.jobBarPop) window.jobBarPop.destroy();
            window.jobBarPop = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Penduduk',
                        data,
                        backgroundColor: colors.purple,
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: {
                            anchor: 'end',
                            align: 'right',
                            formatter: (val) => val,
                            color: colors.slate
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, display: false },
                        y: { grid: { display: false } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderEduBar() {
            const ctx = document.getElementById('eduBarChart');
            if (!ctx) return;
            const labels = statData.pendidikan?.labels || [];
            const data = (statData.pendidikan?.data || []).map(Number);

            if (window.eduBarPop) window.eduBarPop.destroy();
            window.eduBarPop = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Penduduk',
                        data,
                        backgroundColor: colors.cyan,
                        borderRadius: 4,
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 45 } }
                    }
                }
            });
        }

        function renderWilayahBar() {
            const ctx = document.getElementById('wilayahBarChart');
            if (!ctx) return;
            const labels = wilayahData.map(item => item.label);
            const data = wilayahData.map(item => Number(item.total || 0));

            if (window.wilayahBar) window.wilayahBar.destroy();
            window.wilayahBar = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Penduduk',
                        data,
                        backgroundColor: colors.blue,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'right' }
                    },
                    scales: {
                        x: { beginAtZero: true, display: false },
                        y: { grid: { display: false } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderReligionPie() {
            const ctx = document.getElementById('religionPieChart');
            if (!ctx) return;
            const labels = statData.agama?.labels || [];
            const data = (statData.agama?.data || []).map(Number);

            if (window.religionPie) window.religionPie.destroy();
            window.religionPie = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: [colors.blue, colors.green, colors.purple, colors.yellow, colors.red, colors.cyan],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            anchor: 'center',
                            align: 'center',
                            formatter: (value, ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? (value / total * 100) : 0;
                                return percentage > 3 ? percentage.toFixed(0) + '%' : '';
                            }
                        }
                    },
                    cutout: '60%'
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderMarriagePie() {
            const ctx = document.getElementById('marriagePieChart');
            if (!ctx) return;
            const labels = statData.perkawinan?.labels || [];
            const data = (statData.perkawinan?.data || []).map(Number);

            if (window.marriagePie) window.marriagePie.destroy();
            window.marriagePie = new Chart(ctx, {
                type: 'pie', // Changed to Pie for variety
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: [colors.blue, colors.pink, colors.purple, colors.yellow],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                        datalabels: {
                            color: '#fff',
                            formatter: (value, ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? (value / total * 100) : 0;
                                return percentage > 5 ? percentage.toFixed(0) + '%' : '';
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderTribeBar() {
            const ctx = document.getElementById('tribeBarChart');
            if (!ctx) return;
            const labels = statData.suku?.labels || [];
            const data = (statData.suku?.data || []).map(Number);

            if (window.tribeBar) window.tribeBar.destroy();
            window.tribeBar = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Penduduk',
                        data,
                        backgroundColor: colors.green,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'right' }
                    },
                    scales: {
                        x: { beginAtZero: true, display: false },
                        y: { grid: { display: false } }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        function renderBloodPie() {
            const ctx = document.getElementById('bloodPieChart');
            if (!ctx) return;
            const labels = statData.golongan_darah?.labels || [];
            const data = (statData.golongan_darah?.data || []).map(Number);

            if (window.bloodPie) window.bloodPie.destroy();
            window.bloodPie = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: [colors.red, colors.blue, colors.purple, colors.yellow],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true } },
                        datalabels: {
                            color: '#fff',
                            font: {
                                weight: 'bold',
                                size: 14
                            },
                            anchor: 'center',
                            align: 'center',
                            formatter: (value, ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? (value / total * 100) : 0;
                                return percentage > 3 ? percentage.toFixed(0) + '%' : '';
                            }
                        }
                    },
                    cutout: '60%'
                },
                plugins: [ChartDataLabels]
            });
        }

        // Initialize All Charts
        renderGenderPie();
        renderAgeBar();
        renderJobBar();
        renderEduBar();
        renderReligionPie();
        renderBloodPie();
        renderWilayahBar();
        renderMarriagePie();
        renderTribeBar();

        function exportChart(canvasId, type = 'jpg') {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            // Find the title - traverse up to .stats-card then find h3
            const card = canvas.closest('.stats-card');
            let titleText = 'Statistik';
            if (card) {
                const titleEl = card.querySelector('h3');
                if (titleEl) {
                    titleText = titleEl.innerText.trim();
                }
            }

            // Create a temporary canvas with extra space for title
            const padding = 20;
            const titleHeight = 40;
            const tempCanvas = document.createElement('canvas');
            const tempCtx = tempCanvas.getContext('2d');

            tempCanvas.width = canvas.width + (padding * 2);
            tempCanvas.height = canvas.height + titleHeight + (padding * 2);

            // Fill white background ONLY for JPG (PNG keeps transparency)
            if (type === 'jpg' || type === 'jpeg') {
                tempCtx.fillStyle = '#ffffff';
                tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            }

            // Draw Title
            tempCtx.font = 'bold 24px Poppins';
            tempCtx.fillStyle = '#333333';
            tempCtx.textAlign = 'center';
            tempCtx.textBaseline = 'middle';
            tempCtx.fillText(titleText, tempCanvas.width / 2, padding + (titleHeight / 2));

            // Draw original chart over it
            tempCtx.drawImage(canvas, padding, padding + titleHeight);

            const link = document.createElement('a');
            const timestamp = new Date().toISOString().slice(0, 10);
            const filename = `${titleText.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_${timestamp}`;

            if (type === 'jpg' || type === 'jpeg') {
                link.href = tempCanvas.toDataURL('image/jpeg', 1.0);
                link.download = `${filename}.jpg`;
            } else if (type === 'png') {
                link.href = tempCanvas.toDataURL('image/png');
                link.download = `${filename}.png`;
            }
            link.click();
        }

        // Dropdown Menu Logic
        document.querySelectorAll('.menu-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                // Close others
                document.querySelectorAll('.menu-dropdown').forEach(m => {
                    if (m !== btn.nextElementSibling) m.classList.remove('open');
                });
                const menu = btn.nextElementSibling;
                menu.classList.toggle('open');
            });
        });

        document.addEventListener('click', () => {
            document.querySelectorAll('.menu-dropdown').forEach(m => m.classList.remove('open'));
        });
    </script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/accessibility.js') }}"></script>
</body>

</html>
