<header class="bg-white shadow-sm">
  <div class="mx-auto flex h-16 max-w-screen-xl items-center gap-8 px-4 sm:px-6 lg:px-8">
    <a class="block text-teal-600" href="/">
      <span class="sr-only">Home</span>
      <img src={{asset('images/ac54848a-a2c6-4bf4-89e7-3b37aba8d2e6.png')}} alt="product-image" class="rounded object-cover h-10 w-auto sm:h-12" height="48" width="36">
    </a>

    <div class="flex flex-1 items-center justify-end md:justify-between">
      <nav aria-label="Global" class="hidden md:block">
        <div class="flex justify-between items-center">
          <ul class="flex space-x-4">
            <li>
              <a class="text-gray-500 {{Request::is('/') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/" onclick="window.location.href='/'"> Accueil</a>
            </li>
            <li>
              <a class="text-gray-500 {{Request::is('all/products') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/all/products" onclick="window.location.href='/all/products'"> Découvrir plus</a>
            </li>

            <li>
              <a class="text-gray-500 {{Request::is('about') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/about" onclick="window.location.href='/about'">  A propos  </a>
            </li>

            <li>
              <a class="text-gray-500 {{Request::is('contacts') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/contacts" onclick="window.location.href='/contacts'"> Contact </a>
            </li>
          </ul>
        </div>
      </nav>

      <div class="flex items-center gap-4">
        <div class="sm:flex sm:gap-4">
          @if (auth()->check())
              @if (Auth::user()->status !== 'pending')
                <div class="hidden sm:flex items-center">
                  <span class="mr-2 text-sm text-gray-600 dark:text-neutral-400">Bien venu, {{ Auth::user()->username }}</span>
                  @if (Auth::user()->role !== 1) 
                        <livewire:shopping-cart-icon />
                    @endif
                </div>
              @else
                <div class="hidden sm:flex items-center text-yellow-600">
                  <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  <span class="text-sm">En attente</span>
                </div>
              @endif
              <a href="{{ route('auth.logout') }}" onclick="window.location.href='{{ route('auth.logout') }}'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 sm:size-7 hover:text-red-700">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                </svg>
              </a>
          @else
            <a
              class="block rounded-md bg-blue-600 px-4 py-2 text-xs sm:text-sm font-medium text-white transition hover:bg-blue-700"
              href="/auth/login"
              onclick="window.location.href='/auth/login'"
            >
              Connexion
            </a>
          @endif
        </div>

        <button
          x-data="{ open: false }"
          @click.prevent="open = !open"
          class="block rounded bg-gray-100 p-2.5 text-gray-600 transition hover:text-gray-600/75 md:hidden"
        >
          <span class="sr-only">Toggle menu</span>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            x-show="!open"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
            x-show="open"
            style="display: none;"
          >
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
          
          <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="absolute inset-x-0 top-[65px] z-50 w-full bg-white py-4 shadow-lg"
            style="display: none;"
          >
            <div class="px-6 space-y-4">
              <a class="block text-gray-500 {{Request::is('/') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/" onclick="window.location.href='/'"> Accueil</a>
              <a class="block text-gray-500 {{Request::is('all/products') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/all/products" onclick="window.location.href='/all/products'"> Découvrir plus</a>
              <a class="block text-gray-500 {{Request::is('about') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/about" onclick="window.location.href='/about'"> A propos</a>
              <a class="block text-gray-500 {{Request::is('contacts') ? 'text-gray-700 font-bold' : ''}} transition hover:text-gray-500/75 cursor-pointer" href="/contacts" onclick="window.location.href='/contacts'"> Contact</a>
            
              @if (auth()->check() && Auth::user()->status !== 'pending')
                <div class="flex items-center mt-4 border-t pt-4 border-gray-100">
                  <span class="text-sm text-gray-600">{{ Auth::user()->username }}</span>
                  @if (Auth::user()->role !== 1)
                    <div class="ml-auto">
                      <livewire:shopping-cart-icon />
                    </div>
                  @endif
                </div>
              @endif
            </div>
          </div>
        </button>
      </div>
    </div>
  </div>
</header>
