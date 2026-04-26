<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\VisitorLog;
use Carbon\Carbon;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Simple tracking: log if this IP/UA hasn't visited in the last hour
        $ip = $request->ip();
        $ua = $request->userAgent();

        $alreadyLogged = VisitorLog::where('ip_address', $ip)
            ->where('user_agent', $ua)
            ->where('visited_at', '>=', Carbon::now()->subHour())
            ->exists();

        if (!$alreadyLogged) {
            VisitorLog::create([
                'ip_address' => $ip,
                'user_agent' => $ua,
                'visited_at' => Carbon::now(),
            ]);
        }

        return $next($request);
    }
}
