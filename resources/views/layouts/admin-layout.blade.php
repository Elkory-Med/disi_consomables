<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} - Administration</title>
    
    <!-- Styles -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- App Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
</head>

<body class="font-sans antialiased bg-gray-100">
    <div x-data="{ menuOpen: false }">
        <!-- Sidebar -->
        <aside :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed inset-y-0 z-50 flex flex-col w-64 h-screen transition-transform bg-blue-900 border-r rtl:border-r-0 rtl:border-l lg:translate-x-0">
            <div class="flex items-center justify-between px-6 py-4 text-white">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Administration
                </a>
                <button @click="menuOpen = false" class="lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex flex-col h-full">
                <nav class="flex-1 space-y-2 overflow-y-auto p-4 text-gray-300">
                    <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Tableau de bord
                    </a>
                    
                    <a href="{{ route('admin.orders') }}" class="@if(request()->routeIs('admin.orders')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Commandes en attente
                    </a>

                    <a href="{{ route('orders.delivered') }}" class="@if(request()->routeIs('orders.delivered')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Commandes livrées
                    </a>

                    <a href="{{ route('admin.products') }}" class="@if(request()->routeIs('admin.products')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                        Produits
                    </a>

                    <a href="{{ route('admin.categories') }}" class="@if(request()->routeIs('admin.categories')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        Catégories
                    </a>

                    <a href="{{ route('admin.users') }}" class="@if(request()->routeIs('admin.users')) bg-blue-800 @endif flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Utilisateurs
                    </a>
                </nav>

                <div class="p-4 border-t border-blue-800">
                    <a class="w-full flex items-center gap-x-3.5 py-2.5 px-2.5 text-sm text-gray-300 rounded-lg hover:bg-blue-800 transition-all duration-200 focus:outline-none" href="{{ route('auth.logout') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="22" height="22">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                        </svg>
                        Déconnexion
                    </a>
                </div>
            </div>
        </aside>

        <!-- Mobile Menu Button -->
        <div class="fixed top-0 left-0 z-40 p-4 lg:hidden">
            <button @click="menuOpen = true" class="text-gray-600 focus:outline-none hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Main Content -->
        <main class="lg:ml-64 min-h-screen p-4 text-gray-700">
            @if(isset($title))
            <div class="bg-white shadow mb-6 rounded-lg">
                <div class="px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                </div>
            </div>
            @endif
            
            {{ $slot }}
        </main>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Additional scripts -->
    @stack('scripts')
</body>
</html>