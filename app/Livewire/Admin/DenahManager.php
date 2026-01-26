<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Cluster;
use Livewire\Attributes\Layout;

class DenahManager extends Component
{
    #[Layout('layouts.admin')] // Sesuaikan dengan layout admin Anda
    public function render()
    {
        // Ambil semua cluster beserta jumlah unit didalamnya
        $clusters = Cluster::withCount('inventory')->get();

        return view('livewire.admin.denah-manager', [
            'clusters' => $clusters
        ]);
    }
}