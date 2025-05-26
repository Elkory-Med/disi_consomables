<div class="bg-gray-50 min-h-screen">
    <!-- Success Message Modal -->
    @if(session()->has('success') || session()->has('order_success'))
    <div 
        x-data="{ show: true }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        style="background-color: rgba(0,0,0,0.5);"
    >
        <div class="bg-white rounded-lg p-8 max-w-md w-full shadow-lg relative">
            <div class="text-center">
                <!-- Success Icon -->
                <div class="mb-4">
                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                
                <!-- Title -->
                <h3 class="text-lg font-medium text-gray-900 mb-4">Commande soumise</h3>
                
                <!-- Message -->
                <p class="text-sm text-gray-500 mb-6">
                    {{ session('success') ?? session('order_success') }}
                </p>
                
                <!-- Dismiss Button -->
                <button 
                    @click="show = false; {{ session()->forget(['success', 'order_success']) }}"
                    class="inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                >
                    J'ai compris
                </button>
            </div>
        </div>
    </div>
    @endif

    @if(session()->has('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4 py-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif
    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Mes Commandes</h2>
                
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" 
                        wire:model.live="search" 
                        placeholder="Rechercher une commande..." 
                        class="pl-14 pr-4 py-2 border border-gray-300 rounded-lg w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Orders List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($orders->isEmpty())
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune commande trouvée</h3>
                        <p class="mt-1 text-sm text-gray-500">Vos commandes apparaîtront ici une fois que vous en aurez passé.</p>
                    </div>
                @else
                    <!-- Items Per Page Selection -->
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center space-x-2">
                            <label for="perPage" class="text-sm text-gray-600">Afficher</label>
                            <select 
                                id="perPage" 
                                wire:model.live="perPage" 
                                class="form-select rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                            >
                                @foreach($perPageOptions as $value)
                                    <option value="{{ $value }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            <span class="text-sm text-gray-600">commandes par page</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-2 sm:px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commande #</th>
                                    <th class="px-2 sm:px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                    <th class="px-2 sm:px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-2 sm:px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-2 sm:px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #{{ $order->id }}
                                        </td>
                                        <td class="px-2 sm:px-4 md:px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $order->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $order->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $order->status === 'delivered' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                {{ $order->status === 'pending' ? 'En attente' : 
                                                   ($order->status === 'approved' ? 'Approuvé' : 
                                                   ($order->status === 'rejected' ? 'Rejeté' : 
                                                   ($order->status === 'delivered' ? 'Livrée' : $order->status))) }}
                                            </span>
                                        </td>
                                        <td class="px-2 sm:px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-2 sm:px-4 md:px-6 py-4 whitespace-nowrap">
                                            <button 
                                                wire:click.prevent="showOrderDetails({{ $order->id }})" 
                                                type="button"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                            >
                                                <svg class="-ml-0.5 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Voir détails
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-white">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showOrderDetailsFlag)
        <div 
            x-data="{ show: @entangle('showOrderDetailsFlag') }"
            x-show="show"
            x-on:open-modal.window="show = true"
            x-on:close-modal.window="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="relative z-50" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <!-- Background backdrop -->
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <!-- Modal panel -->
                    <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl max-h-[85vh] sm:max-h-[90vh] flex flex-col">
                        @if($selectedOrder)
                            <!-- Modal header -->
                            <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex-shrink-0">
                                <div class="flex items-start justify-between">
                                    <h3 class="text-base sm:text-lg font-semibold text-gray-900" id="modal-title">
                                        Détails commande #{{ $selectedOrder->id }}
                                    </h3>
                                    <button 
                                        wire:click="closeOrderDetails"
                                        class="rounded-md bg-gray-50 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        <span class="sr-only">Fermer</span>
                                        <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="px-4 sm:px-6 py-4 sm:py-5 overflow-y-auto flex-grow">
                                <div class="space-y-4 sm:space-y-6">
                                    <!-- Order Status -->
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-500">Statut:</span>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $selectedOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $selectedOrder->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $selectedOrder->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $selectedOrder->status === 'delivered' ? 'bg-blue-100 text-blue-800' : '' }}">
                                            {{ $selectedOrder->status === 'pending' ? 'En attente' : 
                                               ($selectedOrder->status === 'approved' ? 'Approuvé' : 
                                               ($selectedOrder->status === 'rejected' ? 'Rejeté' : 
                                               ($selectedOrder->status === 'delivered' ? 'Livrée' : $selectedOrder->status))) }}
                                        </span>
                                    </div>

                                    <!-- Order Items -->
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-4">Produits commandés</h4>
                                        <div class="space-y-3">
                                            @foreach($selectedOrder->orderItems as $item)
                                                <div class="flex items-center justify-between bg-white p-3 rounded-lg shadow-sm">
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-900">{{ $item->product->name }}</span>
                                                        <span class="text-sm text-gray-500">Quantité: {{ $item->quantity }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Order Date -->
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-500">Date de commande:</span>
                                        <span class="ml-2 text-sm text-gray-900">{{ $selectedOrder->created_at->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <!-- Admin Notes and History -->
                                    @if($selectedOrder->admin_notes || $selectedOrder->history->count() > 0)
                                        <div class="space-y-4">
                                            @if($selectedOrder->admin_notes)
                                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                                                    <h4 class="text-sm font-medium text-yellow-800">Notes administratives</h4>
                                                    <p class="mt-2 text-sm text-yellow-700">{{ $selectedOrder->admin_notes }}</p>
                                                </div>
                                            @endif

                                            @if($selectedOrder->history->count() > 0)
                                                <div class="bg-gray-50 rounded-lg p-4">
                                                    <h4 class="text-sm font-medium text-gray-900 mb-4">Historique de la commande</h4>
                                                    <div class="space-y-3">
                                                        @foreach($selectedOrder->history as $history)
                                                            <div class="flex items-start space-x-3 bg-white p-3 rounded-lg shadow-sm">
                                                                <div class="flex-1">
                                                                    <div class="flex items-center justify-between">
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                            {{ $history->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                                            {{ $history->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                                                            {{ $history->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                                                            {{ $history->status === 'delivered' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                                            {{ $history->status === 'pending' ? 'En attente' : 
                                                                            ($history->status === 'approved' ? 'Approuvé' : 
                                                                            ($history->status === 'rejected' ? 'Rejeté' : 
                                                                            ($history->status === 'delivered' ? 'Livrée' : $history->status))) }}
                                                                        </span>
                                                                        <span class="text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}</span>
                                                                    </div>
                                                                    @if($history->notes)
                                                                        <p class="mt-1 text-sm text-gray-600">{{ $history->notes }}</p>
                                                                    @endif
                                                                    @if($history->changedByUser)
                                                                        <p class="mt-1 text-xs text-gray-500">
                                                                            Par: {{ $history->changedByUser->name }}
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
