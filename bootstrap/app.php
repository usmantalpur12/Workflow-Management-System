<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.department' => \App\Http\Middleware\CheckDepartmentAccess::class,
            'check.project' => \App\Http\Middleware\CheckProjectAccess::class,
            'check.task' => \App\Http\Middleware\CheckTaskAccess::class,
            'restrict.ip' => \App\Http\Middleware\RestrictIP::class,
            'time.bound' => \App\Http\Middleware\TimeBoundAccess::class,
            'session.timeout' => \App\Http\Middleware\CheckSessionTimeout::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
