<div>
    <div class="max-w-full px-2 py-5 mx-auto">
        <!-- Card -->
        <div class="flex flex-col">
            <div class="-m-1">
                <div class="p-1 inline-block align-middle w-full">
                    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
                        <!-- Header -->
                        <div class="px-3 py-3 grid gap-2 md:flex md:justify-between md:items-center border-b border-gray-200">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">
                                    Utilisateurs
                                </h2>
                                <p class="text-sm text-gray-600">
                                    Gérer les utilisateurs, approuver ou rejeter les demandes d'inscription
                                </p>
                            </div>

                            <div>
                                <div class="inline-flex gap-x-2">
                                    <div class="flex items-center">
                                        <label for="perPage" class="sr-only">Nombre d'éléments par page</label>
                                        <select id="perPage" wire:model.live="perPage" class="py-1.5 px-2 pe-5 block w-full border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none">
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="50">50</option>
                                        </select>
                                    </div>
                                    <div class="relative">
                                        <label for="search" class="sr-only">Rechercher</label>
                                        <input type="text" wire:model.live.debounce.300ms="search" id="search" class="peer py-1.5 px-3 block w-full bg-gray-100 border-blue-500 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none" placeholder="Rechercher...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        @include('livewire.theaders.th',[
                                            'name' => 'name',
                                            'columnName' => 'Nom',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[11%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'username',
                                            'columnName' => 'Username',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[11%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'email',
                                            'columnName' => 'Email',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[14%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'matricule',
                                            'columnName' => 'Matricule',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[8%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'unite',
                                            'columnName' => 'Unité',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[11%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'administration',
                                            'columnName' => 'Direction',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[11%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'created_at',
                                            'columnName' => 'Créé',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[9%] px-1.5 py-1.5'
                                        ])
                                        @include('livewire.theaders.th',[
                                            'name' => 'status',
                                            'columnName' => 'Statut',
                                            'sortBy' => $sortBy,
                                            'sortDir' => $sortDirection,
                                            'class' => 'w-[8%] px-1.5 py-1.5'
                                        ])
                                        <th scope="col" class="w-[8%] px-1.5 py-1.5 text-start text-sm font-medium text-gray-500">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($users as $user)
                                        <tr wire:key="{{$user->id}}" class="hover:bg-gray-50">
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm font-semibold text-gray-800">{{ $user->name }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-800">{{ $user->username }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-800">{{ $user->email }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-800">{{ $user->matricule }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-800">{{ $user->unite }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-800">{{ $user->administration }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5 truncate">
                                                <span class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($user->created_at)->locale('fr')->format('d/m/y') }}</span>
                                            </td>
                                            <td class="px-1.5 py-1.5">
                                                @if($user->status === 'approved')
                                                    <span class="py-0.5 px-1.5 inline-flex items-center gap-x-0.5 text-xs font-medium bg-teal-100 text-teal-800 rounded-full">
                                                        Approuvé
                                                    </span>
                                                @elseif($user->status === 'rejected')
                                                    <span class="py-0.5 px-1.5 inline-flex items-center gap-x-0.5 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                        Rejeté
                                                    </span>
                                                @else
                                                    <span class="py-0.5 px-1.5 inline-flex items-center gap-x-0.5 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                        En attente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-1.5 py-1.5 w-[8%] max-w-[8%]">
                                                @if($user->status === 'pending')
                                                    <div class="flex justify-center w-full">
                                                        <button wire:click="$set('selectedUser', {{ $user->id }})" class="py-1 px-2 text-xs font-medium rounded-lg border border-blue-200 bg-blue-100 text-blue-800 shadow-sm hover:bg-blue-200 disabled:opacity-50 disabled:pointer-events-none whitespace-nowrap">
                                                            Exam.
                                                        </button>
                                                    </div>
                                                @elseif($user->status === 'approved')
                                                    <div class="flex justify-center w-full">
                                                        <button 
                                                            disabled
                                                            class="py-1 px-2 text-xs font-medium rounded-lg border border-gray-300 bg-gray-200 text-gray-500 shadow-sm cursor-not-allowed whitespace-nowrap">
                                                            Exam.
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer -->
                        <div class="px-3 py-3 grid gap-2 md:flex md:justify-between md:items-center border-t border-gray-200">
                            <div class="flex gap-2">
                                <label class="w-20 text-sm font-medium text-gray-400">Par Page</label>
                                <select wire:model.live='perPage'
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-1.5">
                                    <option value="5">5</option>
                                    <option value="7">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <!-- the links to different pages -->
                            <div class="pagination-container text-sm">
                                {{ $users->links() }}
                            </div>
                        </div>
                        <!-- End Footer -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Card -->
    </div>

    <!-- Approval Modal -->
    @if($showApprovalModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 py-6">
                <div class="fixed inset-0 bg-gray-700 bg-opacity-50 transition-opacity"></div>
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-xl bg-white p-6 text-left shadow-xl transition-all">
                    <div class="text-lg font-medium leading-6 text-gray-900">
                        Confirmer l'approbation
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Êtes-vous sûr de vouloir approuver cet utilisateur ?
                        </p>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" wire:click="closeModals" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Annuler
                        </button>
                        <button type="button" wire:click="approveUser" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Approuver
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Rejection Modal -->
    @if($showRejectionModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 py-6">
                <div class="fixed inset-0 bg-gray-700 bg-opacity-50 transition-opacity"></div>
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-xl bg-white p-6 text-left shadow-xl transition-all">
                    <div class="text-lg font-medium leading-6 text-gray-900">
                        Confirmer le rejet
                    </div>
                    <div class="mt-2">
                        <label for="rejectionReason" class="block text-sm font-medium text-gray-700">
                            Raison du rejet (optionnel)
                        </label>
                        <textarea wire:model="rejectionReason" id="rejectionReason" rows="3" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                            placeholder="Veuillez indiquer la raison du rejet..."></textarea>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" wire:click="closeModals" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                            Annuler
                        </button>
                        <button type="button" wire:click="rejectUser" class="inline-flex justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Rejeter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit User Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 py-6">
                <div class="fixed inset-0 bg-gray-700 bg-opacity-50 transition-opacity"></div>
                <div class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white p-6 text-left shadow-xl transition-all">
                    <div class="text-lg font-medium leading-6 text-gray-900 mb-4">
                        Modifier l'utilisateur
                    </div>
                    
                    <form wire:submit.prevent="saveUser" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" wire:model="name" id="name" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Nom complet">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Username -->
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
                                <input type="text" wire:model="username" id="username" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Nom d'utilisateur">
                                @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" wire:model="email" id="email" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Email">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Matricule -->
                            <div>
                                <label for="matricule" class="block text-sm font-medium text-gray-700">Matricule</label>
                                <input type="text" wire:model="matricule" id="matricule" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Matricule">
                                @error('matricule') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Unite -->
                            <div>
                                <label for="unite" class="block text-sm font-medium text-gray-700">Unité</label>
                                <input type="text" wire:model="unite" id="unite" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Unité">
                                @error('unite') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Administration -->
                            <div>
                                <label for="administration" class="block text-sm font-medium text-gray-700">Direction</label>
                                <input type="text" wire:model="administration" id="administration" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Direction">
                                @error('administration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe (laisser vide pour ne pas changer)</label>
                                <input type="password" wire:model="password" id="password" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Nouveau mot de passe">
                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Password Confirmation -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmation du mot de passe</label>
                                <input type="password" wire:model="password_confirmation" id="password_confirmation" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                    placeholder="Confirmer le nouveau mot de passe">
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end space-x-2">
                            <button type="button" wire:click="closeModals" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Annuler
                            </button>
                            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Examine User Modal -->
    @if($selectedUser && !$showApprovalModal && !$showRejectionModal && !$showEditModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex min-h-screen items-center justify-center px-4 py-6">
                <div class="fixed inset-0 bg-gray-700 bg-opacity-50 transition-opacity"></div>
                <div class="relative w-full max-w-md transform overflow-hidden rounded-xl bg-white p-6 text-left shadow-xl transition-all">
                    <div class="text-lg font-medium leading-6 text-gray-900 mb-4">
                        Options pour l'utilisateur #{{ $selectedUser }}
                    </div>
                    
                    <div class="space-y-4">
                        <p class="text-sm text-gray-500">
                            Que souhaitez-vous faire avec cet utilisateur ?
                        </p>
                        
                        <div class="grid grid-cols-1 gap-3">
                            <button 
                                wire:click="openApprovalModal({{ $selectedUser }})" 
                                class="w-full inline-flex justify-center items-center py-2 px-4 border border-green-200 bg-green-100 text-green-800 rounded-md hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 text-sm font-medium"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Approuver l'utilisateur
                            </button>
                            
                            <button 
                                wire:click="openRejectionModal({{ $selectedUser }})" 
                                class="w-full inline-flex justify-center items-center py-2 px-4 border border-red-200 bg-red-100 text-red-800 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 text-sm font-medium"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                                Rejeter l'utilisateur
                            </button>
                            
                            <button 
                                wire:click="openEditModal({{ $selectedUser }})" 
                                class="w-full inline-flex justify-center items-center py-2 px-4 border border-blue-200 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 text-sm font-medium"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                Modifier l'utilisateur
                            </button>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button 
                                wire:click="$set('selectedUser', null)" 
                                class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                            >
                                Fermer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Notifications -->
    <div class="fixed bottom-4 right-4 z-50">
        <div x-data="{ show: false, message: '' }" 
             x-on:success.window="show = true; message = $event.detail; setTimeout(() => { show = false }, 3000)"
             x-on:error.window="show = true; message = $event.detail; setTimeout(() => { show = false }, 3000)">
            <div x-show="show" x-cloak
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 class="rounded-lg p-4" 
                 :class="{ 'bg-green-100 text-green-700': message.includes('succès'), 'bg-red-100 text-red-700': message.includes('erreur') }">
                <span x-text="message"></span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('success', (message) => {
                // You can implement your preferred notification system here
                alert(message);
            });

            @this.on('error', (message) => {
                // You can implement your preferred notification system here
                alert(message);
            });
        });
    </script>

    <style>
        /* Custom pagination styling */
        .pagination-container nav div span span span {
            padding: 0.35rem 0.7rem !important;
            font-size: 0.8rem !important;
        }
        .pagination-container nav div span a {
            padding: 0.35rem 0.7rem !important;
            font-size: 0.8rem !important;
        }
    </style>
</div>