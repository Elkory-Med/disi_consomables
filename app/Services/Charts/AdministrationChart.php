<?php

namespace App\Services\Charts;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdministrationChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'administrationChart';
    
    /**
     * This is explicitly for the Par Directions chart
     */
    protected $forParDirectionsChart = true;
    
    /**
     * Use a different cache duration
     */
    protected $cacheDuration = 15; // 15 minutes
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'administration_stats';
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
    {
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
            
            return [
                'labels' => $labels,
                'series' => [$series], // Ensure series is properly nested
                'all_data' => $all_data,
                'data_type' => 'administration_data', // Explicitly mark as administration data
                'is_user_data' => false, // Explicitly mark as NOT user data
                'real_administrations' => $labels,
                'realValues' => true,
                'totalAdmins' => $adminOrders->count(),
                'shownAdmins' => $adminOrders->count(),
                'totalUsers' => User::count()
            ];
        }
        
        // If no administration data, use default departments
        return $this->getDefaultDepartments();
    }
    
    /**
     * Get default departments when no real data is available
     * 
     * @return array
     */
    private function getDefaultDepartments(): array
    {
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
        
        return [
            'labels' => $labels,
            'series' => [$series], // Ensure series is properly nested
            'all_data' => $all_data,
            'data_type' => 'administration_data', // Explicitly mark as administration data
            'is_user_data' => false, // Explicitly mark as NOT user data
            'real_administrations' => $labels,
            'realValues' => false,
            'totalAdmins' => count($defaultDepartments),
            'shownAdmins' => count($defaultDepartments),
            'info' => 'Using default departments - no order data found',
            'totalUsers' => User::count()
        ];
    }
    
    /**
     * Override getErrorData to provide administration-specific error data
     */
    protected function getErrorData(string $errorMessage): array
    {
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
            'chart_id' => $this->chartId,
            'error' => 'Erreur: ' . $errorMessage,
            'totalUsers' => User::count()
        ];
    }
} 