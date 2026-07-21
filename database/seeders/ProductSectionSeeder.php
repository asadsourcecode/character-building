<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSectionSeeder extends Seeder
{
    // class number → key stage
    private const CLASS_KS = [
        1 => 1, 2 => 1,
        3 => 2, 4 => 2, 5 => 2,
        6 => 3, 7 => 3, 8 => 3,
        9 => 4, 10 => 4, 11 => 4,
    ];

    public function run(): void
    {
        // clear existing sections (use DELETE not truncate — Postgres CASCADE truncate wipes products too)
        DB::statement('UPDATE products SET section_id = NULL');
        DB::table('product_sections')->delete();

        $sections = [
            [
                'data' => [
                    'title'            => 'Key Stage 1 Package (Grade 1 & 2)',
                    'description'      => 'Complete classroom set for Key Stage 1, covering Grades 1 & 2.',
                    'image'            => null,
                    'price'            => 397.00,
                    'sale_price'       => 125.00,
                    'bg_color'         => '#ecfde8',
                    'sort_order'       => 1,
                    'layout'           => 'rows',
                    'buy_btn_text'     => 'Buy Now',
                    'coming_soon_text' => 'Coming Soon',
                    'unavailable_text' => 'Not Available',
                    'separator_text'   => 'Or Buy Separately',
                    'is_available'     => true,
                ],
                'classes' => [1, 2],
                'ks'      => 1,
            ],
            [
                'data' => [
                    'title'            => 'Key Stage 2 Package (Grade 3, 4 & 5)',
                    'description'      => 'Complete classroom set for Key Stage 2, covering Grades 3, 4 & 5.',
                    'image'            => null,
                    'price'            => 580.00,
                    'sale_price'       => 225.00,
                    'bg_color'         => '#e8f7fd',
                    'sort_order'       => 2,
                    'layout'           => 'rows',
                    'buy_btn_text'     => 'Buy Now',
                    'coming_soon_text' => 'Coming Soon',
                    'unavailable_text' => 'Not Available',
                    'separator_text'   => 'Or Buy Separately',
                    'is_available'     => true,
                ],
                'classes' => [3, 4, 5],
                'ks'      => 2,
            ],
            [
                'data' => [
                    'title'            => 'Key Stage 3 Package (Grade 6, 7 & 8)',
                    'description'      => 'Complete classroom set for Key Stage 3, covering Grades 6, 7 & 8.',
                    'image'            => null,
                    'price'            => 445.00,
                    'sale_price'       => 325.00,
                    'bg_color'         => '#f9e8fd',
                    'sort_order'       => 3,
                    'layout'           => 'rows',
                    'buy_btn_text'     => 'Buy Now',
                    'coming_soon_text' => 'Coming Soon',
                    'unavailable_text' => 'Not Available',
                    'separator_text'   => 'Or Buy Separately',
                    'is_available'     => true,
                ],
                'classes' => [6, 7, 8],
                'ks'      => 3,
            ],
            [
                'data' => [
                    'title'            => 'Key Stage 4 Package (Grade 9, 10 & 11)',
                    'description'      => 'Key Stage 4 materials are currently in development.',
                    'image'            => null,
                    'price'            => null,
                    'sale_price'       => null,
                    'bg_color'         => '#fdeee8',
                    'sort_order'       => 4,
                    'layout'           => 'rows',
                    'buy_btn_text'     => 'Buy Now',
                    'coming_soon_text' => 'Coming Soon',
                    'unavailable_text' => 'Not Available',
                    'separator_text'   => 'Or Buy Separately',
                    'is_available'     => false,
                ],
                'classes' => [9, 10, 11],
                'ks'      => 4,
            ],
            [
                'data' => [
                    'title'            => "Gradewise Educator's Course Box",
                    'description'      => "Everything a teacher needs — training manual, student textbooks, and classroom resources in one box.",
                    'image'            => null,
                    'price'            => null,
                    'sale_price'       => null,
                    'bg_color'         => '#ffffff',
                    'sort_order'       => 5,
                    'layout'           => 'grid',
                    'buy_btn_text'     => 'Buy Now',
                    'coming_soon_text' => 'Coming Soon',
                    'unavailable_text' => 'Not Available',
                    'separator_text'   => 'Or Buy Separately',
                    'is_available'     => true,
                ],
                'classes' => [],
                'ks'      => null,
            ],
        ];

        foreach ($sections as $entry) {
            $section = ProductSection::create($entry['data']);

            if ($entry['ks'] !== null) {
                $ks = $entry['ks'];
                $classes = $entry['classes'];

                // Assign individual books for this key stage (textbook, guide, poster)
                Product::whereIn('product_type', ['textbook', 'guide', 'poster'])
                    ->where(function ($q) use ($classes) {
                        foreach ($classes as $c) {
                            $q->orWhere('slug', 'like', "class-{$c}-%");
                        }
                    })
                    ->update(['section_id' => $section->id]);

                // Assign methodology for this key stage
                Product::where('slug', "methodology-key-stage-{$ks}")
                    ->update(['section_id' => $section->id]);

                // KS package products (id 1-4) stay with section_id=NULL — their data is now in the section itself
            } else {
                // Educator boxes
                Product::where('product_type', 'educator-box')
                    ->update(['section_id' => $section->id]);
            }
        }

        $this->command->info('Product sections re-seeded correctly.');
    }
}
