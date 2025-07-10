<?php

namespace App\Livewire;

use App\Models\OrderItems;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use App\Services\CartService;
use App\Traits\WithUserAuthorization;
use App\Livewire\ShoppingCartIcon;

class ShoppingCartComponent extends Component
{
    use WithPagination;
    use WithUserAuthorization;
    
    protected $paginationTheme = 'tailwind';

    // Initialize an empty property for IDE autocompletion
    private CartService $cartService;

    public function boot()
    {
        // Resolve the service from container - guaranteed to run before any other methods
        $this->cartService = app(CartService::class);
    }

    public function mount()
    {
        return $this->redirectIfNotAuthorized();
    }

    protected function getCartItems()
    {
        return $this->cartService->getCartItems(5);
    }

    public function updateQuantity($itemId, $quantity)
    {
        $redirect = $this->redirectIfNotAuthorized('Veuillez vous connecter et attendre l\'approbation de votre compte pour modifier le panier.');
        if ($redirect) return $redirect;

        if ($this->cartService->updateQuantity($itemId, $quantity)) {
            $this->dispatch('cartUpdated');
        }
    }

    public function removeItem($itemId)
    {
        $redirect = $this->redirectIfNotAuthorized('Veuillez vous connecter et attendre l\'approbation de votre compte pour supprimer des articles.');
        if ($redirect) return $redirect;

        if ($this->cartService->removeItem($itemId)) {
            $this->dispatch('cartUpdated');
        }
    }

    public function addToCart($productId)
    {
        $redirect = $this->redirectIfNotAuthorized('Veuillez vous connecter et attendre l\'approbation de votre compte pour ajouter des articles au panier.');
        if ($redirect) return $redirect;

        if ($this->cartService->addToCart($productId)) {
            $this->dispatch('cartUpdated');
            session()->flash('success', 'L\'article a été ajouté au panier avec succès.');
        } else {
            session()->flash('error', 'Une erreur est survenue lors de l\'ajout de l\'article au panier. Veuillez réessayer.');
        }
    }

    public function clearCart()
    {
        $this->cartService->clearCart();
    }

    public function createCheckoutSession()
    {
        $redirect = $this->redirectIfNotAuthorized('Veuillez vous connecter et attendre l\'approbation de votre compte pour créer une commande.');
        if ($redirect) return $redirect;

        $order = $this->cartService->createOrderFromCart();

        if (!$order) {
            session()->flash('error', 'Votre panier est vide. Veuillez ajouter des articles avant de passer commande.');
            return;
        }

        // Clear the cart after creating the order
        $this->clearCart(); 

        $this->dispatch('cartUpdated')->to(ShoppingCartIcon::class);
        
        // Set only one type of success message to avoid duplication
        session(['order_success' => 'Votre commande a été soumise avec succès et est en attente d\'approbation.']);
        
        $this->dispatch('cartUpdated');
        return redirect()->route('user.orders');
    }

    public function render()
    {
        $redirect = $this->redirectIfNotAuthorized();
        if ($redirect) return $redirect;

        $cartItems = $this->getCartItems();
        return view('livewire.shopping-cart-component', ['cartItems' => $cartItems]);
    }
}
