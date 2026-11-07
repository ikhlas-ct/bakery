<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pembayaran;
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
        return view('pages.pemilik.dashboard');
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
