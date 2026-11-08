<?php

namespace Database\Seeders;

use App\Models\KategoriBarang;
use Illuminate\Database\Seeder;

class KategoriBarangSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            'Tepung & Pati',
            'Gula & Pemanis',
            'Telur',
            'Susu & Olahan Susu',
            'Lemak & Minyak',
            'Pengembang, Ragi & Pengental',
            'Cokelat & Kakao',
            'Kacang & Biji-bijian',
            'Buah, Selai & Isian',
            'Perisa, Essence & Pewarna',
            'Rempah & Bumbu',
            'Topping & Dekorasi',
            'Kemasan & Pembungkus',
            'Perlengkapan Baking',
        ];

        foreach ($kategoris as $nama) {
            KategoriBarang::firstOrCreate(['nama_kategori' => $nama]);
        }
    }
}
