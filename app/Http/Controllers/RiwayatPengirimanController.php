<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Riwayat Pengiriman (role produsen, hanya baca).
 *
 * Menampilkan penerimaan bahan baku (penerimaan_bahan_bakus) yang produsen_id-nya
 * milik produsen yang login. Tidak ada aksi tambah, ubah, atau hapus.
 */
class RiwayatPengirimanController extends Controller
{
    /** Kota yang tampil di atas tanda tangan pada laporan cetak. */
    private const KOTA_CETAK = 'Kaohsiung';

    public function index(Request $request): View
    {
        $produsen = $this->produsenLogin();

        $nomor = $this->nomorInput($request);
        [$dari, $sampai] = $this->periodeFilter($request);

        // Total nilai = SUM(jumlah_diterima x harga_satuan permintaan) per penerimaan.
        $totalNilai = PenerimaanBahanBakuDetail::query()
            ->selectRaw('COALESCE(SUM(penerimaan_bahan_baku_details.jumlah_diterima * pd.harga_satuan), 0)')
            ->join('permintaan_bahan_baku_details as pd', 'pd.id', '=', 'penerimaan_bahan_baku_details.permintaan_bahan_baku_detail_id')
            ->whereColumn('penerimaan_bahan_baku_details.penerimaan_bahan_baku_id', 'penerimaan_bahan_bakus.id');

        $penerimaans = $this->queryFilter($produsen, $nomor, $dari, $sampai)
            ->select('penerimaan_bahan_bakus.*')
            ->addSelect(['total_nilai' => $totalNilai])
            ->withCount('penerimaanBahanBakuDetails')
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.riwayat-pengiriman.index', compact('penerimaans'));
    }

    public function show(PenerimaanBahanBaku $penerimaanBahanBaku): View
    {
        $produsen = $this->produsenLogin();

        // Produsen hanya boleh membuka pengiriman miliknya sendiri.
        abort_unless((int) $penerimaanBahanBaku->produsen_id === (int) $produsen->id, 403);

        $details = $penerimaanBahanBaku->penerimaanBahanBakuDetails()
            ->with([
                'bahanBaku.satuan',
                'permintaanBahanBakuDetail:id,permintaan_bahan_baku_id,harga_satuan',
                'permintaanBahanBakuDetail.permintaanBahanBaku:id,nomor_transaksi',
            ])
            ->orderBy('id')
            ->get();

        $items = $details->map(function ($d) {
            $harga = $d->permintaanBahanBakuDetail ? (float) $d->permintaanBahanBakuDetail->harga_satuan : null;
            $jumlah = (float) $d->jumlah_diterima;

            return [
                'kode' => $d->bahanBaku->kode_bahan_baku,
                'nama' => $d->bahanBaku->nama_bahan_baku,
                'satuan' => $d->bahanBaku->satuan->kode_satuan ?: ($d->bahanBaku->satuan->nama ?? ''),
                'permintaan' => $d->permintaanBahanBakuDetail?->permintaanBahanBaku?->nomor_transaksi,
                'jumlah' => $jumlah,
                'kadaluarsa' => $d->tanggal_kadaluarsa,
                'harga' => $harga,
                'subtotal' => $harga !== null ? round($jumlah * $harga, 2) : 0.0,
            ];
        });

        $totalNilai = round((float) $items->sum('subtotal'), 2);

        return view('pages.riwayat-pengiriman.show', [
            'penerimaan' => $penerimaanBahanBaku,
            'items' => $items,
            'totalNilai' => $totalNilai,
        ]);
    }

    /**
     * Laporan cetak Riwayat Pengiriman, memakai filter yang sama dengan tombol "Tampilkan"
     * (jenis laporan + periode + nomor transaksi). Lihat periodeFilter() untuk daftar jenis.
     * Diurutkan dari tanggal terlama (urutan kronologis laporan).
     */
    public function cetak(Request $request): View
    {
        $produsen = $this->produsenLogin();

        $nomor = $this->nomorInput($request);
        [$dari, $sampai, $periode] = $this->periodeFilter($request);

        $penerimaans = $this->queryFilter($produsen, $nomor, $dari, $sampai)
            ->with($this->relasiCetak())
            ->orderBy('tanggal_terima')
            ->orderBy('id')
            ->get();

        return $this->tampilkanCetak($produsen, $penerimaans, [
            'judul' => 'LAPORAN RIWAYAT PENGIRIMAN',
            'subjudul' => 'Periode: ' . $periode . ($nomor !== '' ? '  |  No. Transaksi: ' . $nomor : ''),
            'tampilRekap' => true,
        ]);
    }

