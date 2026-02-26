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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('old_stock'); // Stock before adjustment
            $table->integer('new_stock'); // Stock after adjustment
            $table->enum('adjustment_type', ['addition', 'deduction', 'correction']); // Type of adjustment
            $table->integer('quantity_changed'); // Absolute value of change
            $table->string('reason'); // Why the adjustment was made
            $table->text('notes')->nullable(); // Additional notes
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('restrict'); // Who made the adjustment
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('product_id');
            $table->index('adjusted_by');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
