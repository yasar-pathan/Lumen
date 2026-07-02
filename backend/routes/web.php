<?php

use Illuminate\Support\Facades\Route;

use App\Livewire\Dashboard;
use App\Livewire\TestConsole;
use App\Livewire\KnowledgeManager;
use App\Livewire\ReplayView;
use App\Livewire\DoctorList;
use App\Livewire\GapList;

Route::redirect('/', '/app');

Route::get('/app/{any?}', function () {
    $path = public_path('app/index.html');
    if (!file_exists($path)) {
        abort(404, 'React app build index.html not found. Run npm run build in the frontend/ folder first.');
    }
    return file_get_contents($path);
})->where('any', '.*');
