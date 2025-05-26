<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}?v=2">

    <title>{{ config('app.name', 'DISI COMMANDES') }}</title>

    <!-- Styles -->
    <style>
        html {
            text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            -moz-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
    </style>

    <!-- Scripts and Styles -->
    @php
        if (app()->environment('local')) {
            $vite = [
                'resources/css/app.css',
                'resources/js/app.js'
            ];
        } else {
            $vite = 'resources/js/app.js';
            echo '<link rel="stylesheet" href="'.asset('build/assets/app.css').'">';
        }
    @endphp
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('livewire.header')

        <main>
            @include('components.flash-message')
            @yield('content')
        </main>

        @include('livewire.footer')
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
