<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class PreventAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check if the user is authenticated and is an admin
        if (Auth::check() && Auth::user()->role == 1) {
            // Clear any shopping related session data
            if (session()->has('cart')) {
                session()->forget('cart');
            }
            
            // Redirect admin to the admin dashboard
            return redirect()->route('admin.dashboard')->with('info', 'Admins are automatically redirected to the admin dashboard.');
        }

        return $next($request);
    }
}
