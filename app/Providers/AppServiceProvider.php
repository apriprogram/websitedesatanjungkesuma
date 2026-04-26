<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 for pagination to match admin.css styles
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Observers
        $this->registerActivityObservers();

        View::composer('admin.*', function ($view) {
            $user = Auth::user();
            $name = $user?->nama ?? 'Admin Desa';
            $email = $user?->email ?? 'admin@tanjungkesuma.id';
            $role = $user?->is_admin ? 'Super Admin' : 'Pengelola Konten';

            $fallbackAvatar = '';
            $avatar = $user?->avatar_url ?? '';

            $initials = collect(preg_split('/\s+/', trim($name)))
                ->filter()
                ->map(fn($part) => Str::upper(Str::substr($part, 0, 1)))
                ->take(2)
                ->implode('');

            $view->with('adminUser', [
                'model' => $user,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'avatar' => $avatar,
                'avatar_fallback' => $fallbackAvatar,
                'initials' => $initials ?: 'AD',
            ]);

            // Global Agenda Widget Data
            $widgetAgendas = \App\Models\Agenda::query()
                ->where('is_completed', false)
                ->orderByRaw('COALESCE(due_date, CURRENT_DATE + INTERVAL 365 DAY)')
                ->limit(10)
                ->get();

            $view->with('widgetAgendas', $widgetAgendas);
            $view->with('adminNotifications', $this->recentActivityFeed());
        });

        // Frontend Visitor Stats
        View::composer('frontend.*', function ($view) {
            $statsService = app(\App\Services\VisitorStatsService::class);
            $view->with('visitorStats', $statsService->getStats());
        });
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function recentActivityFeed(): array
    {
        static $activityCache;

        if ($activityCache !== null) {
            return $activityCache;
        }

        $moduleMap = [
            'dashboard' => ['label' => 'Dashboard', 'icon' => 'fa-chart-line'],
            'admin' => ['label' => 'Pengaturan Umum', 'icon' => 'fa-user-gear'],
            'pegawai' => ['label' => 'Pengaturan Umum', 'icon' => 'fa-id-card-clip'],
            'agenda' => ['label' => 'Agenda Desa', 'icon' => 'fa-calendar-check'],
            'references' => ['label' => 'Referensi', 'icon' => 'fa-database'],
            'penduduk' => ['label' => 'Data Penduduk', 'icon' => 'fa-users'],
            'penduduk_meninggal' => ['label' => 'Data Meninggal', 'icon' => 'fa-heart-crack'],
            'penduduk_pindah' => ['label' => 'Data Pindah', 'icon' => 'fa-route'],
            'keluarga' => ['label' => 'Kartu Keluarga', 'icon' => 'fa-people-roof'],
            'dusun' => ['label' => 'Dusun', 'icon' => 'fa-map-location-dot'],
            'rw' => ['label' => 'RW', 'icon' => 'fa-house-chimney'],
            'rt' => ['label' => 'RT', 'icon' => 'fa-house'],
            'page' => ['label' => 'Halaman', 'icon' => 'fa-file-lines'],
        ];

        try {
            $activityCache = ActivityLog::query()
                ->with('user')
                ->latest()
                ->limit(12)
                ->get()
                ->map(function (ActivityLog $log) use ($moduleMap) {
                    $actionParts = explode('.', (string) $log->action);
                    $moduleKey = $actionParts[0] ?? 'dashboard';

                    if ($moduleKey === 'penduduk' && ($actionParts[1] ?? null) === 'meninggal') {
                        $moduleKey = 'penduduk_meninggal';
                    } elseif ($moduleKey === 'penduduk' && ($actionParts[1] ?? null) === 'pindah') {
                        $moduleKey = 'penduduk_pindah';
                    }

                    $module = $moduleMap[$moduleKey]
                        ?? ['label' => Str::headline($moduleKey), 'icon' => 'fa-bell'];
                    $actionLabel = Str::headline(str_replace('.', ' ', $log->action ?? 'Aktivitas terbaru'));
                    $title = $log->meta['title'] ?? null;
                    $message = $log->description
                        ?: ($title ? "{$title} - {$actionLabel}" : ($actionLabel ?: 'Aktivitas terbaru'));

                    // Detailed Change Handling
                    if (isset($log->meta['detailed_changes']) && is_array($log->meta['detailed_changes'])) {
                        $sections = [];
                        foreach ($log->meta['detailed_changes'] as $change) {
                            $sections[$change['section']][] = $change['label'];
                        }

                        if (!empty($sections)) {
                            $changeSummary = [];
                            foreach ($sections as $section => $fields) {
                                $changeSummary[] = "Bagian {$section}: " . implode(', ', $fields);
                            }
                            // Replace general description with more detailed one if it's an update
                            if ($log->action === 'update' || str_contains($log->action, 'updated')) {
                                $message = implode('. ', $changeSummary);
                            }
                        }
                    }

                    $actor = $log->user?->nama
                        ?? $log->user?->name
                        ?? 'Admin Sistem';


                    return [
                        'id' => $log->id,
                        'module' => $module['label'],
                        'icon' => $module['icon'],
                        'message' => $message,
                        'user' => $actor,
                        'time' => optional($log->created_at)->diffForHumans(),
                        'timestamp' => optional($log->created_at)?->format('d M Y, H:i'),
                    ];
                })
                ->toArray();
        } catch (Throwable) {
            $activityCache = [];
        }

        return $activityCache;
    }

    /**
     * Register the activity observer for relevant models.
     */
    protected function registerActivityObservers(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\Penduduk::class,
            \App\Models\Keluarga::class,
            \App\Models\PendudukPindah::class,
            \App\Models\PendudukMeninggal::class,
            \App\Models\News::class,
            \App\Models\Agenda::class,
            \App\Models\Announcement::class,
            \App\Models\BudgetItem::class,
            \App\Models\VillageDocument::class,
            \App\Models\SekilasInfo::class,
            \App\Models\SocialLink::class,
            \App\Models\YoutubeVideo::class,
            \App\Models\Dusun::class,
            \App\Models\Rw::class,
            \App\Models\Rt::class,
            \App\Models\Pegawai::class,
            \App\Models\Page::class,
            \App\Models\PublicInfoSetting::class,
            \App\Models\DashboardStatistic::class,
        ];

        foreach ($models as $model) {
            if (class_exists($model) && method_exists($model, 'observe')) {
                $model::observe(\App\Observers\ActivityObserver::class);
            }
        }
    }
}
