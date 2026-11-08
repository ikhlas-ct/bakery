<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\BahanBaku;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Permintaan bahan baku + detail + pengalihan, mengikuti alur controller:
 *
 *  1. Admin membuat permintaan (nomor PBB-DDMMYYYY-XXX, harga = snapshot pivot, status 'pending').
 *  2. Produsen menanggapi. Status dihitung dari jumlah disetujui:
 *       0 -> ditolak | = diminta -> disetujui | di antaranya -> disetujui_sebagian
 *     Kalau ada kekurangan, otomatis dibuat baris pengalihan 'pending' tanpa pengganti.
 *  3. Admin menentukan pengganti (produsen lain, atau bahan baku setara bersatuan sama).
 *  4. Produsen pengganti menanggapi. Kalau masih kurang, dibuat baris pengalihan baru (berantai).
 *
 * Seeder ini aman dijalankan ulang: permintaan dengan nomor yang sudah ada dilewati.
 */
class PermintaanBahanBakuSeeder extends Seeder
{
    private array $produsenIds = [];
    private $bahanBakus;
    private array $harga = [];

    public function run(): void
    {
        $adminId = Admin::whereHas('user', fn ($q) => $q->where('username', 'admin'))
            ->firstOrFail()->id;

        $this->produsenIds = Produsen::with('user:id,username')
            ->get()->pluck('id', 'user.username')->all();

        $this->bahanBakus = BahanBaku::get(['id', 'kode_bahan_baku', 'satuan_id'])
            ->keyBy('kode_bahan_baku');

        $this->harga = DB::table('bahan_baku_produsens')->get()
            ->mapWithKeys(fn ($r) => ["{$r->bahan_baku_id}-{$r->produsen_id}" => (float) $r->harga])
            ->all();

        foreach (self::daftarBernomor() as $no => $p) {
            $tanggal = Carbon::parse($p['tanggal']);
            $nomor   = $p['nomor'];

            if (DB::table('permintaan_bahan_bakus')->where('nomor_transaksi', $nomor)->exists()) {
                continue;
            }

            DB::transaction(fn () => $this->buatPermintaan($adminId, $nomor, $tanggal, $p, $no));
        }
    }

    private function buatPermintaan(int $adminId, string $nomor, Carbon $tanggal, array $p, int $no): void
    {
        $dibuat = $this->waktu($tanggal, 0, 8, 30 + ($no % 20));

        $permintaanId = DB::table('permintaan_bahan_bakus')->insertGetId([
            'admin_id'           => $adminId,
            'nomor_transaksi'    => $nomor,
            'tanggal_permintaan' => $tanggal->toDateString(),
            'keterangan'         => $p['keterangan'],
            'created_at'         => $dibuat->toDateTimeString(),
            'updated_at'         => $dibuat->toDateTimeString(),
        ]);

        foreach ($p['item'] as $item) {
            $bb = $this->bahanBakus[$item['bb']]
                ?? throw new RuntimeException("Bahan baku {$item['bb']} tidak ditemukan. Jalankan BahanBakuSeeder dulu.");
            $produsenId = $this->produsenIds[$item['produsen']]
                ?? throw new RuntimeException("Produsen {$item['produsen']} tidak ditemukan.");
            $harga = $this->harga["{$bb->id}-{$produsenId}"]
                ?? throw new RuntimeException("{$item['produsen']} tidak memasok {$item['bb']} (cek BahanBakuProdusenSeeder).");

            $diminta   = (float) $item['diminta'];
            $disetujui = $item['disetujui']; // null = produsen belum menanggapi

            if ($disetujui === null) {
                $status    = 'pending';
                $diupdate  = $dibuat;
            } else {
                $status = match (true) {
                    (float) $disetujui === 0.0       => 'ditolak',
                    (float) $disetujui === $diminta  => 'disetujui',
                    default                          => 'disetujui_sebagian',
                };
                $diupdate = $this->waktu($tanggal, 1, 13, 5);
            }

            $detailId = DB::table('permintaan_bahan_baku_details')->insertGetId([
                'permintaan_bahan_baku_id' => $permintaanId,
                'bahan_baku_id'            => $bb->id,
                'produsen_id'              => $produsenId,
                'harga_satuan'             => $harga,
                'jumlah_diminta'           => $diminta,
                'jumlah_disetujui'         => $disetujui,
                'status_produsen'          => $status,
                'created_at'               => $dibuat->toDateTimeString(),
                'updated_at'               => $diupdate->toDateTimeString(),
            ]);

            if ($disetujui !== null && (float) $disetujui < $diminta) {
                $this->buatPengalihan($detailId, $bb, $produsenId, $item, $diminta - (float) $disetujui, $tanggal);
            }
        }
    }

