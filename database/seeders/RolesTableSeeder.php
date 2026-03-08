<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin', 
                'description' => 'Administrator with full access'
            ],
            [
                'name' => 'manager', 
                'description' => 'Manager with limited administrative access'
            ],
            [
                'name' => 'staff', 
                'description' => 'Staff with basic access'
            ],
        ];

        foreach ($roles as $roleData) {
            // Check if role already exists
            $existingRole = Role::where('name', $roleData['name'])->first();
            
            if (!$existingRole) {
                Role::create($roleData);
                echo "Created role: {$roleData['name']}\n";
            } else {
                echo "Role already exists: {$roleData['name']}\n";
            }
        }
        
        echo "Roles seeded successfully!\n";
    }
}