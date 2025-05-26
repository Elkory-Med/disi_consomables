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
                                <a href="{{ route('orders.delivered') }}" class="inline-flex justify-center items-center gap-x-1 rounded-md px-3 py-2 text-sm font-semibold bg-blue-600 text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 ease-in-out">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Détails
                                </a>
                                <div class="max-w-md space-y-3">
                                    <input type="search" wire:model.live.debounce.300ms="search" 
                                    class="peer py-3 px-4 block w-full bg-gray-100 border-blue-500 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" 
                                        placeholder="Rechercher des commandes...">
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

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th scope="col" class="px-1 sm:px-2 md:px-3 py-2 text-start">
                                            <div class="flex items-center gap-x-2">
                                                <span class="text-xs font-semibold text-gray-700">ID</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-1 py-2 text-start">
                                            <button type="button" wire:click="setSortBy('unite')" class="group inline-flex items-center gap-x-2">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                    Unite
                                                </span>
                                                @if ($sortBy === 'unite')
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        @if ($sortDir === 'ASC')
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-1 py-2 text-start">
                                            <button type="button" wire:click="setSortBy('matricule')" class="group inline-flex items-center gap-x-2">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                    Matricule
                                                </span>
                                                @if ($sortBy === 'matricule')
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        @if ($sortDir === 'ASC')
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-1 py-2 text-start">
                                            <button type="button" wire:click="setSortBy('username')" class="group inline-flex items-center gap-x-2">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                    Username
                                                </span>
                                                @if ($sortBy === 'username')
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        @if ($sortDir === 'ASC')
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-1 py-2 text-start">
                                            <button type="button" wire:click="setSortBy('administration')" class="group inline-flex items-center gap-x-2">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                    Direction
                                                </span>
                                                @if ($sortBy === 'administration')
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        @if ($sortDir === 'ASC')
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
                                        </th>
                                        <th scope="col" class="px-1 py-2 text-start">
                                            <button type="button" wire:click="setSortBy('created_at')" class="group inline-flex items-center gap-x-2">
                                                <span class="text-xs font-semibold uppercase tracking-wide text-gray-800">
                                                    Date
                                                </span>
                                                @if ($sortBy === 'created_at')
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        @if ($sortDir === 'ASC')
                                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        @else
                                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        @endif
                                                    </svg>
                                                @endif
                                            </button>
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
                                                   ACTION
                                                </span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($orders as $order)
                                    <tr class="hover:bg-gray-100">
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
                                            <div class="text-sm text-gray-500">
                                                {{ $order->created_at->format('d') }} {{ ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'][$order->created_at->format('n') - 1] }} {{ $order->created_at->format('Y H:i') }}
                                            </div>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                            <div class="text-sm text-gray-600">
                                                @foreach ($order->order_items as $item)
                                                    <div>
                                                        {{ $item->product->name }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                            <div class="text-sm text-gray-600">
                                                @foreach ($order->order_items as $item)
                                                    <div>{{ $item->quantity }}</div>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap px-1 py-2">
                                            <span @class([
                                                'py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-lg',
                                                'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                                'bg-green-100 text-green-800' => $order->status === 'approved',
                                                'bg-red-100 text-red-800' => $order->status === 'rejected',
                                                'bg-blue-100 text-blue-800' => $order->status === 'delivered',
                                            ])>
                                                {{ $order->status_label }}
                                            </span>
                                        </td>
                                        <td class="h-px w-px whitespace-nowrap px-1 py-2 text-end">
                                            @if($order->status === 'pending')
                                                <button type="button" 
                                                    wire:click="selectOrder({{ $order->id }})" 
                                                    class="inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-2 py-1 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                                                    Examiner
                                                </button>
                                            @else
                                                <button type="button" 
                                                    disabled
                                                    class="inline-flex items-center justify-center rounded-md border border-gray-300 shadow-sm px-2 py-1 bg-gray-200 text-base font-medium text-gray-500 cursor-not-allowed sm:text-sm">
                                                    Examiner
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>      
                            </table>
                        </div>
                        <!-- End Table -->
                        <!-- Footer -->
                        <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-t border-gray-200">
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Par Page</label>
                                <select wire:model.live="perPage" 
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="inline-flex gap-x-2">
                                <!-- Pagination -->
                                {{ $orders->links() }}
                            </div>
                        </div>
                        <!-- End Footer -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Modal -->
        @if($selectedOrderId)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-2 sm:p-6 sm:pb-3 border-b border-gray-200">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-2 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-semibold text-gray-900" id="modal-title">
                                    Examiner la Commande #{{ $selectedOrderId }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white px-4 py-3 sm:p-6 sm:pb-4">
                        <!-- Admin Notes Section -->
                        <div class="mt-2">
                            <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-1">Notes de l'administrateur</label>
                            <textarea 
                                id="admin_notes" 
                                wire:model="adminNotes" 
                                rows="2" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 text-sm"
                                placeholder="Entrez des notes pour cette commande (facultatif)"
                            ></textarea>
                        </div>

                        <!-- Approval/Rejection Buttons with Notes -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($selectedOrder)
                                <button 
                                    wire:click="addNoteOnly({{ $selectedOrderId }})" 
                                    class="inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-3 py-1.5 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-colors"
                                >
                                    Ajouter une note
                                </button>
                            
                                @if($selectedOrder->status === 'pending')
                                    <button 
                                        wire:click="approveOrder({{ $selectedOrderId }})" 
                                        class="inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-3 py-1.5 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500 transition-colors"
                                    >
                                        Approuver
                                    </button>
                                    <button 
                                        wire:click="rejectOrder({{ $selectedOrderId }})" 
                                        class="inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-3 py-1.5 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-500 transition-colors"
                                    >
                                        Rejeter
                                    </button>
                                @else
                                    <button 
                                        disabled
                                        class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-gray-200 text-sm font-medium text-gray-500 cursor-not-allowed"
                                    >
                                        Approuver
                                    </button>
                                    <button 
                                        disabled
                                        class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-gray-200 text-sm font-medium text-gray-500 cursor-not-allowed"
                                    >
                                        Rejeter
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse sm:gap-2 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-3 py-1.5 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
