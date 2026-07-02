<?php

namespace App\Livewire;

use App\Models\Diagnostics;
use Livewire\Component;

class DoctorList extends Component
{
    public function render()
    {
        $cases = Diagnostics::with(['message.conversation'])
            ->where('root_cause', '!=', 'healthy')
            ->orderBy('id', 'desc')
            ->get();

        return view('livewire.doctor-list', [
            'cases' => $cases
        ])->layout('components.layouts.app');
    }
}
