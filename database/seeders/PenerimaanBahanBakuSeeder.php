<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BahanBaku;
use App\Models\Pemilik;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Penerimaan bahan baku, mengikuti aturan PenerimaanBahanBakuController:
 *
 *  - Satu nota per produsen per tanggal; boleh berisi barang dari beberapa permintaan.
 *  - Hanya barang berstatus disetujui / disetujui_sebagian yang diterima, maksimal sebesar
 *    jumlah_disetujui. Pengalihan tidak dilibatkan (sama seperti controller).
 *  - Tiap detail menjadi batch FEFO: jumlah_tersisa = jumlah_diterima, plus tanggal kadaluarsa.
 *  - Tagihan di tabel pembayarans (unik per permintaan + produsen) = SUM(diterima x harga_satuan),
 *    dibuat berstatus menunggu/belum. Statusnya diatur lebih lanjut oleh PembayaranSeeder.
 *  - Stok bahan baku (stok_saat_ini) = jumlah seluruh sisa batch.
 *
 * SALDO AWAL: stok awal dari BahanBakuSeeder dicatat sebagai batch pada 1 Agustus 2026
 * (nota "saldo awal", tanpa kaitan ke permintaan dan tanpa tanggal kadaluarsa), supaya
 * stok yang tampil di aplikasi sama dengan jumlah batch yang bisa dipakai pemakaian (FEFO).
 */
class PenerimaanBahanBakuSeeder extends Seeder
{
    /** Ubah ke false bila tidak ingin ada nota saldo awal (stok hanya berasal dari penerimaan). */
    private const BUAT_SALDO_AWAL = true;

    private const TANGGAL_SALDO_AWAL = '2026-08-01';

    /** Tanggal terakhir data transaksi; pengiriman setelah tanggal ini dianggap belum tiba. */
    private const TANGGAL_AKHIR = '2026-11-08';

    private const URUTAN_PRODUSEN = [
        'produsen1' => 1,
        'produsen2' => 2,
        'produsen3' => 3,
        'produsen4' => 4,
    ];

    /** Masa simpan (hari) menurut awalan kode; null = tidak ada tanggal kadaluarsa. */
    private const MASA_SIMPAN_AWALAN = [
        'TPG' => 270, 'GLA' => 720, 'TLR' => 28, 'SSU' => 120, 'LMK' => 270,
        'PGK' => 365, 'CKL' => 365, 'KCG' => 180, 'BUA' => 365, 'PRS' => 540,
        'RMP' => 540, 'TOP' => 365, 'KMS' => null, 'PLB' => null,
    ];

    /** Pengecualian per kode bahan baku. */
    private const MASA_SIMPAN_KHUSUS = [
        'SSU-001' => 14,  'SSU-003' => 365, 'SSU-004' => 365, 'SSU-005' => 540, 'SSU-006' => 540,
        'SSU-007' => 120, 'SSU-008' => 120, 'SSU-009' => 90,  'SSU-010' => 180, 'SSU-011' => 120,
        'SSU-012' => 365, 'SSU-013' => 30,  'SSU-014' => 21,  'SSU-015' => 30,
        'LMK-001' => 180, 'LMK-002' => 180, 'LMK-007' => 540, 'LMK-008' => 720,
        'TLR-004' => 21,  'TLR-005' => 14,  'TLR-006' => 14,  'TLR-007' => 14,
        'CKL-002' => 540, 'CKL-003' => 540, 'CKL-004' => 540,
        'KCG-008' => 365, 'KCG-009' => 365, 'KCG-013' => 365, 'KCG-014' => 120,
        'BUA-010' => 540, 'BUA-011' => 14,
        'RMP-001' => null, 'RMP-011' => 5,
        'TOP-006' => 180, 'TOP-008' => 540, 'TOP-009' => 180,
    ];

    /** @var array<string, int> counter nomor per tanggal (ddmmYYYY) */
    private array $urutan = [];

