protected function getProductDistributionData($page = 1, $itemsPerPage = 15)
{
    $products = Product::select('products.name')
        ->selectRaw('
            SUM(CASE WHEN orders.status = "pending" THEN order_items.quantity ELSE 0 END) as pending,
            SUM(CASE WHEN orders.status = "approved" THEN order_items.quantity ELSE 0 END) as approved,
            SUM(CASE WHEN orders.status = "delivered" THEN order_items.quantity ELSE 0 END) as delivered
        ')
        ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
        ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
        ->groupBy('products.id', 'products.name')
        ->having(DB::raw('pending + approved + delivered'), '>', 0)
        ->orderByRaw('(pending + approved + delivered) DESC')
        ->paginate($itemsPerPage, ['*'], 'page', $page);

    return [
        'distribution' => $products->items(),
        'pagination' => [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'total' => $products->total()
        ]
    ];
}