<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class ProductListing extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $category_id = 0;
    public $current_product_id = 0;
    public $page = 1;
    
    protected $queryString = ['search', 'category', 'page'];
    protected $paginationTheme = 'tailwind';
    
    public function mount($category_id = null, $current_product_id = null)
    {
        if ($category_id !== null) {
            $this->category_id = $category_id;
        }
        
        if ($current_product_id !== null) {
            $this->current_product_id = $current_product_id;
        }
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Cache key based on current filters and pagination
        $cacheKey = 'products_' . $this->search . '_' . $this->category . '_' . $this->category_id . '_' . $this->current_product_id . '_' . $this->page;
        
        // Cache products for 5 minutes
        $products = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $query = Product::query()->with('category');
            
            if ($this->search) {
                $query->search($this->search);
            }
            
            // Handle category filtering from both sources
            if ($this->category) {
                $query->where('category_id', $this->category);
            } elseif ($this->category_id && $this->category_id != '0' && $this->category_id != 'all') {
                $query->where('category_id', $this->category_id);
            }
            
            // Exclude current product if specified
            if ($this->current_product_id) {
                $query->where('id', '!=', $this->current_product_id);
            }
            
            // Limit to 4 products when showing featured (category_id = 0)
            if ($this->category_id == 0) {
                $query->limit(4);
            }
            
            return $query->orderBy('created_at', 'desc')->paginate(12);
        });
        
        // Categories are rarely updated, so cache for longer (30 minutes)
        $categories = Cache::remember('product_categories', now()->addMinutes(30), function () {
            return Category::all();
        });
        
        return view('livewire.product-listing', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
