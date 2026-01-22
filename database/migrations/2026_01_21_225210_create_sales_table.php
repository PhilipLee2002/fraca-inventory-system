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
    Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained();
        $table->foreignId('user_id')->constrained(); // Who made the sale
        $table->string('invoice_number')->unique();
        $table->decimal('total_amount', 10, 2);
        $table->string('payment_method'); // cash, card, transfer
        $table->string('status')->default('completed'); // completed, pending, cancelled
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
