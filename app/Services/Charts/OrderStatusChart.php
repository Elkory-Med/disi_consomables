<?php

namespace App\Services\Charts;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderStatusChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'orderStatusChart';
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'order_stats_' . Order::count();
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
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
            
            // Calculate total for convenience
            $total = $pendingCount + $approvedCount + $rejectedCount + $deliveredCount;
            
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
                'delivered' => ['orders' => $deliveredCount],
                'total' => $total
            ];
        } catch (\Exception $e) {
            throw $e; // Let BaseChart handle the error
        }
    }
} 