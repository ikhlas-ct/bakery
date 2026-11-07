<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Illuminate\Http\Request;
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

        $statusBayar = in_array($request->input('status_bayar'), self::STATUS_BAYAR, true) ? $request->input('status_bayar') : null;
        $statusAcc = in_array($request->input('status_acc'), self::STATUS_ACC, true) ? $request->input('status_acc') : null;
        $metodeBayar = in_array($request->input('metode_bayar'), self::METODE_BAYAR, true) ? $request->input('metode_bayar') : null;
        $dari = $this->tanggalValid($request->input('dari'));
        $sampai = $this->tanggalValid($request->input('sampai'));

        $pembayarans = $milikSaya()
            ->with('permintaanBahanBaku:id,nomor_transaksi,tanggal_permintaan')
            ->when($statusBayar, fn ($q, $v) => $q->where('status_bayar', $v))
            ->when($statusAcc, fn ($q, $v) => $q->where('status_acc', $v))
            ->when($metodeBayar, fn ($q, $v) => $q->where('metode_bayar', $v))
            // Rentang tanggal memakai tanggal bayar; tagihan yang belum dibayar memakai tanggal tagihan dibuat.
            ->when($dari, fn ($q, $v) => $q->whereRaw('COALESCE(tanggal_bayar, DATE(created_at)) >= ?', [$v]))
            ->when($sampai, fn ($q, $v) => $q->whereRaw('COALESCE(tanggal_bayar, DATE(created_at)) <= ?', [$v]))
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
        $rincian = PenerimaanBahanBakuDetail::query()
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

        // ── Gambar receipt (diunggah admin per penerimaan) yang terkait permintaan ini ──
        $receipts = PenerimaanBahanBaku::query()
            ->where('produsen_id', $produsen->id)
            ->whereNotNull('foto_receipt')
            ->whereHas('penerimaanBahanBakuDetails.permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $permintaanId))
            ->orderBy('tanggal_terima')
            ->get(['id', 'nomor_transaksi', 'tanggal_terima', 'foto_receipt']);

        return view('pages.pembayaran-produsen.show', compact('pembayaran', 'items', 'rincian', 'receipts'));
    }

    // =========================================================================

    private function produsenLogin(): Produsen
    {
        $produsen = auth()->user()?->produsen;

        abort_unless($produsen, 403, 'Akun ini tidak terhubung dengan data produsen.');

        return $produsen;
    }

    private function tanggalValid(?string $tanggal): ?string
    {
        return $tanggal && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? $tanggal : null;
    }
}
