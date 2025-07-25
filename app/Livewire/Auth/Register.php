<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Notifications\NewUserRegistrationNotification;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.guest')]
class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $username = '';
    public $unite = '';
    public $matricule = '';
    public $administration = '';
    public $showSuccessModal = false;

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'username' => 'required|min:3|unique:users',
        'unite' => 'required',
        'matricule' => 'required|unique:users',
        'administration' => 'required'
    ];

    protected $messages = [
        'name.required' => 'Le nom est requis.',
        'name.min' => 'Le nom doit contenir au moins 3 caractères.',
        'email.required' => 'L\'email est requis.',
        'email.email' => 'L\'email doit être une adresse email valide.',
        'email.unique' => 'Cette adresse email est déjà utilisée.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        'username.required' => 'Le nom d\'utilisateur est requis.',
        'username.min' => 'Le nom d\'utilisateur doit contenir au moins 3 caractères.',
        'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
        'unite.required' => 'L\'unité est requise.',
        'matricule.required' => 'Le matricule est requis.',
        'matricule.unique' => 'Ce matricule est déjà utilisé.',
        'administration.required' => 'L\'administration est requise.'
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function register()
    {
        $validatedData = $this->validate();

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'username' => $validatedData['username'],
                'unite' => $validatedData['unite'],
                'matricule' => $validatedData['matricule'],
                'administration' => $validatedData['administration'],
                'role' => 0,
                'status' => 'pending'
            ]);

            event(new Registered($user));

            // Send notification email
            try {
                $user->notify(new NewUserRegistrationNotification());
            } catch (\Exception $e) {
                \Log::error('Failed to send registration email: ' . $e->getMessage());
            }

            // Login the user
            Auth::login($user, true);

            // Show success modal
            $this->showSuccessModal = true;

            // Reset the form
            $this->reset(['name', 'email', 'password', 'password_confirmation', 'username', 'unite', 'matricule', 'administration']);

        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            session()->flash('error', 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.');
        }
    }

    public function closeModal()
    {
        $this->redirect(route('auth.pending'));
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
