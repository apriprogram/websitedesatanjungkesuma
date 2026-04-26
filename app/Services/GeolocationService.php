<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeolocationService
{
    /**
     * Get geographic location from IP address using ip-api.com
     * 
     * @param string|null $ipAddress
     * @return string|null
     */
    public function getLocationFromIp(?string $ipAddress): ?string
    {
        // If localhost/private IP, try to get the user's actual public IP
        if (!$ipAddress || $this->isPrivateIp($ipAddress)) {
            $publicIp = $this->getPublicIp();
            if ($publicIp && !$this->isPrivateIp($publicIp)) {
                $ipAddress = $publicIp;
            } else {
                // Cannot geolocate private IPs
                return null;
            }
        }

        try {
            // Use ip-api.com (free, no API key required, 45 requests/minute)
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ipAddress}", [
                'fields' => 'status,message,country,regionName,city,district',
                'lang' => 'id', // Indonesian language
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    return $this->formatLocation($data);
                }

                // Log error if API returns failure
                if (isset($data['message'])) {
                    Log::warning("Geolocation API error: {$data['message']} for IP: {$ipAddress}");
                }
            }
        } catch (\Exception $e) {
            // Log exception but don't fail the login process
            Log::warning("Geolocation service error: {$e->getMessage()} for IP: {$ipAddress}");
        }

        return null;
    }

    /**
     * Get the user's public IP address
     * 
     * @return string|null
     */
    private function getPublicIp(): ?string
    {
        try {
            // Use ipify API to get public IP (free, no API key required)
            $response = Http::timeout(2)->get('https://api.ipify.org?format=json');

            if ($response->successful()) {
                $data = $response->json();
                return $data['ip'] ?? null;
            }
        } catch (\Exception $e) {
            Log::debug("Failed to get public IP: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Format location data into readable string
     * 
     * @param array $data
     * @return string
     */
    private function formatLocation(array $data): string
    {
        $parts = [];

        // Add district/city
        if (!empty($data['district'])) {
            $parts[] = $data['district'];
        } elseif (!empty($data['city'])) {
            $parts[] = $data['city'];
        }

        // Add region/province
        if (!empty($data['regionName'])) {
            $parts[] = $data['regionName'];
        }

        // Add country
        if (!empty($data['country'])) {
            $parts[] = $data['country'];
        }

        return !empty($parts) ? implode(', ', $parts) : 'Lokasi tidak tersedia';
    }

    /**
     * Check if IP is private/local
     * 
     * @param string $ip
     * @return bool
     */
    private function isPrivateIp(string $ip): bool
    {
        // Check for localhost
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Check for private IP ranges
        $privateRanges = [
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '169.254.0.0/16', // Link-local
            'fc00::/7', // IPv6 private
            'fe80::/10', // IPv6 link-local
        ];

        foreach ($privateRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     * 
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        [$subnet, $bits] = explode('/', $range);

        // IPv6 check
        if (strpos($ip, ':') !== false) {
            return $this->ipv6InRange($ip, $subnet, (int) $bits);
        }

        // IPv4 check
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);

        return ($ip & $mask) === ($subnet & $mask);
    }

    /**
     * Check if IPv6 is in range
     * 
     * @param string $ip
     * @param string $subnet
     * @param int $bits
     * @return bool
     */
    private function ipv6InRange(string $ip, string $subnet, int $bits): bool
    {
        $ip = inet_pton($ip);
        $subnet = inet_pton($subnet);

        if ($ip === false || $subnet === false) {
            return false;
        }

        $binaryIp = '';
        $binarySubnet = '';

        for ($i = 0; $i < strlen($ip); $i++) {
            $binaryIp .= str_pad(decbin(ord($ip[$i])), 8, '0', STR_PAD_LEFT);
            $binarySubnet .= str_pad(decbin(ord($subnet[$i])), 8, '0', STR_PAD_LEFT);
        }

        return substr($binaryIp, 0, $bits) === substr($binarySubnet, 0, $bits);
    }
}
