<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role; 

class RolesTableSeeder extends Seeder
{
    
     # Run the database seeds.
   public function run()
{
    $roles = [
        ['name' => 'admin', 'permissions' => json_encode(['*'])],
        ['name' => 'manager', 'permissions' => json_encode(['view', 'create', 'edit', 'delete'])],
        ['name' => 'staff', 'permissions' => json_encode(['view', 'create'])],
    ];
    
    foreach ($roles as $role) {
        Role::create($role);
    }
}
}
