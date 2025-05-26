<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckDeliveredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-delivered-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking delivered orders...');

        // Check delivered orders
        $deliveredOrders = \DB::table('orders')
            ->where('delivered', true)
            ->count();
        $this->info("Total delivered orders: {$deliveredOrders}");

        // Check delivered orders with administration
        $ordersWithAdmin = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.delivered', true)
            ->whereNotNull('users.matricule')
            ->count();
        $this->info("Delivered orders with administration: {$ordersWithAdmin}");

        // List all delivered orders with administration
        $ordersWithAdminDetail = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.delivered', true)
            ->whereNotNull('users.matricule')
            ->select('orders.id', 'users.administration', 'users.name')
            ->get();

        if ($ordersWithAdminDetail->isEmpty()) {
            $this->warn('No delivered orders with administration found');
        } else {
            $this->info("Details of delivered orders with administration:");
            foreach ($ordersWithAdminDetail as $order) {
                $this->line("Order ID: {$order->id}, Admin: {$order->administration}, User: {$order->name}");
            }
        }

        // Examine the filtering criteria used in the administration chart
        $filteredOrders = \DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.delivered', true)
            ->whereNotNull('users.matricule')
            ->whereNotIn(\DB::raw('LOWER(users.administration)'), [
                'user', 'admin', 'utilisateur', 'administrateur', 
                'en attente', 'rejeté', 'pending', 'approved', 'rejected'
            ])
            ->count();
        $this->info("Filtered delivered orders with valid administration: {$filteredOrders}");

        // Count all users with administrations
        $usersWithAdmin = \DB::table('users')
            ->whereNotNull('matricule')
            ->count();
        $this->info("Total users with administration: {$usersWithAdmin}");

        // Check order items for delivered orders
        $this->info("\nChecking order items for delivered orders:");
        $orderItems = \DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.delivered', true)
            ->select('order_items.order_id', 'products.name as product_name', 'order_items.quantity')
            ->get();

        if ($orderItems->isEmpty()) {
            $this->warn('No order items found for delivered orders');
        } else {
            foreach ($orderItems as $item) {
                $this->line("Order ID: {$item->order_id}, Product: {$item->product_name}, Quantity: {$item->quantity}");
            }
        }

        // Check if the processAdministrationData function is filtering out the data
        $this->info("\nChecking if data is being filtered out by the administration chart:");
        $administrations = \DB::table('users')
            ->whereNotNull('matricule')
            ->select('administration')
            ->distinct()
            ->get()
            ->pluck('administration');
        
        $this->info("All distinct administrations:");
        foreach ($administrations as $admin) {
            $this->line("- {$admin}");
            
            // Check if this administration would be filtered by the chart's filter terms
            $isFilteredOut = false;
            $forbiddenExactTerms = [
                'user', 'users', 'utilisateur', 'utilisateurs',
                'admin', 'admins', 'administrator', 'administrators', 'administrateur', 'administrateurs',
                'role', 'roles', 'rôle', 'rôles',
                'en attente', 'attente', 'pending', 
                'rejeté', 'rejetés', 'rejetée', 'rejetées', 'rejete', 'rejetes', 'rejected',
                'approuvé', 'approuvés', 'approuvée', 'approuvées', 'approuve', 'approuves', 'approved',
                'livré', 'livrés', 'livrée', 'livrées', 'livre', 'livres', 'delivered'
            ];
            
            $forbiddenPartialTerms = [
                'user', 'admin', 'role', 'rejet', 'approuv', 'livr', 'attente', 
                'pending', 'approved', 'delivered', 'rejected', 'utilisat'
            ];
            
            $name = strtolower($admin);
            
            if (in_array($name, $forbiddenExactTerms)) {
                $isFilteredOut = true;
                $this->warn("  Would be filtered out (exact match)");
            } else {
                foreach ($forbiddenPartialTerms as $term) {
                    if (strpos($name, $term) !== false) {
                        $isFilteredOut = true;
                        $this->warn("  Would be filtered out (contains '{$term}')");
                        break;
                    }
                }
            }
            
            if (!$isFilteredOut) {
                $this->info("  Would pass the filter");
            }
        }

        // Check if administrationStats vs userDistribution is the issue
        $this->info("\nChecking data structure issue:");
        $this->info("The chart is looking for 'userDistribution' in the data but the backend provides 'administrationStats'");
        
        // Create a temporary route to check dashboard data
        $this->info("\nAdd this temporary route to check dashboard data:");
        $this->line("Route::get('/check-dashboard-data', function () {
        \$adminDashboard = new \App\Livewire\AdminDashboard();
        \$data = \$adminDashboard->loadDashboardData();
        return [
            'has_userDistribution' => isset(\$data['userDistribution']),
            'has_administrationStats' => isset(\$data['administrationStats']),
            'data_keys' => array_keys(\$data)
        ];
    });");

        return self::SUCCESS;
    }
}
