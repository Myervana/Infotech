<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IpTracking;
use Carbon\Carbon;

class AdminMetricsController extends Controller
{
    protected function logPath(): string { return storage_path('app/traffic.log'); }

    public function metrics()
    {
        try {
            // Get IP statistics from database
            $ipStats = IpTracking::getIpStatistics();
            
            $ips = [];
            foreach ($ipStats as $stat) {
                // Convert to Philippine timezone
                $lastSeen = $stat->last_seen ? 
                    Carbon::parse($stat->last_seen)->setTimezone('Asia/Manila')->format('M d, Y H:i:s P') : 
                    'Never';
                
                $ips[] = [
                    'ip' => $stat->ip_address,
                    'visits' => (int)$stat->total_visits,
                    'login_attempts' => (int)$stat->login_attempts,
                    'login_success' => (int)$stat->login_success,
                    'last_seen' => $lastSeen,
                    'latitude' => $stat->latitude ? (float)$stat->latitude : null,
                    'longitude' => $stat->longitude ? (float)$stat->longitude : null,
                    'city' => $stat->city,
                    'country' => $stat->country,
                    'region' => $stat->region,
                    'timezone' => $stat->timezone,
                    'accuracy' => $stat->accuracy,
                    'emails_used' => $stat->emails_used ? explode(',', $stat->emails_used) : [],
                ];
            }
            
            return response()->json(['ips' => $ips]);
        } catch (\Exception $e) {
            \Log::error('AdminMetricsController error: ' . $e->getMessage());
            return response()->json(['ips' => [], 'error' => 'Failed to load data'], 500);
        }
    }
}


