<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define all permissions for the inventory system
        $permissions = [
            // User management
            'view-user',
            'create-user', 
            'edit-user',
            'delete-user',
            
            // Product management
            'view-product',
            'create-product',
            'edit-product', 
            'delete-product',
            
            // Category management
            'view-category',
            'create-category',
            'edit-category',
            'delete-category',
            
            // Supplier management
            'view-supplier',
            'create-supplier',
            'edit-supplier',
            'delete-supplier',
            
            // Customer management
            'view-customer',
            'create-customer',
            'edit-customer',
            'delete-customer',
            
            // Sale management
            'view-sale',
            'create-sale',
            'edit-sale',
            'delete-sale',
            
            // Purchase management
            'view-purchase',
            'create-purchase',
            'edit-purchase',
            'delete-purchase',
            
            // Stock management
            'view-stock',
            'update-stock',
            
            // Reports
            'view-report',
            'export-report',
        ];

        // Create permissions in database
        foreach ($permissions as $permissionName) {
            Permission::create([
                'name' => $permissionName,
                'description' => $this->getPermissionDescription($permissionName)
            ]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function getPermissionDescription($permissionName): string
    {
        $descriptions = [
            'view-user' => 'Can view users list',
            'create-user' => 'Can create new users',
            'edit-user' => 'Can edit existing users',
            'delete-user' => 'Can delete users',
            
            'view-product' => 'Can view products',
            'create-product' => 'Can create new products',
            'edit-product' => 'Can edit products',
            'delete-product' => 'Can delete products',
            
            'view-category' => 'Can view categories',
            'create-category' => 'Can create categories',
            'edit-category' => 'Can edit categories',
            'delete-category' => 'Can delete categories',
            
            'view-supplier' => 'Can view suppliers',
            'create-supplier' => 'Can create suppliers',
            'edit-supplier' => 'Can edit suppliers',
            'delete-supplier' => 'Can delete suppliers',
            
            'view-customer' => 'Can view customers',
            'create-customer' => 'Can create customers',
            'edit-customer' => 'Can edit customers',
            'delete-customer' => 'Can delete customers',
            
            'view-sale' => 'Can view sales',
            'create-sale' => 'Can create sales',
            'edit-sale' => 'Can edit sales',
            'delete-sale' => 'Can delete sales',
            
            'view-purchase' => 'Can view purchases',
            'create-purchase' => 'Can create purchases',
            'edit-purchase' => 'Can edit purchases',
            'delete-purchase' => 'Can delete purchases',
            
            'view-stock' => 'Can view stock history',
            'update-stock' => 'Can update stock quantities',
            
            'view-report' => 'Can view reports',
            'export-report' => 'Can export reports',
        ];

        return $descriptions[$permissionName] ?? 'System permission';
    }

    private function assignPermissionsToRoles(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $staffRole = Role::where('name', 'staff')->first();

        if ($adminRole) {
            // Admin gets ALL permissions
            $adminRole->permissions()->sync(Permission::all()->pluck('id')->toArray());
        }

        if ($managerRole) {
            // Manager gets most permissions except user deletion
            $managerPermissions = Permission::whereNotIn('name', [
                'delete-user',
                'delete-product',
                'delete-category',
                'delete-supplier',
                'delete-customer',
                'delete-sale',
                'delete-purchase'
            ])->pluck('id')->toArray();
            
            $managerRole->permissions()->sync($managerPermissions);
        }

        if ($staffRole) {
            // Staff gets limited permissions
            $staffPermissions = Permission::whereIn('name', [
                'view-product',
                'view-category',
                'view-supplier',
                'view-customer',
                'view-sale',
                'create-sale',
                'view-purchase',
                'create-purchase',
                'view-stock',
                'view-report'
            ])->pluck('id')->toArray();
            
            $staffRole->permissions()->sync($staffPermissions);
        }
    }
}