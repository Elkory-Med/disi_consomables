<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('dashboard', function ($user) {
    return $user->role === 1;
});

Broadcast::channel('dashboard.charts', function ($user) {
    return $user->role === 1;
});