<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::resource('projects', App\Http\Controllers\ProjectController::class);
    Route::resource('tasks', App\Http\Controllers\TaskController::class);
    Route::post('tasks/{task}/files', [App\Http\Controllers\TaskFileController::class, 'store'])->name('tasks.files.store');
    Route::delete('tasks/{task}/files/{file}', [App\Http\Controllers\TaskFileController::class, 'destroy'])->name('tasks.files.destroy');

    Route::get('download/{filename}', [App\Http\Controllers\DownloadFileController::class, 'download'])->name('files.download');
});


