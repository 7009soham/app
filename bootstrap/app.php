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
        // Add SetLocale middleware to web group for language switching
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'citizen.auth' => \App\Http\Middleware\CitizenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // A stale CSRF token used to dump the bare "419 PAGE EXPIRED" screen.
        // On a payment form that reads as "the payment broke", and a citizen
        // who has been sitting on the page, or pressed Back from the gateway,
        // has no idea whether money moved. Send them back to the page they
        // came from with a plain explanation instead.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Please refresh the page and try again.',
                ], 419);
            }

            return redirect()
                ->back()
                ->withInput($request->except(['_token', 'password']))
                ->with('warning', 'Your session expired before that was submitted, so nothing was processed. '
                    . 'Please check the details and try again.');
        });
    })->create();
