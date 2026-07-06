<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\PrefersJsonResponses;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();
    $middleware->api(prepend: [
      PrefersJsonResponses::class,
    ]);
    // 'login' という名前付きルートは存在しない（React SPA が /login を担当）ため、
    // 未ログイン時のリダイレクト先はパス直書きにする。
    $middleware->redirectGuestsTo('/login');
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    //
  })->create();
