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
                                <h2 class="text-xl font-semibold text-gray-800">
                                    Gestion des Commandes
                                </h2>
                                <p class="text-sm text-gray-600">
                                    Examiner et approuver les commandes des clients
                                </p>
                            </div>
                            <div class="inline-flex gap-x-2">
                                <div class="max-w-md space-y-3">
                                    <div class="flex items-center gap-x-2">
                                        <input type="search" wire:model.live.debounce.300ms="search" 
                                        class="peer py-3 px-4 block w-full bg-gray-100 border-blue-500 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" 
                                            placeholder="Rechercher des commandes...">
                                    </div>
                                </div>
                            </div>

                            @if(session()->has('message'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('message') }}</span>
                                </div>
                            @endif

                            @if(session()->has('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <span class="block sm:inline">{{ session('error') }}</span>
                                </div>
                            @endif
                        </div>
                        <!-- End Header -->

                        <!-- Loading Indicator -->
                        <div wire:loading wire:target="getApprovedOrdersProperty, search, setSortBy, updatedPerPage" class="w-full flex justify-center items-center p-4">
                            <div class="animate-spin inline-block w-6 h-6 border-[3px] border-current border-t-transparent text-blue-600 rounded-full" role="status" aria-label="loading">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <span class="ml-2 text-gray-600">Chargement des données...</span>
                        </div>

                        <!-- Table -->
                        <table class="min-w-full divide-y divide-gray-200" style="table-layout: auto; width: 100%;">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('id')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                ID de Commande
                                                @if ($sortBy === 'id')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('unite')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                Unite
                                                @if ($sortBy === 'unite')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('matricule')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                Matricule
                                                @if ($sortBy === 'matricule')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('username')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                Username
                                                @if ($sortBy === 'username')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('administration')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                Direction
                                                @if ($sortBy === 'administration')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <button wire:click="setSortBy('created_at')" class="text-xs font-semibold uppercase tracking-wide text-gray-800 hover:text-blue-500">
                                                Date
                                                @if ($sortBy === 'created_at')
                                                    <span class="text-blue-500 ml-1">{{ $sortDir === 'DESC' ? '↓' : '↑' }}</span>
                                                @endif
                                            </button>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                Produits
                                            </span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                Quantité
                                            </span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                Statut
                                            </span>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-1 py-2 text-start">
                                        <div class="flex items-center gap-x-2">
                                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                Actions
                                            </span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-200">
                                @forelse ($approvedOrders as $order)
                                <tr class="hover:bg-gray-100" wire:key="order-{{ $order->id }}">
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">#{{ $order->id }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">{{ $order->user->unite }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">{{ $order->user->matricule }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">{{ $order->user->username }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">{{ $order->user->administration }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">{{ $order->created_at->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">
                                            @foreach($order->orderItems as $item)
                                                {{ $item->product->name }}<br>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm text-gray-600">
                                            @foreach($order->orderItems as $item)
                                                {{ $item->quantity }}<br>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                        <div class="text-sm">
                                            @if($order->status === 'delivered')
                                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Livré
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 py-0.5 px-2 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    En attente de livraison
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="h-px w-px whitespace-nowrap px-1 py-2 text-end">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" 
                                                @if($order->status !== 'delivered')
                                                    wire:click="markAsDelivered({{ $order->id }})"
                                                    class="py-1 px-2 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent bg-blue-500 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none"
                                                @else
                                                    disabled
                                                    class="py-1 px-2 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent bg-gray-400 text-white cursor-not-allowed"
                                                @endif
                                            >
                                                Délivré
                                            </button>
                                            <button type="button"
                                                wire:click="showSerialNumberInputs({{ $order->id }})"
                                                class="py-1 px-2 inline-flex items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent bg-blue-500 text-white hover:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                Imp
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Aucune commande trouvée
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>      
                        </table>
                        <!-- End Table -->
                        <!-- Footer -->
                        <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Par Page</label>
                                <select wire:model.live="perPage" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                    <option value="200">200</option>
                                </select>
                            </div>
                            
                            <div class="text-sm text-gray-500">
                                Affichage de {{ $approvedOrders->firstItem() ?? 0 }} à {{ $approvedOrders->lastItem() ?? 0 }} sur {{ $approvedOrders->total() }} commandes
                            </div>
                            
                            <!-- Pagination Controls -->
                            <div class="flex items-center justify-center">
                                {{ $approvedOrders->links() }}
                            </div>
                        </div>
                        <!-- End Footer -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Serial Numbers -->
    <div class="fixed inset-0 z-50 overflow-y-auto" style="display: {{ $showSerialInputs ? 'block' : 'none' }}">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                Entrer les numéros de série
                            </h3>
                            @if($currentOrder)
                                @foreach($currentOrder->orderItems as $index => $item)
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ $item->product->name }}
                                        </label>
                                        @for($i = 0; $i < $item->quantity; $i++)
                                            <div class="mb-2">
                                                <label class="block text-xs text-gray-500 mb-1">
                                                    Article {{ $i + 1 }} sur {{ $item->quantity }}
                                                </label>
                                                <input
                                                    type="text"
                                                    wire:model="serialNumbers.{{ $item->id }}.{{ $i }}"
                                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                                    placeholder="Numéro de série pour l'article {{ $i + 1 }}"
                                                >
                                            </div>
                                        @endfor
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        wire:click="generateInvoice"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Générer PDF
                    </button>
                    <button 
                        wire:click="$set('showSerialInputs', false)"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Annuler
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
