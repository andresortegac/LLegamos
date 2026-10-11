<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SyncAuthenticatedUserSession;
use App\Http\Middleware\AuditMutatingRequests;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\RequestContext;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureAccountIsActive::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);

        $middleware->appendToGroup('web', RequestContext::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->appendToGroup('web', SyncAuthenticatedUserSession::class);
        $middleware->appendToGroup('web', AuditMutatingRequests::class);
        $middleware->validateCsrfTokens(except: ['webhooks/wompi']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
