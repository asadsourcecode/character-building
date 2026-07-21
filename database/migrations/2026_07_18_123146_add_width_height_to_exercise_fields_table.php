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
        Schema::table('exercise_fields', function (Blueprint $table) {
            $table->decimal('width', 5, 2)->default(20)->after('position_y');
            $table->decimal('height', 5, 2)->default(8)->after('width');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_fields', function (Blueprint $table) {
            $table->dropColumn(['width', 'height']);
        });
    }
};
