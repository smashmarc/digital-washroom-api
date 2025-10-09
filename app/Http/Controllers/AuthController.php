<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\AuthResource;

class AuthController extends Controller
{
    /**
     * User login with JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return ApiResponse::error(
                'Incorrect username or password',
                401,
                ['username' => ['These credentials do not match our records.']]
            );
        }

        return $this->respondWithToken($token, 'Login successful', auth()->user());
    }

    /**
     * Get current authenticated user.
     */
    public function me()
    {
        return ApiResponse::success('User profile', auth()->user());
    }

    /**
     * Logout (invalidate token).
     */
    public function logout()
    {
        auth()->logout();

        return ApiResponse::success('Successfully logged out', null);
    }

    /**
     * Refresh JWT token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(), 'Token refreshed successfully', auth()->user());
    }

    /**
     * Format token response consistently.
     */
    protected function respondWithToken(string $token, string $message, $user = null)
    {
        return ApiResponse::success($message, new AuthResource([
        'user'         => $user,
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => auth()->factory()->getTTL() * 60, // TTL is in minutes → convert to seconds
    ]));
    }
}
