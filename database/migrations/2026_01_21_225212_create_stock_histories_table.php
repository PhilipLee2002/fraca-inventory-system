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
    Schema::create('stock_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained();
        $table->string('transaction_type'); // purchase, sale, adjustment, return
        $table->integer('quantity_change'); // +10 or -5
        $table->integer('previous_quantity');
        $table->integer('new_quantity');
        $table->foreignId('reference_id')->nullable(); // purchase_id or sale_id
        $table->string('reference_type')->nullable(); // Purchase::class or Sale::class
        $table->text('notes')->nullable();
        $table->timestamps();
        
        $table->index('product_id');
        $table->index('created_at'); // For filtering by date
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_histories');
    }
};
