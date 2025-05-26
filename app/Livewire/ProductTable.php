<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class ProductTable extends Component
{
    use WithPagination;

    public $search = '';
    public $product_id;
    public $isModalOpen = false;

    public function confirmDelete($productId)
    {
        $this->product_id = $productId;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->product_id = null;
    }

    public function delete()
    {
        try {
            $product = Product::find($this->product_id);
            
            if (!$product) {
                session()->flash('message', 'Produit non trouvé.');
                session()->flash('message-type', 'error');
                $this->closeModal();
                return;
            }

            // Check if product has restricted orders
            if ($product->hasRestrictedOrders()) {
                session()->flash('message', 'Ce produit ne peut pas être supprimé car il est associé à des commandes ' . $product->order_statuses);
                session()->flash('message-type', 'error');
                $this->closeModal();
                return;
            }

            // Delete the product image if it exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $product->delete();

            session()->flash('message', 'Produit supprimé avec succès!');
            session()->flash('message-type', 'confirmed');

        } catch (\Exception $e) {
            session()->flash('message', 'Erreur lors de la suppression du produit.');
            session()->flash('message-type', 'error');
            logger()->error('Error deleting product:', [
                'id' => $this->product_id,
                'error' => $e->getMessage()
            ]);
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        return redirect()->route('admin.products.edit', ['id' => $id]);
    }

    public function render()
    {
        $products = Product::when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(10);
        
        return view('livewire.product-table', [
            'products' => $products
        ]);
    }
}
