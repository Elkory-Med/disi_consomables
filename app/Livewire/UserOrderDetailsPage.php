<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Illuminate\Support\Str; // Add this line

class UserOrderDetailsPage extends Component
{
    public $orderId;
    public $order;

    // Mount method to fetch order details based on orderId
    public function mount($orderId)
    {
        // dd($orderId);
        $this->orderId = $orderId;
        $this->order = Order::with('orderItems.product')->findOrFail($this->orderId);
    }
   
    public function render()
    {
        return view('livewire.user-details-page');
    }
}
