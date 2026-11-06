<?php

namespace App\Http\Controllers;

use App\Models\BahanBakuProdusen;
use App\Models\Pembayaran;
use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\Produsen;
use App\Models\WebsiteSetting;
use App\Support\Terbilang;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Laporan > Receipt Pembayaran (admin & pemilik).
 *
 * Satu receipt = satu PENERIMAAN. Semua tagihan (permintaan) milik penerimaan itu
 * yang sudah LUNAS digabung: barang digabung per bahan baku, total = jumlah semua
 * pembayaran lunas tersebut.
 */
class LaporanReceiptPembayaranController extends Controller
{
    private const PER_HALAMAN = 15;

    /** Daftar receipt + filter. */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $semua = $this->susunGrup(PenerimaanBahanBaku::query()->get())
            ->filter(fn ($g) => $g['lunas']->isNotEmpty())
            ->values();

        $cari = Str::lower(trim((string) $request->input('search')));
        $produsenId = $request->input('produsen_id');
        $metode = $request->input('metode_bayar');
        $dari = $request->input('dari');
        $sampai = $request->input('sampai');

        $grups = $semua->filter(function ($g) use ($cari, $produsenId, $metode, $dari, $sampai) {
            if ($produsenId && (int) $g['penerimaan']->produsen_id !== (int) $produsenId) {
                return false;
            }
            if ($metode && ! $g['lunas']->contains(fn ($p) => $p->metode_bayar === $metode)) {
                return false;
            }
            if ($dari && $g['tanggal']->toDateString() < $dari) {
                return false;
            }
            if ($sampai && $g['tanggal']->toDateString() > $sampai) {
                return false;
            }
            if ($cari !== '') {
                $haystack = Str::lower(implode(' ', array_merge(
                    [$g['nomor'], $g['penerimaan']->nomor_transaksi, $g['produsen']->nama_produsen ?? ''],
                    $g['permintaanNomors']
                )));
                if (! str_contains($haystack, $cari)) {
                    return false;
                }
            }

            return true;
        })->sort(function ($a, $b) {
            return ($b['tanggal']->timestamp <=> $a['tanggal']->timestamp)
                ?: ($b['penerimaan']->id <=> $a['penerimaan']->id);
        })->values();

        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $paginator = new LengthAwarePaginator(
            $grups->forPage($halaman, self::PER_HALAMAN)->values(),
            $grups->count(),
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.laporan.receipt-pembayaran.index', [
            'grups' => $paginator,
            'produsens' => Produsen::orderBy('nama_produsen')->get(['id', 'nama_produsen']),
            'metodes' => $semua->flatMap(fn ($g) => $g['lunas']->pluck('metode_bayar'))
                ->filter()->unique()->sort()->values(),
            'totalReceipt' => $grups->count(),
            'totalNominal' => $grups->sum('total'),
            'receiptBulanIni' => $semua->filter(fn ($g) => $g['tanggal']->isSameMonth(now(), true))->count(),
        ]);
    }

    /** Satu receipt (per penerimaan). */
    public function show(Request $request, PenerimaanBahanBaku $penerimaan)
    {
        $this->authorizeRole();

        $grups = $this->susunGrup(PenerimaanBahanBaku::whereKey($penerimaan->id)->get())
            ->filter(fn ($g) => $g['lunas']->isNotEmpty())
            ->values();

        if ($grups->isEmpty()) {
            return redirect()
                ->route('laporan.receipt-pembayaran.index')
                ->with('error', 'Receipt hanya tersedia untuk penerimaan yang pembayarannya sudah lunas.');
        }

        return $this->render($grups, $request);
    }

    // ──────────────────────────────────────────────────────────
    //  Internal
    // ──────────────────────────────────────────────────────────

    private function authorizeRole(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'pemilik'], true), 403);
    }

    private function render(Collection $grups, Request $request)
    {
        // Pakai pengaturan yang sama dengan layout/sidebar bila sudah dibagikan ke semua view.
        $setting = view()->shared('settings') ?: WebsiteSetting::getSetting();

        return view('pages.laporan.receipt-pembayaran.receipt', [
            'setting' => $setting,
            // Sama seperti sidebar: nama dari Pengaturan Website, cadangan 'Roti Baru Bakery'.
            'namaToko' => filled($setting?->nama) ? $setting->nama : 'Roti Baru Bakery',
            'receipts' => $grups->map(fn ($g) => $this->susunReceipt($g))->values(),
            'autoPrint' => $request->boolean('cetak'),
        ]);
    }

    /**
     * Untuk tiap penerimaan: pembayaran (per permintaan) yang lunas dan yang belum.
     * Pembayaran ditemukan lewat pasangan permintaan + produsen.
     */
    private function susunGrup(EloquentCollection $penerimaans): Collection
    {
        $penerimaans->loadMissing(['produsen', 'penerimaanBahanBakuDetails.permintaanBahanBakuDetail']);

        $idPermintaan = fn ($pen) => $pen->penerimaanBahanBakuDetails
            ->map(fn ($d) => $d->permintaanBahanBakuDetail?->permintaan_bahan_baku_id)
            ->filter()->unique()->values();

        $semuaId = $penerimaans->flatMap($idPermintaan)->unique()->values();

        $pembayarans = Pembayaran::query()
            ->whereIn('permintaan_bahan_baku_id', $semuaId)
            ->with(['permintaanBahanBaku', 'pemilik.user'])
            ->get()
            ->keyBy(fn ($p) => $p->permintaan_bahan_baku_id . '-' . $p->produsen_id);

        return $penerimaans->map(function ($pen) use ($pembayarans, $idPermintaan) {
            $milik = $idPermintaan($pen)
                ->map(fn ($id) => $pembayarans->get($id . '-' . $pen->produsen_id))
                ->filter()->values();

            $lunas = $milik->where('status_bayar', 'lunas')->values();
            $belum = $milik->where('status_bayar', '!=', 'lunas')->values();

            $tanggal = $lunas->pluck('tanggal_bayar')->filter()
                ->sortBy(fn ($d) => $d->timestamp)->last() ?? $pen->tanggal_terima;

            return [
                'penerimaan' => $pen,
                'produsen' => $pen->produsen,
                'nomor' => $this->nomorBukti($pen),
                'lunas' => $lunas,
                'belum' => $belum,
                'total' => (float) $lunas->sum(fn ($p) => (float) $p->jumlah_bayar),
                'tanggal' => $tanggal,
                'permintaanNomors' => $lunas
                    ->map(fn ($p) => $p->permintaanBahanBaku->nomor_transaksi ?? null)
                    ->filter()->values()->all(),
            ];
        });
    }

    /** Data satu receipt (gabungan seluruh pembayaran lunas milik penerimaan). */
    private function susunReceipt(array $g): array
    {
        /** @var PenerimaanBahanBaku $pen */
        $pen = $g['penerimaan'];
        $pen->loadMissing('admin.user');
        $lunas = $g['lunas'];

        // Barang yang diterima dari produsen ini untuk permintaan-permintaan yang sudah dibayar.
        $details = PenerimaanBahanBakuDetail::query()
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->where('produsen_id', $pen->produsen_id))
            ->whereHas('permintaanBahanBakuDetail', fn ($q) => $q->whereIn('permintaan_bahan_baku_id', $lunas->pluck('permintaan_bahan_baku_id')))
            ->with(['penerimaanBahanBaku.admin.user', 'bahanBaku.satuan'])
            ->get();

        $penerimaans = $details->pluck('penerimaanBahanBaku')
            ->filter()->unique('id')->sortBy('tanggal_terima')->values();

        $hargaMap = $this->hargaMap($pen->produsen_id, $details->pluck('bahan_baku_id')->unique());

        // Gabungkan per bahan baku (lintas permintaan / penerimaan).
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

        $metode = $lunas->pluck('metode_bayar')->filter()
            ->map(fn ($m) => strcasecmp($m, 'Transfer') === 0 ? 'Transfer bank' : $m)
            ->unique()->implode(' / ');

        $status = $lunas->pluck('status_acc')->filter()->unique();
        $persetujuan = ($status->count() === 1 && $status->first() === 'disetujui')
            ? 'Disetujui pemilik'
            : $status->map(fn ($s) => ucfirst($s))->implode(' / ');

        $belum = $g['belum']
            ->map(fn ($p) => $p->permintaanBahanBaku->nomor_transaksi ?? null)
            ->filter()->values();

        return [
            'nomor' => $g['nomor'],
            'produsen' => $g['produsen'],
            'tanggalBayar' => $g['tanggal']->translatedFormat('j F Y'),
            'permintaanNomors' => $g['permintaanNomors'],
            'penerimaanNomors' => $penerimaans->pluck('nomor_transaksi')->all() ?: [$pen->nomor_transaksi],
            'tanggalTerima' => $penerimaans
                ->map(fn ($p) => $p->tanggal_terima->translatedFormat('j F Y'))
                ->unique()->implode(', ') ?: '-',
            'metode' => $metode ?: '-',
            'persetujuan' => $persetujuan ?: '-',
            'rows' => $rows,
            'total' => $g['total'],
            'terbilang' => Terbilang::rupiah($g['total']),
            'namaPemilik' => $lunas->map(fn ($p) => $p->pemilik?->user?->username)->filter()->first(),
            'namaAdmin' => $pen->admin?->user?->username
                ?? $penerimaans->first()?->admin?->user?->username,
            'catatan' => $belum->isNotEmpty()
                ? 'Tagihan ' . $belum->implode(', ') . ' belum lunas dan tidak termasuk dalam bukti ini.'
                : null,
        ];
    }

    /**
     * Nomor bukti mengikuti nomor penerimaan: PNB-03112026-001 → BYR-03112026-001.
     * Tetap sama selamanya (tidak bergantung urutan pembayaran).
     */
    private function nomorBukti(PenerimaanBahanBaku $pen): string
    {
        return 'BYR-' . preg_replace('/^[A-Za-z]+-/', '', (string) $pen->nomor_transaksi);
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
