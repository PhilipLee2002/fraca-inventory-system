<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('city')->nullable()->after('address');
            $table->string('country')->nullable()->after('city');
            $table->boolean('is_active')->default(true)->after('country');
            $table->text('notes')->nullable()->after('is_active');
            // Make phone nullable
            $table->string('phone')->nullable()->change();
        });

        // Migrate existing 'name' data into first_name
        DB::statement("UPDATE customers SET first_name = name WHERE first_name IS NULL");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'city', 'country', 'is_active', 'notes']);
            $table->string('phone')->nullable(false)->change();
        });
    }
};
