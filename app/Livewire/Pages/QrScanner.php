<?php

namespace App\Livewire\Pages;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class QrScanner extends Component
{
    public function render()
    {
        return view('livewire.pages.qr-scanner');
    }
}