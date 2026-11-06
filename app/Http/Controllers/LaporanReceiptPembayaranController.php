<?php

namespace App\Http\Controllers;

use App\Models\BahanBakuProdusen;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\Produsen;
use App\Models\WebsiteSetting;
use App\Support\Terbilang;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan > Receipt Pembayaran (admin & pemilik).
 * Hanya pembayaran berstatus LUNAS yang punya receipt.
 */
class LaporanReceiptPembayaranController extends Controller
{
    private const MAKS_CETAK = 50;

    /** Daftar receipt + filter + pilihan cetak. */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $query = Pembayaran::query()
            ->where('status_bayar', 'lunas')
            ->with(['produsen', 'permintaanBahanBaku']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('permintaanBahanBaku', fn ($p) => $p->where('nomor_transaksi', 'like', "%{$search}%"))
                    ->orWhereHas('produsen', fn ($p) => $p->where('nama_produsen', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('produsen_id')) {
            $query->where('produsen_id', $request->input('produsen_id'));
        }
        if ($request->filled('metode_bayar')) {
            $query->where('metode_bayar', $request->input('metode_bayar'));
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_bayar', '>=', $request->input('dari'));
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->input('sampai'));
        }

        $totalReceipt = (clone $query)->count();
        $totalNominal = (clone $query)->sum('jumlah_bayar');

        $pembayarans = $query
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('laporan.receipt-pembayaran.index', [
            'pembayarans' => $pembayarans,
            'nomorMap' => $this->nomorMap($pembayarans->getCollection()),
            'produsens' => Produsen::orderBy('nama_produsen')->get(['id', 'nama_produsen']),
            'metodes' => Pembayaran::where('status_bayar', 'lunas')
                ->whereNotNull('metode_bayar')->distinct()->orderBy('metode_bayar')->pluck('metode_bayar'),
            'totalReceipt' => $totalReceipt,
            'totalNominal' => $totalNominal,
            'receiptBulanIni' => Pembayaran::where('status_bayar', 'lunas')
                ->whereYear('tanggal_bayar', now()->year)
                ->whereMonth('tanggal_bayar', now()->month)
                ->count(),
            'maksCetak' => self::MAKS_CETAK,
        ]);
    }

    /** Satu receipt. */
    public function show(Request $request, Pembayaran $pembayaran)
    {
        $this->authorizeRole();

        if ($pembayaran->status_bayar !== 'lunas') {
            return redirect()
                ->route('laporan.receipt-pembayaran.index')
                ->with('error', 'Receipt hanya tersedia untuk pembayaran yang sudah lunas.');
        }

        return $this->render(collect([$pembayaran]), $request);
    }

