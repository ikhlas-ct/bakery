<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware as ConfigMiddleware;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (ConfigMiddleware $middleware) {
        // alias kalau nanti mau pakai per‑route
        $middleware->alias([
        'role' =>  CheckRole::class,
        'track' => TrackVisitor::class,
        ]);


        $middleware->append(TrackVisitor::class);

    })
    ->withExceptions(function ($exceptions) {

    })
    ->create();
