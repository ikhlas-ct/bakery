<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\PemakaianBahanBakuDetail;
use App\Models\PenerimaanBahanBakuDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Perhitungan stok bahan baku berbasis riwayat transaksi (bukan hanya kolom cache stok_saat_ini).
 *
 *  - Stok masuk  : penerimaan_bahan_baku_details.jumlah_diterima  (tanggal = tanggal_terima)
 *  - Stok keluar : pemakaian_bahan_baku_details.jumlah_dipakai    (tanggal = tanggal_pakai)
 *  - Batch FEFO  : tiap baris penerimaan_bahan_baku_details = 1 batch, diurutkan tanggal_kadaluarsa terdekat
 *                  (batch tanpa tanggal kadaluarsa diletakkan paling akhir).
 *
 * Karena semua dihitung dari riwayat, laporan bisa ditampilkan "per tanggal" tertentu.
 */
class StokService
{
    /** Batch dianggap "segera kadaluarsa" bila sisa harinya <= nilai ini. */
    public const BATAS_SEGERA_KADALUARSA = 30;

    /* ─────────────────────────────────────────────────────────────
     |  LAPORAN STOK
     ───────────────────────────────────────────────────────────── */

    /**
     * Posisi stok semua bahan baku pada tanggal $per, lengkap dengan batch FEFO-nya.
     *
     * @param  array{search?:string,kategori_barang_id?:int|string,status?:string,stok?:string}  $filter
     */
    public function posisiStok(Carbon $per, array $filter = []): Collection
    {
        $per = $per->copy()->startOfDay();
        $perStr = $per->toDateString();

        $bahanBakus = BahanBaku::query()
            ->with(['kategoriBarang', 'satuan'])
            ->when($filter['search'] ?? null, function ($q, $s) {
                $q->where(function ($w) use ($s) {
                    $w->where('kode_bahan_baku', 'like', "%{$s}%")
                        ->orWhere('nama_bahan_baku', 'like', "%{$s}%");
                });
            })
            ->when($filter['kategori_barang_id'] ?? null, fn ($q, $v) => $q->where('kategori_barang_id', $v))
            ->when(($filter['status'] ?? null) === 'aktif', fn ($q) => $q->where('is_aktif', true))
            ->when(($filter['status'] ?? null) === 'nonaktif', fn ($q) => $q->where('is_aktif', false))
            ->withSum(['penerimaanBahanBakuDetails as total_masuk' => function ($q) use ($perStr) {
                $q->whereHas('penerimaanBahanBaku', fn ($p) => $p->whereDate('tanggal_terima', '<=', $perStr));
            }], 'jumlah_diterima')
            ->withSum(['pemakaianBahanBakuDetails as total_keluar' => function ($q) use ($perStr) {
                $q->whereHas('pemakaianBahanBaku', fn ($p) => $p->whereDate('tanggal_pakai', '<=', $perStr));
            }], 'jumlah_dipakai')
            ->orderBy('nama_bahan_baku')
            ->get();

        $batches = $this->batchPerBahan($bahanBakus->pluck('id'), $per);

        $rows = $bahanBakus->map(function (BahanBaku $b) use ($batches, $per) {
            $masuk = round((float) $b->total_masuk, 2);
            $keluar = round((float) $b->total_keluar, 2);
            $stok = round($masuk - $keluar, 2);
            $min = round((float) $b->stok_minimum, 2);

            /** @var Collection $bt */
            $bt = $batches->get($b->id, collect());
            $sisaBatch = round((float) $bt->sum('sisa'), 2);

            return (object) [
                'bahan'            => $b,
                'masuk'            => $masuk,
                'keluar'           => $keluar,
                'stok'             => $stok,
                'minimum'          => $min,
                'status_stok'      => $stok <= 0 ? 'habis' : ($stok <= $min ? 'kritis' : 'aman'),
                'batches'          => $bt,
                'jumlah_batch'     => $bt->count(),
                'batch_kadaluarsa' => $bt->where('status', 'kadaluarsa')->count(),
                'batch_segera'     => $bt->where('status', 'segera')->count(),
                // $bt sudah urut FEFO, jadi batch pertama yang punya tanggal = kadaluarsa terdekat
                'exp_terdekat'     => $bt->first(fn ($x) => $x->tanggal_kadaluarsa !== null)?->tanggal_kadaluarsa,
                // stok hasil hitung vs total sisa batch (beda = ada pemakaian yang tidak terhubung ke batch)
                'selisih_batch'    => round($stok - $sisaBatch, 2),
                // stok hasil hitung vs kolom cache bahan_bakus.stok_saat_ini (hanya bermakna untuk hari ini)
                'selisih_cache'    => $per->isToday() ? round((float) $b->stok_saat_ini - $stok, 2) : 0.0,
            ];
        });

        $rows = match ($filter['stok'] ?? null) {
            'kritis'   => $rows->filter(fn ($r) => in_array($r->status_stok, ['kritis', 'habis'], true)),
            'habis'    => $rows->filter(fn ($r) => $r->status_stok === 'habis'),
            'expiring' => $rows->filter(fn ($r) => $r->batch_kadaluarsa > 0 || $r->batch_segera > 0),
            default    => $rows,
        };

        return $rows->values();
    }

