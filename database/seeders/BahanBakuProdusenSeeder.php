<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\Produsen;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanBakuProdusenSeeder extends Seeder
{
    /**
     * Produsen mana saja yang memasok tiap kategori bahan baku.
     * Setiap kategori dipasok minimal 2 produsen supaya fitur
     * pengalihan ke produsen pengganti bisa dicoba.
     */
    public const SUPLAI = [
        'Tepung & Pati'                => ['produsen1', 'produsen4', 'produsen2'],
        'Gula & Pemanis'               => ['produsen1', 'produsen4', 'produsen3'],
        'Telur'                        => ['produsen2', 'produsen4'],
        'Susu & Olahan Susu'           => ['produsen2', 'produsen3'],
        'Lemak & Minyak'               => ['produsen2', 'produsen1', 'produsen4'],
        'Pengembang, Ragi & Pengental' => ['produsen1', 'produsen3'],
        'Cokelat & Kakao'              => ['produsen3', 'produsen2'],
        'Kacang & Biji-bijian'         => ['produsen3', 'produsen4'],
        'Buah, Selai & Isian'          => ['produsen3', 'produsen2'],
        'Perisa, Essence & Pewarna'    => ['produsen3', 'produsen1'],
        'Rempah & Bumbu'               => ['produsen4', 'produsen3'],
        'Topping & Dekorasi'           => ['produsen3', 'produsen4'],
        'Kemasan & Pembungkus'         => ['produsen4', 'produsen1'],
        'Perlengkapan Baking'          => ['produsen4', 'produsen2'],
    ];

    /**
     * Pengali harga tiap produsen terhadap harga acuan,
     * supaya harga antar produsen berbeda-beda.
     */
    private const FAKTOR_HARGA = [
        'produsen1' => 1.00,
        'produsen2' => 1.03,
        'produsen3' => 0.98,
        'produsen4' => 1.05,
    ];

    public function run(): void
    {
        $produsenIds = Produsen::with('user:id,username')
            ->get()
            ->pluck('id', 'user.username')
            ->all();

        $bahanBakuIds = BahanBaku::pluck('id', 'kode_bahan_baku')->all();

        $now  = now();
        $rows = [];

        // Daftar produsen mengikuti bahan baku yang sudah ada (BahanBakuSeeder)
        foreach (BahanBakuSeeder::daftar() as $namaKategori => $kelompok) {
            $pemasok = self::SUPLAI[$namaKategori] ?? [];

            foreach ($kelompok['item'] as $i => [, , $hargaAcuan]) {
                $kode = BahanBakuSeeder::kode($kelompok['prefix'], $i);
                $bahanBakuId = $bahanBakuIds[$kode] ?? null;

                if ($bahanBakuId === null) {
                    continue;
                }

                foreach ($pemasok as $username) {
                    $produsenId = $produsenIds[$username] ?? null;

                    if ($produsenId === null) {
                        continue;
                    }

                    $rows[] = [
                        'bahan_baku_id' => $bahanBakuId,
                        'produsen_id'   => $produsenId,
                        'harga'         => self::hitungHarga($hargaAcuan, self::FAKTOR_HARGA[$username]),
                        'created_at'    => $now,
                        'updated_at'    => $now,
                    ];
                }
            }
        }

        // Unique key (bahan_baku_id, produsen_id) sudah ada di tabel, jadi aman dijalankan ulang
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('bahan_baku_produsens')->upsert(
                $chunk,
                ['bahan_baku_id', 'produsen_id'],
                ['harga', 'updated_at']
            );
        }
    }

    private static function hitungHarga(int $hargaAcuan, float $faktor): int
    {
        // Harga kecil dibulatkan ke 50, harga besar dibulatkan ke 100
        $pembulatan = $hargaAcuan < 5000 ? 50 : 100;

        return (int) (round($hargaAcuan * $faktor / $pembulatan) * $pembulatan);
    }
}
