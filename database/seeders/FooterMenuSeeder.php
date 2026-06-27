<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FooterMenuSeeder extends Seeder
{
    public function run(): void
    {
        $footerId = DB::table('menus')->where('location', 'footer')->value('id');

        if (! $footerId) {
            return;
        }

        DB::table('menu_items')->where('menu_id', $footerId)->delete();

        $items = [
            ['title' => 'Home',                       'custom_url' => '#home',              'sort_order' => 1],
            ['title' => 'About',                      'custom_url' => '#about',             'sort_order' => 2],
            ['title' => 'Our Mission',                'custom_url' => '#mission',           'sort_order' => 3],
            ['title' => 'Video Presentations',        'custom_url' => '#videos',            'sort_order' => 4],
            ['title' => 'Contact Us',                 'custom_url' => '#contact',           'sort_order' => 5],
            ['title' => 'Free Sample Lessons',        'custom_url' => '#samples',           'sort_order' => 6],
            ['title' => 'Introduction',               'custom_url' => '#intro',             'sort_order' => 7],
            ['title' => 'Books',                      'custom_url' => '#books',             'sort_order' => 8],
            ['title' => 'Pricing',                    'custom_url' => '#pricing',           'sort_order' => 9],
            ['title' => 'Online Classes',             'custom_url' => '#online-classes',    'sort_order' => 10],
            ['title' => 'eBooks',                     'custom_url' => '#ebooks',            'sort_order' => 11],
            ['title' => 'Frequently Asked Questions', 'custom_url' => '#faq',               'sort_order' => 12],
            ['title' => 'Blog',                       'custom_url' => '#blog',              'sort_order' => 13],
            ['title' => 'Audio Stories',              'custom_url' => '#audio-stories',     'sort_order' => 14],
            ['title' => 'Methodology',                'custom_url' => '#methodology',       'sort_order' => 15],
            ['title' => 'Digital Courses',            'custom_url' => '#digital-courses',   'sort_order' => 16],
            ['title' => 'Homeschooling',              'custom_url' => '#homeschooling',     'sort_order' => 17],
            ['title' => 'Counselling',                'custom_url' => '#counselling',       'sort_order' => 18],
            ['title' => "Teacher's Training",         'custom_url' => '#teachers-training', 'sort_order' => 19],
            ['title' => 'User Login',                 'custom_url' => '#login',             'sort_order' => 20],
            ['title' => 'Complete Classwise Courses', 'custom_url' => '#courses',           'sort_order' => 21],
            ['title' => 'Logotherapy',                'custom_url' => '#logotherapy',       'sort_order' => 22],
        ];

        $now = now();

        foreach ($items as $item) {
            DB::table('menu_items')->insert([
                'menu_id'    => $footerId,
                'parent_id'  => null,
                'page_id'    => null,
                'title'      => $item['title'],
                'custom_url' => $item['custom_url'],
                'sort_order' => $item['sort_order'],
                'status'     => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
