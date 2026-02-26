<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Rename the column only if it exists and the target doesn't
            if (Schema::hasColumn('roles', 'permissions') && !Schema::hasColumn('roles', 'description')) {
                $table->renameColumn('permissions', 'description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('description', 'permissions');
        });
    }
};