    /** Angka ringkas untuk stat card. */
    public function ringkasan(Collection $rows): array
    {
        return [
            'total'         => $rows->count(),
            'kritis'        => $rows->whereIn('status_stok', ['kritis', 'habis'])->count(),
            'segera'        => (int) $rows->sum('batch_segera'),
            'kadaluarsa'    => (int) $rows->sum('batch_kadaluarsa'),
            'tidak_sinkron' => $rows->filter(fn ($r) => abs($r->selisih_cache) > 0.004)->count(),
        ];
    }

    /**
     * Batch penerimaan yang MASIH punya sisa pada tanggal $per, dikelompokkan per bahan_baku_id
     * dan diurutkan FEFO (kadaluarsa terdekat -> tanggal terima terlama -> id).
     *
     * @return Collection<int, Collection<int, object>>
     */
    public function batchPerBahan(iterable $bahanIds, Carbon $per): Collection
    {
        $per = $per->copy()->startOfDay();
        $perStr = $per->toDateString();
        $ids = collect($bahanIds)->values()->all();

        if (empty($ids)) {
            return collect();
        }

        $details = PenerimaanBahanBakuDetail::query()
            ->with('penerimaanBahanBaku.produsen')
            ->whereIn('bahan_baku_id', $ids)
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->whereDate('tanggal_terima', '<=', $perStr))
            ->withSum(['pemakaianBahanBakuDetails as dipakai_sd' => function ($q) use ($perStr) {
                $q->whereHas('pemakaianBahanBaku', fn ($p) => $p->whereDate('tanggal_pakai', '<=', $perStr));
            }], 'jumlah_dipakai')
            ->get();

