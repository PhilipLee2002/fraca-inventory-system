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
        Schema::table('purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->after('quantity');
            }
            if (!Schema::hasColumn('purchase_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('purchase_items', 'discount')) {
                $table->decimal('discount', 10, 2)->default(0)->after('tax_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumnIfExists('unit_price');
            $table->dropColumnIfExists('tax_rate');
            $table->dropColumnIfExists('discount');
        });
    }
};
