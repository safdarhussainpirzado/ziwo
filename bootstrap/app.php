<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->web(append: [
            \App\Http\Middleware\TwoFactorEnforcement::class,
            \App\Http\Middleware\PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $e, $request) {
            if ($response->getStatusCode() === 403 && ! $request->expectsJson()) {
                /** @var \App\Models\User $user */
                $user = auth()->user();
                if ($user) {
                    $landing = $user->getLandingPageRoute();
                    // Prevent infinite redirect loop if landing page itself is causing the 403
                    if ($request->url() !== $landing) {
                        return redirect($landing)
                            ->with('error', 'Unauthorized access. You have been redirected to your authorized workspace.');
                    }
                }
            }
            return $response;
        });
    })->create();
