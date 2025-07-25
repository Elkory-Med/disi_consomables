<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Connexion</h2>
            <p class="text-sm text-gray-600 mt-1">Connectez-vous à votre compte</p>
        </div>

        <div>
            @if (session('status'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-100 border border-red-400 text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="login" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input wire:model="email" type="email" id="email" 
                        class="mt-1 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        required autofocus>
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                    <input wire:model="password" type="password" id="password" 
                        class="mt-1 block w-full px-3 py-2 text-sm rounded-lg border border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        required>
                    @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center">
                    <input wire:model="remember" type="checkbox" id="remember" 
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Se souvenir de moi</label>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <div wire:loading wire:target="login" class="animate-spin inline-block size-4 border-[3px] border-current border-t-transparent text-white rounded-full mr-2" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        Se connecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
