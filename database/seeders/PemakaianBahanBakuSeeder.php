<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BahanBaku;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pemakaian bahan baku, mengikuti aturan PemakaianBahanBakuController (FEFO versi bakery ini):
 *   1. Batch dengan tanggal penerimaan paling lama dipakai lebih dulu.
 *   2. Tanggal penerimaan sama -> kadaluarsa terdekat dulu (tanpa kadaluarsa paling akhir).
 *   3. Masih sama -> id batch terkecil.
 *
 * Satu bahan baku bisa diambil dari beberapa batch sekaligus (satu baris detail per batch).
 * Batch yang dipakai hanya yang SUDAH diterima pada tanggal pakai. Bila stok tidak cukup,
 * jumlah dikurangi sesuai stok yang ada (tidak pernah minus).
 *
 * Isi tiap transaksi disusun dari resep produksi (RESEP) x skala pada JADWAL.
 */
class PemakaianBahanBakuSeeder extends Seeder
{
    /** Resep produksi: kode bahan baku + jumlah dasar per transaksi (skala 1). */
    private const RESEP = [
        'roti' => ['nama' => 'Produksi roti tawar dan roti manis', 'bahan' => [['TPG-001', 3], ['TPG-004', 3], ['GLA-001', 0.5], ['LMK-003', 12], ['PGK-001', 2], ['PGK-005', 0.5], ['PGK-006', 1.5], ['TLR-001', 150], ['SSU-003', 4], ['SSU-005', 6], ['RMP-001', 1.5]]],
        'donat' => ['nama' => 'Produksi donat dan kue basah', 'bahan' => [['TPG-002', 2], ['GLA-002', 8], ['LMK-005', 8], ['PGK-001', 1.5], ['TLR-001', 120], ['SSU-002', 20], ['SSU-005', 4], ['LMK-007', 20], ['CKL-007', 3], ['TOP-003', 1.5], ['PRS-006', 1]]],
        'brownies' => ['nama' => 'Produksi brownies dan cake', 'bahan' => [['TPG-003', 1.5], ['GLA-003', 15], ['LMK-001', 10], ['CKL-005', 5], ['CKL-001', 8], ['TLR-001', 250], ['PGK-003', 1], ['PRS-001', 1], ['KCG-003', 2], ['PRS-007', 1]]],
        'pastry' => ['nama' => 'Produksi croissant dan pastry', 'bahan' => [['TPG-001', 2], ['LMK-004', 15], ['LMK-001', 6], ['PGK-001', 1.5], ['PGK-005', 0.5], ['GLA-005', 3], ['TLR-001', 80], ['SSU-002', 10]]],
        'kering' => ['nama' => 'Produksi kue kering dan cookies', 'bahan' => [['TPG-002', 1.5], ['LMK-002', 8], ['GLA-005', 8], ['CKL-007', 4], ['KCG-001', 3], ['KCG-005', 4], ['KCG-008', 1], ['KCG-012', 3], ['KCG-013', 3], ['RMP-002', 0.5], ['TLR-001', 120]]],
        'cheesecake' => ['nama' => 'Produksi cheesecake dan dessert', 'bahan' => [['SSU-009', 3], ['SSU-007', 6], ['GLA-003', 6], ['TLR-001', 70], ['TOP-010', 4], ['LMK-001', 4], ['BUA-004', 3], ['BUA-006', 3], ['PGK-008', 0.5]]],
        'ultah' => ['nama' => 'Produksi kue ulang tahun dan dekorasi', 'bahan' => [['TPG-003', 1.5], ['GLA-002', 8], ['LMK-001', 8], ['TOP-001', 4], ['TOP-003', 2], ['TOP-006', 10], ['PRS-012', 2], ['PRS-013', 1], ['PRS-014', 1], ['PRS-015', 1], ['CKL-003', 3], ['TLR-001', 150]]],
        'pizza' => ['nama' => 'Produksi pizza roll dan roti gurih', 'bahan' => [['TPG-001', 2], ['SSU-011', 8], ['SSU-010', 5], ['SSU-008', 4], ['RMP-007', 0.5], ['RMP-008', 0.5], ['RMP-009', 0.5], ['LMK-005', 3], ['PGK-001', 1.5], ['TLR-001', 60]]],
        'kemas' => ['nama' => 'Pengambilan kemasan dan perlengkapan', 'bahan' => [['KMS-001', 250], ['KMS-003', 150], ['KMS-004', 80], ['KMS-006', 12], ['KMS-007', 4], ['KMS-008', 4], ['KMS-009', 5], ['KMS-010', 3], ['PLB-001', 3], ['PLB-002', 60], ['PLB-003', 1], ['PLB-004', 1], ['PLB-005', 2]]],
    ];

