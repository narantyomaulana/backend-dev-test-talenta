<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TodoController;

Route::get('/test', function() {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'laravel_version' => app()->version()
    ]);
});

// Todo API Routes
Route::apiResource('todos', TodoController::class)->only(['index', 'store']);

// Additional routes
Route::get('/todos/export', [TodoController::class, 'exportExcel']);

// Chart routes
Route::get('/chart/status', [TodoController::class, 'chartStatus']);
Route::get('/chart/priority', [TodoController::class, 'chartPriority']);
Route::get('/chart/assignee', [TodoController::class, 'chartAssignee']);