    public function run(): void
    {
        $adminId = Admin::whereHas('user', fn ($q) => $q->where('username', 'admin'))->firstOrFail()->id;
        $pemilikId = Pemilik::whereHas('user', fn ($q) => $q->where('username', 'pemilik'))->firstOrFail()->id;

        $produsenIds = Produsen::with('user:id,username')->get()->pluck('id', 'user.username')->all();
        $bahan = BahanBaku::get(['id', 'kode_bahan_baku', 'stok_saat_ini'])->keyBy('kode_bahan_baku');

        $daftar = PermintaanBahanBakuSeeder::daftarBernomor();
        $permintaanIds = DB::table('permintaan_bahan_bakus')->pluck('id', 'nomor_transaksi')->all();
        $detailIds = DB::table('permintaan_bahan_baku_details')
            ->get(['id', 'permintaan_bahan_baku_id', 'bahan_baku_id', 'produsen_id'])
            ->mapWithKeys(fn ($r) => ["{$r->permintaan_bahan_baku_id}-{$r->bahan_baku_id}-{$r->produsen_id}" => $r->id])
            ->all();

        if (self::BUAT_SALDO_AWAL) {
            $this->buatSaldoAwal($adminId, $produsenIds, $bahan);
        }

        foreach (self::rencanaNota($daftar, self::batasTanggal()) as $m => $nota) {
            $tanggal = $nota['tanggal'];
            $nomor = $this->nomorBaru($tanggal);

            if (DB::table('penerimaan_bahan_bakus')->where('nomor_transaksi', $nomor)->exists()) {
                continue;
            }

            $produsenId = $produsenIds[$nota['produsen']]
                ?? throw new RuntimeException("Produsen {$nota['produsen']} tidak ditemukan.");

            $baris = [];
            foreach ($nota['items'] as $item) {
                $bb = $bahan[$item['bb']]
                    ?? throw new RuntimeException("Bahan baku {$item['bb']} tidak ditemukan.");
                $permintaanId = $permintaanIds[$daftar[$item['perm']]['nomor']]
                    ?? throw new RuntimeException('Permintaan belum ada. Jalankan PermintaanBahanBakuSeeder dulu.');
                $detailId = $detailIds["{$permintaanId}-{$bb->id}-{$produsenId}"]
                    ?? throw new RuntimeException("Detail permintaan untuk {$item['bb']} tidak ditemukan.");

                $baris[] = [
                    'bahan_baku_id' => $bb->id,
                    'permintaan_bahan_baku_detail_id' => $detailId,
                    'jumlah' => $item['qty'],
                    'kadaluarsa' => self::hitungKadaluarsa($item['bb'], $tanggal),
                ];
            }

            // Nota ke-5, ke-10, dst. dicatat pemilik; selebihnya admin.
            $olehPemilik = $m % 5 === 4;

            $catatan = $nota['bertahap']
                ? 'Pengiriman bertahap, barang diterima sesuai surat jalan.'
                : 'Barang diterima lengkap sesuai surat jalan.';

            $this->simpanNota(
                $nomor,
                $tanggal,
                $produsenId,
                $olehPemilik ? null : $adminId,
                $olehPemilik ? $pemilikId : null,
                $catatan,
                $baris,
                $m
            );
        }

        $this->sinkronPembayaran();
        self::sinkronStok();
    }

    // =========================================================================
    // Logika murni (tanpa database) supaya mudah diuji
    // =========================================================================

