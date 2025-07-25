<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class UserOrders extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $perPageOptions = [5, 10, 25, 50];
    public $selectedOrder = null;
    public $showOrderDetailsFlag = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10]
    ];

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->reset(['showOrderDetailsFlag', 'selectedOrder']);
        
        // Keep flash messages after mount
        if (session()->has('error')) {
            session()->keep(['error']);
        }
        
        // We're now using a modal for success messages, so no need to
        // convert order_success to flash messages anymore
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('order-created')]
    public function handleOrderCreated()
    {
        $this->resetPage();
        $this->dispatch('$refresh');
    }

    #[On('close-modal')]
    public function closeOrderDetails()
    {
        $this->reset(['showOrderDetailsFlag', 'selectedOrder']);
    }

    public function showOrderDetails($orderId)
    {
        try {
            $this->selectedOrder = Order::with(['orderItems.product', 'history.changedByUser'])
                ->where('user_id', Auth::id())
                ->findOrFail($orderId);
            
            $this->showOrderDetailsFlag = true;
            $this->dispatch('open-modal');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors du chargement des détails de la commande.');
            Log::error('Error loading order details', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'user_id' => Auth::id()
            ]);
        }
    }

    public function getOrders()
    {
        return Order::where('user_id', Auth::id())
            ->when($this->search, function($query) {
                $query->where('id', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.user-orders', [
            'orders' => $this->getOrders()
        ])->layout('components.layouts.app', [
            'title' => 'Mes Commandes'
        ]);
    }
}
