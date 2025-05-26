<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ManageCategories extends Component
{
    use WithPagination;

    public $perPage = 5;
    public $sortBy = 'created_at';
    public $sortDir = 'DESC';
    public $search = '';
    public $currentUrl;
    public $category_name;
    public $category_id;

    public function setSortBy($sortColum){
        if ($this->sortBy == $sortColum) {
            $this->sortDir = ($this->sortDir == 'ASC') ? 'DESC' : 'ASC';
            return;
        }
        
        $this->sortBy = $sortColum;
        $this->sortDir = 'ASC';
    }

    public function deleteCategory($categoryId) {
        $category = Category::find($categoryId);
        if ($category) {
            // Check if the category is associated with any products
            if ($category->products()->count() > 0) {
                // Check if any products in this category have orders
                $productsWithOrders = false;
                $orderedProducts = [];
                
                foreach ($category->products as $product) {
                    if ($product->hasRestrictedOrders()) {
                        $productsWithOrders = true;
                        $orderedProducts[] = $product->name;
                    }
                }
                
                if ($productsWithOrders) {
                    // Category has products that have been ordered, prevent deletion
                    session()->flash('message', 'Cette catégorie ne peut pas être supprimée car elle contient des produits qui ont été commandés: ' . implode(', ', $orderedProducts));
                    session()->flash('message-type', 'error');
                    return;
                }
                
                // Category has products but none have been ordered, warn user
                session()->flash('message', 'Cette catégorie contient des produits. Supprimez d\'abord ces produits avant de supprimer la catégorie.');
                session()->flash('message-type', 'error');
                return;
            }
            
            // No products using this category, safe to delete
            $category->delete();
            $this->flashMessage('category_deleted');
        }
    }

    public function editCategory($categoryId) {
        $category = Category::find($categoryId);
        if ($category) {
            $this->category_name = $category->name;
            $this->category_id = $category->id;
        }
    }

    public function updateCategory() {
        $this->validate([
            'category_name' => 'required'
        ]);
        $category = Category::find($this->category_id);
        if ($category) {
            $category->name = $this->category_name;
            $category->save();
            $this->flashMessage('category_updated');
          
            // Reset the fields after updating
        $this->category_name = ''; // Clear the category name
        $this->category_id = null;  // Clear the category ID
        }
    }

    public function flashMessage($key) {
        $messages = [
            'category_updated' => 'Catégorie mise à jour avec succès.',
            'category_deleted' => 'Catégorie supprimée avec succès.',
            // Add more messages as needed
        ];

        session()->flash('message', $messages[$key] ?? 'Message par défaut.'); // Default message if key not found
        session()->flash('message-type', 'success'); // Set message type to success for these messages
    }

    public function getCategories()
    {
        return Category::search($this->search)
            ->orderBy($this->sortBy,$this->sortDir)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.manage-categories', [
            'categories' => $this->getCategories()
        ])->layout('layouts.admin-layout', [
            'title' => 'Gestion des Catégories'
        ]);
    }
}
