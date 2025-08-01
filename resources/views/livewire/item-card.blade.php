<div class="group bg-gray-100 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border-0 hover:border hover:border-yellow-700 transform hover:-translate-y-1">
    <a href="/product/{{$product->id}}/details" class="block">
        <div class="relative overflow-hidden">
            <div class="aspect-w-16 aspect-h-9">
                <img 
                    src="{{ $product->image ? Storage::url($product->image) : asset('images/placeholder-image.jpg') }}" 
                    alt="{{ $product->name }}"
                    class="rounded-t-lg object-contain w-full h-[140px] sm:h-[160px] md:h-[180px] p-2 transform group-hover:scale-105 transition-transform duration-300"
                    onerror="this.src='{{ asset('images/placeholder-image.jpg') }}'"
                >
            </div>
            <div class="absolute top-2 right-2">
                <div class="bg-blue-50 rounded-full p-1.5 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </div>
        </div>
        <div class="p-4">
            <h2 class="text-sm sm:text-base font-medium text-gray-800 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ $product->name }}</h2>
            <p class="mt-1 text-xs sm:text-sm text-gray-500 line-clamp-2">{{ $product->description }}</p>
            <div class="flex justify-between items-center mt-3">
                <div class="bg-green-200 px-2.5 py-1 rounded-lg">
                    <h2 class="text-xs sm:text-sm font-medium text-gray-800">{{ $product->category->name }}</h2>
                </div>
            </div>
        </div>
    </a>
    @if (auth()->check())
        @if (auth()->user()->status !== 'pending')
            <div class="px-4 pb-4">
                <button 
                    wire:click="addToCart({{ $product->id }})"
                    class="w-full flex gap-2 justify-center items-center rounded-lg bg-yellow-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transform transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <div wire:loading wire:target="addToCart" class="animate-spin inline-block size-4 border-[3px] border-current border-t-transparent text-white rounded-full" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                    <span>Ajouter à la commande</span>
                </button>
            </div>
        @else
            <div class="px-4 pb-4">
                <div class="w-full px-4 py-2.5 text-sm text-yellow-700 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>En attente d'approbation</span>
                </div>
            </div>
        @endif
    @else
        <div class="px-4 pb-4">
            <a  
                href="/auth/login"
                class="w-full flex gap-2 justify-center items-center rounded-lg bg-yellow-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 transform transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span>Se connecter</span>
            </a>
        </div>
    @endif
</div>
