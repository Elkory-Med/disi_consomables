<div>
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex gap-5 p-20">
        <img 
            src="{{ $product->image ? Storage::url($product->image) : asset('images/placeholder-image.jpg') }}" 
            alt="{{ $product->name }}"
            class="rounded-t-lg object-contain w-[200px] h-[200px]"
            onerror="this.src='{{ asset('images/placeholder-image.jpg') }}'"
        >
        <div>
            <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
            <p class="text-gray-500">{{ $product->description }}</p>
            <div class="flex gap-10">
                <div class="bg-green-200 p-1 rounded-md">
                    <h2 class="text-1xl ">{{ $product->category->name }}</h2>
                </div>
            </div>
            <div class="my-3">
            @if (auth()->check())
                @if (auth()->user()->status !== 'pending')
                    <button wire:click="addToCart({{ $product->id }})">
                        <div class="flex gap-2 justify-center w-full rounded bg-blue-600 px-12 py-2 text-sm font-medium text-white text-center shadow hover:bg-blue-700 focus:outline-none focus:ring active:bg-blue-500 sm:w-auto">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                            </svg>
                            <span>Ajouter à la commande</span>
                        </div>
                    </button>
                @else
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">Votre compte n'est pas encore approuvé. Vous ne pouvez pas passer des commandes pour le moment.</span>
                    </div>
                @endif
            @else
                <a href='/auth/login'>
                    <div class="flex gap-2 justify-center w-full rounded bg-blue-600 px-12 py-2 text-sm font-medium text-white text-center shadow hover:bg-blue-700 focus:outline-none focus:ring active:bg-blue-500 sm:w-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 13.5 3 3m0 0 3-3m-3 3v-6m1.06-4.19-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                        </svg>
                        <span>Se connecter pour commander</span>
                    </div>
                </a>
            @endif
            </div>
        </div>
    </div>
    <div class="my-5 px-20 pt-5">
        <h2 class="text-2xl font-medium">Produits Similaires</h2>
        <livewire:product-listing :category_id="$product->category_id" :current_product_id="$product->id"/>
    </div>
</div>
