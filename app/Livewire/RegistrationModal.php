<?php

namespace App\Livewire;

use Livewire\Component;

class RegistrationModal extends Component
{
    public $show = false;
    public $message = '';

    public function mount()
    {
        $this->show = session()->has('registrationMessage');
        $this->message = session('registrationMessage');
    }

    public function closeModal()
    {
        $this->show = false;
        session()->forget('registrationMessage');
    }

    public function render()
    {
        return view('livewire.registration-modal');
    }
}
