<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use Illuminate\Database\Seeder;
use RuntimeException;

class BahanBakuSeeder extends Seeder
{
    public function run(): void
    {
        $satuans   = Satuan::pluck('id', 'nama')->all();
        $kategoris = KategoriBarang::pluck('id', 'nama_kategori')->all();

        foreach (self::daftar() as $namaKategori => $kelompok) {
            $kategoriId = $kategoris[$namaKategori]
                ?? throw new RuntimeException("Kategori '{$namaKategori}' belum ada. Jalankan KategoriBarangSeeder dulu.");

            foreach ($kelompok['item'] as $i => [$nama, $namaSatuan, $hargaAcuan, $stok, $stokMin]) {
                $satuanId = $satuans[$namaSatuan]
                    ?? throw new RuntimeException("Satuan '{$namaSatuan}' belum ada. Jalankan SatuanSeeder dulu.");

                BahanBaku::updateOrCreate(
                    ['kode_bahan_baku' => self::kode($kelompok['prefix'], $i)],
                    [
                        'nama_bahan_baku'    => $nama,
                        'kategori_barang_id' => $kategoriId,
                        'satuan_id'          => $satuanId,
                        'stok_saat_ini'      => $stok,
                        'stok_minimum'       => $stokMin,
                        'is_aktif'           => true,
                    ]
                );
            }
        }
    }

    /**
     * Kode bahan baku, contoh: TPG-001, GLA-004.
     * Tambah bahan baku baru di AKHIR tiap kelompok agar kode lama tidak bergeser.
     */
    public static function kode(string $prefix, int $index): string
    {
        return sprintf('%s-%03d', $prefix, $index + 1);
    }

