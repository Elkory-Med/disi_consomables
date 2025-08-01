<div>
    @if(auth()->check() && auth()->user()->status === 'pending')
      <div 
        x-data="{ show: true }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
        style="background-color: rgba(0,0,0,0.5);"
      >
        <div class="bg-white rounded-lg p-8 max-w-md w-full shadow-lg relative">
          <div class="text-center">
            <!-- Warning Icon -->
            <div class="mb-4">
              <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-lg font-medium text-gray-900 mb-4">Compte en attente d'approbation</h3>
            
            <!-- Message -->
            <p class="text-sm text-gray-500 mb-6">
              Votre compte est actuellement en attente d'approbation par l'administrateur. Une fois approuvé :
            </p>
            
            <!-- Bullet Points -->
            <ul class="text-sm text-gray-500 mb-6 text-left list-disc pl-4 space-y-2">
              <li>Vous recevrez un e-mail de confirmation</li>
              <li>Vous aurez accès à toutes les fonctionnalités de la plateforme</li>
              <li>Vous pourrez passer des commandes</li>
            </ul>
            
            <!-- Dismiss Button -->
            <button 
              @click="show = false"
              class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              J'ai compris
            </button>
          </div>
        </div>
      </div>
    @endif
</div>