    /**
     * Rantai pengalihan untuk satu item yang kurang.
     * Langkah ke-k: dibuat T+1+2k, ditentukan admin T+2+2k, direspon pengganti T+3+2k.
     */
    private function buatPengalihan(int $detailId, $bbAsal, int $produsenAsalId, array $item, float $kurang, Carbon $tanggal): void
    {
        $langkah = $item['alihan'] ?? [];
        $sisa    = $kurang;
        $k       = 0;

        $alasanBerikut = $item['alasan'] ?? ((float) $item['disetujui'] === 0.0
            ? 'Ditolak produsen (tidak sanggup menyediakan).'
            : 'Produsen hanya sanggup menyediakan sebagian dari jumlah yang diminta.');

        while ($sisa > 0) {
            $step      = $langkah[$k] ?? null;
            $dibuat    = $this->waktu($tanggal, 1 + 2 * $k, 10, 15);
            $ditentukan = $this->waktu($tanggal, 2 + 2 * $k, 14, 20);
            $direspon  = $this->waktu($tanggal, 3 + 2 * $k, 9, 40);

            // Belum ada pengganti: baris menunggu admin menentukan.
            if ($step === null) {
                DB::table('pengalihan_bahan_bakus')->insert([
                    'permintaan_bahan_baku_detail_id' => $detailId,
                    'bahan_baku_asal_id'              => $bbAsal->id,
                    'bahan_baku_pengganti_id'         => null,
                    'produsen_pengganti_id'           => null,
                    'jumlah_dialihkan'                => $sisa,
                    'harga_satuan'                    => null,
                    'status_produsen_pengganti'       => 'pending',
                    'jumlah_disetujui_pengganti'      => null,
                    'alasan_pengalihan'               => $alasanBerikut,
                    'tanggal'                         => $dibuat->toDateString(),
                    'created_at'                      => $dibuat->toDateTimeString(),
                    'updated_at'                      => $dibuat->toDateTimeString(),
                ]);
                break;
            }

            // Admin sudah menentukan pengganti.
            $tujuan = isset($step['bb'])
                ? ($this->bahanBakus[$step['bb']] ?? throw new RuntimeException("Bahan baku pengganti {$step['bb']} tidak ditemukan."))
                : $bbAsal;

            if ($tujuan->id !== $bbAsal->id && $tujuan->satuan_id !== $bbAsal->satuan_id) {
                throw new RuntimeException("Bahan baku pengganti {$step['bb']} harus bersatuan sama dengan bahan baku asal.");
            }

            $produsenId = $this->produsenIds[$step['produsen']]
                ?? throw new RuntimeException("Produsen pengganti {$step['produsen']} tidak ditemukan.");

            if ($tujuan->id === $bbAsal->id && $produsenId === $produsenAsalId) {
                throw new RuntimeException('Untuk bahan baku yang sama, produsen pengganti harus produsen lain.');
            }

            $harga = $this->harga["{$tujuan->id}-{$produsenId}"]
                ?? throw new RuntimeException("{$step['produsen']} tidak memasok bahan baku tujuan pengalihan.");

            $respon = $step['respon'] ?? null;

            if ($respon === null) {
                $status = 'pending';
            } else {
                if ((float) $respon > $sisa) {
                    throw new RuntimeException('Jumlah respon pengganti melebihi jumlah dialihkan.');
                }
                $status = match (true) {
                    (float) $respon === 0.0   => 'ditolak',
                    (float) $respon === $sisa => 'disetujui',
                    default                   => 'disetujui_sebagian',
                };
            }

            DB::table('pengalihan_bahan_bakus')->insert([
                'permintaan_bahan_baku_detail_id' => $detailId,
                'bahan_baku_asal_id'              => $bbAsal->id,
                'bahan_baku_pengganti_id'         => $tujuan->id === $bbAsal->id ? null : $tujuan->id,
                'produsen_pengganti_id'           => $produsenId,
                'jumlah_dialihkan'                => $sisa,
                'harga_satuan'                    => $harga,
                'status_produsen_pengganti'       => $status,
                'jumlah_disetujui_pengganti'      => $respon,
                'alasan_pengalihan'               => $step['alasan'] ?? 'Dialihkan ke produsen lain karena stok produsen awal tidak mencukupi.',
                'tanggal'                         => $ditentukan->toDateString(),
                'created_at'                      => $dibuat->toDateTimeString(),
                'updated_at'                      => ($respon === null ? $ditentukan : $direspon)->toDateTimeString(),
            ]);

            if ($respon === null) {
                break; // masih menunggu respon pengganti
            }

            $sisa = round($sisa - (float) $respon, 2);
            $alasanBerikut = (float) $respon === 0.0
                ? 'Produsen pengganti tidak sanggup menyediakan.'
                : 'Produsen pengganti hanya sanggup menyediakan sebagian.';
            $k++;
        }
    }

