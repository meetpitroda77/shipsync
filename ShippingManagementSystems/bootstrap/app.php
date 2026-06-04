<?php

use App\Http\Middleware\ApiRoleMiddleware;
use App\Http\Middleware\EnsureActiveSubscription;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',

        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'api.role' => ApiRoleMiddleware::class,
            'csrf' => VerifyCsrfToken::class,
            'subscription.active' => EnsureActiveSubscription::class,


        ]);
    })
    ->withExceptions(function ($exceptions) {})->create();
