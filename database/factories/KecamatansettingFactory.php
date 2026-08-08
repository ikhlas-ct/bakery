<?php

namespace Database\Factories;

use App\Models\Kecamatansetting;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

class KecamatansettingFactory extends Factory
{
    protected $model = Kecamatansetting::class;

    public function definition()
    {
        return [
            'nama_kecamatan' => $this->faker->city,
            'kode_kecamatan' => $this->faker->bothify('KC-##'),
            'kode_pos_kecamatan' => $this->faker->numerify('##########'),
            // Jika ada data pegawai, gunakan salah satunya; jika tidak, null
            'id_pegawai' => Pegawai::inRandomOrder()->first()?->id_pegawai ?? null,
            'alamat_kecamatan' => $this->faker->address,
            'email_kecamatan' => $this->faker->unique()->safeEmail,
            'nomor_telepon_kecamatan' => $this->faker->phoneNumber,
            'nama_kabupaten' => $this->faker->city,
            'kode_kabupaten' => $this->faker->bothify('KB-##'),
            'provinsi' => $this->faker->state,
            'kode_provinsi' => $this->faker->bothify('PR-##'),
            'logo' => 'logo.png',
            'social_facebook' => 'https://facebook.com/' . $this->faker->userName,
            'social_instagram' => 'https://instagram.com/' . $this->faker->userName,
            'social_twitter' => 'https://twitter.com/' . $this->faker->userName,
            'visi_misi' => $this->faker->paragraphs(3, true),
            'sejarah' => $this->faker->paragraphs(5, true),
            'geografis' => $this->faker->paragraphs(5, true),
            'tugas_pokok' => $this->faker->sentence,
            'fungsi' => $this->faker->sentence,
            'uraian_tugas' => $this->faker->paragraphs(3, true),
        ];
    }
}
