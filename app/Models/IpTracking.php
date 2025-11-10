<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IpTracking extends Model
{
    protected $table = 'ip_tracking';
    
    protected $fillable = [
        'ip_address',
        'email',
        'user_agent',
        'action_type',
        'success',
        'latitude',
        'longitude',
        'city',
        'country',
        'region',
        'timezone',
        'accuracy',
        'last_seen'
    ];

    protected $casts = [
        'success' => 'boolean',
        'last_seen' => 'datetime',
    ];

    /**
     * Get location data for an IP address using multiple services for accuracy
     */
    public static function getLocationData($ip)
    {
        // Skip geolocation for local/private IPs
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [
                'latitude' => null,
                'longitude' => null,
                'city' => 'Local Network',
                'country' => 'Local',
                'region' => null,
                'timezone' => null,
                'accuracy' => 'local',
            ];
        }

        // Try multiple geolocation services for better accuracy
        $services = [
            'ipapi' => "https://ipapi.co/{$ip}/json/",
            'ipinfo' => "https://ipinfo.io/{$ip}/json",
            'ipgeolocation' => "https://api.ipgeolocation.io/ipgeo?apiKey=free&ip={$ip}",
        ];

        $bestResult = null;
        $maxAccuracy = 0;

        foreach ($services as $service => $url) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 3, // 3 second timeout per service
                        'method' => 'GET',
                        'header' => 'User-Agent: IT-Inventory-System/1.0'
                    ]
                ]);
                
                $response = file_get_contents($url, false, $context);
                
                if ($response === false) continue;
                
                $data = json_decode($response, true);
                
                if ($data && !isset($data['error'])) {
                    $result = self::parseLocationData($data, $service);
                    if ($result && $result['accuracy'] > $maxAccuracy) {
                        $bestResult = $result;
                        $maxAccuracy = $result['accuracy'];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Geolocation service {$service} failed for IP {$ip}: " . $e->getMessage());
                continue;
            }
        }

        return $bestResult ?: [
            'latitude' => null,
            'longitude' => null,
            'city' => null,
            'country' => null,
            'region' => null,
            'timezone' => null,
            'accuracy' => 'unknown',
        ];
    }

    /**
     * Parse location data from different geolocation services
     */
    private static function parseLocationData($data, $service)
    {
        $result = [
            'latitude' => null,
            'longitude' => null,
            'city' => null,
            'country' => null,
            'region' => null,
            'timezone' => null,
            'accuracy' => 0,
        ];

        switch ($service) {
            case 'ipapi':
                if (isset($data['latitude'], $data['longitude'])) {
                    $result = [
                        'latitude' => (float)$data['latitude'],
                        'longitude' => (float)$data['longitude'],
                        'city' => $data['city'] ?? null,
                        'country' => $data['country_name'] ?? null,
                        'region' => $data['region'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'accuracy' => 85, // High accuracy for ipapi
                    ];
                }
                break;

            case 'ipinfo':
                if (isset($data['loc'])) {
                    $coords = explode(',', $data['loc']);
                    if (count($coords) === 2) {
                        $result = [
                            'latitude' => (float)$coords[0],
                            'longitude' => (float)$coords[1],
                            'city' => $data['city'] ?? null,
                            'country' => $data['country'] ?? null,
                            'region' => $data['region'] ?? null,
                            'timezone' => $data['timezone'] ?? null,
                            'accuracy' => 80, // Good accuracy for ipinfo
                        ];
                    }
                }
                break;

            case 'ipgeolocation':
                if (isset($data['latitude'], $data['longitude'])) {
                    $result = [
                        'latitude' => (float)$data['latitude'],
                        'longitude' => (float)$data['longitude'],
                        'city' => $data['city'] ?? null,
                        'country' => $data['country_name'] ?? null,
                        'region' => $data['state_prov'] ?? null,
                        'timezone' => $data['time_zone']['name'] ?? null,
                        'accuracy' => 90, // Very high accuracy for ipgeolocation
                    ];
                }
                break;
        }

        return $result;
    }

    /**
     * Log an IP tracking event
     */
    public static function logEvent($ip, $actionType, $email = null, $success = false, $userAgent = null)
    {
        try {
            $locationData = self::getLocationData($ip);
            
            return self::create([
                'ip_address' => $ip,
                'email' => $email,
                'user_agent' => $userAgent,
                'action_type' => $actionType,
                'success' => $success,
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
                'city' => $locationData['city'],
                'country' => $locationData['country'],
                'region' => $locationData['region'],
                'timezone' => $locationData['timezone'],
                'accuracy' => $locationData['accuracy'],
                'last_seen' => now(),
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the login process
            \Log::error('IP tracking failed: ' . $e->getMessage());
            
            // Return a minimal record without location data
            return self::create([
                'ip_address' => $ip,
                'email' => $email,
                'user_agent' => $userAgent,
                'action_type' => $actionType,
                'success' => $success,
                'latitude' => null,
                'longitude' => null,
                'city' => null,
                'country' => null,
                'region' => null,
                'timezone' => null,
                'accuracy' => 'error',
                'last_seen' => now(),
            ]);
        }
    }

    /**
     * Get aggregated IP statistics
     */
    public static function getIpStatistics()
    {
        return self::selectRaw('
            ip_address,
            COUNT(*) as total_visits,
            SUM(CASE WHEN action_type = "login_attempt" THEN 1 ELSE 0 END) as login_attempts,
            SUM(CASE WHEN action_type = "login_success" THEN 1 ELSE 0 END) as login_success,
            MAX(last_seen) as last_seen,
            MAX(latitude) as latitude,
            MAX(longitude) as longitude,
            MAX(city) as city,
            MAX(country) as country,
            MAX(region) as region,
            MAX(timezone) as timezone,
            MAX(accuracy) as accuracy,
            GROUP_CONCAT(DISTINCT email) as emails_used
        ')
        ->groupBy('ip_address')
        ->orderBy('last_seen', 'desc')
        ->get();
    }
}
