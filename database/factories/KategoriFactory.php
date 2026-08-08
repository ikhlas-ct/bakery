<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KategoriFactory extends Factory
{
    protected $model = Kategori::class;

    public function definition()
    {
        $nama = $this->faker->unique()->word;
        return [
            'nama_kategori' => ucfirst($nama),
            'slug' => Str::slug($nama),
            'status' => $this->faker->boolean(80), // true 80% dari waktu
        ];
    }
}
