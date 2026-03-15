<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Users
            'view-user'       => 'Can view users list',
            'create-user'     => 'Can create new users',
            'edit-user'       => 'Can edit existing users',
            'delete-user'     => 'Can delete users',
            // Products
            'view-product'    => 'Can view products',
            'create-product'  => 'Can create new products',
            'edit-product'    => 'Can edit products',
            'delete-product'  => 'Can delete products',
            // Categories
            'view-category'   => 'Can view categories',
            'create-category' => 'Can create categories',
            'edit-category'   => 'Can edit categories',
            'delete-category' => 'Can delete categories',
            // Suppliers
            'view-supplier'   => 'Can view suppliers',
            'create-supplier' => 'Can create suppliers',
            'edit-supplier'   => 'Can edit suppliers',
            'delete-supplier' => 'Can delete suppliers',
            // Customers
            'view-customer'   => 'Can view customers',
            'create-customer' => 'Can create customers',
            'edit-customer'   => 'Can edit customers',
            'delete-customer' => 'Can delete customers',
            // Sales
            'view-sale'       => 'Can view sales',
            'create-sale'     => 'Can create sales',
            'edit-sale'       => 'Can edit sales',
            'delete-sale'     => 'Can delete sales',
            // Purchases
            'view-purchase'   => 'Can view purchases',
            'create-purchase' => 'Can create purchases',
            'edit-purchase'   => 'Can edit purchases',
            'delete-purchase' => 'Can delete purchases',
            // Stock
            'view-stock'      => 'Can view stock history',
            'manage-stock'    => 'Can perform stock adjustments',
            // Reports
            'view-report'     => 'Can view reports',
            'export-report'   => 'Can export reports',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $admin   = Role::where('name', 'admin')->first();
        $manager = Role::where('name', 'manager')->first();
        $staff   = Role::where('name', 'staff')->first();

        // Admin — everything
        if ($admin) {
            $admin->rolePermissions()->sync(Permission::all()->pluck('id'));
        }

        // Manager — everything except hard deletes and user deletion
        if ($manager) {
            $manager->rolePermissions()->sync(
                Permission::whereNotIn('name', [
                    'delete-user', 'delete-product', 'delete-category',
                    'delete-supplier', 'delete-customer', 'delete-sale', 'delete-purchase',
                ])->pluck('id')
            );
        }

        // Staff — read + create sales/purchases + stock view
        if ($staff) {
            $staff->rolePermissions()->sync(
                Permission::whereIn('name', [
                    'view-product', 'view-category', 'view-supplier', 'view-customer',
                    'view-sale', 'create-sale',
                    'view-purchase', 'create-purchase',
                    'view-stock', 'view-report',
                ])->pluck('id')
            );
        }
    }
}
