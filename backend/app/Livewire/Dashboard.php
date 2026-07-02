<?php

namespace App\Livewire;

use App\Services\HealthScoreCalculator;
use App\Models\Diagnostics;
use Livewire\Component;

class Dashboard extends Component
{
    public array $scoreData = [];
    public int $lookbackDays = 7;

    public function mount(HealthScoreCalculator $calculator)
    {
        $this->scoreData = $calculator->calculate($this->lookbackDays);
    }

    public function render()
    {
        $recentRuns = Diagnostics::with(['message.conversation.promptVersion'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'recentRuns' => $recentRuns
        ])->layout('components.layouts.app');
    }
}
