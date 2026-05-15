<?php

use App\Http\Controllers\Web\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tasks');

Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
Route::get('/tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit')->whereNumber('id');
Route::put('/tasks/{id}', [TaskController::class, 'update'])->name('tasks.update')->whereNumber('id');
Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy')->whereNumber('id');
