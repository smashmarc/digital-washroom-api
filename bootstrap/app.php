<?php

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error('The given data was invalid.', 422, $e->errors());
        });
        $exceptions->render(function (AuthorizationException $e) {
            return ApiResponse::error('Unauthorized.', 403);
        });
        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() === 403) {
                return ApiResponse::error('Unauthorized.', 403);
            }
        });
        $exceptions->render(function (\Exception $e) {
            Log::error(get_class($e) . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error($e->getMessage() ?: 'An unexpected error occurred.', 500);
        });
    })->create();
