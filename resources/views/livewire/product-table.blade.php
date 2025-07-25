<div>
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <!-- Card -->
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">Produits</h2>
                                <p class="text-sm text-gray-600">Ajouter Produits, modifier et plus.</p>
                                @if (session()->has('message'))
                                    <div class="mt-2 p-2 rounded {{ session('message-type') === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                        <div class="flex items-center gap-2">
                                            @if(session('message-type') === 'success')
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-green-700">{{ session('message') }}</span>
                                            @else
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                <span class="text-sm font-medium text-red-700">{{ session('message') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
      
                            <div class="inline-flex gap-x-2">
                                <div class="max-w-md">
                                    <input type="search" 
                                           wire:model.live.debounce.300ms="search" 
                                           class="peer py-3 px-4 block w-full bg-gray-100 border-blue-500 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" 
                                           placeholder="Rechercher Produit">
                                </div>
      
                                <a  
                                   class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none" 
                                   href="{{ route('admin.products.add') }}">
                                    <svg class="size-3" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M2.63452 7.50001L13.6345 7.5M8.13452 13V2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    Ajouter Produit
                                </a>
                            </div>
                        </div>

                        <div wire:loading.class="opacity-50">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nom produit</th>
                                        <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categorie</th>
                                        <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Créé</th>
                                        <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    @forelse ($products as $product)
                                        <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50">
                                            <td class="px-2 py-2">
                                                <div class="flex items-center gap-x-3">
                                                    <img class="size-[38px] rounded-full object-cover" src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                                    <span class="text-sm font-semibold text-gray-800">
                                                        {{ str($product->name)->words(3) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2">
                                                <span class="text-sm text-gray-800">
                                                    {{ str($product->description)->words(8) }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2">
                                                <span class="py-1 px-2 text-xs font-medium bg-teal-100 text-teal-800 rounded-full">
                                                    {{ $product->category->name }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2">
                                                <span class="text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($product->created_at)->locale('fr')->translatedFormat('D d M Y, H:i') }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2">
                                                <div class="flex gap-2">
                                                    <button wire:click="edit({{ $product->id }})" 
                                                            class="py-1 px-2 text-xs rounded bg-blue-500 hover:bg-blue-700 text-white">
                                                        Modifier
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $product->id }})" 
                                                            class="py-1 px-2 text-xs rounded bg-red-500 hover:bg-red-700 text-white">
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-2 py-8 text-center text-gray-500">
                                                Aucune donnée trouvée
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Delete Modal -->
    @if($isModalOpen)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="mb-4 text-xl font-medium text-center text-gray-900">Confirmer la suppression</h3>
                        <p class="mb-6 text-sm text-center text-gray-500">
                            Êtes-vous sûr de vouloir supprimer ce produit ? Cette action ne peut pas être annulée.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button wire:click="closeModal" 
                                    class="py-1 px-2 text-xs rounded bg-gray-500 hover:bg-gray-700 text-white">
                                Annuler
                            </button>
                            <button wire:click="delete" 
                                    class="py-1 px-2 text-xs rounded bg-red-500 hover:bg-red-700 text-white">
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>