<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'sale_date')) {
                $table->date('sale_date')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('sales', 'due_date')) {
                $table->date('due_date')->nullable()->after('sale_date');
            }
            if (!Schema::hasColumn('sales', 'reference_number')) {
                $table->string('reference_number')->unique()->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('sales', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('reference_number');
            }
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('shipping_cost');
            }
            if (!Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            }
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->string('payment_status')->default('unpaid')->after('discount_amount');
            }
            if (!Schema::hasColumn('sales', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('payment_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // skip drop operations on sqlite to avoid index errors during refresh
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumnIfExists('sale_date');
            $table->dropColumnIfExists('due_date');
            $table->dropColumnIfExists('reference_number');
            $table->dropColumnIfExists('shipping_cost');
            $table->dropColumnIfExists('tax_amount');
            $table->dropColumnIfExists('discount_amount');
            $table->dropColumnIfExists('payment_status');
            $table->dropForeignKeyIfExists(['created_by_id']);
            $table->dropColumnIfExists('created_by');
        });
    }
};
