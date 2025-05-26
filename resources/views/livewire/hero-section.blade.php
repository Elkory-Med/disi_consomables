<div>
  <section class="bg-gray-50 relative overflow-hidden">
    @if(!auth()->check() || (auth()->check() && auth()->user()->status !== 'pending'))
    <div class="relative mx-auto max-w-screen-xl px-4 py-12 sm:py-16 md:py-20 lg:flex lg:items-center">
      <div class="mx-auto max-w-xl text-center">
        <h1 class="text-3xl font-black sm:text-5xl text-blue-600">
          DISI COMMANDES
          <strong class="font-extrabold text-gray-800 sm:block text-xl mt-0 sm:mt-1"> DISI SOMELEC - MATERIEL INFORMATIQUE </strong>
        </h1>

        <p class="mt-1 sm:mt-2 text-gray-600 text-sm sm:text-base">
          VOUS POUVEZ CONSULTER LE STOCK ET PASSER VOS COMMANDES SUR LES PRODUITS
        </p>

        <div class="mt-8 sm:mt-10 flex flex-wrap justify-center gap-4">
          @if (auth()->check())
            <a
              class="block w-full rounded-lg bg-blue-600 px-8 sm:px-12 py-3 text-sm font-medium text-white shadow-lg hover:bg-blue-700 transform transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto"
              href="/my-orders"
            >
              <span class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Suivi de vos commandes
              </span>
            </a>
          @else
            <button
              wire:click="$dispatch('toggle-auth-modal')"
              class="block w-full rounded-lg bg-red-600 px-8 sm:px-12 py-3 text-sm font-medium text-white shadow-lg hover:bg-red-700 transform transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto"
            >
              <span class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Créer un compte
              </span>
            </button>
          @endif

          <a
            class="block w-full rounded-lg bg-white px-8 sm:px-12 py-3 text-sm font-medium text-yellow-700 shadow-lg border border-gray-100 hover:bg-gray-50 hover:text-blue-600 transform transition-all duration-300 hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:w-auto"
            href="all/products"
          >
            <span class="flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              Explorer Plus
            </span>
          </a>
        </div>
      </div>
    </div>
    @endif

    <!-- Auth Modal -->
    @if(!auth()->check())
      <livewire:auth.auth-modal />
    @endif

    <!-- Pending User Notification -->
    <livewire:pending-user-notification />
  </section>
</div>