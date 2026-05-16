<?php

use App\Http\Helpers\ApiResponse;
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
        $middleware->alias([
            'owner' => \App\Http\Middleware\EnsureUserIsOwner::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $json = fn ($request) => $request->expectsJson() || str_starts_with($request->path(), 'api/');

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($json) {
            if ($json($request)) {
                return ApiResponse::error('Unauthenticated.', 'UNAUTHENTICATED', 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($json) {
            if ($json($request)) {
                return ApiResponse::error('The given data was invalid.', 'VALIDATION_ERROR', 422, $e->errors());
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) use ($json) {
            if ($json($request)) {
                return ApiResponse::error('Not found.', 'NOT_FOUND', 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) use ($json) {
            if ($json($request)) {
                return ApiResponse::error('Forbidden.', 'FORBIDDEN', 403);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($json) {
            if ($json($request)) {
                return ApiResponse::error($e->getMessage() ?: 'HTTP error.', 'HTTP_ERROR', $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, $request) use ($json) {
            if ($json($request) && app()->environment('production')) {
                return ApiResponse::error('Server error.', 'SERVER_ERROR', 500);
            }
        });
    })->create();
