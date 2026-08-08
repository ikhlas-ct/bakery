<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PegawaiFactory extends Factory
{
    protected $model = Pegawai::class;


    public function definition()
    {
        $user = User::factory()->create(['role' => $this->faker->randomElement(['pegawai', 'camat'])]);

        return [
            'id_user' => $user->id,
            'role' => $user->role,
            'nama_pegawai' => $this->faker->name,
            'alamat_pegawai' => $this->faker->address,
            'nohp_pegawai' => $this->faker->phoneNumber,
            'email_pegawai' => $this->faker->unique()->safeEmail,
            'deskripsi' => $this->faker->text(200),
            'nik' => $this->faker->unique()->numerify('################'), // 16 digit
            'nip' => $this->faker->unique()->numerify('####################'), // 20 digit
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'jabatan' => $this->faker->jobTitle,
            'twitter' => $this->faker->word,
            'instagram' => $this->faker->word,
            'facebook' => $this->faker->word,
            'pangkat_golongan' => $this->faker->word,
            'foto_profil' => 'default.png',
        ];
    }
}
