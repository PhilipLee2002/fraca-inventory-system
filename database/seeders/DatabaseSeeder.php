<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Must run in this order due to foreign key constraints
        $this->call([
            RolesTableSeeder::class,      // First create roles
            PermissionSeeder::class,       // Then create permissions
            UsersTableSeeder::class,       // Then create users (needs roles)
            
            // Your other seeders from Phase 1
            CategoriesTableSeeder::class,
            SuppliersTableSeeder::class,
            ProductsTableSeeder::class,
            // Add other seeders as needed
        ]);
    }
}