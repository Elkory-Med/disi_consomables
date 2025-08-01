<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;

class ProductDetails extends Component
{
    public $product;
    
    public function mount($product_id){
        $this->product = Product::find($product_id);
    }

    //adding item to cart
    public function addToCart($productId)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Please login to add items to cart.');
            return $this->redirect('/auth/login', navigate: true);
        }

        // Check if user is approved
        if (Auth::user()->status === 'pending') {
            session()->flash('error', 'Votre compte n\'est pas encore approuvé. Vous ne pouvez pas passer des commandes pour le moment.');
            return;
        }

        $cartItem = ShoppingCart::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity += 1; // increment its quantity
            $cartItem->save();
        } else {
            ShoppingCart::create([
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'quantity' => 1,
            ]);
        }
        //dispatch
        $this->dispatch('cartUpdated');
    }

    public function render()
    {
        return view('livewire.product-details', [
            'product' => $this->product
        ])->layout('components.layouts.app', [
            'title' => 'Détails du Produit - ' . $this->product->name
        ]);
    }
}
