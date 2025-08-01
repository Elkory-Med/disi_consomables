<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DashboardStatsService;
use App\Services\ChartService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Enhanced Dashboard Controller
 * 
 * This controller provides an alternative dashboard implementation that focuses on:
 * - Improved chart visualizations
 * - More efficient data loading via AJAX
 * - Reduced Livewire dependency
 * - Better separation of concerns (data, presentation, chart configs)
 * 
 * This dashboard uses the DashboardStatsService for data retrieval and the ChartService
 * for generating ApexCharts configurations. The frontend uses dashboard-charts.js for
 * rendering and managing the charts.
 */
class EnhancedDashboardController extends Controller
{
    /**
     * @var DashboardStatsService
     */
    protected $dashboardStatsService;
    
    /**
     * @var ChartService
     */
    protected $chartService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DashboardStatsService $dashboardStatsService, ChartService $chartService)
    {
        $this->dashboardStatsService = $dashboardStatsService;
        $this->chartService = $chartService;
    }

    /**
     * Show the admin dashboard
     */
    public function index(Request $request)
    {
        // Check if a full refresh is requested
        if ($request->has('fullrefresh')) {
            $this->clearAllDashboardCaches();
            Log::info('Full dashboard cache refresh triggered');
        }
        
        return view('admin.enhanced-dashboard');
    }
    
    /**
     * Clear all dashboard-related caches
     */
    protected function clearAllDashboardCaches()
    {
        // Clear all related caches
        Cache::forget('dashboard_data_' . date('Y-m-d'));
        Cache::forget('user_delivery_stats_' . now()->format('YmdH'));
        Cache::forget('admin_dashboard_data');
        Cache::forget('user_distribution_stats');
        Cache::forget('delivered_products_stats');
        Cache::forget('administration_stats');
        Cache::forget('orders_trend_data');
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
            $statsData = $this->dashboardStatsService->getAllStats($bypassCache);
            
            // Process and normalize data
            $processedData = $this->processData($statsData);
            
            // Get chart configurations
            $chartConfigs = $this->getChartConfigurations($processedData);
            
            // Return both the raw data and chart configurations
            $response = [
                'raw_data' => $processedData,
                'chart_configs' => $chartConfigs,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'cache_bypassed' => $bypassCache,
                'version' => '1.0.0'
            ];
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch dashboard data',
                'message' => $e->getMessage(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ], 500);
        }
    }
    
    /**
     * Process and normalize dashboard data
     */
    protected function processData(array $statsData): array
    {
        // Standardize data format for consistency
        if (isset($statsData['administrationStats'])) {
            // Convert all series data to a consistent format
            if (isset($statsData['administrationStats']['series'])) {
                // Ensure series is always an array of numbers
                if (!is_array($statsData['administrationStats']['series'])) {
                    $statsData['administrationStats']['series'] = [(array)$statsData['administrationStats']['series']];
                } else if (is_array($statsData['administrationStats']['series']) && !isset($statsData['administrationStats']['series'][0])) {
                    $statsData['administrationStats']['series'] = [$statsData['administrationStats']['series']];
                }
                
                // Ensure all values are integers
                if (isset($statsData['administrationStats']['series'][0]) && is_array($statsData['administrationStats']['series'][0])) {
                    $statsData['administrationStats']['series'][0] = array_map('intval', $statsData['administrationStats']['series'][0]);
                }
            }
            
            // Mark as administration data explicitly
            $statsData['administrationStats']['data_type'] = 'administration_data';
            $statsData['administrationStats']['is_user_data'] = false;
            
            // Make sure userDistribution is also set for compatibility
            $statsData['userDistribution'] = $statsData['administrationStats'];
        }
        
        // Make sure empty state in administrationStats uses "Aucune direction"
        if (isset($statsData['administrationStats']) && 
            isset($statsData['administrationStats']['labels']) && 
            is_array($statsData['administrationStats']['labels']) && 
            count($statsData['administrationStats']['labels']) === 1 &&
            $statsData['administrationStats']['labels'][0] === 'Aucun utilisateur') {
            
            $statsData['administrationStats']['labels'][0] = 'Aucune direction';
            
            // Sync with userDistribution for consistency
            if (isset($statsData['userDistribution']) && 
                isset($statsData['userDistribution']['labels']) && 
                is_array($statsData['userDistribution']['labels']) && 
                count($statsData['userDistribution']['labels']) === 1) {
                
                $statsData['userDistribution']['labels'][0] = 'Aucune direction';
            }
        }
        
        return $statsData;
    }
    
    /**
     * Get chart configurations for the dashboard
     */
    protected function getChartConfigurations(array $data): array
    {
        return [
            'orderStatusChart' => $this->chartService->getOrderStatusChartConfig($data['orderStats'] ?? []),
            'deliveredOrdersChart' => $this->chartService->getDeliveredOrdersChartConfig($data['deliveredOrdersStats'] ?? []),
            'orderTrendsChart' => $this->chartService->getOrderTrendsChartConfig($data['orderTrends'] ?? []),
            'deliveredProductsChart' => $this->chartService->getDeliveredProductsChartConfig($data['deliveredProducts'] ?? []),
            'userDistributionChart' => $this->chartService->getUserDistributionChartConfig($data['userDistribution'] ?? [])
        ];
    }
} 