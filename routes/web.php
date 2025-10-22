<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;


Auth::routes();


Route::group(['middleware' => ['auth']], function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])->name('tasks.complete');
    
    Route::get('/dashboard', [DashboardController::class, 'metrics'])->name('dashboard');

});


