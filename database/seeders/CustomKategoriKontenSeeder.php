<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Konten;

class CustomKategoriKontenSeeder extends Seeder
{
    public function run()
    {
        // Buat 5 kategori
        $kategoris = Kategori::factory()->count(5)->create();

        // Untuk setiap kategori, buat 3-7 konten
        foreach ($kategoris as $kategori) {
            Konten::factory()->count(rand(3, 7))->create([
                'id_kategori' => $kategori->id_kategori,
            ]);
        }
    }
}
