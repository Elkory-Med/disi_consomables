<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovedUser
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->status !== 'approved') {
            return redirect()->route('auth.pending')->with('error', 'Votre compte est en attente d\'approbation par l\'administrateur.');
        }

        return $next($request);
    }
}
