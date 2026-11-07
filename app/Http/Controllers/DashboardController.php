<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\PengalihanBahanBaku;
use App\Models\PermintaanBahanBaku;
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
        return view('pages.produsen.dashboard');
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
