<?php

namespace App\Services\Charts;

use App\Models\Order;

class RevenueStatsService extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'revenueStats';
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'revenue_stats';
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
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
} 