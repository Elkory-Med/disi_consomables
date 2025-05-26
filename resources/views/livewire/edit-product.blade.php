<div>
    <livewire:bread-crumb :url="$currentUrl" />
    <!-- Card Section -->
    <div class="max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <!-- Card -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-7 border border-gray-200">
            <form wire:submit.prevent="update" enctype="multipart/form-data">
                <!-- Section -->
                <div class="grid sm:grid-cols-12 gap-2 sm:gap-4 py-8 first:pt-0 last:pb-0 border-t first:border-transparent border-gray-200">
                    <div class="sm:col-span-12">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Modifier le produit
                        </h2>
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-3">
                        <label for="af-submit-application-full-name" class="inline-block text-sm font-medium text-gray-500 mt-2.5">
                            Nom du produit
                        </label>
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-9">
                        <div>
                            <input type="text" wire:model="product_name" id="af-submit-application-full-name" class="py-2 px-3 pe-11 block w-full border-gray-200 shadow-sm -mt-px -ms-px first:rounded-t-lg last:rounded-b-lg sm:first:rounded-s-lg sm:mt-0 sm:first:ms-0 sm:first:rounded-se-none sm:last:rounded-es-none sm:last:rounded-e-lg text-sm relative focus:z-10 focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none">
@error('product_name') <span class="text-red-500">{{ $message }}</span> @enderror
</div>
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-3">
                        <label for="af-submit-application-email" class="inline-block text-sm font-medium text-gray-500 mt-2.5">
                            Catégorie
                        </label>
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-9">
                        <select 
                            wire:model="product_category" 
                            id="product_category"
                            name="product_category"
                            aria-label="Catégorie de produit"
                            class="py-3 px-4 pe-9 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none"
                        >
                            <option value="">Sélectionner la catégorie de produit</option>
                            @foreach ($all_categories as $category)
                                <option value="{{ $category->id }}" wire:key="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('product_category') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Section -->

                <!-- Section -->
                <div class="grid sm:grid-cols-12 gap-2 sm:gap-4 py-8 first:pt-0 last:pb-0 border-t first:border-transparent border-gray-200">
                    <div class="sm:col-span-12">
                        <h2 class="text-lg font-semibold text-gray-800">
                            Plus de détails
                        </h2>
                    </div>
                    <!-- End Col -->
                    <div class="sm:col-span-3">
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-9">
                        <div class="mb-4">
                            @if ($product_image && method_exists($product_image, 'temporaryUrl'))
                                <img src="{{ $product_image->temporaryUrl() }}" alt="Image Preview" class="h-48 w-48 object-cover rounded-lg">
                            @elseif($product_details->image)
                                <img src="{{ Storage::url($product_details->image) }}" alt="Current Product Image" class="h-48 w-48 object-cover rounded-lg">
                            @else
                                <div class="h-48 w-48 bg-gray-200 flex items-center justify-center rounded-lg">
                                    <span class="text-gray-500">Aucune image sélectionnée</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="mb-4">
                            <label for="product_image" class="block text-sm font-medium text-gray-700 mb-1">Image du produit</label>
                            <input type="file" 
                                id="product_image" 
                                wire:model.live="product_image"
                                class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                                accept="image/jpeg,image/png,image/jpg,image/gif">
                            <p class="mt-1 text-sm text-gray-500">JPG, PNG ou GIF (max. 2MB)</p>
                        </div>
                        
                        <div wire:loading wire:target="product_image" class="text-sm text-blue-600">
                            Chargement de l'image...
                        </div>
                        
                        @error('product_image')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <div class="inline-block">
                            <label for="af-submit-application-bio" class="inline-block text-sm font-medium text-gray-500 mt-2.5">
                                Description du produit
                            </label>
                        </div>
                    </div>
                    <!-- End Col -->

                    <div class="sm:col-span-9">
                        <textarea id="af-submit-application-bio" wire:model="product_description" class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" rows="6" placeholder="Ajoutez une description du produit ici !"></textarea>
                        @error('product_description') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <!-- End Col -->
                </div>
                <!-- End Section -->
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                    <span wire:loading.remove>Mettre à jour le produit</span>
                    <div wire:loading class="flex items-center">
                        <div class="animate-spin mr-2 size-4 border-[2px] border-current border-t-transparent text-white rounded-full" role="status" aria-label="chargement">
                            <span class="sr-only">Chargement...</span>
                        </div>
                        Traitement...
                    </div>
                </button>
            </form>
        </div>
        <!-- End Card -->
    </div>
    <!-- End Card Section -->
</div>
