<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('contact_person')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
};
