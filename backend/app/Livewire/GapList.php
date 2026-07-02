<?php

namespace App\Livewire;

use App\Services\KnowledgeGapDetector;
use Livewire\Component;

class GapList extends Component
{
    public function render(KnowledgeGapDetector $detector)
    {
        $gaps = $detector->topGaps();

        return view('livewire.gap-list', [
            'gaps' => $gaps
        ])->layout('components.layouts.app');
    }
}
