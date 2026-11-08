<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Pembayaran Saya (role produsen, hanya baca).
 *
 * Produsen melihat tagihan pembayaran miliknya sendiri, termasuk bukti pembayaran
 * (bukti_bayar) dan gambar receipt (foto_receipt) yang diunggah admin.
 * Semua query dibatasi ke produsen_id milik user yang login.
 */
class PembayaranProdusenController extends Controller
{
    private const STATUS_BAYAR = ['belum', 'lunas'];
    private const STATUS_ACC = ['menunggu', 'disetujui', 'ditolak'];
    private const METODE_BAYAR = ['Tunai', 'Transfer'];

    /** Kota yang tampil di atas tanda tangan pada laporan cetak. */
    private const KOTA_CETAK = 'Kaohsiung';

    public function index(Request $request): View
    {
        $produsen = $this->produsenLogin();

        $milikSaya = fn () => Pembayaran::query()->where('produsen_id', $produsen->id);

        // Stat card (tidak terpengaruh filter). Tagihan yang ditolak tidak dihitung sebagai "belum dibayar"
        // karena memang tidak dapat dibayar.
        $totalBelumDibayar = (float) $milikSaya()
            ->where('status_bayar', 'belum')
            ->where('status_acc', '!=', 'ditolak')
            ->sum('jumlah_bayar');
        $totalLunas = (float) $milikSaya()->where('status_bayar', 'lunas')->sum('jumlah_bayar');
        $menungguPersetujuan = $milikSaya()->where('status_acc', 'menunggu')->count();

        [$statusBayar, $statusAcc, $metodeBayar] = $this->filterStatus($request);
        [$dari, $sampai] = $this->periodeFilter($request);

        $pembayarans = $this->queryFilter($produsen, $statusBayar, $statusAcc, $metodeBayar, $dari, $sampai)
            ->with('permintaanBahanBaku:id,nomor_transaksi,tanggal_permintaan')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.pembayaran-produsen.index', compact(
            'pembayarans',
            'totalBelumDibayar',
            'totalLunas',
            'menungguPersetujuan'
        ));
    }

    public function show(Pembayaran $pembayaran): View
    {
        $produsen = $this->produsenLogin();

        // Produsen hanya boleh membuka pembayaran miliknya sendiri.
        abort_unless((int) $pembayaran->produsen_id === (int) $produsen->id, 403);

        $pembayaran->load('permintaanBahanBaku:id,nomor_transaksi,tanggal_permintaan,keterangan');
        $permintaanId = (int) $pembayaran->permintaan_bahan_baku_id;

        // ── Item permintaan milik produsen ini ──
        $details = PermintaanBahanBakuDetail::query()
            ->where('permintaan_bahan_baku_id', $permintaanId)
            ->where('produsen_id', $produsen->id)
            ->orderBy('id')
            ->get();

        $bahans = BahanBaku::with('satuan')
            ->whereIn('id', $details->pluck('bahan_baku_id')->unique())
            ->get()
            ->keyBy('id');

        $diterima = PenerimaanBahanBakuDetail::query()
            ->whereIn('permintaan_bahan_baku_detail_id', $details->pluck('id'))
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->where('produsen_id', $produsen->id))
            ->selectRaw('permintaan_bahan_baku_detail_id, SUM(jumlah_diterima) as total')
            ->groupBy('permintaan_bahan_baku_detail_id')
            ->pluck('total', 'permintaan_bahan_baku_detail_id');

        $items = $details->map(function ($d) use ($bahans, $diterima) {
            $b = $bahans[$d->bahan_baku_id] ?? null;
            $harga = (float) $d->harga_satuan;
            $terima = round((float) ($diterima[$d->id] ?? 0), 2);

            return [
                'kode' => $b->kode_bahan_baku ?? '-',
                'nama' => $b->nama_bahan_baku ?? '-',
                'satuan' => $b?->satuan?->kode_satuan ?: ($b?->satuan?->nama ?? ''),
                'harga' => $harga,
                'diminta' => (float) $d->jumlah_diminta,
                'disetujui' => $d->jumlah_disetujui !== null ? (float) $d->jumlah_disetujui : null,
                'diterima' => $terima,
                'status' => $d->status_produsen,
                'subtotal' => round($terima * $harga, 2),
            ];
        });

        // ── Rincian tagihan per nota penerimaan (jumlah diterima x harga satuan) ──
        $rincian = $this->rincianPembayaran($pembayaran, $produsen);

        // ── Gambar receipt (diunggah admin per penerimaan) yang terkait permintaan ini ──
        $receipts = PenerimaanBahanBaku::query()
            ->where('produsen_id', $produsen->id)
            ->whereNotNull('foto_receipt')
            ->whereHas('penerimaanBahanBakuDetails.permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $permintaanId))
            ->orderBy('tanggal_terima')
            ->get(['id', 'nomor_transaksi', 'tanggal_terima', 'foto_receipt']);

        return view('pages.pembayaran-produsen.show', compact('pembayaran', 'items', 'rincian', 'receipts'));
    }

    /**
     * Laporan cetak Pembayaran Saya per periode, memakai filter yang sama dengan tombol "Tampilkan"
     * (jenis laporan + periode + status bayar + status ACC + metode). Lihat periodeFilter().
     *
     * Periode memakai tanggal bayar; tagihan yang belum dibayar memakai tanggal tagihan dibuat
     * (sama dengan halaman daftar). Diurutkan dari tanggal terlama.
     */
    public function cetak(Request $request): View
    {
        $produsen = $this->produsenLogin();

        [$statusBayar, $statusAcc, $metodeBayar] = $this->filterStatus($request);
        [$dari, $sampai, $periode] = $this->periodeFilter($request);

        $pembayarans = $this->queryFilter($produsen, $statusBayar, $statusAcc, $metodeBayar, $dari, $sampai)
            ->with('permintaanBahanBaku:id,nomor_transaksi,tanggal_permintaan')
            ->orderByRaw('COALESCE(tanggal_bayar, DATE(created_at))')
            ->orderBy('id')
            ->get();

        // A. Rincian: satu baris per pembayaran.
        $rows = $pembayarans->map(function ($p) {
            // Lunas > ditolak > belum dibayar (konsisten dengan stat card di halaman daftar).
            $status = $p->status_bayar === 'lunas' ? 'lunas' : ($p->status_acc === 'ditolak' ? 'ditolak' : 'belum');

            return [
                'permintaan' => $p->permintaanBahanBaku->nomor_transaksi ?? '-',
                'tgl_permintaan' => $p->permintaanBahanBaku?->tanggal_permintaan
                    ? Carbon::parse($p->permintaanBahanBaku->tanggal_permintaan)
                    : null,
                'tgl_bayar' => $p->tanggal_bayar,
                'metode' => $p->metode_bayar ?: '-',
                'status' => $status,
                'acc' => ucfirst($p->status_acc),
                'jumlah' => (float) $p->jumlah_bayar,
            ];
        });

        // B. Rekapitulasi per status. Yang ditolak ditampilkan tetapi tidak ikut total tagihan.
        $rekap = collect([
            'lunas' => 'Lunas',
            'belum' => 'Belum dibayar',
            'ditolak' => 'Ditolak (tidak ditagihkan)',
        ])->map(fn ($label, $key) => [
            'key' => $key,
            'label' => $label,
            'jumlah_transaksi' => $rows->where('status', $key)->count(),
            'total' => round((float) $rows->where('status', $key)->sum('jumlah'), 2),
        ])->values();

        $totalTagihan = round((float) $rows->whereIn('status', ['lunas', 'belum'])->sum('jumlah'), 2);

        $filters = array_values(array_filter([
            $statusBayar ? 'Status bayar: ' . ($statusBayar === 'lunas' ? 'Lunas' : 'Belum dibayar') : null,
            $statusAcc ? 'Status ACC: ' . ucfirst($statusAcc) : null,
            $metodeBayar ? 'Metode: ' . $metodeBayar : null,
        ]));

        return view('pages.pembayaran-produsen.cetak', $this->dataKop($produsen) + [
            'mode' => 'daftar',
            'judul' => 'LAPORAN PEMBAYARAN',
            'subjudul' => 'Periode: ' . $periode,
            'filters' => $filters,
            'rows' => $rows,
            'rekap' => $rekap,
            'totalTagihan' => $totalTagihan,
        ]);
    }

    /**
     * Cetak rincian satu pembayaran (tombol Cetak di daftar & halaman detail).
     */
    public function cetakTransaksi(Pembayaran $pembayaran): View
    {
        $produsen = $this->produsenLogin();

        // Produsen hanya boleh mencetak pembayaran miliknya sendiri.
        abort_unless((int) $pembayaran->produsen_id === (int) $produsen->id, 403);

        $pembayaran->load('permintaanBahanBaku:id,nomor_transaksi,tanggal_permintaan,keterangan');

        $nomor = $pembayaran->permintaanBahanBaku->nomor_transaksi ?? '-';

        return view('pages.pembayaran-produsen.cetak', $this->dataKop($produsen) + [
            'mode' => 'detail',
            'judul' => 'RINCIAN PEMBAYARAN',
            'subjudul' => 'No. Permintaan: ' . $nomor,
            'pembayaran' => $pembayaran,
            'tglPermintaan' => $pembayaran->permintaanBahanBaku?->tanggal_permintaan
                ? Carbon::parse($pembayaran->permintaanBahanBaku->tanggal_permintaan)
                : null,
            'rincian' => $this->rincianPembayaran($pembayaran, $produsen),
        ]);
    }

    // =========================================================================

    private function produsenLogin(): Produsen
    {
        $produsen = auth()->user()?->produsen;

        abort_unless($produsen, 403, 'Akun ini tidak terhubung dengan data produsen.');

        return $produsen;
    }

    /**
     * Rincian tagihan per nota penerimaan (jumlah diterima x harga satuan) untuk satu pembayaran.
     * Dipakai halaman detail dan cetak rincian.
     */
    private function rincianPembayaran(Pembayaran $pembayaran, Produsen $produsen): Collection
    {
        $permintaanId = (int) $pembayaran->permintaan_bahan_baku_id;

        return PenerimaanBahanBakuDetail::query()
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->where('produsen_id', $produsen->id))
            ->whereHas('permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $permintaanId))
            ->with([
                'penerimaanBahanBaku:id,nomor_transaksi,tanggal_terima',
                'permintaanBahanBakuDetail:id,harga_satuan',
                'bahanBaku.satuan',
            ])
            ->get()
            ->sortBy(fn ($d) => $d->penerimaanBahanBaku->tanggal_terima->format('Ymd') . str_pad((string) $d->id, 10, '0', STR_PAD_LEFT))
            ->map(function ($d) {
                $harga = (float) $d->permintaanBahanBakuDetail->harga_satuan;
                $jumlah = (float) $d->jumlah_diterima;

                return [
                    'nota' => $d->penerimaanBahanBaku->nomor_transaksi,
                    'tanggal' => $d->penerimaanBahanBaku->tanggal_terima,
                    'nama' => $d->bahanBaku->nama_bahan_baku,
                    'satuan' => $d->bahanBaku->satuan->kode_satuan ?: ($d->bahanBaku->satuan->nama ?? ''),
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => round($jumlah * $harga, 2),
                ];
            })
            ->values();
    }

    /**
     * Query dasar pembayaran milik produsen + filter (dipakai index dan cetak).
     * Rentang tanggal memakai tanggal bayar; tagihan yang belum dibayar memakai tanggal tagihan dibuat.
     */
    private function queryFilter(Produsen $produsen, ?string $statusBayar, ?string $statusAcc, ?string $metodeBayar, ?string $dari, ?string $sampai): Builder
    {
        return Pembayaran::query()
            ->where('produsen_id', $produsen->id)
            ->when($statusBayar, fn ($q, $v) => $q->where('status_bayar', $v))
            ->when($statusAcc, fn ($q, $v) => $q->where('status_acc', $v))
            ->when($metodeBayar, fn ($q, $v) => $q->where('metode_bayar', $v))
            ->when($dari, fn ($q, $v) => $q->whereRaw('COALESCE(tanggal_bayar, DATE(created_at)) >= ?', [$v]))
            ->when($sampai, fn ($q, $v) => $q->whereRaw('COALESCE(tanggal_bayar, DATE(created_at)) <= ?', [$v]));
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string} [status_bayar, status_acc, metode_bayar]
     */
    private function filterStatus(Request $request): array
    {
        return [
            in_array($request->input('status_bayar'), self::STATUS_BAYAR, true) ? $request->input('status_bayar') : null,
            in_array($request->input('status_acc'), self::STATUS_ACC, true) ? $request->input('status_acc') : null,
            in_array($request->input('metode_bayar'), self::METODE_BAYAR, true) ? $request->input('metode_bayar') : null,
        ];
    }

    /**
     * Data kop & identitas laporan cetak (nama/alamat/kontak/logo toko, produsen, pencetak).
     */
    private function dataKop(Produsen $produsen): array
    {
        $toko = DB::table('website_settings')->first();
        $logoUrl = $toko && $toko->logo && Storage::disk('public')->exists($toko->logo)
            ? asset('storage/' . $toko->logo)
            : null;

        return [
            'produsen' => $produsen,
            'toko' => $toko,
            'logoUrl' => $logoUrl,
            'pencetak' => auth()->user()->username,
            'tanggalCetak' => now(),
            'kota' => self::KOTA_CETAK,
        ];
    }

    /**
     * Tentukan batas tanggal & label periode dari "Jenis laporan" (dipakai index dan cetak).
     *
     * jenis = harian (tanggal) | bulanan (bulan, format YYYY-MM) | tahunan (tahun)
     *       | rentang (dari + sampai) | semua / kosong (tanpa batas tanggal).
     *
     * @return array{0: ?string, 1: ?string, 2: string} [dari, sampai, label]
     */
    private function periodeFilter(Request $request): array
    {
        switch ((string) $request->input('jenis')) {
            case 'harian':
                $tgl = $this->tanggalValid($request->input('tanggal')) ?? now()->toDateString();

                return [$tgl, $tgl, $this->tglIndo($tgl)];

            case 'bulanan':
                $bulan = (string) $request->input('bulan');
                $awal = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)
                    ? Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfMonth()
                    : now()->startOfMonth();

                return [
                    $awal->toDateString(),
                    $awal->copy()->endOfMonth()->toDateString(),
                    $awal->copy()->locale('id')->translatedFormat('F Y'),
                ];

            case 'tahunan':
                $tahun = (int) $request->input('tahun');
                if ($tahun < 2000 || $tahun > 2100) {
                    $tahun = now()->year;
                }

                return [$tahun . '-01-01', $tahun . '-12-31', 'Tahun ' . $tahun];

            default: // rentang, semua, atau tanpa jenis
                $dari = $this->tanggalValid($request->input('dari'));
                $sampai = $this->tanggalValid($request->input('sampai'));

                // Tanggal terbalik? Tukar saja supaya hasilnya tidak kosong.
                if ($dari && $sampai && $dari > $sampai) {
                    [$dari, $sampai] = [$sampai, $dari];
                }

                return [$dari, $sampai, $this->labelPeriode($dari, $sampai)];
        }
    }

    private function tglIndo(string $tanggal): string
    {
        return Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y');
    }

    private function labelPeriode(?string $dari, ?string $sampai): string
    {
        $fmt = fn (string $t) => $this->tglIndo($t);

        return match (true) {
            $dari !== null && $sampai !== null => $fmt($dari) . ' s/d ' . $fmt($sampai),
            $dari !== null => 'Mulai ' . $fmt($dari),
            $sampai !== null => 'Sampai dengan ' . $fmt($sampai),
            default => 'Semua periode',
        };
    }

    private function tanggalValid(?string $tanggal): ?string
    {
        if (! $tanggal || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            return null;
        }

        // Tolak tanggal yang formatnya benar tetapi tidak ada (mis. 2026-02-31).
        try {
            return Carbon::createFromFormat('Y-m-d', $tanggal)->format('Y-m-d') === $tanggal ? $tanggal : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
