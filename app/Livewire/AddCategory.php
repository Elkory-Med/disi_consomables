<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;

class AddCategory extends Component
{
    public $currentUrl;
    public $category_name = '';

    protected $messages = [
        'category_name.required' => 'Le nom de la catégorie est requis.',
        'category_name.min' => 'Le nom de la catégorie doit contenir au moins :min caractères.',
        'category_name.max' => 'Le nom de la catégorie ne peut pas dépasser :max caractères.',
        'category_name.unique' => 'Cette catégorie existe déjà.'
    ];

    public function mount()
    {
        $current_url = url()->current();
        $explode_url = explode('/', $current_url);
        $this->currentUrl = $explode_url[3] . ' ' . $explode_url[4];
    }

    public function save()
    {
        try {
            // Validate input
            $validated = $this->validate([
                'category_name' => 'required|min:2|max:255|unique:categories,name'
            ]);

            // Create category directly with validated data
            $category = new Category();
            $category->name = $validated['category_name'];
            $category->save();

            // Store success message in session
            session()->flash('message', 'La catégorie a été créée avec succès!');
            session()->flash('message-type', 'success');

            // Redirect to manage categories page
            return redirect()->route('manage-categories');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            logger()->info('Validation failed:', ['errors' => $e->errors()]);
            foreach ($e->errors() as $field => $errors) {
                $this->addError($field, $errors[0]);
            }
        } catch (\Exception $e) {
            logger()->error('Error saving category:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'category_name' => $this->category_name
            ]);
            
            session()->flash('message', 'Échec de l\'ajout de la catégorie. Veuillez réessayer.');
            session()->flash('message-type', 'error');
            $this->addError('category_name', 'Échec de l\'enregistrement de la catégorie. Veuillez réessayer.');
        }
    }

    public function render()
    {
        return view('livewire.add-category')->layout('layouts.admin-layout');
    }
}