    /**
     * Daftar bahan baku per kategori.
     * Format item: [nama, satuan, harga acuan per satuan (Rp), stok saat ini, stok minimum]
     * Harga acuan dipakai BahanBakuProdusenSeeder untuk menentukan harga tiap produsen.
     */
    public static function daftar(): array
    {
        return [
            'Tepung & Pati' => ['prefix' => 'TPG', 'item' => [
                ['Tepung Terigu Protein Tinggi (Sak 25 kg)', 'Sak', 335000, 80, 25],
                ['Tepung Terigu Protein Sedang (Sak 25 kg)', 'Sak', 300000, 60, 20],
                ['Tepung Terigu Protein Rendah (Sak 25 kg)', 'Sak', 290000, 40, 15],
                ['Tepung Terigu Whole Wheat', 'Kilogram', 18000, 120, 30],
                ['Tepung Almond', 'Kilogram', 145000, 25, 8],
                ['Tepung Maizena', 'Kilogram', 16000, 120, 30],
                ['Tepung Tapioka', 'Kilogram', 13000, 100, 25],
                ['Tepung Beras', 'Kilogram', 12500, 80, 20],
                ['Tepung Ketan', 'Kilogram', 15000, 50, 15],
                ['Tepung Kentang', 'Kilogram', 38000, 25, 8],
                ['Tepung Rye', 'Kilogram', 52000, 20, 6],
                ['Tepung Mocaf', 'Kilogram', 17000, 30, 10],
                ['Vital Wheat Gluten', 'Kilogram', 45000, 15, 5],
            ]],

            'Gula & Pemanis' => ['prefix' => 'GLA', 'item' => [
                ['Gula Pasir (Karung 50 kg)', 'Karung', 850000, 20, 6],
                ['Gula Halus / Icing Sugar', 'Kilogram', 19500, 200, 60],
                ['Gula Kastor', 'Kilogram', 21000, 120, 35],
                ['Gula Merah / Gula Aren', 'Kilogram', 28000, 60, 20],
                ['Brown Sugar', 'Kilogram', 26000, 80, 25],
                ['Gula Palem', 'Kilogram', 32000, 30, 10],
                ['Madu Murni', 'Liter', 110000, 30, 10],
                ['Glukosa Cair', 'Kilogram', 24000, 60, 20],
                ['Maple Syrup (Botol 250 ml)', 'Botol', 135000, 15, 5],
                ['Molases', 'Kilogram', 30000, 20, 6],
            ]],

            'Telur' => ['prefix' => 'TLR', 'item' => [
                ['Telur Ayam Ras', 'Butir', 1900, 6000, 1500],
                ['Telur Ayam Kampung', 'Butir', 3000, 600, 150],
                ['Telur Bebek', 'Butir', 3200, 300, 80],
                ['Telur Puyuh', 'Butir', 500, 1000, 300],
                ['Kuning Telur Cair Pasteurisasi', 'Kilogram', 75000, 30, 10],
                ['Putih Telur Cair Pasteurisasi', 'Kilogram', 45000, 40, 12],
                ['Telur Cair Utuh Pasteurisasi', 'Kilogram', 38000, 60, 20],
            ]],

            'Susu & Olahan Susu' => ['prefix' => 'SSU', 'item' => [
                ['Susu Segar Cair Full Cream', 'Liter', 19000, 200, 60],
                ['Susu UHT Full Cream', 'Liter', 18500, 200, 60],
                ['Susu Bubuk Full Cream', 'Kilogram', 85000, 80, 25],
                ['Susu Bubuk Skim', 'Kilogram', 78000, 50, 15],
                ['Susu Kental Manis (Kaleng 370 g)', 'Kaleng', 13000, 200, 60],
                ['Susu Evaporasi (Kaleng 385 g)', 'Kaleng', 14500, 100, 30],
                ['Whipping Cream Dairy', 'Liter', 68000, 80, 25],
                ['Krim Masak / Cooking Cream', 'Liter', 62000, 40, 12],
                ['Cream Cheese', 'Kilogram', 98000, 18, 20],
                ['Keju Cheddar', 'Kilogram', 125000, 60, 20],
                ['Keju Mozzarella', 'Kilogram', 118000, 50, 15],
                ['Keju Parmesan', 'Kilogram', 285000, 10, 3],
                ['Mascarpone', 'Kilogram', 195000, 10, 3],
                ['Yogurt Plain', 'Kilogram', 38000, 25, 8],
                ['Sour Cream', 'Kilogram', 92000, 15, 5],
            ]],

            'Lemak & Minyak' => ['prefix' => 'LMK', 'item' => [
                ['Mentega Tawar / Unsalted Butter', 'Kilogram', 125000, 35, 40],
                ['Mentega Asin / Salted Butter', 'Kilogram', 118000, 50, 15],
                ['Margarin Roti', 'Kilogram', 38000, 200, 60],
                ['Margarin Pastry / Croissant Lembaran', 'Kilogram', 52000, 80, 25],
                ['Margarin Serbaguna', 'Kilogram', 32000, 100, 30],
                ['Shortening', 'Kilogram', 36000, 80, 25],
                ['Minyak Goreng', 'Liter', 18000, 150, 50],
                ['Minyak Zaitun / Olive Oil', 'Liter', 125000, 10, 3],
            ]],

            'Pengembang, Ragi & Pengental' => ['prefix' => 'PGK', 'item' => [
                ['Ragi Instan / Instant Dry Yeast', 'Kilogram', 75000, 40, 12],
                ['Ragi Aktif Kering', 'Kilogram', 82000, 15, 5],
                ['Baking Powder', 'Kilogram', 52000, 25, 8],
                ['Baking Soda', 'Kilogram', 24000, 25, 8],
                ['Emulsifier SP / TBM', 'Kilogram', 98000, 20, 6],
                ['Bread Improver', 'Kilogram', 70000, 40, 12],
                ['Cream of Tartar', 'Kilogram', 140000, 5, 2],
                ['Gelatin Bubuk', 'Kilogram', 120000, 20, 6],
                ['Agar-agar Bubuk (Bungkus 7 g)', 'Bungkus', 3500, 150, 40],
                ['Pektin', 'Kilogram', 240000, 3, 1],
            ]],

            'Cokelat & Kakao' => ['prefix' => 'CKL', 'item' => [
                ['Dark Cooking Chocolate (Compound)', 'Kilogram', 75000, 80, 25],
                ['Dark Couverture 54%', 'Kilogram', 165000, 50, 15],
                ['Milk Chocolate Couverture', 'Kilogram', 160000, 40, 12],
                ['White Chocolate Couverture', 'Kilogram', 165000, 40, 12],
                ['Cokelat Bubuk / Cocoa Powder', 'Kilogram', 82000, 18, 20],
                ['Dutch Cocoa Powder', 'Kilogram', 125000, 20, 6],
                ['Choco Chips', 'Kilogram', 70000, 70, 20],
                ['Meses Cokelat / Chocolate Rice', 'Kilogram', 58000, 40, 12],
                ['Selai Cokelat / Chocolate Spread', 'Kilogram', 62000, 40, 12],
                ['Cocoa Butter', 'Kilogram', 220000, 5, 2],
            ]],

            'Kacang & Biji-bijian' => ['prefix' => 'KCG', 'item' => [
                ['Almond Slice', 'Kilogram', 195000, 30, 8],
                ['Almond Utuh', 'Kilogram', 175000, 20, 6],
                ['Kenari / Walnut', 'Kilogram', 165000, 20, 6],
                ['Kacang Mede / Cashew', 'Kilogram', 140000, 25, 8],
                ['Kacang Tanah Sangrai', 'Kilogram', 42000, 40, 12],
                ['Pistachio', 'Kilogram', 320000, 5, 2],
                ['Hazelnut', 'Kilogram', 280000, 2, 3],
                ['Wijen Putih', 'Kilogram', 55000, 25, 8],
                ['Wijen Hitam', 'Kilogram', 62000, 15, 5],
                ['Biji Bunga Matahari', 'Kilogram', 48000, 15, 5],
                ['Chia Seed', 'Kilogram', 85000, 10, 3],
                ['Kelapa Parut Kering / Desiccated Coconut', 'Kilogram', 38000, 60, 20],
                ['Rolled Oats', 'Kilogram', 32000, 50, 15],
                ['Granola', 'Kilogram', 58000, 20, 6],
            ]],

            'Buah, Selai & Isian' => ['prefix' => 'BUA', 'item' => [
                ['Kismis / Raisin', 'Kilogram', 75000, 30, 10],
                ['Cranberry Kering', 'Kilogram', 110000, 15, 5],
                ['Kurma Cincang', 'Kilogram', 70000, 20, 6],
                ['Selai Stroberi', 'Kilogram', 52000, 50, 15],
                ['Selai Nanas', 'Kilogram', 45000, 60, 20],
                ['Selai Blueberry', 'Kilogram', 68000, 30, 10],
                ['Selai Kacang', 'Kilogram', 58000, 30, 10],
                ['Selai Srikaya', 'Kilogram', 60000, 25, 8],
                ['Fruit Filling Apel', 'Kilogram', 55000, 20, 6],
                ['Nanas Potong Kaleng (Kaleng 580 g)', 'Kaleng', 30000, 40, 12],
                ['Lemon Segar', 'Kilogram', 42000, 15, 5],
            ]],

            'Perisa, Essence & Pewarna' => ['prefix' => 'PRS', 'item' => [
                ['Ekstrak Vanili (Botol 100 ml)', 'Botol', 85000, 20, 6],
                ['Pasta Vanilla Bean (Botol 100 g)', 'Botol', 195000, 5, 2],
                ['Essence Strawberry (Botol 60 ml)', 'Botol', 22000, 20, 6],
                ['Essence Pandan (Botol 60 ml)', 'Botol', 18000, 30, 10],
                ['Essence Rhum (Botol 60 ml)', 'Botol', 25000, 10, 3],
                ['Pasta Pandan', 'Botol', 25000, 25, 8],
                ['Pasta Moka', 'Botol', 32000, 20, 6],
                ['Pasta Durian', 'Botol', 45000, 8, 3],
                ['Pasta Taro', 'Botol', 34000, 10, 3],
                ['Matcha Powder', 'Kilogram', 380000, 5, 2],
                ['Kopi Bubuk Arabika', 'Kilogram', 125000, 10, 3],
                ['Pewarna Makanan Merah', 'Botol', 12000, 30, 10],
                ['Pewarna Makanan Kuning', 'Botol', 12000, 30, 10],
                ['Pewarna Makanan Hijau', 'Botol', 12000, 30, 10],
                ['Pewarna Makanan Biru', 'Botol', 12000, 20, 6],
            ]],

            'Rempah & Bumbu' => ['prefix' => 'RMP', 'item' => [
                ['Garam Halus', 'Kilogram', 9000, 100, 30],
                ['Kayu Manis Bubuk', 'Kilogram', 85000, 10, 3],
                ['Kayu Manis Batang', 'Kilogram', 95000, 5, 2],
                ['Cengkeh', 'Kilogram', 140000, 3, 1],
                ['Pala Bubuk', 'Kilogram', 180000, 3, 1],
                ['Jahe Bubuk', 'Kilogram', 70000, 5, 2],
                ['Lada Hitam Bubuk', 'Kilogram', 125000, 5, 2],
                ['Bawang Putih Bubuk', 'Kilogram', 85000, 5, 2],
                ['Oregano Kering', 'Kilogram', 160000, 3, 1],
                ['Italian Seasoning', 'Kilogram', 150000, 3, 1],
                ['Daun Pandan Segar', 'Ikat', 5000, 30, 10],
            ]],

            'Topping & Dekorasi' => ['prefix' => 'TOP', 'item' => [
                ['Fondant Putih', 'Kilogram', 75000, 30, 10],
                ['Marzipan', 'Kilogram', 140000, 8, 3],
                ['Sprinkles Warna-warni', 'Kilogram', 62000, 20, 6],
                ['Mirror Glaze Netral', 'Kilogram', 85000, 10, 3],
                ['Mutiara Gula / Edible Pearl (Bungkus 100 g)', 'Bungkus', 18000, 25, 8],
                ['Whipping Cream Non-Dairy', 'Liter', 55000, 40, 12],
                ['Keju Olahan Parut', 'Kilogram', 98000, 25, 8],
                ['Cherry Merah / Maraschino (Botol 600 g)', 'Botol', 55000, 20, 6],
                ['Remah Biskuit Cokelat / Cookie Crumb', 'Kilogram', 68000, 20, 6],
                ['Biskuit Marie (Dasar Cheesecake)', 'Kilogram', 48000, 20, 6],
            ]],

            'Kemasan & Pembungkus' => ['prefix' => 'KMS', 'item' => [
                ['Box Kue 20x20 cm', 'Pieces', 3500, 2000, 500],
                ['Box Kue 25x25 cm', 'Pieces', 4500, 1500, 400],
                ['Box Donat Isi 6', 'Pieces', 3000, 1500, 400],
                ['Box Roti Tawar', 'Pieces', 2800, 1000, 300],
                ['Box Brownies 20x10 cm', 'Pieces', 2500, 1200, 300],
                ['Plastik PP Roti Tawar (isi 100 lembar)', 'Pak', 18000, 200, 50],
                ['Standing Pouch Kraft (isi 50 lembar)', 'Pak', 35000, 100, 30],
                ['Mika Kue Bulat (isi 50 pcs)', 'Pak', 22000, 100, 30],
                ['Cup Puding Plastik (isi 50 pcs)', 'Pak', 15000, 120, 40],
                ['Kantong Kertas Kraft (isi 50 lembar)', 'Pak', 28000, 80, 25],
                ['Pita / Tali Raffia', 'Roll', 8500, 15, 20],
                ['Stiker Label Roll', 'Roll', 25000, 40, 12],
            ]],

            'Perlengkapan Baking' => ['prefix' => 'PLB', 'item' => [
                ['Baking Paper / Kertas Roti', 'Roll', 45000, 50, 15],
                ['Cupcake Liner (isi 100 pcs)', 'Pak', 12000, 150, 50],
                ['Aluminium Foil', 'Roll', 38000, 40, 12],
                ['Cling Wrap', 'Roll', 42000, 40, 12],
                ['Sarung Tangan Plastik (Box isi 100)', 'Box', 32000, 60, 20],
                ['Hairnet / Penutup Rambut (isi 100)', 'Pak', 25000, 40, 12],
                ['Masker Sekali Pakai (Box isi 50)', 'Box', 28000, 40, 12],
                ['Pan Spray Anti Lengket', 'Botol', 45000, 25, 8],
                ['Lilin Ulang Tahun (isi 24 pcs)', 'Pak', 8000, 100, 30],
            ]],
        ];
    }
}
