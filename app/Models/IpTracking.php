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

        // Try multiple geolocation services for better accuracy (ordered by reliability)
        $services = [
            'ipapi' => [
                'url' => "https://ipapi.co/{$ip}/json/",
                'priority' => 1
            ],
            'ip-api' => [
                'url' => "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,timezone,query",
                'priority' => 2
            ],
            'ipinfo' => [
                'url' => "https://ipinfo.io/{$ip}/json",
                'priority' => 3
            ],
            'ipgeolocation' => [
                'url' => "https://api.ipgeolocation.io/ipgeo?apiKey=free&ip={$ip}",
                'priority' => 4
            ],
            'geojs' => [
                'url' => "https://get.geojs.io/v1/ip/geo/{$ip}.json",
                'priority' => 5
            ],
        ];

        $bestResult = null;
        $maxAccuracy = 0;
        $results = [];

        // Try all services in parallel for faster response
        foreach ($services as $service => $config) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5, // Increased timeout for better reliability
                        'method' => 'GET',
                        'header' => 'User-Agent: IT-Inventory-System/1.0',
                        'ignore_errors' => true
                    ]
                ]);
                
                $response = @file_get_contents($config['url'], false, $context);
                
                if ($response === false) continue;
                
                $data = json_decode($response, true);
                
                if ($data && !isset($data['error']) && !isset($data['message'])) {
                    $result = self::parseLocationData($data, $service);
                    if ($result && isset($result['latitude']) && isset($result['longitude'])) {
                        $results[] = $result;
                        // Use the result with highest accuracy
                        if ($result['accuracy'] > $maxAccuracy) {
                            $bestResult = $result;
                            $maxAccuracy = $result['accuracy'];
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("Geolocation service {$service} failed for IP {$ip}: " . $e->getMessage());
                continue;
            }
        }

        // If we have multiple results, calculate average for better accuracy
        if (count($results) > 1) {
            $validResults = array_filter($results, function($r) {
                return isset($r['latitude']) && isset($r['longitude']) && 
                       $r['latitude'] != 0 && $r['longitude'] != 0;
            });
            
            if (count($validResults) > 1) {
                $avgLat = array_sum(array_column($validResults, 'latitude')) / count($validResults);
                $avgLng = array_sum(array_column($validResults, 'longitude')) / count($validResults);
                
                // Use the most common city/country
                $cities = array_filter(array_column($validResults, 'city'));
                $countries = array_filter(array_column($validResults, 'country'));
                
                $mostCommonCity = null;
                if (!empty($cities)) {
                    $cityCounts = array_count_values($cities);
                    $mostCommonCity = array_search(max($cityCounts), $cityCounts);
                }
                
                $mostCommonCountry = null;
                if (!empty($countries)) {
                    $countryCounts = array_count_values($countries);
                    $mostCommonCountry = array_search(max($countryCounts), $countryCounts);
                }
                
                $bestResult = [
                    'latitude' => round($avgLat, 6),
                    'longitude' => round($avgLng, 6),
                    'city' => $mostCommonCity ?: ($bestResult['city'] ?? null),
                    'country' => $mostCommonCountry ?: ($bestResult['country'] ?? null),
                    'region' => $bestResult['region'] ?? null,
                    'timezone' => $bestResult['timezone'] ?? null,
                    'accuracy' => min(95, $maxAccuracy + 5), // Boost accuracy when multiple services agree
                ];
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
                        'country' => $data['country_name'] ?? $data['country'] ?? null,
                        'region' => $data['region'] ?? $data['region_code'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'accuracy' => 88, // High accuracy for ipapi
                    ];
                }
                break;

            case 'ip-api':
                if (isset($data['lat'], $data['lon']) && $data['status'] === 'success') {
                    $result = [
                        'latitude' => (float)$data['lat'],
                        'longitude' => (float)$data['lon'],
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? null,
                        'region' => $data['regionName'] ?? $data['region'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'accuracy' => 90, // Very high accuracy for ip-api
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
                            'accuracy' => 85, // Good accuracy for ipinfo
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
                        'country' => $data['country_name'] ?? $data['country'] ?? null,
                        'region' => $data['state_prov'] ?? $data['region'] ?? null,
                        'timezone' => isset($data['time_zone']) ? ($data['time_zone']['name'] ?? $data['time_zone']) : null,
                        'accuracy' => 92, // Very high accuracy for ipgeolocation
                    ];
                }
                break;

            case 'geojs':
                if (isset($data['latitude'], $data['longitude'])) {
                    $result = [
                        'latitude' => (float)$data['latitude'],
                        'longitude' => (float)$data['longitude'],
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? null,
                        'region' => $data['region'] ?? null,
                        'timezone' => $data['timezone'] ?? null,
                        'accuracy' => 82, // Good accuracy for geojs
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
