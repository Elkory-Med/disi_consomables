<div>
    @if($show)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-white px-4 pb-6 pt-6 sm:p-8 sm:pb-6">
                    <div class="w-full">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4 text-center">Inscription</h3>
                        
                        <!-- Register Form -->
                        <form wire:submit="register" class="space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="name" class="block text-xs font-medium text-gray-700">Nom complet</label>
                                    <input wire:model="name" type="text" id="name" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="username" class="block text-xs font-medium text-gray-700">Nom d'utilisateur</label>
                                    <input wire:model="username" type="text" id="username" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                    @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-medium text-gray-700">Email</label>
                                <input wire:model="email" type="email" id="email" 
                                    class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="administration" class="block text-xs font-medium text-gray-700">Direction</label>
                                <input wire:model="administration" type="text" id="administration" 
                                    class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                @error('administration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="unite" class="block text-xs font-medium text-gray-700">Unité</label>
                                    <input wire:model="unite" type="text" id="unite" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                    @error('unite') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="matricule" class="block text-xs font-medium text-gray-700">Matricule</label>
                                    <input wire:model="matricule" type="text" id="matricule" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                    @error('matricule') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="password" class="block text-xs font-medium text-gray-700">Mot de passe</label>
                                    <input wire:model="password" type="password" id="password" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-xs font-medium text-gray-700">Confirmer</label>
                                    <input wire:model="password_confirmation" type="password" id="password_confirmation" 
                                        class="mt-0.5 block w-full rounded-md bg-gray-50 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2">
                                </div>
                            </div>

                            <button type="submit" 
                                class="w-full flex justify-center py-1 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                S'inscrire
                            </button>
                        </form>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6">
                    <button wire:click="toggleModal" type="button" 
                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-1 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
