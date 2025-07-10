<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardStatsService
{
    /**
     * Get all dashboard statistics with caching
     *
     * @param bool $bypassCache
     * @return array
     */
    public function getAllStats($bypassCache = false)
    {
        // Cache key that includes date to invalidate at midnight
        $cacheKey = 'dashboard_data_' . date('Y-m-d');
        
        // If bypassing cache, clear all related caches
        if ($bypassCache) {
            \Log::info('Bypassing cache for dashboard data');
            Cache::forget($cacheKey);
            Cache::forget('user_delivery_stats_' . now()->format('YmdH'));
            Cache::forget('admin_dashboard_data');
            Cache::forget('user_distribution_stats');
            Cache::forget('delivered_products_stats');
            Cache::forget('administration_stats');
            Cache::forget('orders_trend_data');
        }
        
        // If not bypassing cache, try to get from cache
        if (!$bypassCache && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            $data['is_cached'] = true;
            return $data;
        }
        
        // Get order counts by status
        $orderStats = $this->getOrderStats();
        
        // Add total orders to orderStats
        $orderStats['total'] = array_sum([
            (int)($orderStats['pending']['orders'] ?? 0),
            (int)($orderStats['approved']['orders'] ?? 0),
            (int)($orderStats['rejected']['orders'] ?? 0),
            (int)($orderStats['delivered']['orders'] ?? 0)
        ]);
        
        // Get delivered vs not delivered orders
        $deliveredOrdersStats = $this->getDeliveredOrdersStats();
        
        // Extract the delivered and not delivered counts for easy access
        if (isset($deliveredOrdersStats['series']) && 
            isset($deliveredOrdersStats['series'][0]) && 
            count($deliveredOrdersStats['series'][0]) >= 2) {
            $deliveredOrdersStats['delivered'] = (int)$deliveredOrdersStats['series'][0][0];
            $deliveredOrdersStats['notDelivered'] = (int)$deliveredOrdersStats['series'][0][1];
        } else {
            $deliveredOrdersStats['delivered'] = 0;
            $deliveredOrdersStats['notDelivered'] = 0;
        }
        
        // Get top delivered products
        $deliveredProducts = $this->getDeliveredProducts();
        
        // Get user distribution by administration (for Par Directions chart)
        $administrationStats = $this->getUserDistribution();
        
        // CRITICAL FIX: Explicitly mark administrationStats as the Par Directions chart data
        // This is the critical fix to ensure the chart uses the right data
        $administrationStats['for_par_directions_chart'] = true;
        $administrationStats['chart_id'] = 'parDirectionsChart';
        $administrationStats['totalUsers'] = User::count();
        
        // Get order trends
        $orderTrends = $this->getOrderTrends();
        
        // Get revenue information
        $revenue = $this->getRevenueStats();
        
        // Get user delivery statistics
        $userDeliveryStats = $this->getUserDeliveryStats($bypassCache);
        
        // CRITICAL FIX: Explicitly mark userDeliveryStats as NOT for Par Directions chart
        $userDeliveryStats['for_par_directions_chart'] = false;
        $userDeliveryStats['chart_id'] = 'userDeliveryChart'; // Different chart ID
        
        // Prepare response data
        $data = [
            'orderStats' => $orderStats,
            'deliveredOrdersStats' => $deliveredOrdersStats,
            'deliveredProducts' => $deliveredProducts,
            'administrationStats' => $administrationStats, // Primary name is administrationStats
            'userDistribution' => $administrationStats,    // Alias for backward compatibility
            'orderTrends' => $orderTrends,
            'revenue' => $revenue,
            'userDeliveryStats' => $userDeliveryStats,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'is_cached' => false
        ];
        
        // Store in cache for 30 minutes
        Cache::put($cacheKey, $data, now()->addMinutes(30));
        
        return $data;
    }

    /**
     * Get order stats by status
     *
     * @return array
     */
    public function getOrderStats()
    {
        try {
            // Use string literals to ensure consistency
            $pending = Order::STATUS_PENDING;
            $approved = Order::STATUS_APPROVED;
            $rejected = Order::STATUS_REJECTED;
            $delivered = Order::STATUS_DELIVERED;
            
            $counts = DB::table('orders')
                ->select(
                    DB::raw("COUNT(CASE WHEN status = '{$pending}' THEN 1 END) as pending_count"),
                    DB::raw("COUNT(CASE WHEN status = '{$approved}' THEN 1 END) as approved_count"),
                    DB::raw("COUNT(CASE WHEN status = '{$rejected}' THEN 1 END) as rejected_count"),
                    DB::raw("COUNT(CASE WHEN status = '{$delivered}' THEN 1 END) as delivered_count")
                )
                ->first();
            
            // Convert to integers to avoid type issues
            $pendingCount = $counts ? (int)$counts->pending_count : 0;
            $approvedCount = $counts ? (int)$counts->approved_count : 0;
            $rejectedCount = $counts ? (int)$counts->rejected_count : 0;
            $deliveredCount = $counts ? (int)$counts->delivered_count : 0;
            
            return [
                'labels' => ['En attente', 'Approuvée', 'Rejetée', 'Livrée'],
                'series' => [[
                    $pendingCount, 
                    $approvedCount, 
                    $rejectedCount,
                    $deliveredCount
                ]],
                'pending' => ['orders' => $pendingCount],
                'approved' => ['orders' => $approvedCount],
                'rejected' => ['orders' => $rejectedCount],
                'delivered' => ['orders' => $deliveredCount]
            ];
        } catch (\Exception $e) {
            // Return fallback data
            return [
                'labels' => ['En attente', 'Approuvée', 'Rejetée', 'Livrée'],
                'series' => [[0, 0, 0, 0]],
                'pending' => ['orders' => 0],
                'approved' => ['orders' => 0],
                'rejected' => ['orders' => 0],
                'delivered' => ['orders' => 0]
            ];
        }
    }
    
    /**
     * Get delivered vs undelivered orders stats
     * 
     * @return array
     */
    public function getDeliveredOrdersStats()
    {
        try {
            // Use a variable for the delivered constant to avoid any issues
            $deliveredStatus = Order::STATUS_DELIVERED;
            
            $counts = DB::table('orders')
                ->select(
                    DB::raw("COUNT(CASE WHEN delivered = 1 OR status = '{$deliveredStatus}' THEN 1 END) as delivered_count"),
                    DB::raw("COUNT(CASE WHEN (delivered = 0 OR delivered IS NULL) AND status != '{$deliveredStatus}' THEN 1 END) as not_delivered_count")
                )
                ->first();
            
            $deliveredCount = $counts ? (int)$counts->delivered_count : 0;
            $notDeliveredCount = $counts ? (int)$counts->not_delivered_count : 0;
            
            return [
                'labels' => ['Livrée', 'Non livrée'],
                'series' => [[
                    $deliveredCount, 
                    $notDeliveredCount
                ]]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => ['Livrée', 'Non livrée'],
                'series' => [[0, 0]]
            ];
        }
    }
    
    /**
     * Get top delivered products
     * 
     * @return array
     */
    public function getDeliveredProducts()
    {
        try {
            // Use a variable for the constant
            $deliveredStatus = Order::STATUS_DELIVERED;
            
            $products = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_quantity')
                )
                ->where(function($query) use ($deliveredStatus) {
                    $query->where('orders.delivered', 1)
                          ->orWhere('orders.status', $deliveredStatus);
                })
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->limit(15)
                ->get();
            
            if ($products->isNotEmpty()) {
                $labels = $products->pluck('name')->toArray();
                // Convert quantities to integers to avoid any type conversion issues
                $series = array_map('intval', $products->pluck('total_quantity')->toArray());
                
                return [
                    'labels' => $labels,
                    'series' => [$series]
                ];
            }
            
            return [
                'labels' => [],
                'series' => [[]]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'series' => [[]]
            ];
        }
    }
    
    /**
     * Get user distribution by administration
     * 
     * @return array
     */
    public function getUserDistribution()
    {
        try {
            // Use the cache if available
            $cacheKey = 'administration_stats';
            if (Cache::has($cacheKey)) {
                $result = Cache::get($cacheKey);
                \Log::info('Using cached administration stats');
                return $result;
            }
            
            // Direct query to count orders by administration
            $adminOrders = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    DB::raw('TRIM(COALESCE(users.administration, "Non spécifié")) as administration'),
                    DB::raw('COUNT(orders.id) as order_count')
                )
                ->where('orders.delivered', true) // Only include delivered orders
                ->whereNotNull('users.administration')
                ->where('users.administration', '!=', '')
                // Filter out administration names that look like user IDs or matricules
                ->whereRaw('users.administration NOT REGEXP "^[0-9]{3,}$"') // Not just digits
                ->whereRaw('users.administration NOT REGEXP "^[A-Z][0-9]+$"') // Not like A12345
                ->whereRaw('users.administration NOT LIKE "%utilisateur%"') // Not containing utilisateur
                ->groupBy('administration')
                ->orderByDesc('order_count')
                ->limit(50)
                ->get();
            
            \Log::info('Found ' . $adminOrders->count() . ' administrations with orders');
            
            if ($adminOrders->count() > 0) {
                // Extract administration names and order counts
                $labels = $adminOrders->pluck('administration')->toArray();
                $series = $adminOrders->pluck('order_count')->map(function($count) {
                    return (int) $count;
                })->toArray();
                
                // Create all_data array for pagination
                $all_data = [];
                foreach ($adminOrders as $item) {
                    $all_data[] = [
                        'administration' => $item->administration,
                        'delivered_orders' => (int) $item->order_count
                    ];
                }
                
                $result = [
                    'labels' => $labels,
                    'series' => [$series], // Ensure series is properly nested
                    'all_data' => $all_data,
                    'data_type' => 'administration_data', // Explicitly mark as administration data
                    'is_user_data' => false, // Explicitly mark as NOT user data
                    'real_administrations' => $labels,
                    'realValues' => true,
                    'totalAdmins' => $adminOrders->count(),
                    'shownAdmins' => $adminOrders->count()
                ];
                
                // Cache the result for 15 minutes
                Cache::put($cacheKey, $result, now()->addMinutes(15));
                
                return $result;
            }
            
            // If no administration data, try to get predefined administration departments
            $defaultDepartments = [
                'Direction Générale',
                'Direction Financière',
                'Direction Technique',
                'Direction des RH',
                'Direction Commerciale'
            ];
            
            $labels = $defaultDepartments;
            $series = array_fill(0, count($defaultDepartments), 0);
            
            // Create all_data array
            $all_data = [];
            foreach ($defaultDepartments as $admin) {
                $all_data[] = [
                    'administration' => $admin,
                    'delivered_orders' => 0
                ];
            }
            
            $result = [
                'labels' => $labels,
                'series' => [$series], // Ensure series is properly nested
                'all_data' => $all_data,
                'data_type' => 'administration_data', // Explicitly mark as administration data
                'is_user_data' => false, // Explicitly mark as NOT user data
                'real_administrations' => $labels,
                'realValues' => false,
                'totalAdmins' => count($defaultDepartments),
                'shownAdmins' => count($defaultDepartments),
                'info' => 'Using default departments - no order data found'
            ];
            
            // Cache the result for 15 minutes
            Cache::put($cacheKey, $result, now()->addMinutes(15));
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getUserDistribution: ' . $e->getMessage());
            
            // Even on error, return explicitly marked administration data
            return [
                'labels' => ['Aucune administration'],
                'series' => [[0]], // Ensure series is properly nested
                'all_data' => [
                    ['administration' => 'Aucune administration', 'delivered_orders' => 0]
                ],
                'data_type' => 'administration_data',
                'is_user_data' => false,
                'real_administrations' => [],
                'realValues' => false,
                'error' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get order trends over time
     * 
     * @return array
     */
    public function getOrderTrends()
    {
        try {
            // Get the last 7 days
            $dates = [];
            $labels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dates[] = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
            }
            
            // Get orders by day
            $orders = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereIn(DB::raw('DATE(created_at)'), $dates)
                ->groupBy('date')
                ->get()
                ->keyBy('date');
            
            // Prepare series data
            $orderCounts = [];
            foreach ($dates as $date) {
                $orderCounts[] = isset($orders[$date]) ? (int)$orders[$date]->count : 0;
            }
            
            return [
                'labels' => $labels,
                'series' => [$orderCounts]
            ];
        } catch (\Exception $e) {
            return [
                'labels' => [],
                'series' => [[]]
            ];
        }
    }
    
    /**
     * Get basic revenue stats
     * 
     * @return array
     */
    public function getRevenueStats()
    {
        return [
            'totalOrders' => Order::count(),
            'deliveredOrders' => Order::where('delivered', true)->count(),
            'pendingDelivery' => Order::where('status', Order::STATUS_APPROVED)
                                    ->where(function($query) {
                                        $query->where('delivered', false)
                                            ->orWhereNull('delivered');
                                    })->count()
        ];
    }

    /**
     * Get delivery statistics grouped by user/matricule
     * 
     * @param bool $bypassCache
     * @return array
     */
    public function getUserDeliveryStats($bypassCache = false)
    {
        try {
            // Cache key with hourly invalidation
            $cacheKey = 'user_delivery_stats_' . now()->format('YmdH');
            
            // Try to get data from cache first for performance
            if (!$bypassCache && Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            // Query to get users with their delivered order counts
            $users = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.matricule',
                    DB::raw('COUNT(orders.id) as delivered_count')
                )
                ->where(function($query) {
                    // Check both delivered boolean flag AND status column
                    $query->where('orders.delivered', true)
                          ->orWhere('orders.status', Order::STATUS_DELIVERED);
                })
                ->groupBy('users.id', 'users.name', 'users.matricule')
                ->orderByDesc('delivered_count')
                ->get();
            
            if ($users->isEmpty()) {
                // Return well-structured empty data
                return [
                    'labels' => ['Aucun utilisateur'],
                    'series' => [[0]], // Nested array format for ApexCharts
                    'data_type' => 'user_data',
                    'is_user_data' => true,
                    'empty_state' => true,
                    'all_data' => [
                        [
                            'user' => 'Aucun utilisateur',
                            'delivered_orders' => 0,
                            'user_id' => null,
                            'matricule' => null,
                            'name' => 'Aucun utilisateur'
                        ]
                    ]
                ];
            }
            
            // Extract names/matricules and counts
            $labels = $users->map(function($user) {
                return $user->matricule 
                    ? $user->name . ' (' . $user->matricule . ')'
                    : $user->name;
            })->toArray();
            
            // Convert counts to integers to avoid any type issues
            $counts = $users->pluck('delivered_count')
                ->map(function($count) {
                    return (int)$count;
                })
                ->toArray();
            
            // Create all_data array for detailed information
            $all_data = [];
            foreach ($users as $index => $user) {
                $all_data[] = [
                    'user' => $labels[$index],
                    'delivered_orders' => (int) $user->delivered_count,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'matricule' => $user->matricule
                ];
            }
            
            // Ensure we have complete and valid data structure
            $result = [
                'labels' => $labels,
                'series' => [$counts], // Nested array format for ApexCharts
                'data_type' => 'user_data',
                'is_user_data' => true,
                'all_data' => $all_data,
                'total_users' => count($users),
                'total_deliveries' => array_sum($counts)
            ];
            
            // Cache the results for 15 minutes
            Cache::put($cacheKey, $result, now()->addMinutes(15));
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Error getting user delivery stats: ' . $e->getMessage());
            return [
                'labels' => ['Aucun utilisateur'],
                'series' => [[0]], // Nested array format for ApexCharts
                'data_type' => 'user_data',
                'is_user_data' => true,
                'error' => $e->getMessage(),
                'all_data' => [
                    [
                        'user' => 'Aucun utilisateur',
                        'delivered_orders' => 0,
                        'user_id' => null,
                        'matricule' => null,
                        'name' => 'Aucun utilisateur'
                    ]
                ]
            ];
        }
    }
} 