<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Masyarakat;
use App\Models\Pegawai;


class CustomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

        public function run()
    {
        // Buat 10 user dengan role 'masyarakat'
        // $masyarakatUsers = User::factory()->count(10)->create([
        //     'role' => 'masyarakat'
        // ]);

        // Untuk setiap user masyarakat, buat record masyarakat dengan id_user sesuai
        // foreach ($masyarakatUsers as $user) {
        //     Masyarakat::factory()->create([
        //         'id_user' => $user->id,
        //         // Pastikan field lain di factory Masyarakat akan menggunakan data dummy yang sesuai
        //     ]);
        // }

        // Buat 5 user dengan role 'pegawai'
        $pegawaiUsers = User::factory()->count(100)->create([
            'role' => 'pegawai'
        ]);

        // Untuk setiap user pegawai, buat record pegawai dengan id_user yang sama
        foreach ($pegawaiUsers as $user) {
            Pegawai::factory()->create([
                'id_user' => $user->id,
                // Pastikan field lain di factory Pegawai akan menggunakan data dummy yang sesuai
            ]);
        }
    }

 }

