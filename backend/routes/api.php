<?php

use App\Http\Controllers\Api\KnowledgeController;
use App\Http\Controllers\Api\ConsoleController;
use App\Http\Controllers\Api\ReplayController;
use App\Http\Controllers\Api\HealthScoreController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\KnowledgeGapController;
use App\Http\Controllers\Api\EvaluationController;
use Illuminate\Support\Facades\Route;

// Knowledge CRUD
Route::get('/knowledge', [KnowledgeController::class, 'index']);
Route::post('/knowledge', [KnowledgeController::class, 'store']);
Route::put('/knowledge/{id}', [KnowledgeController::class, 'update']);
Route::delete('/knowledge/{id}', [KnowledgeController::class, 'destroy']);

// Test Console Execution
Route::post('/console/query', [ConsoleController::class, 'query']);

// Replay Lifecycle
Route::get('/messages/{id}/replay', [ReplayController::class, 'show']);
Route::post('/messages/{id}/replay', [ReplayController::class, 'replay']);

// Human Review Evaluation
Route::post('/messages/{id}/evaluation', [EvaluationController::class, 'store']);

// Metrics & Analytics
Route::get('/health-score', [HealthScoreController::class, 'show']);
Route::get('/doctor/cases', [DoctorController::class, 'cases']);
Route::get('/knowledge-gaps', [KnowledgeGapController::class, 'index']);
