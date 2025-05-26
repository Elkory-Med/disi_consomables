<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AddProductForm extends Component
{
    use WithFileUploads;
    
    public $submitting = false;
    public $iteration = 0;
    public $product_name;
    public $product_category;
    public $product_description;
    public $product_image;
    public $currentUrl = 'Ajouter un produit';

    protected function rules()
    {
        return [
            'product_name' => 'required|min:3',
            'product_category' => 'required|exists:categories,id',
            'product_description' => 'nullable|min:10',
            'product_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    protected function messages()
    {
        return [
            'product_name.required' => 'Le nom du produit est requis.',
            'product_name.min' => 'Le nom du produit doit contenir au moins 3 caractères.',
            'product_category.required' => 'La catégorie du produit est requise.',
            'product_category.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'product_description.min' => 'Si fournie, la description du produit doit contenir au moins 10 caractères.',
            'product_image.required' => 'L\'image du produit est requise.',
            'product_image.image' => 'Le fichier doit être une image.',
            'product_image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, ou gif.',
            'product_image.max' => 'L\'image ne doit pas dépasser 2048 Ko.'
        ];
    }

    protected $listeners = ['refresh' => '$refresh'];

    public function hydrate()
    {
        \Log::info('Composant hydraté', [
            'a_image' => isset($this->product_image),
            'type_image' => $this->product_image ? get_class($this->product_image) : 'aucun'
        ]);
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'product_image') {
            $this->iteration++;
        }
    }

    public function save()
    {
        $this->submitting = true;
        
        try {
            // Validate the form
            $validatedData = $this->validate();
            
            // Upload the image
            $imagePath = $this->product_image->store('products', 'public');
            
            // Create the product
            $product = Product::create([
                'name' => $this->product_name,
                'description' => $this->product_description,
                'category_id' => $this->product_category,
                'image' => $imagePath,
            ]);
            
            session()->flash('success', 'Le produit a été ajouté avec succès.');
            return redirect()->route('admin.products');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur: ' . $e->getMessage());
            $this->submitting = false;
        }
    }

    public function mount()
    {
        // Mount component (cleanup moved to scheduled task)
    }

    public function render()
    {
        return view('livewire.add-product-form', [
            'categories' => Category::all()
        ])->layout('layouts.admin-layout', [
            'title' => 'Ajouter un produit'
        ]);
    }
}
