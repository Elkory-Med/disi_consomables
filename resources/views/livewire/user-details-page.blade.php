<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Détails de la Commande</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Détails de la commande et des produits achetés.</p>
        </div>

        <div class="border-t border-gray-200">
            <dl>
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Commande #</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $order->id }}</dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Statut</dt>
                    <dd class="mt-1 sm:col-span-2 sm:mt-0">
                        <span @class([
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                            'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                            'bg-green-100 text-green-800' => $order->status === 'approved',
                            'bg-red-100 text-red-800' => $order->status === 'rejected',
                            'bg-blue-100 text-blue-800' => $order->status === 'delivered',
                        ])>
                            {{ $order->status_label }}
                        </span>
                    </dd>
                </div>

                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Date de Commande</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $order->created_at->format('Y-m-d H:i') }}</dd>
                </div>

                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Articles Commandés</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                        <ul class="divide-y divide-gray-200">
                            @foreach($order->orderItems as $item)
                                <li class="py-3 flex justify-between">
                                    <div class="flex items-center">
                                        <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-auto object-contain rounded">
                                        <div class="ml-4">
                                            <div class="flex items-center justify-between">
                                                <p class="text-sm">{{ $item->product->name }} x {{ $item->quantity }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </dd>
                </div>

                @if($order->admin_notes)
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Notes Admin</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">{{ $order->admin_notes }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>
