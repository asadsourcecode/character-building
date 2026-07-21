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
        Schema::create('exercise_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->string('type'); // radio|checkbox|text|textarea
            $table->string('label');
            $table->json('options')->nullable();
            $table->decimal('position_x', 5, 2)->default(0);
            $table->decimal('position_y', 5, 2)->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_fields');
    }
};
