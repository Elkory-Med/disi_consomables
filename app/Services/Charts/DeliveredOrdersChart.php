<?php

namespace App\Services\Charts;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeliveredOrdersChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'deliveredOrdersChart';
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'delivered_orders_stats_' . Order::where('status', Order::STATUS_APPROVED)->count();
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
    {
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
            ]],
            'delivered' => $deliveredCount,
            'notDelivered' => $notDeliveredCount,
            'total' => $deliveredCount + $notDeliveredCount
        ];
    }
} 