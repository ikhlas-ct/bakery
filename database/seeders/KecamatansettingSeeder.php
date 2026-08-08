<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kecamatansetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KecamatansettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Misalnya kita ingin membuat 5 data kecamatansetting
        Kecamatansetting::factory()->count(1)->create();
    }
}