    /** Beberapa receipt sekaligus (?ids[]=1&ids[]=2). */
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $ids = collect($request->input('ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(self::MAKS_CETAK)
            ->values();

        $pembayarans = Pembayaran::query()
            ->where('status_bayar', 'lunas')
            ->whereIn('id', $ids)
            ->orderBy('tanggal_bayar')
            ->orderBy('id')
            ->get();

        if ($pembayarans->isEmpty()) {
            return redirect()
                ->route('laporan.receipt-pembayaran.index')
                ->with('error', 'Pilih minimal satu receipt yang sudah lunas untuk dicetak.');
        }

        return $this->render($pembayarans, $request);
    }

    // ──────────────────────────────────────────────────────────
    //  Internal
    // ──────────────────────────────────────────────────────────

    private function authorizeRole(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'pemilik'], true), 403);
    }

    private function render(Collection $pembayarans, Request $request)
    {
        $pembayarans->load(['produsen', 'pemilik.user', 'permintaanBahanBaku']);
        $nomorMap = $this->nomorMap($pembayarans);

        return view('laporan.receipt-pembayaran.receipt', [
            'setting' => WebsiteSetting::getSetting(),
            'receipts' => $pembayarans->map(fn ($p) => $this->susunReceipt($p, $nomorMap[$p->id] ?? '-'))->values(),
            'autoPrint' => $request->boolean('cetak'),
        ]);
    }

    /** Data satu receipt. */
    private function susunReceipt(Pembayaran $pembayaran, string $nomor): array
    {
        // Seluruh barang yang diterima dari produsen ini untuk permintaan yang dibayar.
        $details = PenerimaanBahanBakuDetail::query()
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->where('produsen_id', $pembayaran->produsen_id))
            ->whereHas('permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $pembayaran->permintaan_bahan_baku_id))
            ->with(['penerimaanBahanBaku.admin.user', 'bahanBaku.satuan'])
            ->get();

        $penerimaans = $details->pluck('penerimaanBahanBaku')
            ->filter()->unique('id')->sortBy('tanggal_terima')->values();

        $hargaMap = $this->hargaMap($pembayaran->produsen_id, $details->pluck('bahan_baku_id')->unique());

        // Gabungkan baris bahan baku yang sama (bisa datang dari beberapa penerimaan).
        $rows = $details->groupBy('bahan_baku_id')->map(function ($group) use ($hargaMap) {
            $bahan = $group->first()->bahanBaku;
            $harga = $this->hargaSatuan($bahan->id, $hargaMap);
            $jumlah = $group->sum(fn ($d) => (float) $d->jumlah_diterima);

            return [
                'nama' => $bahan->nama_bahan_baku,
                'kode' => $bahan->kode_bahan_baku,
                'satuan' => $bahan->satuan->kode_satuan ?: ($bahan->satuan->nama ?? ''),
                'jumlah' => $jumlah,
                'harga' => $harga,
                'subtotal' => $jumlah * $harga,
            ];
        })->values();

        $metode = $pembayaran->metode_bayar;
        if ($metode && strcasecmp($metode, 'Transfer') === 0) {
            $metode = 'Transfer bank';
        }

        $total = (float) $pembayaran->jumlah_bayar;

        return [
            'pembayaran' => $pembayaran,
            'nomor' => $nomor,
            'rows' => $rows,
            'penerimaans' => $penerimaans,
            'tanggalTerima' => $penerimaans
                ->map(fn ($p) => $p->tanggal_terima->translatedFormat('j F Y'))
                ->unique()->implode(', ') ?: '-',
            'metode' => $metode ?: '-',
            'total' => $total,
            'terbilang' => Terbilang::rupiah($total),
            'namaPemilik' => $pembayaran->pemilik?->user?->username,
            'namaAdmin' => $penerimaans->first()?->admin?->user?->username,
        ];
    }

    /**
     * [id pembayaran => nomor bukti], format BYR-ddmmYYYY-urutan (urutan per tanggal bayar).
     */
    private function nomorMap(Collection $pembayarans): array
    {
        $tanggals = $pembayarans
            ->map(fn ($p) => $p->tanggal_bayar->toDateString())
            ->unique()->values();

        if ($tanggals->isEmpty()) {
            return [];
        }

        $map = [];
        $semua = Pembayaran::query()
            ->where('status_bayar', 'lunas')
            ->whereIn(DB::raw('DATE(tanggal_bayar)'), $tanggals->all())
            ->orderBy('id')
            ->get(['id', 'tanggal_bayar']);

        foreach ($semua->groupBy(fn ($p) => $p->tanggal_bayar->toDateString()) as $grup) {
            foreach ($grup->values() as $i => $p) {
                $map[$p->id] = sprintf('BYR-%s-%03d', $p->tanggal_bayar->format('dmY'), $i + 1);
            }
        }

        return $map;
    }

    /** [bahan_baku_id => harga per satuan] dari pivot bahan_baku_produsens. */
    private function hargaMap(int $produsenId, $bahanBakuIds): array
    {
        return BahanBakuProdusen::query()
            ->where('produsen_id', $produsenId)
            ->whereIn('bahan_baku_id', $bahanBakuIds)
            ->pluck('harga', 'bahan_baku_id')
            ->map(fn ($h) => (float) $h)
            ->all();
    }

    /**
     * Satu-satunya tempat harga satuan ditentukan. Jika tagihan di
     * PenerimaanBahanBakuController memakai sumber harga lain, sesuaikan di sini.
     */
    private function hargaSatuan(int $bahanBakuId, array $hargaMap): float
    {
        return $hargaMap[$bahanBakuId] ?? 0.0;
    }
}
