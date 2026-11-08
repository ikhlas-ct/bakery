<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('username', 'admin')->firstOrFail();

        $data = [
            'nip'     => 'ADM-001',
            'no_telp' => '081200000001',
            'alamat'  => 'Jl. Braga No. 25, Bandung, Jawa Barat',
        ];

        // Model Admin punya "nama" di $fillable, tapi tabel di file SQL belum punya kolomnya.
        // Diisi hanya kalau kolomnya memang ada di database.
        if (Schema::hasColumn('admins', 'nama')) {
            $data['nama'] = 'Dewi Lestari';
        }

        Admin::updateOrCreate(['user_id' => $user->id], $data);
    }
}
