<?php

namespace App\Livewire;

use Livewire\Component;

class AboutUs extends Component
{
    public function render()
    {
        return view('livewire.about-us')->layout('components.layouts.app', [
            'title' => 'À Propos'
        ]);
    }
}
