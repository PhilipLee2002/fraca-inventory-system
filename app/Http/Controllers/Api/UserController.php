<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $query = User::with('role');

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"));
            }

            $users = $query->orderBy('name')->paginate($request->input('per_page', 20));

            return $this->sendPaginated($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving users: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => ['required', Rules\Password::defaults()],
                'role_id'  => 'required|exists:roles,id',
                'status'   => 'nullable|in:active,inactive,pending',
            ]);

            $data['password'] = Hash::make($data['password']);
            $data['status'] = $data['status'] ?? 'active';

            $user = User::create($data);
            $user->load('role');

            return $this->sendCreated($user, 'User created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Error creating user: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        $user->load('role');
        return $this->sendSuccess($user, 'User retrieved successfully');
    }

    public function update(Request $request, User $user)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email,' . $user->id,
                'password' => ['nullable', Rules\Password::defaults()],
                'role_id'  => 'required|exists:roles,id',
                'status'   => 'nullable|in:active,inactive,pending',
            ]);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            $user->load('role');

            return $this->sendUpdated($user, 'User updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (\Exception $e) {
            return $this->sendError('Error updating user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            if (auth()->id() === $user->id) {
                return $this->sendError('You cannot delete your own account.', [], 422);
            }
            $user->delete();
            return $this->sendDeleted('User deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting user: ' . $e->getMessage());
        }
    }

    public function roles()
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return $this->sendSuccess($roles, 'Roles retrieved successfully');
    }
}
