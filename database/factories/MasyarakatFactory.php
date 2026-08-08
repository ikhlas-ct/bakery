<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Masyarakat;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Masyarakat>
 */
class MasyarakatFactory extends Factory
{

    protected $model = Masyarakat::class;

    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numerify('################'), // 16 digit
            // Untuk id_user, kita buat user terlebih dahulu:
            'id_user' => User::factory()->create()->id,
            'kk' => $this->faker->numerify('################'), // 16 digit
            'nama_masyarakat' => $this->faker->name,
            'jenis_kelamin' => $this->faker->randomElement(['laki-laki', 'perempuan']),
            'no_hp' => $this->faker->phoneNumber,
            'nama_ibu' => $this->faker->name('female'),
            'status_keluarga' => $this->faker->randomElement(['Kepala Keluarga', 'Anggota']),
            'nama_ayah' => $this->faker->name('male'),
            'alamat' => $this->faker->address,
            'tempat_lahir' => $this->faker->city,
            'tanggal_lahir' => $this->faker->date,
            'scan_ktp' => 'scan_ktp.png', // nilai dummy
            'scan_kk' => 'scan_kk.png',
            'foto_diri_ktp' => 'foto_diri_ktp.png',
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']),
            'pendidikan' => $this->faker->randomElement(['SD', 'SMP', 'SMA', 'S1', 'S2']),
            'pekerjaan' => $this->faker->jobTitle,
            'golongan_darah' => $this->faker->randomElement(['A', 'B', 'AB', 'O']),
            'status_perkawinan' => $this->faker->randomElement(['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'foto_profil' => 'default.png',
        ];

    }
}
