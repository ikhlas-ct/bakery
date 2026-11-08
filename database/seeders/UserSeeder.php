<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun login. Password sama dengan username
     * (admin/admin, pemilik/pemilik, produsen1/produsen1, dst).
     */
    public function run(): void
    {
        $akuns = [
            ['username' => 'admin',     'role' => 'admin'],
            ['username' => 'pemilik',   'role' => 'pemilik'],
            ['username' => 'produsen1', 'role' => 'produsen'],
            ['username' => 'produsen2', 'role' => 'produsen'],
            ['username' => 'produsen3', 'role' => 'produsen'],
            ['username' => 'produsen4', 'role' => 'produsen'],
        ];

        foreach ($akuns as $akun) {
            $email = $akun['username'] . '@bakery.test';

            // forceFill dipakai supaya tidak bergantung pada $fillable model User
            $user = User::where('email', $email)->first() ?? new User();

            $user->forceFill([
                'username'    => $akun['username'],
                'email'       => $email,
                'password'    => Hash::make($akun['username']),
                'role'        => $akun['role'],
                'is_active'   => true,
                'foto_profil' => 'default.png',
            ])->save();
        }
    }
}
