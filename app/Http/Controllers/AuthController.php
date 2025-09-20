<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class AuthController extends Controller
{
    /**
     * User login with JWT.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return ApiResponse::error('Invalid email or password', 401);
        }

        return $this->respondWithToken($token, 'Login successful');
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

        return ApiResponse::success('Successfully logged out');
    }

    /**
     * Refresh JWT token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh(), 'Token refreshed successfully');
    }

    /**
     * Format token response consistently.
     */
    protected function respondWithToken(string $token, string $message = null)
    {
        return ApiResponse::success($message ?? 'Token generated', [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
        ]);
    }
}
