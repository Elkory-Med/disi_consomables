<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserApproval
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->status === 'pending') {
            session()->flash('error', 'Votre compte est en attente d\'approbation par l\'administrateur.');
            return redirect()->route('home');
        }

        return $next($request);
    }
}
