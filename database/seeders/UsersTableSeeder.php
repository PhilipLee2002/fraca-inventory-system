<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $staffRole = Role::where('name', 'staff')->first();

        if (!$adminRole || !$managerRole || !$staffRole) {
            echo "ERROR: Roles not found! Run RolesTableSeeder first.\n";
            return;
        }

        // Users to create - ONLY with columns that exist
        $users = [
            [
                'email' => 'admin@inventory.com',
                'name' => 'Admin User',
                'role_id' => $adminRole->id,
            ],
            [
                'email' => 'manager@inventory.com', 
                'name' => 'Manager User',
                'role_id' => $managerRole->id,
            ],
            [
                'email' => 'staff@inventory.com',
                'name' => 'Staff User', 
                'role_id' => $staffRole->id,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();
            
            if (!$user) {
                // Create user with only EXISTING columns
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password123'),
                    'role_id' => $userData['role_id'],
                    // Don't include status, phone, address - they don't exist
                ]);
                echo "Created user: {$userData['email']}\n";
            } else {
                echo "User already exists: {$userData['email']}\n";
            }
        }
        
        echo "Users seeded successfully!\n";
    }
}