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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('site_name')->nullable()->after('id');
            $table->string('email')->nullable()->after('site_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('secondary_phone')->nullable()->after('phone');
            $table->string('logo')->nullable()->after('secondary_phone');
            $table->string('favicon')->nullable()->after('logo');
            $table->text('address')->nullable()->after('favicon');
            $table->string('facebook_url')->nullable()->after('address');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('youtube_url')->nullable()->after('instagram_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'site_name',
                'email',
                'phone',
                'secondary_phone',
                'logo',
                'favicon',
                'address',
                'facebook_url',
                'instagram_url',
                'youtube_url',
            ]);
        });
    }
};
