<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardStatsService;

/**
 * Admin Dashboard Controller
 * 
 * This controller works with the Livewire-based dashboard implementation (AdminDashboard component).
 * It provides:
 * - The main dashboard view
 * - An API endpoint (/admin/dashboard/data) for AJAX chart updates
 * 
 * Note: This works alongside the Livewire component. For a non-Livewire alternative,
 * see the EnhancedDashboardController which provides similar functionality with
 * a different architecture focused on direct AJAX loading.
 */
class DashboardController extends Controller
{
    protected $dashboardStatsService;
    
    /**
     * Create a new controller instance.
     */
    public function __construct(DashboardStatsService $dashboardStatsService)
    {
        $this->dashboardStatsService = $dashboardStatsService;
    }
    
    /**
     * Show the admin dashboard
     */
    public function index(Request $request)
    {
        // Check if a full refresh is requested
        if ($request->has('fullrefresh')) {
            // Clear all related caches
            \Cache::forget('dashboard_data_' . date('Y-m-d'));
            \Cache::forget('user_delivery_stats_' . now()->format('YmdH'));
            \Cache::forget('admin_dashboard_data');
            \Cache::forget('user_distribution_stats');
            \Cache::forget('delivered_products_stats');
            \Cache::forget('administration_stats');
            \Cache::forget('orders_trend_data');
            
            // Log the cache clearing for debugging
            \Log::info('Full dashboard cache refresh triggered');
        }
        
        // Create empty demo data for the charts in case the Livewire component fails
        // Don't show fictive data when database is empty
        $demoChartData = [
            'orderStats' => [
                'pending' => ['orders' => 0],
                'approved' => ['orders' => 0],
                'rejected' => ['orders' => 0],
                'delivered' => ['orders' => 0]
            ],
            'deliveredProducts' => [
                'labels' => ['Aucun produit'],
                'series' => [[0]]
            ]
        ];
        
        return view('admin.dashboard', ['demoChartData' => json_encode($demoChartData)]);
    }
    
