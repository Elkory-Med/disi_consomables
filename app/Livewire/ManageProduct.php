<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ManageProduct extends Component
{
    use WithPagination;

    public $search = '';
    public $product_id;
    public $currentUrl;

    public function mount()
    {
        $current_url = url()->current();
        $explode_url = explode('/', $current_url);
        $this->currentUrl = $explode_url[3] . ' ' . $explode_url[4];
    }

    public function deleteProduct($id)
    {
        try {
            $product = Product::find($id);
            
            if (!$product) {
                session()->flash('message', 'Produit non trouvé.');
                session()->flash('message-type', 'error');
                return;
            }

            // Check if product has orders in restricted states
            if ($product->hasRestrictedOrders()) {
                session()->flash('message', 'Ce produit ne peut pas être supprimé car il est associé à des commandes ' . $product->order_statuses);
                session()->flash('message-type', 'error');
                return;
            }

            // Delete the product image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            session()->flash('message', 'Produit supprimé avec succès!');
            session()->flash('message-type', 'success');

        } catch (\Exception $e) {
            session()->flash('message', 'Erreur lors de la suppression du produit.');
            session()->flash('message-type', 'error');
            logger()->error('Error deleting product:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $products = Product::when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        })->paginate(10);
        
        return view('livewire.manage-product', [
            'products' => $products
        ])->layout('layouts.admin-layout', [
            'title' => 'Gestion des produits'
        ]);
    }
}
