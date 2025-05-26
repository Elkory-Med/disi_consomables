<?php

namespace App\Services\Charts;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeliveredProductsChart extends BaseChart
{
    /**
     * Chart identifier
     */
    protected $chartId = 'deliveredProductsChart';
    
    /**
     * Get chart's cache key
     * 
     * @return string
     */
    public function getCacheKey(): string
    {
        return 'delivered_products_stats';
    }
    
    /**
     * Fetch data from database
     * 
     * @return array
     */
    protected function fetchData(): array
    {
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
                'series' => [$series],
                'total_products' => count($products)
            ];
        }
        
        return [
            'labels' => ['Aucun produit'],
            'series' => [[0]],
            'total_products' => 0,
            'empty_state' => true
        ];
    }
} 