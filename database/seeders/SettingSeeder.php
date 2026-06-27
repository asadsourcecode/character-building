<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'site_settings'],
            [
                'group' => 'general',
                'type' => 'site',
                'site_name' => 'ICE | Integrated Character Education',
                'email' => 'Info@characterbuilding.education',
                'phone' => '+45 50106941',
                'address' => '124-128, City Road London EC1V 2NX',
                'status' => true,
            ],
        );
    }
}
