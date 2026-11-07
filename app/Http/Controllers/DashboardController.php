<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\PemakaianBahanBaku;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\PengalihanBahanBaku;
use App\Models\PermintaanBahanBaku;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /** Batas hari untuk peringatan batch mendekati kedaluwarsa. */
    private const BATAS_HARI_KADALUARSA = 30;

    public function admin(Request $request)
    {
        $today = Carbon::today();

        // ── Kartu statistik ──────────────────────────────────────────────
        $totalBahanBaku = BahanBaku::count();
        $bahanBakuAktif = BahanBaku::where('is_aktif', true)->count();

        $bahanBakuKritis = BahanBaku::where('is_aktif', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->count();

        $produsenAktif = Produsen::where('status_mitra', 'aktif')->count();

        $permintaanBulanIni = PermintaanBahanBaku::whereBetween('tanggal_permintaan', [
            $today->copy()->startOfMonth(),
            $today->copy()->endOfMonth(),
        ])->count();

        $pengalihanPending = PengalihanBahanBaku::where('status_produsen_pengganti', 'pending')->count();

        $tagihanBelumLunas = (float) Pembayaran::where('status_bayar', 'belum')->sum('jumlah_bayar');
        $jumlahTagihanBelumLunas = Pembayaran::where('status_bayar', 'belum')->count();

        // ── Stok kritis (paling tipis di atas) ───────────────────────────
        $stokKritis = BahanBaku::with(['satuan', 'kategoriBarang'])
            ->where('is_aktif', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum')
            ->orderByRaw('(stok_saat_ini - stok_minimum) asc')
            ->limit(6)
            ->get();

        // ── Batch mendekati / sudah kedaluwarsa (FEFO) ───────────────────
        $batasKadaluarsa = $today->copy()->addDays(self::BATAS_HARI_KADALUARSA);

        $batchQuery = PenerimaanBahanBakuDetail::where('jumlah_tersisa', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', $batasKadaluarsa);

        $totalBatchKadaluarsa = (clone $batchQuery)->count();

        $batchKadaluarsa = $batchQuery
            ->with(['bahanBaku.satuan', 'penerimaanBahanBaku.produsen'])
            ->orderBy('tanggal_kadaluarsa')
            ->limit(6)
            ->get()
            ->each(function ($batch) use ($today) {
                // negatif = sudah lewat
                $batch->sisa_hari = (int) $today->diffInDays($batch->tanggal_kadaluarsa, false);
            });

        // ── Permintaan terbaru ───────────────────────────────────────────
        $permintaanTerbaru = PermintaanBahanBaku::with([
            'permintaanBahanBakuDetails.produsen',
            'permintaanBahanBakuDetails.bahanBaku',
        ])
            ->latest('tanggal_permintaan')
            ->latest('id')
            ->limit(6)
            ->get()
            ->each(function ($permintaan) {
                $details = $permintaan->permintaanBahanBakuDetails;

                // Nilai memakai jumlah disetujui bila produsen sudah merespons
                $permintaan->total_nilai = $details->sum(
                    fn ($d) => (float) $d->harga_satuan * (float) ($d->jumlah_disetujui ?? $d->jumlah_diminta)
                );
                $permintaan->daftar_produsen = $details->pluck('produsen.nama_produsen')->filter()->unique()->values();
                [$permintaan->status_label, $permintaan->status_kelas] = $this->ringkasStatus($details);
            });

        // ── Pengalihan yang belum selesai (pengganti belum ditentukan / belum direspon) ──
        $pengalihanMenunggu = PengalihanBahanBaku::with([
            'permintaanBahanBakuDetail.permintaanBahanBaku',
            'permintaanBahanBakuDetail.produsen',
            'bahanBakuAsal.satuan',
            'bahanBakuPengganti',
            'produsenPengganti',
        ])
            ->where('status_produsen_pengganti', 'pending')
            ->latest('tanggal')
            ->latest('id')
            ->limit(6)
            ->get();

        return view('pages.admin.dashboard', [
            'namaAdmin'               => auth()->user()->admin->nama ?? auth()->user()->username,
            'totalBahanBaku'          => $totalBahanBaku,
            'bahanBakuAktif'          => $bahanBakuAktif,
            'bahanBakuKritis'         => $bahanBakuKritis,
            'produsenAktif'           => $produsenAktif,
            'permintaanBulanIni'      => $permintaanBulanIni,
            'pengalihanPending'       => $pengalihanPending,
            'tagihanBelumLunas'       => $tagihanBelumLunas,
            'jumlahTagihanBelumLunas' => $jumlahTagihanBelumLunas,
            'stokKritis'              => $stokKritis,
            'batchKadaluarsa'         => $batchKadaluarsa,
            'totalBatchKadaluarsa'    => $totalBatchKadaluarsa,
            'batasHariKadaluarsa'     => self::BATAS_HARI_KADALUARSA,
            'permintaanTerbaru'       => $permintaanTerbaru,
            'pengalihanMenunggu'      => $pengalihanMenunggu,
        ]);
    }

    public function pemilik(Request $request)
    {
        $today = Carbon::today();

        $awalBulan      = $today->copy()->startOfMonth();
        $akhirBulan     = $today->copy()->endOfMonth();
        $awalBulanLalu  = $today->copy()->subMonthNoOverflow()->startOfMonth();
        $akhirBulanLalu = $today->copy()->subMonthNoOverflow()->endOfMonth();

        // Pengeluaran = pembayaran yang sudah lunas, dihitung per tanggal bayar
        $pengeluaran = fn (Carbon $dari, Carbon $sampai) => (float) Pembayaran::where('status_bayar', 'lunas')
            ->whereBetween('tanggal_bayar', [$dari->toDateString(), $sampai->toDateString()])
            ->sum('jumlah_bayar');

        // ── Kartu statistik: pengeluaran ─────────────────────────────────
        $pengeluaranBulanIni   = $pengeluaran($awalBulan, $akhirBulan);
        $pengeluaranBulanLalu  = $pengeluaran($awalBulanLalu, $akhirBulanLalu);
        $pengeluaranTahunIni   = $pengeluaran($today->copy()->startOfYear(), $today->copy()->endOfYear());
        $persenDariBulanLalu   = $pengeluaranBulanLalu > 0
            ? (($pengeluaranBulanIni - $pengeluaranBulanLalu) / $pengeluaranBulanLalu) * 100
            : null;

        // ── Grafik pengeluaran 12 bulan terakhir ─────────────────────────
        $awalGrafik = $awalBulan->copy()->subMonthsNoOverflow(11);

        $totalPerBulan = Pembayaran::where('status_bayar', 'lunas')
            ->whereNotNull('tanggal_bayar')
            ->whereBetween('tanggal_bayar', [$awalGrafik->toDateString(), $akhirBulan->toDateString()])
            ->get(['jumlah_bayar', 'tanggal_bayar'])
            ->groupBy(fn ($p) => $p->tanggal_bayar->format('Y-m'))
            ->map(fn ($grup) => $grup->sum(fn ($p) => (float) $p->jumlah_bayar));

        $grafikBulanan = collect(range(0, 11))->map(function ($i) use ($awalGrafik, $awalBulan, $totalPerBulan) {
            $bulan = $awalGrafik->copy()->addMonthsNoOverflow($i);

            return [
                'label'   => $bulan->locale('id')->translatedFormat('M'),
                'lengkap' => $bulan->locale('id')->translatedFormat('F Y'),
                'total'   => (float) ($totalPerBulan[$bulan->format('Y-m')] ?? 0),
                'bulanIni' => $bulan->isSameMonth($awalBulan),
            ];
        })->values();

        $totalGrafik = $grafikBulanan->sum('total');

        // ── Tagihan ──────────────────────────────────────────────────────
        $jumlahMenungguAcc = Pembayaran::where('status_acc', 'menunggu')->count();
        $nilaiMenungguAcc  = (float) Pembayaran::where('status_acc', 'menunggu')->sum('jumlah_bayar');

        // Belum lunas = belum dibayar dan tidak ditolak (tagihan ditolak bukan utang)
        $belumLunas = fn () => Pembayaran::where('status_bayar', 'belum')->where('status_acc', '!=', 'ditolak');
        $jumlahBelumLunas = $belumLunas()->count();
        $nilaiBelumLunas  = (float) $belumLunas()->sum('jumlah_bayar');

        // Tagihan yang butuh perhatian: menunggu persetujuan dulu, lalu disetujui tapi belum dibayar
        $tagihanPerhatian = Pembayaran::with(['permintaanBahanBaku', 'produsen'])
            ->where(function ($q) {
                $q->where('status_acc', 'menunggu')
                    ->orWhere(fn ($q2) => $q2->where('status_acc', 'disetujui')->where('status_bayar', 'belum'));
            })
            ->orderByRaw("status_acc = 'menunggu' desc")
            ->latest('id')
            ->limit(6)
            ->get();

        // ── Stok kritis ──────────────────────────────────────────────────
        $bahanBakuAktif = BahanBaku::where('is_aktif', true)->count();

        $stokKritisQuery = fn () => BahanBaku::where('is_aktif', true)
            ->whereColumn('stok_saat_ini', '<=', 'stok_minimum');

        $jumlahStokKritis = $stokKritisQuery()->count();

        $stokKritis = $stokKritisQuery()
            ->with(['satuan', 'kategoriBarang'])
            ->orderByRaw('(stok_saat_ini - stok_minimum) asc')
            ->limit(6)
            ->get();

        // ── Batch mendekati / sudah kedaluwarsa ──────────────────────────
        $batasKadaluarsa = $today->copy()->addDays(self::BATAS_HARI_KADALUARSA);

        $batchQuery = PenerimaanBahanBakuDetail::where('jumlah_tersisa', '>', 0)
            ->whereNotNull('tanggal_kadaluarsa')
            ->whereDate('tanggal_kadaluarsa', '<=', $batasKadaluarsa);

        $totalBatchKadaluarsa = (clone $batchQuery)->count();

        $batchKadaluarsa = $batchQuery
            ->with(['bahanBaku.satuan', 'penerimaanBahanBaku.produsen'])
            ->orderBy('tanggal_kadaluarsa')
            ->limit(6)
            ->get()
            ->each(function ($batch) use ($today) {
                // negatif = sudah lewat
                $batch->sisa_hari = (int) $today->diffInDays($batch->tanggal_kadaluarsa, false);
            });

        // ── Produsen dengan nilai transaksi terbesar (12 bulan terakhir) ─
        $produsenTeratas = Pembayaran::selectRaw('produsen_id, SUM(jumlah_bayar) as total_nilai, COUNT(*) as jumlah_transaksi')
            ->where('status_acc', '!=', 'ditolak')
            ->where('created_at', '>=', $awalGrafik)
            ->groupBy('produsen_id')
            ->orderByDesc('total_nilai')
            ->limit(5)
            ->with('produsen')
            ->get();

        $nilaiProdusenTerbesar = (float) ($produsenTeratas->first()->total_nilai ?? 0);

        return view('pages.pemilik.dashboard', [
            'namaPemilik'           => auth()->user()->username,
            'pengeluaranBulanIni'   => $pengeluaranBulanIni,
            'pengeluaranBulanLalu'  => $pengeluaranBulanLalu,
            'pengeluaranTahunIni'   => $pengeluaranTahunIni,
            'persenDariBulanLalu'   => $persenDariBulanLalu,
            'grafikBulanan'         => $grafikBulanan,
            'totalGrafik'           => $totalGrafik,
            'jumlahMenungguAcc'     => $jumlahMenungguAcc,
            'nilaiMenungguAcc'      => $nilaiMenungguAcc,
            'jumlahBelumLunas'      => $jumlahBelumLunas,
            'nilaiBelumLunas'       => $nilaiBelumLunas,
            'tagihanPerhatian'      => $tagihanPerhatian,
            'bahanBakuAktif'        => $bahanBakuAktif,
            'jumlahStokKritis'      => $jumlahStokKritis,
            'stokKritis'            => $stokKritis,
            'batchKadaluarsa'       => $batchKadaluarsa,
            'totalBatchKadaluarsa'  => $totalBatchKadaluarsa,
            'batasHariKadaluarsa'   => self::BATAS_HARI_KADALUARSA,
            'produsenTeratas'       => $produsenTeratas,
            'nilaiProdusenTerbesar' => $nilaiProdusenTerbesar,
            'aktivitasAdmin'        => $this->aktivitasAdminTerbaru(8),
        ]);
    }

    public function produsen(Request $request)
    {
        $produsen = auth()->user()->produsen;

        abort_if(! $produsen, 403, 'Akun ini belum terhubung ke data produsen.');

        $id = $produsen->id;
        $today = Carbon::today();

        // Filter: item permintaan milik produsen ini yang belum direspon
        $itemPending = fn ($q) => $q->where('produsen_id', $id)->where('status_produsen', 'pending');

        // ── Kartu statistik ──────────────────────────────────────────────
        $jumlahBahanBaku = $produsen->bahanBakuProdusens()->count();

        $jumlahPermintaanMenunggu = PermintaanBahanBaku::whereHas('permintaanBahanBakuDetails', $itemPending)->count();

        $jumlahPermintaanTambahan = PengalihanBahanBaku::where('produsen_pengganti_id', $id)
            ->where('status_produsen_pengganti', 'pending')
            ->count();

        $belumDibayar = (float) Pembayaran::where('produsen_id', $id)->where('status_bayar', 'belum')->sum('jumlah_bayar');
        $sudahDibayar = (float) Pembayaran::where('produsen_id', $id)->where('status_bayar', 'lunas')->sum('jumlah_bayar');

        // ── Permintaan masuk yang perlu direspon ─────────────────────────
        $permintaanMasuk = PermintaanBahanBaku::with([
            'permintaanBahanBakuDetails' => fn ($q) => $q->where('produsen_id', $id)->with('bahanBaku.satuan'),
        ])
            ->whereHas('permintaanBahanBakuDetails', $itemPending)
            ->latest('tanggal_permintaan')
            ->latest('id')
            ->limit(6)
            ->get()
            ->each(function ($permintaan) {
                $details = $permintaan->permintaanBahanBakuDetails;

                $permintaan->total_item  = $details->count();
                $permintaan->total_nilai = $details->sum(fn ($d) => (float) $d->harga_satuan * (float) $d->jumlah_diminta);
                $permintaan->nama_bahan  = $details->pluck('bahanBaku.nama_bahan_baku')->filter()->values();
            });

        // ── Permintaan tambahan (produsen ini ditunjuk sebagai pengganti) ─
        $permintaanTambahan = PengalihanBahanBaku::with([
            'permintaanBahanBakuDetail.permintaanBahanBaku',
            'bahanBakuAsal.satuan',
            'bahanBakuPengganti.satuan',
        ])
            ->where('produsen_pengganti_id', $id)
            ->where('status_produsen_pengganti', 'pending')
            ->latest('tanggal')
            ->latest('id')
            ->limit(6)
            ->get();

        // ── Item disetujui yang belum dikirim penuh ──────────────────────
        $itemDisetujui = PermintaanBahanBakuDetail::with(['permintaanBahanBaku', 'bahanBaku.satuan'])
            ->withSum('penerimaanBahanBakuDetails as total_diterima', 'jumlah_diterima')
            ->where('produsen_id', $id)
            ->whereIn('status_produsen', ['disetujui', 'disetujui_sebagian'])
            ->latest('id')
            ->get()
            ->each(function ($d) {
                $disetujui = (float) ($d->jumlah_disetujui ?? $d->jumlah_diminta);
                $d->sisa_kirim = max(0, $disetujui - (float) $d->total_diterima);
                $d->jumlah_final = $disetujui;
            })
            ->filter(fn ($d) => $d->sisa_kirim > 0)
            ->values();

        $jumlahMenungguPengiriman = $itemDisetujui->count();
        $menungguPengiriman = $itemDisetujui->take(6);

        // ── Pembayaran terbaru ───────────────────────────────────────────
        $pembayaranTerbaru = Pembayaran::with('permintaanBahanBaku')
            ->where('produsen_id', $id)
            ->latest('id')
            ->limit(6)
            ->get();

        // ── Bahan baku yang disediakan beserta harga ─────────────────────
        $bahanBakuSaya = $produsen->bahanBakus()
            ->with('satuan')
            ->orderBy('nama_bahan_baku')
            ->limit(6)
            ->get();

        return view('pages.produsen.dashboard', compact(
            'produsen',
            'jumlahBahanBaku',
            'jumlahPermintaanMenunggu',
            'jumlahPermintaanTambahan',
            'jumlahMenungguPengiriman',
            'belumDibayar',
            'sudahDibayar',
            'permintaanMasuk',
            'permintaanTambahan',
            'menungguPengiriman',
            'pembayaranTerbaru',
            'bahanBakuSaya'
        ));
    }

    /**
     * Gabungkan aktivitas admin terbaru dari permintaan, penerimaan, dan pemakaian
     * bahan baku menjadi satu linimasa, terbaru di atas.
     *
     * @return Collection<int, array{waktu: Carbon, ikon: string, warna: string, judul: string, nomor: ?string, admin: string, catatan: ?string}>
     */
    private function aktivitasAdminTerbaru(int $batas = 8): Collection
    {
        $permintaan = PermintaanBahanBaku::with('admin')
            ->latest()->limit($batas)->get()
            ->map(fn ($p) => [
                'waktu'   => $p->created_at,
                'ikon'    => 'fa-file-alt',
                'warna'   => 'blue',
                'judul'   => 'Membuat permintaan bahan baku',
                'nomor'   => $p->nomor_transaksi,
                'admin'   => $p->admin->nama ?? '-',
                'catatan' => null,
            ]);

        $penerimaan = PenerimaanBahanBaku::with(['admin', 'produsen'])
            ->latest()->limit($batas)->get()
            ->map(fn ($p) => [
                'waktu'   => $p->created_at,
                'ikon'    => 'fa-truck-loading',
                'warna'   => 'green',
                'judul'   => 'Mencatat penerimaan bahan baku',
                'nomor'   => $p->nomor_transaksi,
                'admin'   => $p->admin->nama ?? '-',
                'catatan' => $p->produsen ? 'dari ' . $p->produsen->nama_produsen : null,
            ]);

        $pemakaian = PemakaianBahanBaku::with('admin')
            ->latest()->limit($batas)->get()
            ->map(fn ($p) => [
                'waktu'   => $p->created_at,
                'ikon'    => 'fa-utensils',
                'warna'   => 'orange',
                'judul'   => 'Mencatat pemakaian bahan baku',
                'nomor'   => $p->nomor_transaksi,
                'admin'   => $p->admin->nama ?? '-',
                'catatan' => $p->keterangan,
            ]);

        return $permintaan->concat($penerimaan)->concat($pemakaian)
            ->filter(fn ($a) => $a['waktu'] !== null)
            ->sortByDesc('waktu')
            ->take($batas)
            ->values();
    }

    /**
     * Ringkas status beberapa baris detail permintaan menjadi satu label.
     *
     * @return array{0: string, 1: string} [label, kelas css]
     */
    private function ringkasStatus(Collection $details): array
    {
        $status = $details->pluck('status_produsen');

        if ($status->isEmpty()) {
            return ['-', 'pending'];
        }
        if ($status->contains('pending')) {
            return ['Menunggu Produsen', 'pending'];
        }
        if ($status->every(fn ($s) => $s === 'ditolak')) {
            return ['Ditolak', 'ditolak'];
        }
        if ($status->contains('disetujui_sebagian') || $status->contains('ditolak')) {
            return ['Disetujui Sebagian', 'sebagian'];
        }

        return ['Disetujui', 'disetujui'];
    }
}
