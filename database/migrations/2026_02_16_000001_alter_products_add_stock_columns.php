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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'current_stock')) {
                $table->integer('current_stock')->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('products', 'initial_stock')) {
                $table->integer('initial_stock')->nullable()->after('current_stock');
            }
            if (!Schema::hasColumn('products', 'minimum_stock')) {
                $table->integer('minimum_stock')->nullable()->after('initial_stock');
            }
            if (!Schema::hasColumn('products', 'unit_of_measurement')) {
                $table->string('unit_of_measurement')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumnIfExists('current_stock');
            $table->dropColumnIfExists('initial_stock');
            $table->dropColumnIfExists('minimum_stock');
            $table->dropColumnIfExists('unit_of_measurement');
            $table->dropColumnIfExists('is_active');
        });
    }
};
