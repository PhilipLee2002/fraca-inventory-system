<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    User::create([
        'name' => 'Admin User',
        'email' => 'admin@inventory.com',
        'password' => bcrypt('password123'),
        'role_id' => 1, // admin
    ]);
    
    User::create([
        'name' => 'Manager User',
        'email' => 'manager@inventory.com',
        'password' => bcrypt('password123'),
        'role_id' => 2, // manager
    ]);
    
    // Add more test users as needed
}
}
