<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('bg_color', 20)->default('#ecfde8');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('buy_btn_text', 100)->default('Buy Now');
            $table->string('coming_soon_text', 100)->default('Coming Soon');
            $table->string('unavailable_text', 100)->default('Not Available');
            $table->string('separator_text', 100)->default('Or Buy Separately');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sections');
    }
};
