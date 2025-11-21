<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            "delivery_coverage" => 5,
            "company_share" => 1,
            'cost_per_km' => 5,
            'phone' => '+201012345678',
            'address' => '@test123',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
