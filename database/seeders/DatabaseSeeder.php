<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pegawai;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

         // Panggil seeder khusus untuk user, masyarakat, dan pegawai
        $this->call([
            CustomSeeder::class,
            // CustomKategoriKontenSeeder::class,
            // KecamatansettingSeeder::class,


        ]);



    }
}

