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
        $table->string('purchase_number')->unique();
        $table->decimal('total_amount', 10, 2);
        $table->string('status')->default('pending'); // pending, completed, cancelled
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
