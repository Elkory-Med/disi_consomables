<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class ManageUsers extends Component
{
    use WithPagination;

    #[Layout('layouts.admin-layout')]
    public $search = '';
    public $perPage = 10;
    public $selectedUser = null;
    public $showApprovalModal = false;
    public $showRejectionModal = false;
    public $showEditModal = false;
    public $rejectionReason = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $currentUrl = 'admin.users';

    // User edit form fields
    public $userId;
    public $name;
    public $username;
    public $email;
    public $matricule;
    public $unite;
    public $administration;
    public $password;
    public $password_confirmation;

    protected $rules = [
        'name' => 'required|string|max:255',
        'username' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'matricule' => 'required|string|max:255',
        'unite' => 'required|string|max:255',
        'administration' => 'required|string|max:255',
        'password' => 'nullable|min:8|confirmed',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc']
    ];

    public function mount()
    {
        $this->resetModals();
    }

    public function resetModals()
    {
        $this->showApprovalModal = false;
        $this->showRejectionModal = false;
        $this->showEditModal = false;
        $this->selectedUser = null;
        $this->rejectionReason = '';
        $this->resetEditForm();
    }

    public function resetEditForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->matricule = '';
        $this->unite = '';
        $this->administration = '';
        $this->password = '';
        $this->password_confirmation = '';
    }

    public function openApprovalModal($userId)
    {
        $this->selectedUser = User::find($userId);
        if ($this->selectedUser) {
            $this->showApprovalModal = true;
        }
    }

    public function openRejectionModal($userId)
    {
        $this->selectedUser = User::find($userId);
        if ($this->selectedUser) {
            $this->showRejectionModal = true;
        }
    }

    public function openEditModal($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->dispatch('error', 'Utilisateur non trouvé.');
            return;
        }

        // Only allow editing pending users
        if ($user->status !== 'pending') {
            $this->dispatch('error', 'Impossible de modifier cet utilisateur car il est déjà approuvé.');
            return;
        }

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->matricule = $user->matricule;
        $this->unite = $user->unite;
        $this->administration = $user->administration;
        $this->showEditModal = true;
    }

    public function closeModals()
    {
        $this->resetModals();
    }

    public function approveUser()
    {
        if (!$this->selectedUser) {
            $this->dispatch('error', 'Utilisateur non trouvé.');
            return;
        }

        try {
            DB::beginTransaction();

            $this->selectedUser->status = 'approved';
            $this->selectedUser->save();

            // Send approval email
            Mail::send('emails.user-approved', ['user' => $this->selectedUser], function($message) {
                $message->to($this->selectedUser->email)
                    ->subject('SOMELEC-DISI - Votre compte a été approuvé');
            });

            DB::commit();
            $this->resetModals();
            $this->dispatch('success', 'Utilisateur approuvé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function rejectUser()
    {
        if (!$this->selectedUser) {
            $this->dispatch('error', 'Utilisateur non trouvé.');
            return;
        }

        try {
            DB::beginTransaction();

            // Delete the selected user
            $this->selectedUser->delete();

            // Send rejection email
            Mail::send('emails.user-rejected', ['user' => $this->selectedUser, 'reason' => $this->rejectionReason], function($message) {
                $message->to($this->selectedUser->email)
                    ->subject('SOMELEC-DISI - Votre compte a été rejeté');
            });

            DB::commit();
            $this->resetModals();
            $this->dispatch('success', 'Utilisateur rejeté avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function saveUser()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $user = User::find($this->userId);
            if (!$user) {
                $this->dispatch('error', 'Utilisateur non trouvé.');
                return;
            }

            $user->name = $this->name;
            $user->username = $this->username;
            $user->email = $this->email;
            $user->matricule = $this->matricule;
            $user->unite = $this->unite;
            $user->administration = $this->administration;
            
            // Only update password if provided
            if (!empty($this->password)) {
                $user->password = Hash::make($this->password);
            }

            $user->save();

            DB::commit();
            $this->resetModals();
            $this->dispatch('success', 'Utilisateur modifié avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $users = User::where('role', '!=', 1)
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('matricule', 'like', '%' . $this->search . '%')
                      ->orWhere('username', 'like', '%' . $this->search . '%')
                      ->orWhere('administration', 'like', '%' . $this->search . '%')
                      ->orWhere('unite', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.manage-users', [
            'users' => $users
        ])->layout('layouts.admin-layout', [
            'title' => 'Gestion des Utilisateurs'
        ]);
    }
}