    /**
     * Get dashboard data as JSON for AJAX requests
     */
    public function getDashboardData(Request $request)
    {
        try {
            // Check if cache should be bypassed
            $bypassCache = $request->has('fresh') || $request->has('refresh') || $request->has('nocache');
            
            // Get all dashboard stats (will use cache if available)
            $data = $this->dashboardStatsService->getAllStats($bypassCache);
            
            // CRITICAL FIX: Always ensure administration data and user data are properly tagged
            // Set explicit data types for both data sources to prevent confusion
            
            // Ensure administrationStats is properly marked as administration data
            if (isset($data['administrationStats'])) {
                $data['administrationStats']['data_type'] = 'administration_data';
                $data['administrationStats']['is_user_data'] = false;
                
                // Make sure userDistribution is also set for compatibility
                // Only if it doesn't already exist
                if (!isset($data['userDistribution'])) {
                    $data['userDistribution'] = $data['administrationStats'];
                }
            }
            
            // Ensure userDeliveryStats is always properly marked as user data
            if (isset($data['userDeliveryStats'])) {
                $data['userDeliveryStats']['data_type'] = 'user_data';
                $data['userDeliveryStats']['is_user_data'] = true;
                
                // If the labels are 'Aucun utilisateur', replace with 'Aucune direction'
                if (isset($data['userDeliveryStats']['labels']) && 
                    is_array($data['userDeliveryStats']['labels']) && 
                    count($data['userDeliveryStats']['labels']) === 1 && 
                    $data['userDeliveryStats']['labels'][0] === 'Aucun utilisateur') {
                    
                    $data['userDeliveryStats']['labels'][0] = 'Aucune direction';
                }
            }
            
            // Ensure userDistribution is correctly tagged based on its content
            if (isset($data['userDistribution'])) {
                // Explicitly check if this is actually administration data
                // If it matches administrationStats, it's administration data
                if (isset($data['administrationStats']) && 
                    json_encode($data['userDistribution']['labels']) === json_encode($data['administrationStats']['labels'])) {
                    $data['userDistribution']['data_type'] = 'administration_data';
                    $data['userDistribution']['is_user_data'] = false;
                }
                // Otherwise, keep the existing data type if set
                else if (!isset($data['userDistribution']['data_type'])) {
                    // Default to user data if not specified
                    $data['userDistribution']['data_type'] = 'user_data';
                    $data['userDistribution']['is_user_data'] = true;
                }
            }
            
            // Make sure empty state in administrationStats uses "Aucune direction"
            if (isset($data['administrationStats']) && 
                isset($data['administrationStats']['labels']) && 
                is_array($data['administrationStats']['labels']) && 
                count($data['administrationStats']['labels']) === 1 &&
                $data['administrationStats']['labels'][0] === 'Aucun utilisateur') {
                
                $data['administrationStats']['labels'][0] = 'Aucune direction';
                
                // Sync with userDistribution for consistency
                if (isset($data['userDistribution']) && 
                    isset($data['userDistribution']['labels']) && 
                    is_array($data['userDistribution']['labels']) && 
                    count($data['userDistribution']['labels']) === 1) {
                    
                    $data['userDistribution']['labels'][0] = 'Aucune direction';
                }
            }
            
            // Add a timestamp to help with cache debugging
            $data['timestamp'] = now()->format('Y-m-d H:i:s');
            $data['cache_bypassed'] = $bypassCache;
            
            // Log what we're returning for debugging
            \Log::debug('Returning dashboard data with ' . 
                (isset($data['userDistribution']['labels']) ? count($data['userDistribution']['labels']) : 0) . 
                ' direction labels');
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user delivery data specifically for the "Livraisons par Utilisateur" chart
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDeliveryData(Request $request)
    {
        try {
            // Check if cache should be bypassed
            $bypassCache = $request->has('fresh') || $request->has('refresh') || $request->has('nocache');
            
            // Get only the user delivery stats specifically
            $userDeliveryStats = $this->dashboardStatsService->getUserDeliveryStats($bypassCache);
            
            // Ensure data is properly formatted for ApexCharts
            $userDeliveryStats = $this->ensureValidChartData($userDeliveryStats);
            
            \Log::info('Returning user delivery data with ' . 
                (isset($userDeliveryStats['labels']) ? count($userDeliveryStats['labels']) : 0) . 
                ' user labels');
            
            return response()->json([
                'success' => true,
                'data' => $userDeliveryStats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('User delivery data error: ' . $e->getMessage());
            
            // Return a valid empty data structure even in case of error
            $emptyData = $this->getEmptyUserDeliveryData();
            $emptyData['error'] = 'Failed to fetch user delivery data: ' . $e->getMessage();
            
            return response()->json([
                'success' => false,
                'data' => $emptyData,
                'error' => $e->getMessage()
            ], 200); // Return 200 with error flag rather than 500 to allow client to handle it
        }
    }
    
    /**
     * Ensure the chart data is properly structured for ApexCharts
     *
     * @param array $data
     * @return array
     */
    private function ensureValidChartData($data)
    {
        // If data is completely invalid, return empty structure
        if (!is_array($data)) {
            return $this->getEmptyUserDeliveryData();
        }
        
        // Ensure it's properly marked as user data
        $data['data_type'] = 'user_data';
        $data['is_user_data'] = true;
        $data['chart_type'] = 'user_delivery';
        
        // Ensure series is properly formatted for ApexCharts
        if (isset($data['series']) && is_array($data['series'])) {
            // If it's a nested array like [[1,2,3]], keep it
            if (isset($data['series'][0]) && is_array($data['series'][0])) {
                // Good format, no change needed
                $data['series'] = $data['series'];
            } else {
                // If it's a flat array like [1,2,3], wrap it in another array
                $data['series'] = [$data['series']];
            }
        } else {
            // If no series, create an empty one matching labels length
            $length = isset($data['labels']) && is_array($data['labels']) ? count($data['labels']) : 0;
            $data['series'] = [array_fill(0, $length, 0)];
        }
        
        // Ensure labels are an array of strings
        if (isset($data['labels']) && is_array($data['labels'])) {
            $data['labels'] = array_map('strval', $data['labels']);
        } else {
            $data['labels'] = ['Aucun utilisateur'];
            $data['series'] = [[0]]; // Reset series to match labels
        }
        
        // Ensure all_data array exists
        if (!isset($data['all_data']) || !is_array($data['all_data'])) {
            $data['all_data'] = [];
            
            // Create all_data from labels and series if possible
            if (isset($data['labels']) && isset($data['series'][0])) {
                foreach ($data['labels'] as $index => $label) {
                    $value = isset($data['series'][0][$index]) ? $data['series'][0][$index] : 0;
                    $data['all_data'][] = [
                        'user' => $label,
                        'delivered_orders' => (int) $value,
                        'user_id' => null,
                        'name' => $label,
                        'matricule' => null
                    ];
                }
            }
        }
        
        // If no data available, use a clearer empty state
        if (count($data['labels']) === 1 && ($data['labels'][0] === 'Aucune direction' || $data['labels'][0] === '')) {
            $data['labels'][0] = 'Aucun utilisateur';
            $data['empty_state'] = true;
            
            // Make sure series matches
            $data['series'] = [[0]];
            
            // Update all_data too
            $data['all_data'] = [
                [
                    'user' => 'Aucun utilisateur',
                    'delivered_orders' => 0,
                    'user_id' => null,
                    'name' => 'Aucun utilisateur',
                    'matricule' => null
                ]
            ];
        }
        
        // Add timestamp for cache debugging
        $data['timestamp'] = now()->format('Y-m-d H:i:s');
        
        // Add debug info to help diagnose chart issues
        $data['debug_info'] = [
            'labels_count' => count($data['labels']),
            'series_format' => json_encode($data['series']),
            'all_data_count' => isset($data['all_data']) ? count($data['all_data']) : 0
        ];
        
        return $data;
    }
    
    /**
     * Get empty user delivery data structure
     *
     * @return array
     */
    private function getEmptyUserDeliveryData()
    {
        return [
            'labels' => ['Aucun utilisateur'],
            'series' => [[0]],
            'data_type' => 'user_data',
            'is_user_data' => true,
            'empty_state' => true,
            'all_data' => [
                [
                    'user' => 'Aucun utilisateur',
                    'delivered_orders' => 0,
                    'user_id' => null,
                    'name' => 'Aucun utilisateur',
                    'matricule' => null
                ]
            ],
            'timestamp' => now()->format('Y-m-d H:i:s')
        ];
    }
} 