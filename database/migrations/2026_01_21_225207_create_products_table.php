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
        $table->string('barcode')->unique(); // ← UNIQUE CONSTRAINT
        $table->foreignId('category_id')->constrained();
        $table->foreignId('supplier_id')->constrained();
        $table->decimal('cost_price', 10, 2); // What you paid
        $table->decimal('selling_price', 10, 2); // What you sell for
        $table->integer('quantity')->default(0);
        $table->integer('reorder_level')->default(10); // Alert when stock ≤ this
        $table->string('unit'); // pieces, kg, liters, etc.
        $table->string('image')->nullable();
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
