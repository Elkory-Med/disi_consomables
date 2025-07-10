<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersDelivered extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';
    
    public $totalDelivered = 0;
    public $deliveredOrders = [];
    public $search = '';
    public $showSerialInputs = false;
    public $currentOrder;
    public $serialNumbers = [];
    public $errorMessage = '';
    public $perPage = 20;
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $isLoading = false;
    public $dataCount = 0;

    public function mount()
    {
        // Cache the count for better performance with large datasets
        $this->totalDelivered = $this->getCachedOrderCount();
        // Only load IDs instead of full records for better performance
        $this->deliveredOrders = $this->getCachedDeliveredOrderIds();
        // Store the total count for pagination calculations
        $this->dataCount = $this->getCachedTotalOrderCount();
    }

    /**
     * Get cached count of delivered orders
     */
    protected function getCachedOrderCount()
    {
        $cacheKey = 'delivered_orders_count';
        
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        $count = Order::where('delivered', true)->count();
        cache()->put($cacheKey, $count, now()->addMinutes(10));
        
        return $count;
    }
    
    /**
     * Get cached IDs of delivered orders
     */
    protected function getCachedDeliveredOrderIds()
    {
        $cacheKey = 'delivered_orders_ids';
        
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        $ids = Order::where('delivered', true)
            ->select('id')
            ->orderBy('id', 'desc')
            ->take(1000) // Limit to the most recent 1000 for performance
            ->pluck('id')
            ->toArray();
            
        cache()->put($cacheKey, $ids, now()->addMinutes(10));
        
        return $ids;
    }
    
    /**
     * Get cached total order count (for pagination)
     */
    protected function getCachedTotalOrderCount()
    {
        $cacheKey = 'total_orders_count';
        
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        $count = Order::count();
        cache()->put($cacheKey, $count, now()->addMinutes(10));
        
        return $count;
    }

    public function setSortBy($sortColumn)
    {
        if ($this->sortBy == $sortColumn) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';
            return;
        }
        $this->sortBy = $sortColumn;
        $this->sortDir = 'ASC';
    }

    public function getApprovedOrdersProperty()
    {
        $this->isLoading = true;
        
        // Use query builder instead of Eloquent for better performance with large datasets
        $query = Order::query()
            ->select('orders.*')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where(function($query) {
                $query->where('orders.status', Order::STATUS_APPROVED)
                      ->orWhere('orders.status', Order::STATUS_DELIVERED);
            });
            
        // Only apply search if actually searching (improves performance)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('orders.id', 'like', '%' . $this->search . '%')
                  ->orWhere('users.username', 'like', '%' . $this->search . '%')
                  ->orWhere('users.unite', 'like', '%' . $this->search . '%')
                  ->orWhere('users.matricule', 'like', '%' . $this->search . '%')
                  ->orWhere('users.administration', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply sorting - ensure proper column names for user fields
        if (in_array($this->sortBy, ['unite', 'matricule', 'administration', 'name'])) {
            $query->orderBy('users.' . $this->sortBy, $this->sortDir);
        } else {
            $query->orderBy('orders.' . $this->sortBy, $this->sortDir);
        }
        
        // Use efficient eager loading
        $result = $query->with([
                'user:id,username,unite,matricule,administration',
                'orderItems:id,order_id,product_id,quantity',
                'orderItems.product:id,name'
            ])
            ->paginate($this->perPage);
            
        $this->isLoading = false;
        
        return $result;
    }

    public function markAsDelivered($orderId)
    {
        $order = Order::find($orderId);
        if ($order) {
            try {
                if ($order->delivered || $order->status === Order::STATUS_DELIVERED) {
                    session()->flash('error', 'Cette commande est déjà marquée comme livrée.');
                    return;
                }

                DB::beginTransaction();
                
                // Update both the delivered flag and the status to 'delivered'
                $order->delivered = true;
                $order->delivered_at = now();
                $order->status = Order::STATUS_DELIVERED; // Set status to 'delivered'
                $order->save();
                
                // Log this change in order history
                $order->addHistoryEntry(
                    Order::STATUS_DELIVERED,
                    'Commande marquée comme livrée',
                    auth()->id()
                );
                
                // Clear all dashboard-related caches
                cache()->forget('delivered_orders_count');
                cache()->forget('delivered_orders_ids');
                cache()->forget('total_orders_count');
                cache()->forget('order_stats_' . Order::count());
                cache()->forget('delivered_orders_stats_' . Order::where('status', Order::STATUS_APPROVED)->count());
                cache()->forget('delivered_products_chart_' . Order::where('status', Order::STATUS_APPROVED)->where('delivered', true)->count());
                cache()->forget('user_distribution_chart_' . Order::count());
                cache()->forget('order_trends_' . now()->format('Y-m-d'));
                
                // Update local count and refresh UI
                $this->totalDelivered = $this->getCachedOrderCount();
                $this->resetPage();
                
                // Dispatch event to update charts
                $this->dispatch('chartDataUpdated');
                
                DB::commit();
                
                session()->flash('message', 'Commande marquée comme livrée avec succès.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                
                \Log::error('Error marking order as delivered:', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Erreur lors de la livraison de la commande.');
            }
        } else {
            session()->flash('error', 'Commande non trouvée ou inaccessible.');
        }
    }

    public function showSerialNumberInputs($orderId)
    {
        Log::info('showSerialNumberInputs called with order ID: ' . $orderId);
        $this->currentOrder = Order::with(['user', 'orderItems.product'])->find($orderId);
        Log::info('Current order fetched:', ['currentOrder' => $this->currentOrder]);
        $this->showSerialInputs = true;
    }

    public function generateInvoice()
    {
        try {
            if (!$this->currentOrder) {
                throw new \Exception('No order selected');
            }

            // Validate serial numbers
            if (empty($this->serialNumbers)) {
                throw new \Exception('Veuillez saisir les numéros de série pour tous les articles');
            }

            foreach ($this->currentOrder->orderItems as $item) {
                if (!isset($this->serialNumbers[$item->id])) {
                    throw new \Exception('Veuillez saisir les numéros de série pour ' . $item->product->name);
                }

                for ($i = 0; $i < $item->quantity; $i++) {
                    if (!isset($this->serialNumbers[$item->id][$i]) || empty($this->serialNumbers[$item->id][$i])) {
                        throw new \Exception('Veuillez saisir le numéro de série pour l\'article ' . ($i + 1) . ' de ' . $item->product->name);
                    }
                }
            }

            $logoPath = public_path('images/logo.png');
            
            $mpdf = new \Mpdf\Mpdf([
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            $mpdf->WriteHTML(view('invoices.invoice', [
                'order' => $this->currentOrder,
                'serialNumbers' => $this->serialNumbers,
                'logoPath' => $logoPath
            ])->render());

            $filename = 'invoice_' . $this->currentOrder->id . '_' . time() . '.pdf';
            
            return response()->streamDownload(
                fn () => print($mpdf->Output('', 'S')),
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateSerialNumber($itemId, $index, $value)
    {
        if (!$this->currentOrder || !isset($this->currentOrder->orderItems[$itemId])) {
            return;
        }

        $item = $this->currentOrder->orderItems[$itemId];
        if (!isset($this->serialNumbers[$item->id])) {
            $this->serialNumbers[$item->id] = [];
        }
        $this->serialNumbers[$item->id][$index] = $value;
    }

    public function render()
    {
        $approvedOrders = $this->approvedOrders;
        return view('livewire.orders-delivered', [
            'approvedOrders' => $approvedOrders
        ])->layout('layouts.admin-layout', [
            'title' => 'Commandes Livrées'
        ]);
    }

    // Reset pagination when search changes
    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Reset pagination when perPage changes
    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
