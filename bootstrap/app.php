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
            'ensure.admin' => \App\Http\Middleware\EnsureAdmin::class,
            'ensure.manager' => \App\Http\Middleware\EnsureManager::class,
            'ensure.agent' => \App\Http\Middleware\EnsureAgent::class,
            'ensure.landlord' => \App\Http\Middleware\EnsureLandlord::class,
            'ensure.tenant' => \App\Http\Middleware\EnsureTeranrt::class,
            'check.organization' => \App\Http\Middleware\CheckOrganizationAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
