<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login form or redirect authenticated users.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        $isInactive = isset($user) && isset($user->is_active) && !(bool) $user->is_active;

        if (!$user || $isInactive || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi tidak valid, atau akun tidak aktif.']);
        }

        Auth::login($user, (bool) ($credentials['remember'] ?? false));

        $ipAddress = $this->extractIpAddress($request);
        $loginLocation = $this->resolveLoginLocation($request, $ipAddress);

        $user->forceFill([
            'last_login' => now(),
            'last_login_ip' => $ipAddress,
            'last_login_location' => $loginLocation,
        ])->save();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function extractIpAddress(Request $request): ?string
    {
        $forwarded = $request->headers->get('X-Forwarded-For');
        if ($forwarded) {
            $forwardedIp = trim(explode(',', $forwarded)[0]);
            if (filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                return $forwardedIp;
            }
        }

        $realIp = $request->headers->get('X-Real-IP');
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        $clientIp = $request->getClientIp();

        return $clientIp ?: null;
    }

    private function resolveLoginLocation(Request $request, ?string $ipAddress): ?string
    {
        // Try to get location from GeolocationService first
        $geolocationService = app(\App\Services\GeolocationService::class);
        $geoLocation = $geolocationService->getLocationFromIp($ipAddress);

        if ($geoLocation) {
            return $geoLocation;
        }

        // Fallback: Try to get location from HTTP headers (Cloudflare, proxy, etc.)
        $city = $request->headers->get('X-City') ?? $request->headers->get('CF-IPCity') ?? $request->headers->get('X-Geo-City');
        $region = $request->headers->get('X-Region') ?? $request->headers->get('CF-IPRegion') ?? $request->headers->get('X-Geo-Region');
        $country = $request->headers->get('CF-IPCountry') ?? $request->headers->get('X-Country') ?? $request->headers->get('X-AppEngine-Country');
        $latitude = $request->headers->get('X-Geo-Latitude');
        $longitude = $request->headers->get('X-Geo-Longitude');

        $parts = array_filter([
            $city ? trim($city) : null,
            $region ? trim($region) : null,
            $country ? trim($country) : null,
        ], static fn($value) => !empty($value));

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        if ($latitude && $longitude) {
            return trim($latitude) . ', ' . trim($longitude);
        }

        if ($ipAddress) {
            return 'IP: ' . $ipAddress;
        }

        return 'Lokasi tidak tersedia';
    }
}
