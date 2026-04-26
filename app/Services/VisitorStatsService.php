<?php

namespace App\Services;

use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class VisitorStatsService
{
    public function getStats(): array
    {
        // Cache stats for 5 minutes to avoid heavy queries on every page load
        return Cache::remember('visitor_stats', 300, function () {
            $now = Carbon::now();

            return [
                'today' => VisitorLog::whereDate('visited_at', $now->toDateString())->count(),
                'yesterday' => VisitorLog::whereDate('visited_at', $now->copy()->subDay()->toDateString())->count(),
                'this_week' => VisitorLog::whereBetween('visited_at', [
                    $now->copy()->startOfWeek()->toDateTimeString(),
                    $now->copy()->endOfWeek()->toDateTimeString()
                ])->count(),
                'last_week' => VisitorLog::whereBetween('visited_at', [
                    $now->copy()->subWeek()->startOfWeek()->toDateTimeString(),
                    $now->copy()->subWeek()->endOfWeek()->toDateTimeString()
                ])->count(),
                'this_month' => VisitorLog::whereMonth('visited_at', $now->month)
                    ->whereYear('visited_at', $now->year)
                    ->count(),
                'last_month' => VisitorLog::whereMonth('visited_at', $now->copy()->subMonth()->month)
                    ->whereYear('visited_at', $now->copy()->subMonth()->year)
                    ->count(),
                'total' => VisitorLog::count(),
            ];
        });
    }
}
