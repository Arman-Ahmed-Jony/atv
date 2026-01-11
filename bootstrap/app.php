<?php

use Illuminate\Auth\AuthenticationException;
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
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Prevent redirects for API routes on authentication failure
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return null; // Don't redirect for API routes - exception handler will return JSON
            }

            // For web routes, return null to let exception handler decide
            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle unauthenticated exceptions for API routes - must be first
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        // Handle PostTooLargeException for API routes
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'The uploaded file is too large. Maximum file size allowed is 100MB.',
                    'error' => 'File size exceeds the maximum allowed limit.',
                ], 413);
            }
        });

        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();
