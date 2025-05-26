<div>
    <div class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen py-6 sm:py-8 md:py-12">
        <div class="max-w-5xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-4 sm:mb-6 md:mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Mon Panier</h1>
                <div wire:loading class="animate-spin inline-block size-5 sm:size-6 border-[3px] border-current border-t-transparent text-blue-600 rounded-full" role="status" aria-label="loading">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 sm:p-4 rounded-md shadow-sm mb-4 sm:mb-6" role="alert">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm sm:text-base font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 sm:p-4 rounded-md shadow-sm mb-4 sm:mb-6" role="alert">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm sm:text-base font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Current Cart Items -->
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg overflow-hidden border border-gray-100">
                @if($cartItems->isEmpty())
                    <div class="text-center py-10 sm:py-12 md:py-16 px-4">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto rounded-full bg-blue-50 flex items-center justify-center mb-4 sm:mb-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-12 sm:w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-2">Votre panier est vide</h3>
                        <p class="text-sm sm:text-base text-gray-500 mb-6 sm:mb-8 max-w-md mx-auto">Ajoutez des produits à votre panier pour passer une commande</p>
                        <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                            <a href="/" class="inline-flex items-center px-4 py-2.5 sm:px-5 sm:py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm sm:text-base rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 transition-all transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="whitespace-nowrap">Continuer vos commandes</span>
                            </a>
                            <a href="{{ route('user.orders') }}" class="inline-flex items-center px-4 py-2.5 sm:px-5 sm:py-3 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm sm:text-base rounded-lg shadow-md hover:from-green-700 hover:to-green-800 transition-all transform hover:scale-105">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span class="whitespace-nowrap">Voir mes commandes</span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="border-b border-gray-100">
                        <div class="grid grid-cols-12 text-2xs sm:text-xs text-gray-500 uppercase tracking-wider px-3 sm:px-4 md:px-6 py-2 sm:py-3 bg-gray-50">
                            <div class="col-span-5 sm:col-span-6 flex items-center">Produit</div>
                            <div class="col-span-3 sm:col-span-3 text-center">Quantité</div>
                            <div class="col-span-4 sm:col-span-3 text-right">Actions</div>
                        </div>
                    </div>
                    
                    <ul class="divide-y divide-gray-100">
                        @foreach($cartItems as $item)
                            <li class="group hover:bg-blue-50 transition-colors duration-150">
                                <div class="grid grid-cols-12 gap-1.5 sm:gap-2 px-3 sm:px-4 md:px-6 py-3 sm:py-4">
                                    <!-- Product Image and Details -->
                                    <div class="col-span-5 sm:col-span-6 flex items-center space-x-2 sm:space-x-3 md:space-x-4">
                                        <div class="h-14 w-14 sm:h-16 sm:w-16 md:h-20 md:w-20 flex-shrink-0 overflow-hidden rounded-md sm:rounded-lg bg-gray-50 border border-gray-200 group-hover:border-blue-200 transition-colors">
                                            <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}" 
                                                class="h-full w-full object-contain object-center p-1">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-xs sm:text-sm md:text-base font-medium text-gray-900 truncate mb-0.5 sm:mb-1">{{ $item->product->name }}</h3>
                                            <p class="text-2xs sm:text-xs text-gray-500 line-clamp-1 sm:line-clamp-2">{{ $item->product->description }}</p>
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <div class="col-span-3 sm:col-span-3 flex justify-center items-center">
                                        <div class="relative flex items-center">
                                            <button type="button" class="rounded-l-md border border-gray-300 px-1.5 sm:px-2 py-0.5 sm:py-1 text-gray-600 hover:bg-gray-100 touch-manipulation" 
                                                    wire:click="updateQuantity({{ $item->id }}, {{ max(1, $item->quantity - 1) }})">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            <input type="number" 
                                                min="1" 
                                                value="{{ $item->quantity }}" 
                                                id="Line{{ $item->id }}Qty" 
                                                wire:input="updateQuantity({{ $item->id }}, $event.target.value)" 
                                                class="h-7 sm:h-8 w-8 sm:w-10 md:w-12 border-t border-b border-gray-300 text-center text-gray-900 text-xs sm:text-sm focus:outline-none" />
                                            <button type="button" class="rounded-r-md border border-gray-300 px-1.5 sm:px-2 py-0.5 sm:py-1 text-gray-600 hover:bg-gray-100 touch-manipulation"
                                                    wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="col-span-4 sm:col-span-3 flex justify-end items-center">
                                        <button wire:click="removeItem({{ $item->id }})" 
                                            class="inline-flex items-center justify-center rounded-md bg-red-50 px-2 sm:px-3 py-1.5 sm:py-2 text-2xs sm:text-xs font-medium text-red-700 hover:bg-red-100 transition-colors touch-manipulation">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4 mr-0.5 sm:mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span class="whitespace-nowrap">Supprimer</span>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="px-3 sm:px-4 md:px-6 py-3 sm:py-4 bg-gray-50 border-t border-gray-100">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-2xs sm:text-xs md:text-sm text-gray-600">
                                Affichage de <span class="font-medium">{{ $cartItems->firstItem() ?? 0 }}</span>
                                à <span class="font-medium">{{ $cartItems->lastItem() ?? 0 }}</span>
                                sur <span class="font-medium">{{ $cartItems->total() }}</span> articles
                            </div>
                            <div class="pagination-container text-xs sm:text-sm">
                                {{ $cartItems->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="px-3 sm:px-4 md:px-6 pt-4 sm:pt-5 md:pt-6 pb-5 sm:pb-6 md:pb-8 border-t border-gray-100 bg-gradient-to-b from-white to-blue-50">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 sm:mb-6">
                            <div class="text-lg sm:text-xl font-bold text-gray-900">
                                Total Articles: <span class="text-blue-700">{{ $cartItems->sum('quantity') }}</span>
                            </div>
                            <div class="text-xs sm:text-sm text-gray-500">
                                Toutes les commandes sont sujettes à disponibilité
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-center sm:justify-end gap-3 sm:gap-4">
                            <a  
                               href="/" 
                               class="inline-flex justify-center items-center px-4 py-2.5 sm:px-5 sm:py-3 border border-gray-300 shadow-sm text-xs sm:text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors touch-manipulation">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span class="whitespace-nowrap">Continuer vos achats</span>
                            </a>
                            
                            <button 
                                type="button"
                                wire:click="createCheckoutSession"
                                wire:loading.attr="disabled"
                                wire:target="createCheckoutSession"
                                class="inline-flex justify-center items-center px-4 py-2.5 sm:px-5 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs sm:text-sm font-medium rounded-lg shadow-md hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform hover:scale-105 touch-manipulation"
                                wire:dirty.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="createCheckoutSession" class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="whitespace-nowrap">Passer la commande</span>
                                </span>
                                <span wire:loading wire:target="createCheckoutSession" class="flex items-center">
                                    <svg class="animate-spin h-4 w-4 sm:h-5 sm:w-5 mr-1.5 sm:mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="whitespace-nowrap">Traitement en cours...</span>
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
