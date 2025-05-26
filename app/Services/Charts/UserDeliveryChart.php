<?php

namespace App\Services\Charts;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserDeliveryChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'userDeliveryChart';
    
    /**
     * This is NOT for the Par Directions chart
     */
    protected $forParDirectionsChart = false;
    
    /**
     * Use a shorter cache duration for user data
     */
    protected $cacheDuration = 60; // 1 hour
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'user_delivery_stats_' . now()->format('YmdH');
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
    {
        try {
            Log::info('UserDeliveryChart: Fetching data from database');
            
            // Define the status constant for delivered orders
            $deliveredStatus = Order::STATUS_DELIVERED;
            
            // Query to get users with their delivered order counts
            // Enhanced query to make sure we're capturing ALL delivered orders
            $users = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.matricule',
                    DB::raw('COUNT(orders.id) as delivered_count')
                )
                ->where(function($query) use ($deliveredStatus) {
                    // Check both delivered boolean flag AND status column
                    $query->where('orders.delivered', true)
                          ->orWhere('orders.status', $deliveredStatus);
                })
                ->groupBy('users.id', 'users.name', 'users.matricule')
                ->orderByDesc('delivered_count')
                ->get();
            
            Log::info('UserDeliveryChart: Query result count: ' . $users->count());
            
            if ($users->isEmpty()) {
                Log::info('UserDeliveryChart: No delivered orders found');
                
                // Check if there are any delivered orders at all
                $deliveredOrdersCount = DB::table('orders')
                    ->where(function($query) use ($deliveredStatus) {
                        $query->where('orders.delivered', true)
                              ->orWhere('orders.status', $deliveredStatus);
                    })
                    ->count();
                
                Log::info('UserDeliveryChart: Total delivered orders count: ' . $deliveredOrdersCount);
                
                return [
                    'labels' => ['Aucune direction'],
                    'series' => [[0]],
                    'data_type' => 'user_data',
                    'is_user_data' => true,
                    'empty_state' => true,
                    'debug_info' => [
                        'delivered_orders_count' => $deliveredOrdersCount,
                        'timestamp' => now()->format('Y-m-d H:i:s')
                    ]
                ];
            }
            
            // Extract names/matricules and counts
            $labels = $users->map(function($user) {
                return $user->matricule 
                    ? $user->name . ' (' . $user->matricule . ')'
                    : $user->name;
            })->toArray();
            
            $counts = $users->pluck('delivered_count')->toArray();
            
            // Create all_data array for pagination
            $all_data = [];
            foreach ($users as $index => $user) {
                $all_data[] = [
                    'user' => $labels[$index],
                    'delivered_orders' => (int) $user->delivered_count
                ];
            }
            
            Log::info('UserDeliveryChart: Successfully found user data with ' . count($labels) . ' users');
            
            return [
                'labels' => $labels,
                'series' => [$counts],
                'data_type' => 'user_data',
                'is_user_data' => true,
                'total_users' => count($users),
                'all_data' => $all_data,
                'debug_info' => [
                    'user_count' => count($users),
                    'timestamp' => now()->format('Y-m-d H:i:s')
                ]
            ];
        } catch (\Exception $e) {
            Log::error('UserDeliveryChart: Error in fetchData: ' . $e->getMessage());
            
            return [
                'labels' => ['Erreur de chargement'],
                'series' => [[0]],
                'data_type' => 'user_data',
                'is_user_data' => true,
                'empty_state' => true,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Override getErrorData to provide user-specific error data
     */
    protected function getErrorData(string $errorMessage): array
    {
        return [
            'labels' => ['Aucun utilisateur'],
            'series' => [[0]],
            'data_type' => 'user_data',
            'is_user_data' => true,
            'chart_id' => $this->chartId,
            'error' => 'Erreur: ' . $errorMessage
        ];
    }
} 