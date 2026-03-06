<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->string('sku')->unique(); // Stock Keeping Unit
        $table->string('barcode')->unique()->nullable(); // Optional barcode for future scanner support
        $table->foreignId('category_id')->constrained();
        $table->foreignId('supplier_id')->constrained();
        $table->decimal('cost_price', 10, 2); // What you paid
        $table->decimal('selling_price', 10, 2); // What you sell for
        $table->integer('quantity')->default(0); // Legacy field
        $table->integer('current_stock')->default(0); // Current stock quantity
        $table->integer('initial_stock')->nullable(); // Initial stock when added
        $table->integer('reorder_level')->default(10); // Alert when stock ≤ this
        $table->integer('minimum_stock')->nullable(); // Minimum stock to maintain
        $table->string('unit')->nullable(); // Legacy field
        $table->string('unit_of_measurement')->nullable(); // Unit of measurement
        $table->string('image')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        
        // Add indexes for faster searching
        $table->index('sku');
        $table->index('barcode');
        $table->index('category_id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
