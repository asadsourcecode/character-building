<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->decimal('ebook_price', 10, 2)->nullable()->after('sale_price');
            $table->decimal('hardcopy_price', 10, 2)->nullable()->after('ebook_price');
            $table->integer('pages')->nullable()->after('hardcopy_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'ebook_price', 'hardcopy_price', 'pages']);
        });
    }
};
