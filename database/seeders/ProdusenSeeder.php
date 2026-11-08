<?php

namespace Database\Seeders;

use App\Models\Produsen;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProdusenSeeder extends Seeder
{
    public function run(): void
    {
        $produsens = [
            'produsen1' => [
                'nama_produsen' => 'CV Sumber Tepung Makmur',
                'alamat'        => 'Jl. Raya Cakung No. 45, Cakung, Jakarta Timur',
                'no_telp'       => '081234567801',
            ],
            'produsen2' => [
                'nama_produsen' => 'UD Segar Dairy Nusantara',
                'alamat'        => 'Jl. Raya Lembang No. 112, Lembang, Bandung Barat',
                'no_telp'       => '082134567802',
            ],
            'produsen3' => [
                'nama_produsen' => 'PT Cokelat Prima Rasa',
                'alamat'        => 'Jl. Ahmad Yani No. 88, Gayungan, Surabaya',
                'no_telp'       => '081334567803',
            ],
            'produsen4' => [
                'nama_produsen' => 'Toko Grosir Berkah Jaya',
                'alamat'        => 'Jl. Pasar Baru No. 21, Sawah Besar, Jakarta Pusat',
                'no_telp'       => '085234567804',
            ],
        ];

        foreach ($produsens as $username => $data) {
            $user = User::where('username', $username)->firstOrFail();

            Produsen::updateOrCreate(
                ['user_id' => $user->id],
                $data + ['status_mitra' => 'aktif']
            );
        }
    }
}