    /**
     * Rencana pengiriman produsen: dikelompokkan per tanggal + produsen menjadi satu nota.
     *
     * Aturan:
     *  - Barang tiba 3-5 hari setelah permintaan (permintaan -> ditanggapi H+1 -> dikirim 2-4 hari).
     *  - Permintaan ke-3, ke-7, ke-11, ... dikirim dua tahap: 60% lebih dulu, sisanya 3 hari kemudian.
     *  - Pengiriman yang tanggalnya melewati batas tanggal data dianggap belum tiba.
     *
     * @param  array<int, array>  $permintaans  hasil PermintaanBahanBakuSeeder::daftarBernomor()
     * @return array<int, array{tanggal: Carbon, produsen: string, bertahap: bool, items: array}>
     */
    public static function rencanaNota(array $permintaans, Carbon $batas): array
    {
        $kiriman = [];

        foreach ($permintaans as $no => $p) {
            $n = $no + 1;
            $tanggalPermintaan = Carbon::parse($p['tanggal']);

            foreach ($p['item'] as $item) {
                $disetujui = $item['disetujui'];
                if ($disetujui === null || (float) $disetujui <= 0) {
                    continue;
                }

                $k = self::URUTAN_PRODUSEN[$item['produsen']];
                $d1 = $tanggalPermintaan->copy()->addDays(1 + 2 + (($n + $k) % 3));

                if ($n % 4 === 3) {
                    $tahap1 = (int) floor(0.6 * $disetujui);
                    $bagian = [
                        [$d1, $tahap1, true],
                        [$d1->copy()->addDays(3), $disetujui - $tahap1, true],
                    ];
                } else {
                    $bagian = [[$d1, $disetujui, false]];
                }

                foreach ($bagian as [$tanggal, $jumlah, $bertahap]) {
                    if ($jumlah <= 0 || $tanggal->toDateString() > $batas->toDateString()) {
                        continue;
                    }

                    $kunci = $tanggal->toDateString() . '|' . $k;
                    $kiriman[$kunci] ??= [
                        'tanggal' => $tanggal,
                        'produsen' => $item['produsen'],
                        'bertahap' => false,
                        'items' => [],
                    ];
                    $kiriman[$kunci]['bertahap'] = $kiriman[$kunci]['bertahap'] || $bertahap;
                    $kiriman[$kunci]['items'][] = ['perm' => $no, 'bb' => $item['bb'], 'qty' => $jumlah];
                }
            }
        }

        ksort($kiriman); // tanggal, lalu urutan produsen

        return array_values($kiriman);
    }

    public static function masaSimpan(string $kode): ?int
    {
        if (array_key_exists($kode, self::MASA_SIMPAN_KHUSUS)) {
            return self::MASA_SIMPAN_KHUSUS[$kode];
        }

        return self::MASA_SIMPAN_AWALAN[substr($kode, 0, 3)] ?? null;
    }

    private static function hitungKadaluarsa(string $kode, Carbon $tanggalTerima): ?string
    {
        $hari = self::masaSimpan($kode);

        return $hari === null ? null : $tanggalTerima->copy()->addDays($hari)->toDateString();
    }

    private static function batasTanggal(): Carbon
    {
        $batas = Carbon::parse(self::TANGGAL_AKHIR);

        return now()->lt($batas) ? now()->copy() : $batas;
    }

    // =========================================================================
    // Penulisan ke database
    // =========================================================================

    private function buatSaldoAwal(int $adminId, array $produsenIds, $bahan): void
    {
        $tanggal = Carbon::parse(self::TANGGAL_SALDO_AWAL);

        // Kelompokkan stok awal per produsen utama (pemasok pertama tiap kategori).
        $perProdusen = [];
        foreach (BahanBakuSeeder::daftar() as $namaKategori => $kelompok) {
            $username = BahanBakuProdusenSeeder::SUPLAI[$namaKategori][0];

            foreach (array_keys($kelompok['item']) as $i) {
                $kode = BahanBakuSeeder::kode($kelompok['prefix'], $i);
                $bb = $bahan[$kode] ?? null;

                if ($bb && (float) $bb->stok_saat_ini > 0) {
                    $perProdusen[$username][] = [
                        'bahan_baku_id' => $bb->id,
                        'permintaan_bahan_baku_detail_id' => null,
                        'jumlah' => (float) $bb->stok_saat_ini,
                        'kadaluarsa' => null,
                    ];
                }
            }
        }

        foreach (self::URUTAN_PRODUSEN as $username => $urut) {
            if (empty($perProdusen[$username])) {
                continue;
            }

            $nomor = $this->nomorBaru($tanggal);
            if (DB::table('penerimaan_bahan_bakus')->where('nomor_transaksi', $nomor)->exists()) {
                continue;
            }

            $this->simpanNota(
                $nomor,
                $tanggal,
                $produsenIds[$username],
                $adminId,
                null,
                'Saldo awal stok gudang per 1 Agustus 2026 (hasil stok opname).',
                $perProdusen[$username],
                $urut - 1
            );
        }
    }

