@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 py-6 px-4">
    <div class="w-full max-w-md">
        <h2 class="text-center text-xl font-semibold text-gray-900 mb-4">
            Créer un compte
        </h2>
        <div class="bg-white shadow-sm rounded-lg p-4">
            <livewire:auth.register />
        </div>
    </div>
</div>
@endsection
