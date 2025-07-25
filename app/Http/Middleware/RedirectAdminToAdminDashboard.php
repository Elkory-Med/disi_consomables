<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminToAdminDashboard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and is an admin (role == 1)
        if (Auth::check() && Auth::user()->role == 1) {
            // Only redirect if this is the root URL
            if ($request->is('/')) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
} 