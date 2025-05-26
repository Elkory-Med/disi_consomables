<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class AuthModal extends Component
{
    public $show = false;

    // Registration properties
    public $name = '';
    public $username = '';
    public $email = '';
    public $administration = '';
    public $unite = '';
    public $matricule = '';
    public $password = '';
    public $password_confirmation = '';

    protected $listeners = ['toggle-auth-modal' => 'toggleModal'];

    public function toggleModal()
    {
        $this->show = !$this->show;
    }

    protected function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'administration' => ['required', 'string', 'max:255'],
            'unite' => ['required', 'string', 'max:255'],
            'matricule' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    protected $messages = [
        'name.required' => 'Le nom est requis.',
        'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
        'username.required' => 'Le nom d\'utilisateur est requis.',
        'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
        'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
        'email.required' => 'L\'adresse e-mail est requise.',
        'email.email' => 'Veuillez entrer une adresse e-mail valide.',
        'email.max' => 'L\'adresse e-mail ne peut pas dépasser 255 caractères.',
        'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        'administration.required' => 'L\'administration est requise.',
        'administration.max' => 'L\'administration ne peut pas dépasser 255 caractères.',
        'unite.required' => 'L\'unité est requise.',
        'unite.max' => 'L\'unité ne peut pas dépasser 255 caractères.',
        'matricule.required' => 'Le matricule est requis.',
        'matricule.max' => 'Le matricule ne peut pas dépasser 255 caractères.',
        'matricule.unique' => 'Ce matricule est déjà utilisé.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.'
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'administration' => $this->administration,
            'unite' => $this->unite,
            'matricule' => $this->matricule,
            'password' => Hash::make($this->password),
            'role' => 0, // Set default role as regular user
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.auth.auth-modal');
    }
}
