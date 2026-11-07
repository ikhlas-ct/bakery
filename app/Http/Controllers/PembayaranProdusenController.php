<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBaku;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Illuminate\Http\Request;

/**
 * Halaman "Pembayaran Saya" untuk role produsen (hanya baca).
 * Produsen hanya bisa melihat pembayaran miliknya sendiri.
 */
class PembayaranProdusenController extends Controller
{
    private function produsenLogin(): Produsen
    {
        $produsen = auth()->user()->produsen;

        abort_if(! $produsen, 403, 'Akun ini belum terhubung ke data produsen.');

        return $produsen;
    }

    public function index(Request $request)
    {
        $produsen = $this->produsenLogin();
        $dasar = Pembayaran::where('produsen_id', $produsen->id);

        // ── Kartu statistik (tidak terpengaruh filter) ───────────────────
        $totalBelumDibayar = (float) (clone $dasar)->where('status_bayar', 'belum')->sum('jumlah_bayar');
        $totalLunas        = (float) (clone $dasar)->where('status_bayar', 'lunas')->sum('jumlah_bayar');
        $jumlahMenunggu    = (clone $dasar)->where('status_bayar', 'belum')->where('status_acc', 'menunggu')->count();

        // ── Filter ───────────────────────────────────────────────────────
        $statusBayar = in_array($request->status_bayar, ['belum', 'lunas'], true) ? $request->status_bayar : null;
        $statusAcc   = in_array($request->status_acc, ['menunggu', 'disetujui', 'ditolak'], true) ? $request->status_acc : null;

        $pembayarans = (clone $dasar)
            ->with('permintaanBahanBaku')
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->whereHas('permintaanBahanBaku', fn ($p) => $p->where('nomor_transaksi', 'like', '%' . trim($request->q) . '%'));
            })
            ->when($statusBayar, fn ($query) => $query->where('status_bayar', $statusBayar))
            ->when($statusAcc, fn ($query) => $query->where('status_acc', $statusAcc))
            ->when($request->filled('dari'), fn ($query) => $query->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($query) => $query->whereDate('created_at', '<=', $request->sampai))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.produsen.pembayaran.index', compact(
            'produsen',
            'pembayarans',
            'totalBelumDibayar',
            'totalLunas',
            'jumlahMenunggu'
        ));
    }

    public function show(Pembayaran $pembayaran)
    {
        $produsen = $this->produsenLogin();

        // Produsen tidak boleh membuka pembayaran milik produsen lain
        abort_unless((int) $pembayaran->produsen_id === (int) $produsen->id, 403);

        $pembayaran->load('permintaanBahanBaku');

        // Rincian: item permintaan milik produsen ini × jumlah yang sudah diterima admin
        $items = PermintaanBahanBakuDetail::with('bahanBaku.satuan')
            ->withSum('penerimaanBahanBakuDetails as total_diterima', 'jumlah_diterima')
            ->where('permintaan_bahan_baku_id', $pembayaran->permintaan_bahan_baku_id)
            ->where('produsen_id', $produsen->id)
            ->get()
            ->each(function ($d) {
                $d->total_diterima = (float) $d->total_diterima;
                $d->subtotal = (float) $d->harga_satuan * $d->total_diterima;
            })
            ->filter(fn ($d) => $d->total_diterima > 0)
            ->values();

        $totalRincian = $items->sum('subtotal');

        // Penerimaan (pengiriman) yang menjadi dasar tagihan ini
        $penerimaans = PenerimaanBahanBaku::where('produsen_id', $produsen->id)
            ->whereHas('penerimaanBahanBakuDetails.permintaanBahanBakuDetail', function ($q) use ($pembayaran) {
                $q->where('permintaan_bahan_baku_id', $pembayaran->permintaan_bahan_baku_id);
            })
            ->orderBy('tanggal_terima')
            ->get();

        return view('pages.produsen.pembayaran.show', compact(
            'produsen',
            'pembayaran',
            'items',
            'totalRincian',
            'penerimaans'
        ));
    }
}
