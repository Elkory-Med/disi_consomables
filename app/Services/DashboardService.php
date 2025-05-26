<?php

namespace App\Services;

use App\Services\Charts\AdministrationChart;
use App\Services\Charts\DeliveredOrdersChart;
use App\Services\Charts\DeliveredProductsChart;
use App\Services\Charts\OrderStatusChart;
use App\Services\Charts\OrderTrendsChart;
use App\Services\Charts\RevenueStatsService;
use App\Services\Charts\UserDeliveryChart;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    /**
     * @var AdministrationChart
     */
    protected $administrationChart;
    
    /**
     * @var UserDeliveryChart
     */
    protected $userDeliveryChart;
    
    /**
     * @var OrderStatusChart
     */
    protected $orderStatusChart;
    
    /**
     * @var DeliveredOrdersChart
     */
    protected $deliveredOrdersChart;
    
    /**
     * @var DeliveredProductsChart
     */
    protected $deliveredProductsChart;
    
    /**
     * @var OrderTrendsChart
     */
    protected $orderTrendsChart;
    
    /**
     * @var RevenueStatsService
     */
    protected $revenueStats;
    
    /**
     * Create a new service instance.
     */
    public function __construct(
        AdministrationChart $administrationChart,
        UserDeliveryChart $userDeliveryChart,
        OrderStatusChart $orderStatusChart,
        DeliveredOrdersChart $deliveredOrdersChart,
        DeliveredProductsChart $deliveredProductsChart,
        OrderTrendsChart $orderTrendsChart,
        RevenueStatsService $revenueStats
    ) {
        $this->administrationChart = $administrationChart;
        $this->userDeliveryChart = $userDeliveryChart;
        $this->orderStatusChart = $orderStatusChart;
        $this->deliveredOrdersChart = $deliveredOrdersChart;
        $this->deliveredProductsChart = $deliveredProductsChart;
        $this->orderTrendsChart = $orderTrendsChart;
        $this->revenueStats = $revenueStats;
    }
    
    /**
     * Get all dashboard data
     * 
     * @param bool $bypassCache
     * @return array
     */
    public function getAllData(bool $bypassCache = false): array
    {
        Log::info('Getting all dashboard data' . ($bypassCache ? ' (bypassing cache)' : ''));
        
        // Get data from all charts
        $data = [
            // Administration data (for Par Directions chart)
            'administrationStats' => $this->administrationChart->getData($bypassCache),
            
            // User delivery data
            'userDeliveryStats' => $this->userDeliveryChart->getData($bypassCache),
            
            // Order status data
            'orderStats' => $this->orderStatusChart->getData($bypassCache),
            
            // Delivered orders data
            'deliveredOrdersStats' => $this->deliveredOrdersChart->getData($bypassCache),
            
            // Delivered products data
            'deliveredProducts' => $this->deliveredProductsChart->getData($bypassCache),
            
            // Order trends data
            'orderTrends' => $this->orderTrendsChart->getData($bypassCache),
            
            // Revenue stats
            'revenue' => $this->revenueStats->getData($bypassCache),
            
            // Metadata
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'cache_bypassed' => $bypassCache
        ];
        
        // For backward compatibility, add userDistribution as alias for administrationStats
        $data['userDistribution'] = $data['administrationStats'];
        
        return $data;
    }
    
    /**
     * Clear all caches
     */
    public function clearAllCaches(): void
    {
        Log::info('Clearing all dashboard caches');
        
        $this->administrationChart->clearCache();
        $this->userDeliveryChart->clearCache();
        $this->orderStatusChart->clearCache();
        $this->deliveredOrdersChart->clearCache();
        $this->deliveredProductsChart->clearCache();
        $this->orderTrendsChart->clearCache();
        $this->revenueStats->clearCache();
    }
} 