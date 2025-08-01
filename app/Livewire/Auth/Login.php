<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    protected $messages = [
        'email.required' => 'L\'adresse e-mail est requise.',
        'email.email' => 'Veuillez entrer une adresse e-mail valide.',
        'password.required' => 'Le mot de passe est requis.'
    ];

    public function login()
    {
        $this->validate();

        // Check if user exists and is pending
        $user = User::where('email', $this->email)->first();
        if ($user && $user->status === 'pending') {
            $this->addError('email', 'Votre compte est en attente d\'approbation. Veuillez patienter jusqu\'à ce qu\'un administrateur approuve votre compte.');
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            // Redirect based on user role
            if (auth()->user()->role == 1) {
                return redirect()->intended(route('admin.dashboard'));
            }
            return redirect()->intended(route('home'));
        } else {
            $this->addError('email', 'Ces informations d\'identification ne correspondent pas à nos enregistrements.');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
