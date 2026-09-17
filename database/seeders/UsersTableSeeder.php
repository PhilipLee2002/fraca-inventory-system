<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $staffRole = Role::where('name', 'staff')->first();

        if (! $adminRole || ! $managerRole || ! $staffRole) {
            echo "ERROR: Roles not found! Run RolesTableSeeder first.\n";
            return;
        }

        $plain = env('FRACASERVCOM_STAFF_PASSWORD');
        if (! is_string($plain) || $plain === '') {
            throw new \RuntimeException('Set FRACASERVCOM_STAFF_PASSWORD in .env before seeding.');
        }
        $password = Hash::make($plain);

        $users = [
            [
                'email' => 'benjamin@fracaservcomltd.co.ke',
                'name' => 'Benjamin Shitsukane',
                'phone' => '0725151495',
                'role_id' => $adminRole->id,
            ],
            [
                'email' => 'anne@fracaservcomltd.co.ke',
                'name' => 'Anne Jerubet',
                'phone' => '0719273159',
                'role_id' => $managerRole->id,
            ],
            [
                'email' => 'franklin@fracaservcomltd.co.ke',
                'name' => 'Franklin Shitsukane',
                'phone' => '0789296733',
                'role_id' => $managerRole->id,
            ],
            [
                'email' => 'reception@fracaservcomltd.co.ke',
                'name' => 'Receptionist',
                'phone' => null,
                'role_id' => $staffRole->id,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::where('email', $userData['email'])->first();

            if (! $user) {
                User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'password' => $password,
                    'role_id' => $userData['role_id'],
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                echo "Created user: {$userData['email']}\n";
            } else {
                $user->update([
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'role_id' => $userData['role_id'],
                    'status' => 'active',
                ]);
                echo "Updated user: {$userData['email']}\n";
            }
        }

        $deactivated = User::whereIn('email', [
            'admin@inventory.com',
            'manager@inventory.com',
            'staff@inventory.com',
        ])->update(['status' => 'inactive']);

        if ($deactivated) {
            echo "Deactivated {$deactivated} demo account(s).\n";
        }

        echo "Users seeded successfully!\n";
    }
}
