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
            $table->unsignedSmallInteger('font_size')->default(14)->after('height');
            $table->string('font_family')->default('Work Sans, sans-serif')->after('font_size');
            $table->string('font_weight')->default('normal')->after('font_family');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_fields', function (Blueprint $table) {
            $table->dropColumn(['font_size', 'font_family', 'font_weight']);
        });
    }
};
