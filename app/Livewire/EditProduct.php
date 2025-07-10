<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditProduct extends Component
{
    use WithFileUploads;
    
    public $submitting = false;
    public $iteration = 0;
    public $product_name = '';
    public $product_description = '';
    public $product_category = '';
    public $product_image;
    public $currentUrl;
    public $all_categories;
    public $product_details;

    public function mount($id){
        $this->product_details = Product::findOrFail($id);
        $this->product_name = $this->product_details->name;
        $this->product_description = $this->product_details->description;
        $this->product_category = $this->product_details->category_id;
        $this->all_categories = Category::all();
    }

    protected function rules()
    {
        return [
            'product_name' => 'required|min:3',
            'product_description' => 'nullable|min:10',
            'product_category' => 'required|exists:categories,id',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            'product_image.image' => 'Le fichier doit être une image.',
            'product_image.mimes' => 'L\'image doit être au format: jpeg, png, jpg, ou gif.',
            'product_image.max' => 'L\'image ne doit pas dépasser 2048 Ko.'
        ];
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'product_image') {
            $this->iteration++;
            try {
                if (!$this->product_image) {
                    \Log::info('Aucun fichier sélectionné');
                    return;
                }

                \Log::info('Téléchargement du fichier commencé', [
                    'fichier' => $this->product_image->getClientOriginalName(),
                    'taille' => $this->product_image->getSize(),
                    'type_mime' => $this->product_image->getMimeType()
                ]);

                if (!$this->product_image->isValid()) {
                    throw new \Exception('Le fichier image est invalide.');
                }

                \Log::info('Fichier validé avec succès', [
                    'chemin_temporaire' => $this->product_image->getPath(),
                    'type_mime' => $this->product_image->getMimeType()
                ]);
                
                // Generate preview URL
                $tempUrl = $this->product_image->temporaryUrl();
                \Log::info('URL de prévisualisation générée', ['url' => $tempUrl]);
                
                $this->dispatch('upload-success');
            } catch (\Exception $e) {
                \Log::error('Erreur lors du téléchargement du fichier', [
                    'erreur' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'info_fichier' => $this->product_image ? [
                        'nom' => $this->product_image->getClientOriginalName(),
                        'taille' => $this->product_image->getSize(),
                        'type_mime' => $this->product_image->getMimeType()
                    ] : 'Aucune information de fichier disponible'
                ]);
                session()->flash('error', 'Erreur lors du téléchargement: ' . $e->getMessage());
                $this->product_image = null;
            }
        }
    }

    public function update()
    {
        if ($this->submitting) {
            return;
        }
        $this->submitting = true;
        
        try {
            // Validate all fields first
            $validatedData = $this->validate($this->rules(), $this->messages());

            $imagePath = null;

            if ($this->product_image && !is_string($this->product_image)) {
                \Log::info('Validation réussie, traitement de l\'image', [
                    'a_image' => isset($this->product_image),
                    'donnees_image' => $this->product_image ? [
                        'nom_original' => $this->product_image->getClientOriginalName(),
                        'type_mime' => $this->product_image->getMimeType(),
                        'taille' => $this->product_image->getSize()
                    ] : null
                ]);

                if (!method_exists($this->product_image, 'isValid') || !$this->product_image->isValid()) {
                    \Log::error('Téléchargement de fichier invalide', [
                        'info_fichier' => $this->product_image ? [
                            'classe' => get_class($this->product_image),
                            'methodes' => get_class_methods($this->product_image)
                        ] : 'Aucun fichier'
                    ]);
                    throw new \Exception('Le fichier image est invalide. Veuillez réessayer.');
                }

                // Verify file type and size
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                $maxSize = 2048 * 1024; // 2MB

                if (!in_array($this->product_image->getMimeType(), $allowedTypes)) {
                    throw new \Exception('Type de fichier non autorisé. Utilisez JPEG, PNG, ou GIF.');
                }

                if ($this->product_image->getSize() > $maxSize) {
                    throw new \Exception('L\'image est trop volumineuse. Maximum 2MB.');
                }

                \Log::info('Validation du fichier réussie', [
                    'type_mime' => $this->product_image->getMimeType(),
                    'taille' => $this->product_image->getSize()
                ]);

                // Generate unique filename
                $fileName = time() . '_' . uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $this->product_image->getClientOriginalExtension();
                
                try {
                    $imagePath = $this->product_image->storeAs('products', $fileName, 'public');
                    
                    if (!$imagePath) {
                        throw new \Exception('Échec de l\'enregistrement de l\'image');
                    }

                    // Verify file was stored correctly
                    if (!Storage::disk('public')->exists($imagePath)) {
                        throw new \Exception('Le fichier n\'a pas été correctement enregistré');
                    }

                    // Verify file is readable
                    $fileContents = Storage::disk('public')->get($imagePath);
                    if (empty($fileContents)) {
                        throw new \Exception('Le fichier enregistré est vide');
                    }

                    \Log::info('Fichier enregistré avec succès', [
                        'chemin' => $imagePath,
                        'taille' => Storage::disk('public')->size($imagePath)
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Échec de l\'enregistrement de l\'image', [
                        'erreur' => $e->getMessage(),
                        'fichier' => $fileName,
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw new \Exception('Échec de l\'enregistrement de l\'image: ' . $e->getMessage());
                }
            }

            // Update product in database
            try {
                $product = Product::find($this->product_details->id);
                
                // If a new image was uploaded, delete the old one
                if ($imagePath && $product->image) {
                    Storage::disk('public')->delete($product->image);
                }

                $product->update([
                    'name' => $this->product_name,
                    'description' => $this->product_description,
                    'category_id' => $this->product_category,
                    'image' => $imagePath ?? $product->image,
                ]);

                \Log::info('Produit mis à jour avec succès', [
                    'id_produit' => $product->id,
                    'chemin_image' => $imagePath ?? $product->image
                ]);

                session()->flash('success', 'Le produit a été mis à jour avec succès.');
                return redirect()->route('admin.products');

            } catch (\Exception $e) {
                // Cleanup uploaded file if product update fails
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
                \Log::error('Échec de la mise à jour du produit', [
                    'erreur' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw new \Exception('Échec de la mise à jour du produit: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            \Log::error('Erreur dans le processus de mise à jour', [
                'erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', $e->getMessage());
            $this->submitting = false;
            return null;
        }
    }

    public function render()
    {
        try {
            return view('livewire.edit-product')->layout('layouts.admin-layout');
        } catch (\Exception $e) {
            \Log::error('Erreur lors du rendu : ' . $e->getMessage());
            return view('livewire.edit-product', [
                'all_categories' => collect([])
            ])->layout('layouts.admin-layout');
        }
    }
}
