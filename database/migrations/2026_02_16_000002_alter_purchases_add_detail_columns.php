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
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('purchase_number');
            }
            if (!Schema::hasColumn('purchases', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('purchases', 'reference_number')) {
                $table->string('reference_number')->unique()->nullable()->after('delivery_date');
            }
            if (!Schema::hasColumn('purchases', 'invoice_number')) {
                $table->string('invoice_number')->unique()->nullable()->after('reference_number');
            }
            if (!Schema::hasColumn('purchases', 'shipping_cost')) {
                $table->decimal('shipping_cost', 10, 2)->default(0)->after('invoice_number');
            }
            if (!Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('shipping_cost');
            }
            if (!Schema::hasColumn('purchases', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            }
            if (!Schema::hasColumn('purchases', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->after('discount_amount');
            }
            if (!Schema::hasColumn('purchases', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // skip entire down process on sqlite to avoid index-removal bugs
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumnIfExists('purchase_date');
            $table->dropColumnIfExists('delivery_date');
            $table->dropColumnIfExists('reference_number');
            $table->dropColumnIfExists('invoice_number');
            $table->dropColumnIfExists('shipping_cost');
            $table->dropColumnIfExists('tax_amount');
            $table->dropColumnIfExists('discount_amount');
            $table->dropForeignKeyIfExists(['created_by_id']);
            $table->dropColumnIfExists('created_by');
            $table->dropColumnIfExists('payment_method');
        });
    }
};