    /**
     * Cetak satu transaksi pengiriman saja (tombol Cetak di daftar & halaman detail).
     */
    public function cetakTransaksi(PenerimaanBahanBaku $penerimaanBahanBaku): View
    {
        $produsen = $this->produsenLogin();

        // Produsen hanya boleh mencetak pengiriman miliknya sendiri.
        abort_unless((int) $penerimaanBahanBaku->produsen_id === (int) $produsen->id, 403);

        $penerimaanBahanBaku->load($this->relasiCetak());

        return $this->tampilkanCetak($produsen, collect([$penerimaanBahanBaku]), [
            'judul' => 'LAPORAN PENGIRIMAN',
            'subjudul' => 'No. Transaksi: ' . $penerimaanBahanBaku->nomor_transaksi
                . '  |  Tanggal Terima: ' . $this->tglIndo($penerimaanBahanBaku->tanggal_terima->toDateString()),
            'tampilRekap' => false,
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
     * Ambil & bersihkan input pencarian nomor transaksi.
     */
    private function nomorInput(Request $request): string
    {
        return trim((string) $request->input('nomor'));
    }

    /**
     * Query dasar penerimaan milik produsen + filter (tanpa select/urutan,
     * supaya bisa dipakai index (paginate) maupun cetak (get)).
     */
    private function queryFilter(Produsen $produsen, string $nomor, ?string $dari, ?string $sampai): Builder
    {
        return PenerimaanBahanBaku::query()
            ->where('produsen_id', $produsen->id)
            ->when($nomor !== '', fn ($q) => $q->where('nomor_transaksi', 'like', '%' . $nomor . '%'))
            ->when($dari, fn ($q, $v) => $q->whereDate('tanggal_terima', '>=', $v))
            ->when($sampai, fn ($q, $v) => $q->whereDate('tanggal_terima', '<=', $v));
    }

    /**
     * Relasi yang dibutuhkan laporan cetak (dipakai cetak & cetakTransaksi).
     */
    private function relasiCetak(): array
    {
        return [
            'penerimaanBahanBakuDetails' => fn ($q) => $q->orderBy('id'),
            'penerimaanBahanBakuDetails.bahanBaku.satuan',
            'penerimaanBahanBakuDetails.permintaanBahanBakuDetail:id,permintaan_bahan_baku_id,harga_satuan',
        ];
    }

    /**
     * Susun data laporan (A. rincian per transaksi, B. rekap per bahan baku) lalu render view cetak.
     *
     * @param  iterable<PenerimaanBahanBaku>  $penerimaans  sudah di-load dengan relasiCetak()
     * @param  array{judul: string, subjudul: string, tampilRekap: bool}  $tampilan
     */
    private function tampilkanCetak(Produsen $produsen, $penerimaans, array $tampilan): View
    {
        // A. Rincian: satu blok per transaksi penerimaan.
        $transaksis = collect($penerimaans)->map(function ($p) {
            $items = $p->penerimaanBahanBakuDetails->map(function ($d) {
                $harga = $d->permintaanBahanBakuDetail ? (float) $d->permintaanBahanBakuDetail->harga_satuan : null;
                $jumlah = (float) $d->jumlah_diterima;
                $satuan = $d->bahanBaku->satuan->kode_satuan ?: ($d->bahanBaku->satuan->nama ?? '');

                return [
                    'bahan_baku_id' => $d->bahan_baku_id,
                    'kode' => $d->bahanBaku->kode_bahan_baku,
                    'nama' => $d->bahanBaku->nama_bahan_baku,
                    'satuan' => Str::lower($satuan),
                    'jumlah' => $jumlah,
                    'kadaluarsa' => $d->tanggal_kadaluarsa,
                    'harga' => $harga,
                    'subtotal' => $harga !== null ? round($jumlah * $harga, 2) : 0.0,
                ];
            });

            return [
                'nomor' => $p->nomor_transaksi,
                'tanggal' => $p->tanggal_terima,
                'catatan' => $p->catatan,
                'items' => $items,
                'total' => round((float) $items->sum('subtotal'), 2),
            ];
        });

        // B. Rekapitulasi per bahan baku (lintas transaksi).
        $rekap = $transaksis
            ->flatMap(fn ($t) => $t['items']->map(fn ($i) => $i + ['nomor' => $t['nomor']]))
            ->groupBy('bahan_baku_id')
            ->map(fn ($rows) => [
                'kode' => $rows->first()['kode'],
                'nama' => $rows->first()['nama'],
                'satuan' => $rows->first()['satuan'],
                'jumlah_transaksi' => $rows->pluck('nomor')->unique()->count(),
                'total_jumlah' => round((float) $rows->sum('jumlah'), 2),
                'total_nilai' => round((float) $rows->sum('subtotal'), 2),
            ])
            ->sortBy('kode')
            ->values();

        // Total jumlah hanya bermakna bila semua bahan baku memakai satuan yang sama.
        $satuanTunggal = $rekap->pluck('satuan')->unique()->count() === 1
            ? $rekap->first()['satuan']
            : null;

        // Pengaturan website (nama, alamat, kontak, logo) untuk kop laporan.
        $toko = DB::table('website_settings')->first();
        $logoUrl = $toko && $toko->logo && Storage::disk('public')->exists($toko->logo)
            ? asset('storage/' . $toko->logo)
            : null;

        return view('pages.riwayat-pengiriman.cetak', $tampilan + [
            'produsen' => $produsen,
            'toko' => $toko,
            'logoUrl' => $logoUrl,
            'transaksis' => $transaksis,
            'rekap' => $rekap,
            'totalNilai' => round((float) $transaksis->sum('total'), 2),
            'totalJumlah' => round((float) $rekap->sum('total_jumlah'), 2),
            'satuanTunggal' => $satuanTunggal,
            'pencetak' => auth()->user()->username,
            'tanggalCetak' => now(),
            'kota' => self::KOTA_CETAK,
        ]);
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
