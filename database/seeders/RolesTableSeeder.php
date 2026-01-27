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
        // Clear existing roles
        //Role::truncate();

        $roles = [
            [
                'name' => 'admin', 
                'permissions' => 'Administrator with full access' // Use 'permissions' column
            ],
            [
                'name' => 'manager', 
                'permissions' => 'Manager with limited administrative access'
            ],
            [
                'name' => 'staff', 
                'permissions' => 'Staff with basic access'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
        
        echo "Roles seeded successfully!\n";
    }
}