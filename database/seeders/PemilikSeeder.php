<?php

namespace Database\Seeders;

use App\Models\Pemilik;
use App\Models\User;
use Illuminate\Database\Seeder;

class PemilikSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('username', 'pemilik')->firstOrFail();

        Pemilik::updateOrCreate(
            ['user_id' => $user->id],
            [
                'no_telp' => '081200000002',
                'alamat'  => 'Jl. Asia Afrika No. 88, Bandung, Jawa Barat',
            ]
        );
    }
}
