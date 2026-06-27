<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            'header' => 'Header',
            'footer' => 'Footer',
        ] as $location => $name) {
            if (! DB::table('menus')->where('location', $location)->exists()) {
                DB::table('menus')->insert([
                    'location' => $location,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')
            ->whereIn('location', ['header', 'footer'])
            ->whereNotExists(function ($query) {
                $query
                    ->selectRaw('1')
                    ->from('menu_items')
                    ->whereColumn('menu_items.menu_id', 'menus.id');
            })
            ->delete();
    }
};