        return $details
            ->map(function (PenerimaanBahanBakuDetail $d) use ($per) {
                $diterima = round((float) $d->jumlah_diterima, 2);
                $dipakai = round((float) $d->dipakai_sd, 2);
                $exp = $d->tanggal_kadaluarsa;
                $sisaHari = $exp ? (int) $per->diffInDays($exp->copy()->startOfDay(), false) : null;
                $pen = $d->penerimaanBahanBaku;

                return (object) [
                    'id'                 => $d->id,
                    'bahan_baku_id'      => $d->bahan_baku_id,
                    'nomor_penerimaan'   => $pen?->nomor_transaksi ?? '-',
                    'produsen'           => $pen?->produsen?->nama_produsen ?? '-',
                    'tanggal_terima'     => $pen?->tanggal_terima,
                    'tanggal_kadaluarsa' => $exp,
                    'diterima'           => $diterima,
                    'dipakai'            => $dipakai,
                    'sisa'               => round($diterima - $dipakai, 2),
                    'sisa_hari'          => $sisaHari,
                    'status'             => $this->statusKadaluarsa($sisaHari),
                    'urutan'             => 0,
                    'sort_key'           => ($exp?->format('Y-m-d') ?? '9999-12-31')
                        . '|' . ($pen?->tanggal_terima?->format('Y-m-d') ?? '0000-00-00')
                        . '|' . str_pad((string) $d->id, 10, '0', STR_PAD_LEFT),
                ];
            })
            ->filter(fn ($b) => $b->sisa > 0)
            ->sortBy('sort_key')
            ->groupBy('bahan_baku_id')
            ->map(function (Collection $group) {
                $group = $group->values();
                $group->each(function ($b, $i) {
                    $b->urutan = $i + 1;
                });

                return $group;
            });
    }

    /* ─────────────────────────────────────────────────────────────
     |  BAHAN BAKU MENDEKATI KEDALUWARSA
     ───────────────────────────────────────────────────────────── */

    /** Sisa hari <= nilai ini dianggap "mendesak" di laporan kedaluwarsa. */
    public const BATAS_MENDESAK = 7;

    /**
     * Batch yang sudah kedaluwarsa atau akan kedaluwarsa dalam $hari hari ke depan
     * (hanya batch yang MASIH punya sisa stok), diurutkan dari yang paling mendesak.
     *
     * level: kadaluarsa (sisa hari < 0) | mendesak (0..BATAS_MENDESAK) | mendekati (sisanya)
     *
     * @param  string  $tampil  semua | mendekati | kadaluarsa
     * @param  array{search?:string,kategori_barang_id?:int|string}  $filter
     */
    public function batchKedaluwarsa(Carbon $per, int $hari = 30, string $tampil = 'semua', array $filter = []): Collection
    {
        $per = $per->copy()->startOfDay();

        $bahanBakus = BahanBaku::query()
            ->with(['kategoriBarang', 'satuan'])
            ->when($filter['search'] ?? null, function ($q, $s) {
                $q->where(function ($w) use ($s) {
                    $w->where('kode_bahan_baku', 'like', "%{$s}%")
                        ->orWhere('nama_bahan_baku', 'like', "%{$s}%");
                });
            })
            ->when($filter['kategori_barang_id'] ?? null, fn ($q, $v) => $q->where('kategori_barang_id', $v))
            ->get();

        $batches = $this->batchPerBahan($bahanBakus->pluck('id'), $per);

        return $bahanBakus->toBase()
            ->flatMap(function (BahanBaku $b) use ($batches, $hari, $tampil) {
                return $batches->get($b->id, collect())
                    ->filter(function ($bt) use ($hari, $tampil) {
                        if ($bt->sisa_hari === null) {
                            return false; // tanpa tanggal kedaluwarsa
                        }

                        $lewat = $bt->sisa_hari < 0;
                        $dekat = ! $lewat && $bt->sisa_hari <= $hari;

                        return match ($tampil) {
                            'kadaluarsa' => $lewat,
                            'mendekati'  => $dekat,
                            default      => $lewat || $dekat,
                        };
                    })
                    ->map(fn ($bt) => (object) [
                        'bahan' => $b,
                        'batch' => $bt,
                        'level' => $bt->sisa_hari < 0
                            ? 'kadaluarsa'
                            : ($bt->sisa_hari <= self::BATAS_MENDESAK ? 'mendesak' : 'mendekati'),
                    ]);
            })
            ->sort(fn ($a, $b) => [$a->batch->sisa_hari, $a->bahan->nama_bahan_baku]
                <=> [$b->batch->sisa_hari, $b->bahan->nama_bahan_baku])
            ->values();
    }

    /** Angka ringkas untuk stat card laporan kedaluwarsa. */
    public function ringkasanKedaluwarsa(Collection $rows): array
    {
        return [
            'total_batch' => $rows->count(),
            'bahan'       => $rows->pluck('bahan.id')->unique()->count(),
            'kadaluarsa'  => $rows->where('level', 'kadaluarsa')->count(),
            'mendesak'    => $rows->where('level', 'mendesak')->count(),
            'mendekati'   => $rows->where('level', 'mendekati')->count(),
        ];
    }

    /* ─────────────────────────────────────────────────────────────
     |  KARTU STOK
     ───────────────────────────────────────────────────────────── */

    /**
     * Buku besar mutasi satu bahan baku untuk periode $dari – $sampai.
     *
     * @return array{saldo_awal:float,rows:Collection,total_masuk:float,total_keluar:float,saldo_akhir:float}
     */
    public function kartuStok(BahanBaku $bahan, Carbon $dari, Carbon $sampai): array
    {
        $dariStr = $dari->copy()->startOfDay()->toDateString();
        $sampaiStr = $sampai->copy()->startOfDay()->toDateString();

        // ── Saldo awal = semua mutasi sebelum tanggal $dari ──
        $masukAwal = (float) PenerimaanBahanBakuDetail::query()
            ->where('bahan_baku_id', $bahan->id)
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->whereDate('tanggal_terima', '<', $dariStr))
            ->sum('jumlah_diterima');

        $keluarAwal = (float) PemakaianBahanBakuDetail::query()
            ->where('bahan_baku_id', $bahan->id)
            ->whereHas('pemakaianBahanBaku', fn ($q) => $q->whereDate('tanggal_pakai', '<', $dariStr))
            ->sum('jumlah_dipakai');

        $saldoAwal = round($masukAwal - $keluarAwal, 2);

        // ── Mutasi masuk (penerimaan) dalam periode ──
        $masuk = PenerimaanBahanBakuDetail::query()
            ->with('penerimaanBahanBaku.produsen')
            ->where('bahan_baku_id', $bahan->id)
            ->whereHas('penerimaanBahanBaku', function ($q) use ($dariStr, $sampaiStr) {
                $q->whereDate('tanggal_terima', '>=', $dariStr)->whereDate('tanggal_terima', '<=', $sampaiStr);
            })
            ->get()
            ->map(function (PenerimaanBahanBakuDetail $d) {
                $pen = $d->penerimaanBahanBaku;

                return (object) [
                    'tanggal'          => $pen->tanggal_terima,
                    'tipe'             => 'masuk',
                    'nomor'            => $pen->nomor_transaksi,
                    'keterangan'       => 'Penerimaan dari ' . ($pen->produsen?->nama_produsen ?? '-'),
                    'batch_nomor'      => $pen->nomor_transaksi,
                    'batch_kadaluarsa' => $d->tanggal_kadaluarsa,
                    'masuk'            => round((float) $d->jumlah_diterima, 2),
                    'keluar'           => 0.0,
                    'saldo'            => 0.0,
                    'sort_key'         => $pen->tanggal_terima->format('Y-m-d') . '|0|' . str_pad((string) $d->id, 10, '0', STR_PAD_LEFT),
                ];
            });

        // ── Mutasi keluar (pemakaian) dalam periode ──
        $keluar = PemakaianBahanBakuDetail::query()
            ->with(['pemakaianBahanBaku', 'penerimaanBahanBakuDetail.penerimaanBahanBaku'])
            ->where('bahan_baku_id', $bahan->id)
            ->whereHas('pemakaianBahanBaku', function ($q) use ($dariStr, $sampaiStr) {
                $q->whereDate('tanggal_pakai', '>=', $dariStr)->whereDate('tanggal_pakai', '<=', $sampaiStr);
            })
            ->get()
            ->map(function (PemakaianBahanBakuDetail $d) {
                $pmk = $d->pemakaianBahanBaku;
                $batch = $d->penerimaanBahanBakuDetail;

                return (object) [
                    'tanggal'          => $pmk->tanggal_pakai,
                    'tipe'             => 'keluar',
                    'nomor'            => $pmk->nomor_transaksi,
                    'keterangan'       => 'Pemakaian' . ($pmk->keterangan ? ' – ' . $pmk->keterangan : ''),
                    'batch_nomor'      => $batch?->penerimaanBahanBaku?->nomor_transaksi,
                    'batch_kadaluarsa' => $batch?->tanggal_kadaluarsa,
                    'masuk'            => 0.0,
                    'keluar'           => round((float) $d->jumlah_dipakai, 2),
                    'saldo'            => 0.0,
                    'sort_key'         => $pmk->tanggal_pakai->format('Y-m-d') . '|1|' . str_pad((string) $d->id, 10, '0', STR_PAD_LEFT),
                ];
            });

        // Pada tanggal yang sama, penerimaan didahulukan dari pemakaian agar saldo tidak sempat negatif.
        $rows = $masuk->concat($keluar)->sortBy('sort_key')->values();

        $saldo = $saldoAwal;
        foreach ($rows as $r) {
            $saldo = round($saldo + $r->masuk - $r->keluar, 2);
            $r->saldo = $saldo;
        }

        return [
            'saldo_awal'   => $saldoAwal,
            'rows'         => $rows,
            'total_masuk'  => round((float) $rows->sum('masuk'), 2),
            'total_keluar' => round((float) $rows->sum('keluar'), 2),
            'saldo_akhir'  => $saldo,
        ];
    }

    /* ─────────────────────────────────────────────────────────────
     |  Helper
     ───────────────────────────────────────────────────────────── */

    private function statusKadaluarsa(?int $sisaHari): string
    {
        if ($sisaHari === null) {
            return 'tanpa';
        }
        if ($sisaHari < 0) {
            return 'kadaluarsa';
        }

        return $sisaHari <= self::BATAS_SEGERA_KADALUARSA ? 'segera' : 'aman';
    }
}
