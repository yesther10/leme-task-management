<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/complete', [TaskController::class, 'markComplete'])->name('tasks.complete');

    // Route::post('tasks/{task}/files', [App\Http\Controllers\TaskFileController::class, 'store'])->name('tasks.files.store');
    // Route::delete('tasks/{task}/files/{file}', [App\Http\Controllers\TaskFileController::class, 'destroy'])->name('tasks.files.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('download/{filename}', [App\Http\Controllers\DownloadFileController::class, 'download'])->name('files.download');
});


