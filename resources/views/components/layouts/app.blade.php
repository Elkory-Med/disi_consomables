<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}?v=2">
        <title>{{ $title ?? config('app.name', 'DISI COMMANDES') }}</title>

        <style>
            [x-cloak] { display: none !important; }
        </style>

        <!-- Scripts and Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen flex flex-col font-sans antialiased bg-gray-100">
        <div class="flex-grow flex flex-col">
            <!-- Header -->
            <livewire:header />

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Content -->
            <main class="flex-grow py-4">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <livewire:footer />
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
