<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// React SPA（public/index.html）が / と /login を受け持つ。nginx 未経由の場合の保険として JSON を返す。
Route::get('/', fn () => response()->json(['app' => 'tech-update-task-app-react-api']));

Route::get('/dashboard', fn () => redirect('/'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// React SPA 向け認証エンドポイント（Cookie セッション + Sanctum CSRF）。
// Blade の /login とパスが衝突しないよう /api 配下に置く。
Route::post('/api/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/api/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth');
Route::post('/api/register', [RegisteredUserController::class, 'store'])->middleware('guest');
Route::middleware('auth')->get('/api/user', fn (Request $request) => $request->user());

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
