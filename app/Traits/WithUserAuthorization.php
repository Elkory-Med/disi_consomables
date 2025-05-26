<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait WithUserAuthorization
{
    /**
     * Check if user is authenticated and approved
     *
     * @return bool
     */
    protected function isAuthorized(): bool
    {
        return Auth::check() && Auth::user()->status === 'approved';
    }

    /**
     * Redirect user if not authorized
     *
     * @param string $errorMessage
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function redirectIfNotAuthorized(string $errorMessage = '')
    {
        if (!$this->isAuthorized()) {
            if ($errorMessage) {
                session()->flash('error', $errorMessage);
            }
            
            if (!Auth::check()) {
                return redirect()->to('/login');
            }
            
            return redirect()->to('/auth/pending');
        }
        
        return null;
    }
} 