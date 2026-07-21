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
        Schema::table('products', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('sample_pdf');
            $table->string('pdf_conversion_status')->nullable()->after('pdf_path'); // processing|ready|failed
            $table->unsignedInteger('pdf_page_count')->default(0)->after('pdf_conversion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['pdf_path', 'pdf_conversion_status', 'pdf_page_count']);
        });
    }
};
