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
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained();
        $table->foreignId('user_id')->constrained(); // Who made the purchase
        $table->string('purchase_number')->unique()->nullable();
        $table->date('purchase_date')->nullable();
        $table->date('delivery_date')->nullable();
        $table->string('reference_number')->nullable();
        $table->string('invoice_number')->nullable();
        $table->decimal('total_amount', 10, 2);
        $table->decimal('shipping_cost', 10, 2)->default(0);
        $table->decimal('tax_amount', 10, 2)->default(0);
        $table->decimal('discount_amount', 10, 2)->default(0);
        $table->string('payment_method')->nullable();
        $table->string('status')->default('pending'); // pending, received, cancelled
        $table->foreignId('created_by')->nullable()->constrained('users');
        $table->text('notes')->nullable();
        $table->timestamps();
    });
    
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
