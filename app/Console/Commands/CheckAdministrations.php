<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAdministrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:administrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check real administration values in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking administrations in users table...');
        
        // Get all distinct administration values
        $allAdministrations = DB::table('users')
            ->select('administration')
            ->whereNotNull('matricule')
            ->where('administration', '!=', '')
            ->distinct()
            ->get()
            ->pluck('administration')
            ->toArray();
            
        $this->info('Found '.count($allAdministrations).' distinct administrations:');
        foreach($allAdministrations as $admin) {
            $this->line('- '.$admin);
        }
        
        $this->info('');
        $this->info('Checking administrations with delivered orders...');
        
        // Get administrations with delivered orders
        $adminWithDeliveredOrders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->select('users.administration', DB::raw('COUNT(orders.id) as order_count'))
            ->where('orders.delivered', true)
            ->whereNotNull('users.matricule')
            ->where('users.administration', '!=', '')
            ->groupBy('users.administration')
            ->orderByDesc('order_count')
            ->get()
            ->toArray();
            
        $this->info('Found '.count($adminWithDeliveredOrders).' administrations with delivered orders:');
        foreach($adminWithDeliveredOrders as $admin) {
            $this->line('- '.$admin->administration.' ('.$admin->order_count.' orders)');
        }
        
        return Command::SUCCESS;
    }
}
