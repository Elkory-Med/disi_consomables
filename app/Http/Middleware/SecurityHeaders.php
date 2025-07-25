<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Set proper charset
        $response->headers->set('Content-Type', 'text/html; charset=utf-8', true);
        
        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Ensure cookies are secure
        Config::set('session.secure', true);
        Config::set('session.http_only', true);
        Config::set('session.same_site', 'lax');
        
        if ($response->headers->has('Set-Cookie')) {
            $cookies = $response->headers->getCookies();
            foreach ($cookies as $cookie) {
                $cookie->setSecure(true);
                $cookie->setHttpOnly(true);
                $cookie->setSameSite('lax');
            }
        }

        return $response;
    }
}
