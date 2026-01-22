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
    Schema::create('alerts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained();
        $table->string('type'); // low_stock, out_of_stock, expiry_near
        $table->text('message');
        $table->boolean('is_read')->default(false);
        $table->timestamps();
        
        $table->index('is_read');
        $table->index('created_at');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
