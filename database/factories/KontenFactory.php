<?php

namespace Database\Factories;

use App\Models\Konten;
use App\Models\Pegawai;
use App\Models\Kategori;
use App\Models\Masyarakat;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class KontenFactory extends Factory
{
    protected $model = Konten::class;

    public function definition()
    {
        $judul = $this->faker->unique()->sentence;
        $jenis = $this->faker->randomElement(['berita', 'artikel']);
        $user = $this->faker->randomElement([
            Pegawai::inRandomOrder()->first()?->id_pegawai ?? Pegawai::factory()->create()->id_pegawai,
            Masyarakat::inRandomOrder()->first()?->id_masyarakat ?? Masyarakat::factory()->create()->id_masyarakat,
        ]);


        return [
            'judul'             => $judul,
            'isi'               => $this->faker->paragraphs(100, true),
            // Pilih pegawai secara acak; jika belum ada, maka buat baru
            'id_user'           => $user,
            'slug'              => Str::slug($judul),
            'tanggal_publikasi' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'jenis_konten'      => $jenis,
            // Pilih kategori secara acak; jika belum ada, maka buat baru
            'id_kategori'       => Kategori::inRandomOrder()->first()?->id_kategori ?? Kategori::factory()->create()->id_kategori,
            'gambar'            => $this->faker->imageUrl(800, 600, 'news', true),
            'aktif'             => $this->faker->boolean(70),
        ];
    }
}