    /**
     * @param  array<int, array{bahan_baku_id:int, permintaan_bahan_baku_detail_id:int|null, jumlah:float|int, kadaluarsa:string|null}>  $baris
     */
    private function simpanNota(
        string $nomor,
        Carbon $tanggal,
        int $produsenId,
        ?int $adminId,
        ?int $pemilikId,
        string $catatan,
        array $baris,
        int $urutMenit
    ): void {
        $waktu = $tanggal->copy()->setTime(14, 0)->addMinutes($urutMenit * 3);

        DB::transaction(function () use ($nomor, $tanggal, $produsenId, $adminId, $pemilikId, $catatan, $baris, $waktu) {
            $penerimaanId = DB::table('penerimaan_bahan_bakus')->insertGetId([
                'admin_id' => $adminId,
                'pemilik_id' => $pemilikId,
                'produsen_id' => $produsenId,
                'nomor_transaksi' => $nomor,
                'tanggal_terima' => $tanggal->toDateString(),
                'catatan' => $catatan,
                'foto_receipt' => null,
                'created_at' => $waktu->toDateTimeString(),
                'updated_at' => $waktu->toDateTimeString(),
            ]);

            DB::table('penerimaan_bahan_baku_details')->insert(array_map(fn ($b) => [
                'penerimaan_bahan_baku_id' => $penerimaanId,
                'permintaan_bahan_baku_detail_id' => $b['permintaan_bahan_baku_detail_id'],
                'bahan_baku_id' => $b['bahan_baku_id'],
                'jumlah_diterima' => $b['jumlah'],
                'jumlah_tersisa' => $b['jumlah'],
                'tanggal_kadaluarsa' => $b['kadaluarsa'],
                'created_at' => $waktu->toDateTimeString(),
                'updated_at' => $waktu->toDateTimeString(),
            ], $baris));
        });
    }

    private function nomorBaru(Carbon $tanggal): string
    {
        $kunci = $tanggal->format('dmY');
        $this->urutan[$kunci] = ($this->urutan[$kunci] ?? 0) + 1;

        return sprintf('PNB-%s-%03d', $kunci, $this->urutan[$kunci]);
    }

    /**
     * Sama seperti sinkronPembayaran() di controller: tagihan per (permintaan + produsen)
     * = SUM(jumlah_diterima x harga_satuan). Tagihan baru berstatus menunggu / belum dibayar.
     */
    private function sinkronPembayaran(): void
    {
        $totals = DB::table('penerimaan_bahan_baku_details as d')
            ->join('penerimaan_bahan_bakus as pn', 'pn.id', '=', 'd.penerimaan_bahan_baku_id')
            ->join('permintaan_bahan_baku_details as pbd', 'pbd.id', '=', 'd.permintaan_bahan_baku_detail_id')
            ->groupBy('pbd.permintaan_bahan_baku_id', 'pn.produsen_id')
            ->selectRaw('pbd.permintaan_bahan_baku_id as permintaan_id, pn.produsen_id, SUM(d.jumlah_diterima * pbd.harga_satuan) as total, MIN(pn.tanggal_terima) as pertama')
            ->orderBy('permintaan_id')
            ->orderBy('pn.produsen_id')
            ->get();

        foreach ($totals as $t) {
            $total = round((float) $t->total, 2);
            $ada = DB::table('pembayarans')
                ->where('permintaan_bahan_baku_id', $t->permintaan_id)
                ->where('produsen_id', $t->produsen_id)
                ->first();

            if ($ada) {
                DB::table('pembayarans')->where('id', $ada->id)->update([
                    'jumlah_bayar' => $total,
                    'updated_at' => now(),
                ]);

                continue;
            }

            $dibuat = Carbon::parse($t->pertama)->setTime(15, 0)->toDateTimeString();

            DB::table('pembayarans')->insert([
                'permintaan_bahan_baku_id' => $t->permintaan_id,
                'produsen_id' => $t->produsen_id,
                'pemilik_id' => null,
                'jumlah_bayar' => $total,
                'tanggal_bayar' => null,
                'status_acc' => 'menunggu',
                'status_bayar' => 'belum',
                'metode_bayar' => null,
                'bukti_bayar' => null,
                'created_at' => $dibuat,
                'updated_at' => $dibuat,
            ]);
        }
    }

    /** stok_saat_ini = jumlah seluruh sisa batch (sama seperti sinkronStok di PemakaianBahanBakuController). */
    public static function sinkronStok(): void
    {
        DB::statement(
            'UPDATE bahan_bakus SET stok_saat_ini = COALESCE(('
            . 'SELECT SUM(d.jumlah_tersisa) FROM penerimaan_bahan_baku_details d WHERE d.bahan_baku_id = bahan_bakus.id'
            . '), 0)'
        );
    }
}
