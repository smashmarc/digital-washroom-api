<?php

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // API-only app: there's no 'login' web route to redirect to, so the
        // default unauthenticated-redirect behavior would throw its own
        // RouteNotFoundException. Force a plain JSON 401 instead.
        Authenticate::redirectUsing(fn () => null);

        $middleware->alias([
            'active.user' => \App\Http\Middleware\EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error('The given data was invalid.', 422, $e->errors());
        });
        $exceptions->render(function (AuthorizationException $e) {
            return ApiResponse::error('Unauthorized.', 403);
        });
        // Reachable for direct jwt-auth calls that don't go through the
        // guard's user()/check() (which swallows these into a bool) — e.g.
        // the /api/refresh endpoint's auth()->refresh().
        $exceptions->render(function (TokenExpiredException $e) {
            return ApiResponse::error('Token expired', 401, null, 'TOKEN_EXPIRED');
        });
        $exceptions->render(function (TokenInvalidException $e) {
            return ApiResponse::error('Token is invalid', 401, null, 'TOKEN_INVALID');
        });
        $exceptions->render(function (JWTException $e) {
            return ApiResponse::error('Token not provided', 401, null, 'TOKEN_ABSENT');
        });
        // The actual path taken by 'auth:api'-protected routes: JWTGuard's
        // user()/check() swallows jwt-auth's own exceptions and returns
        // null/false, so Laravel's Authenticate middleware throws this
        // generic exception instead. Re-validate the raw bearer token here
        // to recover the specific reason for the frontend.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $errorCode = 'TOKEN_ABSENT';
            $message = 'Token not provided';

            if ($token = $request->bearerToken()) {
                try {
                    JWTAuth::setToken($token)->checkOrFail();
                    // Guard already rejected this token for another reason
                    // (e.g. user no longer exists) — treat as invalid.
                    $errorCode = 'TOKEN_INVALID';
                    $message = 'Token is invalid';
                } catch (TokenExpiredException $ex) {
                    $errorCode = 'TOKEN_EXPIRED';
                    $message = 'Token expired';
                } catch (JWTException $ex) {
                    $errorCode = 'TOKEN_INVALID';
                    $message = 'Token is invalid';
                }
            }

            return ApiResponse::error($message, 401, null, $errorCode);
        });
        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() === 403) {
                return ApiResponse::error('Unauthorized.', 403);
            }
            return ApiResponse::error($e->getMessage() ?: 'HTTP Error.', $e->getStatusCode());
        });
        $exceptions->render(function (\Exception $e) {
            Log::error(get_class($e) . ' ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return ApiResponse::error($e->getMessage() ?: 'An unexpected error occurred.', 500);
        });
    })->create();