    /** Jadwal pemakaian: [tanggal, resep, skala]. */
    private const JADWAL = [
        ['2026-08-04', 'roti', 1.0],
        ['2026-08-07', 'donat', 1.2],
        ['2026-08-10', 'brownies', 0.8],
        ['2026-08-14', 'pastry', 1.5],
        ['2026-08-17', 'kering', 1.1],
        ['2026-08-20', 'kemas', 0.9],
        ['2026-08-24', 'cheesecake', 1.3],
        ['2026-08-27', 'ultah', 1.0],
        ['2026-08-30', 'pizza', 1.2],
        ['2026-09-03', 'roti', 0.8],
        ['2026-09-06', 'donat', 1.5],
        ['2026-09-09', 'brownies', 1.1],
        ['2026-09-13', 'pastry', 0.9],
        ['2026-09-16', 'kering', 1.3],
        ['2026-09-19', 'kemas', 1.0],
        ['2026-09-23', 'cheesecake', 1.2],
        ['2026-09-26', 'ultah', 0.8],
        ['2026-09-29', 'pizza', 1.5],
        ['2026-10-03', 'roti', 1.1],
        ['2026-10-06', 'donat', 0.9],
        ['2026-10-09', 'brownies', 1.3],
        ['2026-10-13', 'pastry', 1.0],
        ['2026-10-16', 'kering', 1.2],
        ['2026-10-19', 'kemas', 0.8],
        ['2026-10-23', 'cheesecake', 1.5],
        ['2026-10-26', 'ultah', 1.1],
        ['2026-10-29', 'pizza', 0.9],
        ['2026-11-02', 'roti', 1.3],
        ['2026-11-05', 'donat', 1.0],
    ];

    /** @var array<string, int> */
    private array $urutan = [];

