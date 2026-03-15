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
            if (isset($user->status) && $user->status === 'inactive') {
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
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            return $this->sendSuccess(null, 'Logged out successfully');

        } catch (\Exception $e) {
            return $this->sendError('Logout failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify admin credentials (used by Manager for sensitive operations).
     */
    public function verifyAdmin(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $admin = User::where('email', $request->email)->first();

            if (!$admin || !$admin->isAdmin()) {
                return $this->sendError('Invalid admin credentials.', [], 401);
            }

            if (!\Illuminate\Support\Facades\Hash::check($request->password, $admin->password)) {
                return $this->sendError('Invalid admin credentials.', [], 401);
            }

            // Log the verification attempt
            \Illuminate\Support\Facades\Log::info('Admin verification', [
                'admin_email'  => $request->email,
                'manager_id'   => auth()->id(),
                'ip'           => $request->ip(),
                'success'      => true,
            ]);

            return $this->sendSuccess(['verified' => true], 'Admin verified successfully');

        } catch (\Exception $e) {
            return $this->sendError('Verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('role');

            return $this->sendSuccess([
                'user' => $user,
            ], 'User retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving user: ' . $e->getMessage());
        }
    }
}
