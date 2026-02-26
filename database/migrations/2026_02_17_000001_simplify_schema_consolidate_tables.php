<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Drop permissions and pivot table
        if (Schema::hasTable('role_permission')) {
            Schema::dropIfExists('role_permission');
        }
        if (Schema::hasTable('permissions')) {
            Schema::dropIfExists('permissions');
        }

        // Step 2: Add permissions JSON column to roles if not exists
        if (Schema::hasTable('roles')) {
            if (!Schema::hasColumn('roles', 'permissions')) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->json('permissions')->nullable()->after('description');
                });
            }
        }

        // Step 3: Simplify stock_histories table
        if (Schema::hasTable('stock_histories')) {
            // If reference_type column exists, drop it
            if (Schema::hasColumn('stock_histories', 'reference_type')) {
                Schema::table('stock_histories', function (Blueprint $table) {
                    $table->dropColumn('reference_type');
                });
            }

            // Add transaction_type if not exists
            if (!Schema::hasColumn('stock_histories', 'transaction_type')) {
                Schema::table('stock_histories', function (Blueprint $table) {
                    $table->string('transaction_type')
                        ->nullable()
                        ->after('product_id')
                        ->comment('purchase, sale, adjustment');
                });
            }
        }

        // Step 4: Drop unnecessary columns from products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $driver = Schema::getConnection()->getDriverName();
                // only SQLite needs indexes removed explicitly
                if ($driver === 'sqlite' && Schema::hasColumn('products', 'barcode')) {
                    try {
                        $table->dropUnique(['barcode']);
                    } catch (\Exception $e) {
                        // ignore
                    }
                    try {
                        $table->dropIndex(['barcode']);
                    } catch (\Exception $e) {
                        // ignore
                    }
                }

                $columnsToRemove = ['unit_of_measurement', 'minimum_stock', 'initial_stock', 'barcode', 'image'];
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Step 5: Simplify purchases table
        if (Schema::hasTable('purchases')) {
            $driver = Schema::getConnection()->getDriverName();
            
            // For SQLite, drop indexes using raw SQL before modifying the table
            if ($driver === 'sqlite') {
                $indexesToDrop = ['purchases_reference_number_unique', 'purchases_invoice_number_unique'];
                foreach ($indexesToDrop as $index) {
                    try {
                        DB::statement("DROP INDEX IF EXISTS `$index`");
                    } catch (\Exception $e) {
                        // Index might not exist
                    }
                }
            }
            
            Schema::table('purchases', function (Blueprint $table) {
                // drop foreign key on created_by if present
                if (Schema::hasColumn('purchases', 'created_by')) {
                    try {
                        $table->dropForeign(['created_by']);
                    } catch (\Exception $e) {
                    }
                    $table->dropColumn('created_by');
                }

                $columnsToRemove = ['reference_number', 'invoice_number', 'delivery_date', 'shipping_cost', 'tax_amount', 'discount_amount', 'payment_method'];
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('purchases', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Step 6: Simplify purchase_items table
        if (Schema::hasTable('purchase_items')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_items', 'tax_rate')) {
                    $table->dropColumn('tax_rate');
                }
                if (Schema::hasColumn('purchase_items', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        }

        // Step 7: Simplify sales table
        if (Schema::hasTable('sales')) {
            $driver = Schema::getConnection()->getDriverName();
            
            // For SQLite, drop indexes using raw SQL before modifying the table
            if ($driver === 'sqlite') {
                try {
                    DB::statement("DROP INDEX IF EXISTS `sales_reference_number_unique`");
                } catch (\Exception $e) {
                    // Index might not exist
                }
            }
            
            Schema::table('sales', function (Blueprint $table) {
                // drop foreign key on created_by if present
                if (Schema::hasColumn('sales', 'created_by')) {
                    try {
                        $table->dropForeign(['created_by']);
                    } catch (\Exception $e) {
                    }
                    $table->dropColumn('created_by');
                }

                $columnsToRemove = ['reference_number', 'due_date', 'shipping_cost', 'tax_amount', 'discount_amount', 'payment_status'];
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('sales', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        // Step 8: Simplify sale_items table
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (Schema::hasColumn('sale_items', 'tax_rate')) {
                    $table->dropColumn('tax_rate');
                }
                if (Schema::hasColumn('sale_items', 'discount')) {
                    $table->dropColumn('discount');
                }
            });
        }

        // Step 9: Drop stock_adjustments table
        if (Schema::hasTable('stock_adjustments')) {
            Schema::dropIfExists('stock_adjustments');
        }
    }

    public function down(): void
    {
        throw new \Exception('This migration cannot be reversed. Restore from backup if needed.');
    }
};