    public function run(): void
    {
        $adminId = Admin::whereHas('user', fn ($q) => $q->where('username', 'admin'))->firstOrFail()->id;

        $bahan = BahanBaku::with('satuan:id,nama')
            ->get(['id', 'kode_bahan_baku', 'satuan_id'])
            ->keyBy('kode_bahan_baku');

        foreach (self::JADWAL as $i => [$tgl, $namaResep, $skala]) {
            $tanggal = Carbon::parse($tgl);
            $nomor = $this->nomorBaru($tanggal);

            if (DB::table('pemakaian_bahan_bakus')->where('nomor_transaksi', $nomor)->exists()) {
                continue;
            }

            $resep = self::RESEP[$namaResep];

            DB::transaction(function () use ($adminId, $bahan, $tanggal, $nomor, $resep, $skala, $i) {
                $waktu = $tanggal->copy()->setTime(16, 0)->addMinutes($i * 2);

                $pemakaianId = DB::table('pemakaian_bahan_bakus')->insertGetId([
                    'admin_id' => $adminId,
                    'nomor_transaksi' => $nomor,
                    'tanggal_pakai' => $tanggal->toDateString(),
                    'keterangan' => $resep['nama'],
                    'created_at' => $waktu->toDateTimeString(),
                    'updated_at' => $waktu->toDateTimeString(),
                ]);

                $adaItem = false;

                foreach ($resep['bahan'] as [$kode, $dasar]) {
                    $bb = $bahan[$kode] ?? null;
                    if (! $bb) {
                        continue;
                    }

                    $jumlah = self::bulatkan((string) $bb->satuan?->nama, $dasar * $skala);
                    if ($jumlah <= 0) {
                        continue;
                    }

                    // Batch urutan FEFO yang sudah diterima pada tanggal pakai.
                    $batches = DB::table('penerimaan_bahan_baku_details as d')
                        ->join('penerimaan_bahan_bakus as pn', 'pn.id', '=', 'd.penerimaan_bahan_baku_id')
                        ->where('d.bahan_baku_id', $bb->id)
                        ->where('d.jumlah_tersisa', '>', 0)
                        ->whereDate('pn.tanggal_terima', '<=', $tanggal->toDateString())
                        ->orderBy('pn.tanggal_terima')
                        ->orderByRaw('d.tanggal_kadaluarsa IS NULL')
                        ->orderBy('d.tanggal_kadaluarsa')
                        ->orderBy('d.id')
                        ->select('d.id', 'd.jumlah_tersisa')
                        ->get()
                        ->map(fn ($b) => ['id' => $b->id, 'sen' => self::keSen($b->jumlah_tersisa)])
                        ->all();

                    foreach (self::alokasi($batches, self::keSen($jumlah)) as [$batchId, $ambilSen, $sisaSen]) {
                        DB::table('penerimaan_bahan_baku_details')->where('id', $batchId)->update([
                            'jumlah_tersisa' => self::dariSen($sisaSen),
                            'updated_at' => $waktu->toDateTimeString(),
                        ]);

                        DB::table('pemakaian_bahan_baku_details')->insert([
                            'pemakaian_bahan_baku_id' => $pemakaianId,
                            'bahan_baku_id' => $bb->id,
                            'penerimaan_bahan_baku_detail_id' => $batchId,
                            'jumlah_dipakai' => self::dariSen($ambilSen),
                            'created_at' => $waktu->toDateTimeString(),
                            'updated_at' => $waktu->toDateTimeString(),
                        ]);

                        $adaItem = true;
                    }
                }

                // Tidak ada stok sama sekali untuk resep ini: jangan simpan transaksi kosong.
                if (! $adaItem) {
                    DB::table('pemakaian_bahan_bakus')->where('id', $pemakaianId)->delete();
                }
            });
        }

        PenerimaanBahanBakuSeeder::sinkronStok();
    }

    // =========================================================================
    // Logika murni (tanpa database) supaya mudah diuji
    // =========================================================================

    /**
     * Ambil dari batch sesuai urutan yang diberikan sampai kebutuhan terpenuhi
     * (atau batch habis). Satuan hitung: 1/100, agar bebas selisih pembulatan desimal.
     *
     * @param  array<int, array{id:int, sen:int}>  $batches  sudah berurutan FEFO
     * @return array<int, array{0:int, 1:int, 2:int}>  [id batch, diambil, sisa batch] dalam sen
     */
    public static function alokasi(array $batches, int $kebutuhanSen): array
    {
        $hasil = [];
        $sisaKebutuhan = $kebutuhanSen;

        foreach ($batches as $batch) {
            if ($sisaKebutuhan <= 0) {
                break;
            }

            $ambil = min($sisaKebutuhan, $batch['sen']);
            if ($ambil <= 0) {
                continue;
            }

            $hasil[] = [$batch['id'], $ambil, $batch['sen'] - $ambil];
            $sisaKebutuhan -= $ambil;
        }

        return $hasil;
    }

    /** Sak/Karung/Kilogram/Liter dibulatkan ke 0,25; satuan hitungan ke bilangan bulat. */
    public static function bulatkan(string $satuan, float $jumlah): float
    {
        if (in_array($satuan, ['Sak', 'Karung', 'Kilogram', 'Liter'], true)) {
            return floor($jumlah * 4 + 0.5) / 4;
        }

        return floor($jumlah + 0.5);
    }

    public static function keSen(int|float|string|null $nilai): int
    {
        return (int) round(((float) $nilai) * 100);
    }

    public static function dariSen(int $sen): string
    {
        return number_format($sen / 100, 2, '.', '');
    }

    private function nomorBaru(Carbon $tanggal): string
    {
        $kunci = $tanggal->format('dmY');
        $this->urutan[$kunci] = ($this->urutan[$kunci] ?? 0) + 1;

        return sprintf('PMK-%s-%03d', $kunci, $this->urutan[$kunci]);
    }
}
