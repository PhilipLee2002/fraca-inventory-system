<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends BaseController
{
    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'device_name' => 'nullable|string',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return $this->sendError('Invalid credentials', [], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            
            // Check if user is active
            if (!$user->is_active) {
                return $this->sendError('Account is deactivated', [], 403);
            }

            // Revoke existing tokens (optional - for single device login)
            // $user->tokens()->delete();

            $token = $user->createToken($request->device_name ?? 'api-token')->plainTextToken;

            return $this->sendSuccess([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 'Login successful');

        } catch (\Exception $e) {
            return $this->sendError('Login failed: ' . $e->getMessage());
        }
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->sendSuccess(null, 'Logged out successfully');

        } catch (\Exception $e) {
            return $this->sendError('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        try {
            $user = $request->user()->load('permissions');

            return $this->sendSuccess([
                'user' => $user,
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ], 'User retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving user: ' . $e->getMessage());
        }
    }
}