    /**
     * Daftar permintaan lengkap dengan nomor transaksinya (PBB-DDMMYYYY-XXX, urut per tanggal).
     * Dipakai juga oleh PenerimaanBahanBakuSeeder dan PembayaranSeeder.
     */
    public static function daftarBernomor(): array
    {
        $urutan = [];
        $hasil  = [];

        foreach (self::daftar() as $no => $p) {
            $kunci = Carbon::parse($p['tanggal'])->format('dmY');
            $urutan[$kunci] = ($urutan[$kunci] ?? 0) + 1;

            $hasil[$no] = $p + ['nomor' => sprintf('PBB-%s-%03d', $kunci, $urutan[$kunci])];
        }

        return $hasil;
    }

    /** Waktu kejadian; tidak boleh melewati saat ini. */
    private function waktu(Carbon $tanggal, int $tambahHari, int $jam, int $menit): Carbon
    {
        $w = $tanggal->copy()->addDays($tambahHari)->setTime($jam, $menit);

        return $w->greaterThan(now()) ? now() : $w;
    }

    /**
     * Format item:
     *   bb         kode bahan baku (lihat BahanBakuSeeder)
     *   produsen   username produsen yang dituju
     *   diminta    jumlah diminta admin
     *   disetujui  jumlah disetujui produsen (null = belum ditanggapi)
     *   alasan     (opsional) alasan produsen saat jumlahnya kurang
     *   alihan     (opsional) langkah pengganti berurutan:
     *                bb       (opsional) bahan baku pengganti; kosong = bahan baku yang sama
     *                produsen produsen pengganti
     *                respon   jumlah disetujui pengganti (null = menunggu respon)
     *                alasan   alasan admin
     *              Kalau pengganti kurang dan langkah berikutnya tidak ditulis,
     *              otomatis dibuat baris "belum ditentukan" untuk sisanya.
     */
    public static function daftar(): array
    {
        return [
            // 1. Seluruhnya disetujui
            [
                'tanggal' => '2026-08-03',
                'keterangan' => 'Restock awal Agustus: ragi, pengembang, dan perisa.',
                'item' => [
                    ['bb' => 'PGK-001', 'produsen' => 'produsen1', 'diminta' => 40, 'disetujui' => 40], // Ragi Instan
                    ['bb' => 'PGK-003', 'produsen' => 'produsen3', 'diminta' => 30, 'disetujui' => 30], // Baking Powder
                    ['bb' => 'PGK-005', 'produsen' => 'produsen1', 'diminta' => 15, 'disetujui' => 15], // Emulsifier
                    ['bb' => 'PGK-008', 'produsen' => 'produsen3', 'diminta' => 10, 'disetujui' => 10], // Gelatin
                    ['bb' => 'PRS-001', 'produsen' => 'produsen3', 'diminta' => 15, 'disetujui' => 15], // Ekstrak Vanili
                    ['bb' => 'PRS-004', 'produsen' => 'produsen3', 'diminta' => 25, 'disetujui' => 25], // Essence Pandan
                    ['bb' => 'PRS-012', 'produsen' => 'produsen1', 'diminta' => 30, 'disetujui' => 30], // Pewarna Merah
                    ['bb' => 'RMP-002', 'produsen' => 'produsen4', 'diminta' => 8,  'disetujui' => 8],  // Kayu Manis Bubuk
                ],
            ],

            // 2. Seluruhnya disetujui
            [
                'tanggal' => '2026-08-10',
                'keterangan' => 'Stok awal bulan Agustus: tepung, gula, dan lemak.',
                'item' => [
                    ['bb' => 'TPG-001', 'produsen' => 'produsen1', 'diminta' => 40,  'disetujui' => 40],  // Terigu Protein Tinggi
                    ['bb' => 'TPG-002', 'produsen' => 'produsen1', 'diminta' => 30,  'disetujui' => 30],  // Terigu Protein Sedang
                    ['bb' => 'TPG-006', 'produsen' => 'produsen4', 'diminta' => 150, 'disetujui' => 150], // Maizena
                    ['bb' => 'GLA-001', 'produsen' => 'produsen1', 'diminta' => 12,  'disetujui' => 12],  // Gula Pasir Karung
                    ['bb' => 'GLA-002', 'produsen' => 'produsen4', 'diminta' => 100, 'disetujui' => 100], // Icing Sugar
                    ['bb' => 'LMK-001', 'produsen' => 'produsen2', 'diminta' => 60,  'disetujui' => 60],  // Mentega Tawar
                    ['bb' => 'LMK-003', 'produsen' => 'produsen1', 'diminta' => 120, 'disetujui' => 120], // Margarin Roti
                ],
            ],

            // 3. Mozzarella sebagian -> produsen lain (selesai); cooking cream ditolak -> diganti whipping non-dairy sebagian -> sisa ke produsen lain
            [
                'tanggal' => '2026-08-17',
                'keterangan' => 'Susu dan keju untuk produksi cheesecake dan pizza.',
                'item' => [
                    ['bb' => 'SSU-002', 'produsen' => 'produsen2', 'diminta' => 150, 'disetujui' => 150], // Susu UHT
                    ['bb' => 'SSU-003', 'produsen' => 'produsen3', 'diminta' => 50,  'disetujui' => 50],  // Susu Bubuk Full Cream
                    ['bb' => 'SSU-010', 'produsen' => 'produsen2', 'diminta' => 40,  'disetujui' => 40],  // Keju Cheddar
                    ['bb' => 'SSU-011', 'produsen' => 'produsen3', 'diminta' => 30,  'disetujui' => 20,   // Keju Mozzarella
                        'alasan' => 'Stok mozzarella produsen tinggal 20 kg.',
                        'alihan' => [
                            ['produsen' => 'produsen2', 'respon' => 10, 'alasan' => 'Sisa 10 kg dialihkan ke produsen lain.'],
                        ]],
                    ['bb' => 'SSU-006', 'produsen' => 'produsen3', 'diminta' => 80,  'disetujui' => 80],  // Susu Evaporasi
                    ['bb' => 'SSU-008', 'produsen' => 'produsen2', 'diminta' => 40,  'disetujui' => 0,    // Cooking Cream
                        'alasan' => 'Cooking cream kosong, pasokan baru belum tiba.',
                        'alihan' => [
                            ['bb' => 'TOP-006', 'produsen' => 'produsen3', 'respon' => 25, 'alasan' => 'Cooking cream kosong, diganti whipping cream non-dairy.'],
                            ['produsen' => 'produsen3', 'respon' => 15, 'alasan' => 'Sisa 15 liter dialihkan ke produsen lain.'],
                        ]],
                ],
            ],

            // 4. Seluruhnya disetujui
            [
                'tanggal' => '2026-08-24',
                'keterangan' => 'Restock kemasan dan perlengkapan baking.',
                'item' => [
                    ['bb' => 'KMS-001', 'produsen' => 'produsen4', 'diminta' => 1500, 'disetujui' => 1500], // Box Kue 20x20
                    ['bb' => 'KMS-003', 'produsen' => 'produsen4', 'diminta' => 1000, 'disetujui' => 1000], // Box Donat
                    ['bb' => 'KMS-006', 'produsen' => 'produsen1', 'diminta' => 150,  'disetujui' => 150],  // Plastik PP Roti Tawar
                    ['bb' => 'PLB-001', 'produsen' => 'produsen4', 'diminta' => 40,   'disetujui' => 40],   // Baking Paper
                    ['bb' => 'PLB-002', 'produsen' => 'produsen2', 'diminta' => 120,  'disetujui' => 120],  // Cupcake Liner
                    ['bb' => 'PLB-005', 'produsen' => 'produsen4', 'diminta' => 40,   'disetujui' => 40],   // Sarung Tangan Plastik
                ],
            ],

            // 5. Selai cokelat sebagian -> produsen lain, selesai
            [
                'tanggal' => '2026-08-31',
                'keterangan' => 'Cokelat dan selai untuk produk bulan September.',
                'item' => [
                    ['bb' => 'CKL-001', 'produsen' => 'produsen3', 'diminta' => 60, 'disetujui' => 60], // Dark Compound
                    ['bb' => 'CKL-003', 'produsen' => 'produsen2', 'diminta' => 30, 'disetujui' => 30], // Milk Couverture
                    ['bb' => 'CKL-007', 'produsen' => 'produsen3', 'diminta' => 50, 'disetujui' => 50], // Choco Chips
                    ['bb' => 'CKL-009', 'produsen' => 'produsen2', 'diminta' => 30, 'disetujui' => 24, // Selai Cokelat
                        'alasan' => 'Stok selai cokelat tersisa 24 kg.',
                        'alihan' => [
                            ['produsen' => 'produsen3', 'respon' => 6, 'alasan' => 'Sisa 6 kg dialihkan ke produsen lain.'],
                        ]],
                    ['bb' => 'BUA-004', 'produsen' => 'produsen3', 'diminta' => 40, 'disetujui' => 40], // Selai Stroberi
                    ['bb' => 'BUA-005', 'produsen' => 'produsen2', 'diminta' => 50, 'disetujui' => 50], // Selai Nanas
                ],
            ],

            // 6. Whipping cream sebagian -> produsen lain, disetujui penuh
            [
                'tanggal' => '2026-09-07',
                'keterangan' => 'Susu bubuk, krim, dan keju untuk kebutuhan mingguan.',
                'item' => [
                    ['bb' => 'SSU-002', 'produsen' => 'produsen2', 'diminta' => 120, 'disetujui' => 120], // Susu UHT
                    ['bb' => 'SSU-007', 'produsen' => 'produsen2', 'diminta' => 60,  'disetujui' => 45,   // Whipping Cream Dairy
                        'alasan' => 'Stok whipping cream produsen tersisa 45 liter.',
                        'alihan' => [
                            ['produsen' => 'produsen3', 'respon' => 15, 'alasan' => 'Sisa 15 liter dialihkan ke produsen lain karena stok produsen awal kurang.'],
                        ]],
                    ['bb' => 'SSU-003', 'produsen' => 'produsen3', 'diminta' => 60,  'disetujui' => 60],  // Susu Bubuk Full Cream
                    ['bb' => 'SSU-005', 'produsen' => 'produsen3', 'diminta' => 100, 'disetujui' => 100], // Susu Kental Manis
                    ['bb' => 'SSU-010', 'produsen' => 'produsen2', 'diminta' => 30,  'disetujui' => 30],  // Keju Cheddar
                ],
            ],

            // 7. Cocoa powder ditolak -> diganti Dutch cocoa dari produsen lain, disetujui
            [
                'tanggal' => '2026-09-14',
                'keterangan' => 'Cokelat dan kacang untuk produksi kue kering.',
                'item' => [
                    ['bb' => 'CKL-002', 'produsen' => 'produsen3', 'diminta' => 40, 'disetujui' => 40], // Dark Couverture
                    ['bb' => 'CKL-005', 'produsen' => 'produsen3', 'diminta' => 30, 'disetujui' => 0,  // Cocoa Powder
                        'alasan' => 'Cocoa powder kosong, pasokan baru belum datang.',
                        'alihan' => [
                            ['bb' => 'CKL-006', 'produsen' => 'produsen2', 'respon' => 30, 'alasan' => 'Diganti Dutch cocoa powder karena cocoa powder biasa tidak tersedia.'],
                        ]],
                    ['bb' => 'KCG-001', 'produsen' => 'produsen3', 'diminta' => 25, 'disetujui' => 25], // Almond Slice
                    ['bb' => 'KCG-005', 'produsen' => 'produsen4', 'diminta' => 40, 'disetujui' => 40], // Kacang Tanah Sangrai
                ],
            ],

            // 8. Seluruhnya disetujui
            [
                'tanggal' => '2026-09-21',
                'keterangan' => 'Kacang, biji-bijian, dan rempah.',
                'item' => [
                    ['bb' => 'KCG-002', 'produsen' => 'produsen3', 'diminta' => 20, 'disetujui' => 20], // Almond Utuh
                    ['bb' => 'KCG-003', 'produsen' => 'produsen4', 'diminta' => 15, 'disetujui' => 15], // Kenari
                    ['bb' => 'KCG-004', 'produsen' => 'produsen3', 'diminta' => 20, 'disetujui' => 20], // Kacang Mede
                    ['bb' => 'KCG-008', 'produsen' => 'produsen4', 'diminta' => 20, 'disetujui' => 20], // Wijen Putih
                    ['bb' => 'KCG-012', 'produsen' => 'produsen3', 'diminta' => 40, 'disetujui' => 40], // Kelapa Parut Kering
                    ['bb' => 'KCG-013', 'produsen' => 'produsen4', 'diminta' => 40, 'disetujui' => 40], // Rolled Oats
                    ['bb' => 'RMP-001', 'produsen' => 'produsen4', 'diminta' => 60, 'disetujui' => 60], // Garam Halus
                    ['bb' => 'RMP-002', 'produsen' => 'produsen3', 'diminta' => 6,  'disetujui' => 6],  // Kayu Manis Bubuk
                    ['bb' => 'RMP-006', 'produsen' => 'produsen4', 'diminta' => 5,  'disetujui' => 5],  // Jahe Bubuk
                ],
            ],

            // 9. Margarin pastry sebagian -> pengganti pertama sebagian -> pengganti kedua menutup sisa
            [
                'tanggal' => '2026-09-28',
                'keterangan' => 'Lemak dan pengembang untuk persiapan produksi roti.',
                'item' => [
                    ['bb' => 'LMK-004', 'produsen' => 'produsen2', 'diminta' => 60, 'disetujui' => 36, // Margarin Pastry
                        'alasan' => 'Produsen hanya sanggup memenuhi 36 kg dari stok yang tersedia.',
                        'alihan' => [
                            ['produsen' => 'produsen1', 'respon' => 10, 'alasan' => 'Kekurangan 24 kg dialihkan ke produsen lain.'],
                            ['produsen' => 'produsen4', 'respon' => 14, 'alasan' => 'Sisa 14 kg dialihkan lagi karena pengganti pertama hanya sanggup sebagian.'],
                        ]],
                    ['bb' => 'LMK-007', 'produsen' => 'produsen4', 'diminta' => 100, 'disetujui' => 100], // Minyak Goreng
                    ['bb' => 'PGK-001', 'produsen' => 'produsen1', 'diminta' => 30,  'disetujui' => 30],  // Ragi Instan
                    ['bb' => 'PGK-003', 'produsen' => 'produsen3', 'diminta' => 20,  'disetujui' => 20],  // Baking Powder
                    ['bb' => 'PGK-006', 'produsen' => 'produsen1', 'diminta' => 30,  'disetujui' => 30],  // Bread Improver
                ],
            ],

            // 10. Mirror glaze ditolak -> pengganti juga menolak -> sisa belum ditentukan admin
            [
                'tanggal' => '2026-10-05',
                'keterangan' => 'Topping dan dekorasi untuk pesanan kue ulang tahun.',
                'item' => [
                    ['bb' => 'TOP-001', 'produsen' => 'produsen3', 'diminta' => 20, 'disetujui' => 20], // Fondant Putih
                    ['bb' => 'TOP-003', 'produsen' => 'produsen3', 'diminta' => 15, 'disetujui' => 15], // Sprinkles
                    ['bb' => 'TOP-004', 'produsen' => 'produsen4', 'diminta' => 8,  'disetujui' => 0,  // Mirror Glaze
                        'alasan' => 'Mirror glaze sedang kosong, pasokan baru belum datang.',
                        'alihan' => [
                            ['produsen' => 'produsen3', 'respon' => 0, 'alasan' => 'Dialihkan ke produsen lain karena produsen awal kosong.'],
                        ]],
                    ['bb' => 'TOP-006', 'produsen' => 'produsen3', 'diminta' => 30, 'disetujui' => 30], // Whipping Cream Non-Dairy
                    ['bb' => 'TOP-009', 'produsen' => 'produsen4', 'diminta' => 15, 'disetujui' => 15], // Cookie Crumb
                ],
            ],

            // 11. Seluruhnya disetujui
            [
                'tanggal' => '2026-10-12',
                'keterangan' => 'Perisa dan selai untuk varian rasa baru.',
                'item' => [
                    ['bb' => 'PRS-001', 'produsen' => 'produsen3', 'diminta' => 12, 'disetujui' => 12], // Ekstrak Vanili
                    ['bb' => 'PRS-006', 'produsen' => 'produsen3', 'diminta' => 20, 'disetujui' => 20], // Pasta Pandan
                    ['bb' => 'PRS-010', 'produsen' => 'produsen1', 'diminta' => 4,  'disetujui' => 4],  // Matcha Powder
                    ['bb' => 'BUA-004', 'produsen' => 'produsen3', 'diminta' => 35, 'disetujui' => 35], // Selai Stroberi
                    ['bb' => 'BUA-006', 'produsen' => 'produsen2', 'diminta' => 20, 'disetujui' => 20], // Selai Blueberry
                    ['bb' => 'BUA-001', 'produsen' => 'produsen3', 'diminta' => 20, 'disetujui' => 20], // Kismis
                ],
            ],

            // 12. Telur sebagian -> pengganti sebagian -> sisa diganti telur kampung, disetujui
            [
                'tanggal' => '2026-10-15',
                'keterangan' => 'Telur, susu UHT, dan mentega kebutuhan mingguan.',
                'item' => [
                    ['bb' => 'TLR-001', 'produsen' => 'produsen2', 'diminta' => 3000, 'disetujui' => 2400, // Telur Ayam Ras
                        'alasan' => 'Stok telur produsen tersisa 2400 butir.',
                        'alihan' => [
                            ['produsen' => 'produsen4', 'respon' => 300, 'alasan' => 'Kekurangan 600 butir dialihkan ke produsen lain.'],
                            ['bb' => 'TLR-002', 'produsen' => 'produsen4', 'respon' => 300, 'alasan' => 'Sisa kekurangan diganti telur ayam kampung.'],
                        ]],
                    ['bb' => 'SSU-002', 'produsen' => 'produsen2', 'diminta' => 100, 'disetujui' => 100],  // Susu UHT
                    ['bb' => 'LMK-001', 'produsen' => 'produsen2', 'diminta' => 50,  'disetujui' => 50],   // Mentega Tawar
                    ['bb' => 'LMK-003', 'produsen' => 'produsen1', 'diminta' => 80,  'disetujui' => 80],   // Margarin Roti
                    ['bb' => 'GLA-003', 'produsen' => 'produsen3', 'diminta' => 40,  'disetujui' => 40],   // Gula Kastor
                ],
            ],

            // 13. Terigu protein tinggi sebagian -> diganti protein sedang, menunggu respon pengganti
            [
                'tanggal' => '2026-10-19',
                'keterangan' => 'Tepung dan gula stok besar menjelang akhir tahun.',
                'item' => [
                    ['bb' => 'TPG-001', 'produsen' => 'produsen1', 'diminta' => 60, 'disetujui' => 40, // Terigu Protein Tinggi
                        'alasan' => 'Stok terigu protein tinggi tersisa 40 sak.',
                        'alihan' => [
                            ['bb' => 'TPG-002', 'produsen' => 'produsen2', 'respon' => null, 'alasan' => 'Terigu protein tinggi kurang, kekurangan dialihkan ke protein sedang.'],
                        ]],
                    ['bb' => 'TPG-002', 'produsen' => 'produsen4', 'diminta' => 40, 'disetujui' => 40], // Terigu Protein Sedang
                    ['bb' => 'GLA-001', 'produsen' => 'produsen1', 'diminta' => 15, 'disetujui' => 15], // Gula Pasir Karung
                    ['bb' => 'GLA-003', 'produsen' => 'produsen3', 'diminta' => 60, 'disetujui' => 60], // Gula Kastor
                    ['bb' => 'GLA-005', 'produsen' => 'produsen4', 'diminta' => 80, 'disetujui' => 80], // Brown Sugar
                ],
            ],

            // 14. Box 25x25 sebagian -> diganti box 20x20 sebagian -> sisa belum ditentukan
            [
                'tanggal' => '2026-10-22',
                'keterangan' => 'Kemasan dan perlengkapan stok kuartal akhir.',
                'item' => [
                    ['bb' => 'KMS-002', 'produsen' => 'produsen4', 'diminta' => 1500, 'disetujui' => 900, // Box Kue 25x25
                        'alasan' => 'Stok box 25x25 produsen tinggal 900 pcs.',
                        'alihan' => [
                            ['bb' => 'KMS-001', 'produsen' => 'produsen1', 'respon' => 400, 'alasan' => 'Box 25x25 kurang, diganti box 20x20.'],
                        ]],
                    ['bb' => 'KMS-004', 'produsen' => 'produsen4', 'diminta' => 800, 'disetujui' => 800], // Box Roti Tawar
                    ['bb' => 'KMS-007', 'produsen' => 'produsen1', 'diminta' => 100, 'disetujui' => 100], // Standing Pouch
                    ['bb' => 'KMS-008', 'produsen' => 'produsen4', 'diminta' => 120, 'disetujui' => 120], // Mika Kue Bulat
                    ['bb' => 'KMS-009', 'produsen' => 'produsen1', 'diminta' => 150, 'disetujui' => 150], // Cup Puding
                    ['bb' => 'PLB-003', 'produsen' => 'produsen4', 'diminta' => 40,  'disetujui' => 40],  // Aluminium Foil
                    ['bb' => 'PLB-004', 'produsen' => 'produsen2', 'diminta' => 40,  'disetujui' => 40],  // Cling Wrap
                    ['bb' => 'PLB-008', 'produsen' => 'produsen4', 'diminta' => 30,  'disetujui' => 30],  // Pan Spray
                ],
            ],

            // 15. Cream cheese ditolak -> belum ditentukan pengganti
            [
                'tanggal' => '2026-10-26',
                'keterangan' => 'Produk susu dan keju untuk cheesecake dan pizza roll.',
                'item' => [
                    ['bb' => 'SSU-009', 'produsen' => 'produsen2', 'diminta' => 50,  'disetujui' => 0, // Cream Cheese
                        'alasan' => 'Cream cheese kosong, menunggu kiriman importir.'],
                    ['bb' => 'SSU-010', 'produsen' => 'produsen2', 'diminta' => 50,  'disetujui' => 50],  // Keju Cheddar
                    ['bb' => 'SSU-011', 'produsen' => 'produsen3', 'diminta' => 40,  'disetujui' => 40],  // Keju Mozzarella
                    ['bb' => 'SSU-002', 'produsen' => 'produsen2', 'diminta' => 120, 'disetujui' => 120], // Susu UHT
                    ['bb' => 'SSU-005', 'produsen' => 'produsen3', 'diminta' => 150, 'disetujui' => 150], // Susu Kental Manis
                ],
            ],

            // 16. Mentega tawar sebagian -> diganti mentega asin, menunggu respon pengganti
            [
                'tanggal' => '2026-10-31',
                'keterangan' => 'Mentega dan margarin, stok mentega menipis.',
                'item' => [
                    ['bb' => 'LMK-001', 'produsen' => 'produsen2', 'diminta' => 100, 'disetujui' => 70, // Mentega Tawar
                        'alasan' => 'Stok mentega tawar produsen tersisa 70 kg.',
                        'alihan' => [
                            ['bb' => 'LMK-002', 'produsen' => 'produsen1', 'respon' => null, 'alasan' => 'Mentega tawar kurang, diganti mentega asin untuk produk yang tidak sensitif terhadap garam.'],
                        ]],
                    ['bb' => 'LMK-003', 'produsen' => 'produsen1', 'diminta' => 150, 'disetujui' => 150], // Margarin Roti
                    ['bb' => 'LMK-006', 'produsen' => 'produsen4', 'diminta' => 80,  'disetujui' => 80],  // Shortening
                    ['bb' => 'LMK-004', 'produsen' => 'produsen4', 'diminta' => 60,  'disetujui' => 60],  // Margarin Pastry
                ],
            ],

            // 17. Sebagian sudah ditanggapi, sebagian masih pending
            [
                'tanggal' => '2026-11-04',
                'keterangan' => 'Restock mingguan campuran.',
                'item' => [
                    ['bb' => 'CKL-005', 'produsen' => 'produsen3', 'diminta' => 40, 'disetujui' => 40], // Cocoa Powder
                    ['bb' => 'KCG-007', 'produsen' => 'produsen3', 'diminta' => 6,  'disetujui' => 3,  // Hazelnut
                        'alasan' => 'Stok hazelnut produsen tinggal 3 kg.',
                        'alihan' => [
                            ['produsen' => 'produsen4', 'respon' => 3, 'alasan' => 'Sisa 3 kg dialihkan ke produsen lain.'],
                        ]],
                    ['bb' => 'SSU-009', 'produsen' => 'produsen3', 'diminta' => 40,   'disetujui' => 40],   // Cream Cheese
                    ['bb' => 'TLR-001', 'produsen' => 'produsen2', 'diminta' => 3000, 'disetujui' => null], // Telur Ayam Ras
                    ['bb' => 'TLR-002', 'produsen' => 'produsen4', 'diminta' => 400,  'disetujui' => null], // Telur Ayam Kampung
                    ['bb' => 'KMS-011', 'produsen' => 'produsen4', 'diminta' => 40,   'disetujui' => null], // Pita / Tali Raffia
                ],
            ],

            // 18. Seluruhnya belum ditanggapi (nomor urut kedua di hari yang sama)
            [
                'tanggal' => '2026-11-04',
                'keterangan' => 'Kemasan dan perlengkapan untuk akhir tahun.',
                'item' => [
                    ['bb' => 'KMS-002', 'produsen' => 'produsen4', 'diminta' => 1000, 'disetujui' => null], // Box Kue 25x25
                    ['bb' => 'KMS-004', 'produsen' => 'produsen1', 'diminta' => 800,  'disetujui' => null], // Box Roti Tawar
                    ['bb' => 'KMS-005', 'produsen' => 'produsen4', 'diminta' => 800,  'disetujui' => null], // Box Brownies
                    ['bb' => 'KMS-010', 'produsen' => 'produsen4', 'diminta' => 60,   'disetujui' => null], // Kantong Kertas Kraft
                    ['bb' => 'PLB-009', 'produsen' => 'produsen4', 'diminta' => 80,   'disetujui' => null], // Lilin Ulang Tahun
                ],
            ],

            // 19. Baru ditanggapi sebagian; almond slice kurang dan belum ada pengganti
            [
                'tanggal' => '2026-11-06',
                'keterangan' => 'Cokelat, kacang, dan buah untuk produksi akhir tahun.',
                'item' => [
                    ['bb' => 'CKL-002', 'produsen' => 'produsen3', 'diminta' => 50, 'disetujui' => 50], // Dark Couverture
                    ['bb' => 'CKL-004', 'produsen' => 'produsen2', 'diminta' => 25, 'disetujui' => 25], // White Couverture
                    ['bb' => 'KCG-001', 'produsen' => 'produsen3', 'diminta' => 30, 'disetujui' => 18, // Almond Slice
                        'alasan' => 'Stok almond slice produsen tersisa 18 kg.'],
                    ['bb' => 'KCG-006', 'produsen' => 'produsen3', 'diminta' => 6,  'disetujui' => null], // Pistachio
                    ['bb' => 'BUA-002', 'produsen' => 'produsen3', 'diminta' => 15, 'disetujui' => null], // Cranberry Kering
                    ['bb' => 'BUA-003', 'produsen' => 'produsen2', 'diminta' => 15, 'disetujui' => null], // Kurma Cincang
                ],
            ],

            // 20. Permintaan terbaru, seluruhnya belum ditanggapi
            [
                'tanggal' => '2026-11-07',
                'keterangan' => 'Persiapan produksi Natal dan tahun baru.',
                'item' => [
                    ['bb' => 'TPG-001', 'produsen' => 'produsen1', 'diminta' => 80,  'disetujui' => null], // Terigu Protein Tinggi
                    ['bb' => 'GLA-002', 'produsen' => 'produsen4', 'diminta' => 150, 'disetujui' => null], // Icing Sugar
                    ['bb' => 'LMK-001', 'produsen' => 'produsen2', 'diminta' => 120, 'disetujui' => null], // Mentega Tawar
                    ['bb' => 'CKL-003', 'produsen' => 'produsen3', 'diminta' => 30,  'disetujui' => null], // Milk Chocolate Couverture
                    ['bb' => 'TOP-003', 'produsen' => 'produsen3', 'diminta' => 25,  'disetujui' => null], // Sprinkles
                    ['bb' => 'PRS-012', 'produsen' => 'produsen3', 'diminta' => 20,  'disetujui' => null], // Pewarna Merah
                ],
            ],
        ];
    }
}
