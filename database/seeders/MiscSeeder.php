<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MiscSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('misc_pages')->insert([
            'about' => "about page",
            "privacy_terms" => "privacy and terms",
            "faq" => json_encode([
                "question" => "answer",
            ]),
            "contact_us" => "put your number or a link",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
