<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Urutan penting karena foreign key:
     * satuans & kategori_barangs -> users -> admins/pemiliks/produsens
     * -> bahan_bakus -> bahan_baku_produsens -> permintaan -> penerimaan -> pembayaran -> pemakaian
     */
    public function run(): void
    {
        $this->call([
            // 1. Master tanpa dependensi
            SatuanSeeder::class,
            KategoriBarangSeeder::class,

            // 2. Akun login, lalu profil per role (butuh users)
            UserSeeder::class,
            AdminSeeder::class,
            PemilikSeeder::class,
            ProdusenSeeder::class,

            // 3. Bahan baku (butuh satuans & kategori_barangs)
            BahanBakuSeeder::class,

            // 4. Harga bahan baku per produsen (butuh bahan_bakus & produsens)
            BahanBakuProdusenSeeder::class,

            // 5. Permintaan + detail + pengalihan (butuh admin, produsen, bahan_baku_produsens)
            PermintaanBahanBakuSeeder::class,

            // 6. Penerimaan barang (saldo awal + dari permintaan yang disetujui) dan tagihan pembayarannya
            PenerimaanBahanBakuSeeder::class,

            // 7. Status pembayaran (menunggu / disetujui / ditolak / lunas) + gambar bukti contoh
            PembayaranSeeder::class,

            // 8. Pemakaian bahan baku (FEFO per batch penerimaan); stok dihitung ulang di sini
            PemakaianBahanBakuSeeder::class,

            // CustomSeeder::class, // dimatikan: memakai model Masyarakat/Pegawai yang tidak ada di proyek ini
        ]);
    }
}
