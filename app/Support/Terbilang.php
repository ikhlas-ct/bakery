<?php

namespace App\Support;

class Terbilang
{
    private const DASAR = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas',
    ];

    private const SKALA = [
        1_000_000_000_000 => 'triliun',
        1_000_000_000 => 'miliar',
        1_000_000 => 'juta',
    ];

    /**
     * 55000 → "Lima puluh lima ribu rupiah"
     */
    public static function rupiah(int|float|string $angka): string
    {
        $n = (int) round((float) $angka);

        if ($n === 0) {
            return 'Nol rupiah';
        }

        $teks = trim(preg_replace('/\s+/', ' ', self::bilang(abs($n))));
        $teks = ($n < 0 ? 'minus ' : '') . $teks;

        return ucfirst($teks) . ' rupiah';
    }

    private static function bilang(int $n): string
    {
        if ($n < 12) {
            return self::DASAR[$n];
        }
        if ($n < 20) {
            return self::bilang($n - 10) . ' belas';
        }
        if ($n < 100) {
            return self::bilang(intdiv($n, 10)) . ' puluh ' . self::bilang($n % 10);
        }
        if ($n < 200) {
            return 'seratus ' . self::bilang($n - 100);
        }
        if ($n < 1000) {
            return self::bilang(intdiv($n, 100)) . ' ratus ' . self::bilang($n % 100);
        }
        if ($n < 2000) {
            return 'seribu ' . self::bilang($n - 1000);
        }
        if ($n < 1_000_000) {
            return self::bilang(intdiv($n, 1000)) . ' ribu ' . self::bilang($n % 1000);
        }

        foreach (self::SKALA as $nilai => $nama) {
            if ($n >= $nilai) {
                return self::bilang(intdiv($n, $nilai)) . ' ' . $nama . ' ' . self::bilang($n % $nilai);
            }
        }

        return '';
    }
}
