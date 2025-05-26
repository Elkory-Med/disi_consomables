<?php

namespace App\Services\Charts;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderTrendsChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'orderTrendsChart';
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'order_trends_' . now()->format('Y-m-d');
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
    {
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
            'series' => [$orderCounts],
            'period' => 'last_7_days',
            'total_days' => count($dates),
            'start_date' => $dates[0],
            'end_date' => $dates[count($dates) - 1]
        ];
    }
} 