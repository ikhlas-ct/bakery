<?php

namespace Database\Seeders;

use App\Models\Satuan;
use Illuminate\Database\Seeder;

class SatuanSeeder extends Seeder
{
    public function run(): void
    {
        $satuans = [
            // Satuan berat
            ['nama' => 'Kilogram', 'kode_satuan' => 'kg'],
            ['nama' => 'Gram', 'kode_satuan' => 'gr'],
            ['nama' => 'Ons', 'kode_satuan' => 'ons'],
            ['nama' => 'Kwintal', 'kode_satuan' => 'kw'],

            // Satuan volume
            ['nama' => 'Liter', 'kode_satuan' => 'ltr'],
            ['nama' => 'Mililiter', 'kode_satuan' => 'ml'],
            ['nama' => 'Galon', 'kode_satuan' => 'gln'],

            // Satuan takaran
            ['nama' => 'Sendok Teh', 'kode_satuan' => 'tsp'],
            ['nama' => 'Sendok Makan', 'kode_satuan' => 'tbsp'],
            ['nama' => 'Cangkir', 'kode_satuan' => 'cup'],

            // Satuan hitungan (bahan baku)
            ['nama' => 'Butir', 'kode_satuan' => 'btr'],
            ['nama' => 'Buah', 'kode_satuan' => 'bh'],
            ['nama' => 'Lembar', 'kode_satuan' => 'lbr'],
            ['nama' => 'Batang', 'kode_satuan' => 'btg'],
            ['nama' => 'Ikat', 'kode_satuan' => 'ikt'],
            ['nama' => 'Siung', 'kode_satuan' => 'siung'],

            // Satuan kemasan (bahan baku)
            ['nama' => 'Sachet', 'kode_satuan' => 'sct'],
            ['nama' => 'Bungkus', 'kode_satuan' => 'bks'],
            ['nama' => 'Pak', 'kode_satuan' => 'pak'],
            ['nama' => 'Karung', 'kode_satuan' => 'krg'],
            ['nama' => 'Sak', 'kode_satuan' => 'sak'],
            ['nama' => 'Kaleng', 'kode_satuan' => 'klg'],
            ['nama' => 'Botol', 'kode_satuan' => 'btl'],
            ['nama' => 'Jerigen', 'kode_satuan' => 'jrg'],
            ['nama' => 'Toples', 'kode_satuan' => 'tpl'],
            ['nama' => 'Tube', 'kode_satuan' => 'tube'],
            ['nama' => 'Pail', 'kode_satuan' => 'pail'],
            ['nama' => 'Roll', 'kode_satuan' => 'roll'],
            ['nama' => 'Kardus', 'kode_satuan' => 'krds'],
            ['nama' => 'Dus', 'kode_satuan' => 'dus'],
            ['nama' => 'Slop', 'kode_satuan' => 'slop'],

            // Satuan produk jadi (roti & kue)
            ['nama' => 'Pieces', 'kode_satuan' => 'pcs'],
            ['nama' => 'Potong', 'kode_satuan' => 'ptg'],
            ['nama' => 'Slice', 'kode_satuan' => 'slice'],
            ['nama' => 'Loyang', 'kode_satuan' => 'loyang'],
            ['nama' => 'Box', 'kode_satuan' => 'box'],
            ['nama' => 'Lusin', 'kode_satuan' => 'lsn'],
            ['nama' => 'Kodi', 'kode_satuan' => 'kodi'],
            ['nama' => 'Gross', 'kode_satuan' => 'gross'],
            ['nama' => 'Paket', 'kode_satuan' => 'pkt'],
            ['nama' => 'Porsi', 'kode_satuan' => 'porsi'],
            ['nama' => 'Cup Kue', 'kode_satuan' => 'cup-kue'],
            ['nama' => 'Loaf', 'kode_satuan' => 'loaf'],
            ['nama' => 'Tray', 'kode_satuan' => 'tray'],
            ['nama' => 'Bal', 'kode_satuan' => 'bal'],
        ];

        foreach ($satuans as $satuan) {
            // Kunci pencarian: nama (unik), jadi aman dijalankan berulang kali
            Satuan::updateOrCreate(
                ['nama' => $satuan['nama']],
                ['kode_satuan' => $satuan['kode_satuan']]
            );
        }
    }
}
