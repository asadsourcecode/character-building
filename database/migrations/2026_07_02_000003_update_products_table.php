<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('compare_at_price', 10, 2)->nullable()->after('sale_price');
            $table->decimal('unit_price', 10, 2)->nullable()->after('compare_at_price');
            $table->string('unit')->nullable()->after('unit_price');
            $table->decimal('size', 10, 2)->nullable()->after('unit');
            $table->string('size_unit')->nullable()->after('size');
            $table->decimal('total_amount', 10, 2)->nullable()->after('size_unit');
            $table->string('base_measure')->nullable()->after('total_amount');
            $table->json('variants')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'compare_at_price',
                'unit_price',
                'unit',
                'size',
                'size_unit',
                'total_amount',
                'base_measure',
                'variants'
            ]);
        });
    }
};
