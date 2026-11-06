<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanBahanBaku;
use App\Models\PengalihanBahanBaku;
use App\Models\Produsen;
use App\Models\WebsiteSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanPenerimaanBahanBakuController extends Controller
{
    private const JENIS = ['harian', 'bulanan', 'tahunan', 'rentang'];

    /**
     * Halaman laporan (preview + form filter periode).
     */
    public function index(Request $request)
    {
        $this->authorizeRole();

        return view('pages.laporan.penerimaan-bahan-baku.index', $this->buildReport($request));
    }

    /**
     * Halaman cetak (siap print / simpan sebagai PDF lewat browser).
     */
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $data = $this->buildReport($request);
        $data['tanggalCetak'] = Carbon::now()->locale('id')->translatedFormat('j F Y');

        return view('pages.laporan.penerimaan-bahan-baku.cetak', $data);
        
    }

    // ─────────────────────────────────────────────────────────────
    //  INTERNAL
    // ─────────────────────────────────────────────────────────────

    /** Hanya admin & pemilik yang boleh membuka laporan. */
    private function authorizeRole(): void
    {
        abort_unless(in_array(auth()->user()->role ?? null, ['admin', 'pemilik'], true), 403);
    }

    private function buildReport(Request $request): array
    {
        $periode    = $this->resolvePeriode($request);
        $produsenId = $request->query('produsen_id');
        $produsenId = is_numeric($produsenId) ? (int) $produsenId : null;

        $penerimaans = PenerimaanBahanBaku::with([
                'produsen:id,nama_produsen',
                'penerimaanBahanBakuDetails.bahanBaku.satuan',
                'penerimaanBahanBakuDetails.permintaanBahanBakuDetail',
            ])
            ->whereBetween('tanggal_terima', [
                $periode['start']->toDateString(),
                $periode['end']->toDateString(),
            ])
            ->when($produsenId, fn ($q) => $q->where('produsen_id', $produsenId))
            ->orderBy('tanggal_terima')
            ->orderBy('nomor_transaksi')
            ->get();

        // ── Data pendukung untuk menentukan harga satuan ──────────
        $permintaanDetailIds = $penerimaans
            ->flatMap(fn ($p) => $p->penerimaanBahanBakuDetails)
            ->pluck('permintaan_bahan_baku_detail_id')
            ->filter()->unique()->values();

        $pengalihans = PengalihanBahanBaku::whereIn('permintaan_bahan_baku_detail_id', $permintaanDetailIds)
            ->get()
            ->groupBy('permintaan_bahan_baku_detail_id');

        $hargaPivot = DB::table('bahan_baku_produsens')
            ->whereIn('produsen_id', $penerimaans->pluck('produsen_id')->unique())
            ->get()
            ->keyBy(fn ($r) => $r->produsen_id . '-' . $r->bahan_baku_id);

        // ── Susun data transaksi + item ───────────────────────────
        $transaksis = $penerimaans->map(function ($p) use ($pengalihans, $hargaPivot) {
            $items = $p->penerimaanBahanBakuDetails->map(function ($d) use ($p, $pengalihans, $hargaPivot) {
                $harga  = $this->resolveHarga($p, $d, $pengalihans, $hargaPivot);
                $jumlah = (float) $d->jumlah_diterima;
                $satuan = $d->bahanBaku?->satuan;

                return (object) [
                    'nama'       => $d->bahanBaku?->nama_bahan_baku ?? '-',
                    'kode'       => $d->bahanBaku?->kode_bahan_baku ?? '-',
                    'jumlah'     => $jumlah,
                    'satuan'     => strtolower($satuan?->kode_satuan ?: ($satuan?->nama ?? '')),
                    'harga'      => $harga,
                    'subtotal'   => round($jumlah * $harga, 2),
                    'kadaluarsa' => $d->tanggal_kadaluarsa,
                ];
            });

            return (object) [
                'nomor'    => $p->nomor_transaksi,
                'tanggal'  => $p->tanggal_terima,
                'produsen' => $p->produsen?->nama_produsen ?? '-',
                'items'    => $items,
                'subtotal' => $items->sum('subtotal'),
            ];
        });

        // ── Ringkasan ─────────────────────────────────────────────
        $semuaItem = $transaksis->flatMap(fn ($t) => $t->items);
        $totalNilai = $semuaItem->sum('subtotal');

        // Satuan bisa berbeda-beda (kg, liter, pcs), jadi total jumlah dipisah per satuan.
        $totalPerSatuan = $semuaItem->groupBy('satuan')->map(fn (Collection $g) => $g->sum('jumlah'));

        $ringkasan = [
            'jumlah_transaksi' => $transaksis->count(),
            'jumlah_supplier'  => $transaksis->pluck('produsen')->unique()->count(),
            'total_diterima'   => $totalPerSatuan->isEmpty()
                ? '0'
                : $totalPerSatuan->map(fn ($v, $s) => trim($this->qty($v) . ' ' . $s))->implode(' + '),
            'total_nilai'      => $this->rp($totalNilai),
        ];

        return [
            'periode'    => $periode,
            'filter'     => $periode['filter'] + ['produsen_id' => $produsenId],
            'produsen'   => $produsenId ? Produsen::find($produsenId) : null,
            'produsens'  => Produsen::orderBy('nama_produsen')->get(['id', 'nama_produsen']),
            'transaksis' => $transaksis,
            'ringkasan'  => $ringkasan,
            'totalNilai' => $totalNilai,
            'setting'    => WebsiteSetting::getSetting(),
        ];
    }

    /**
     * Harga satuan per baris penerimaan.
     *  1. Harga dari permintaan (jika produsen & bahan baku sama dengan yang diterima)
     *  2. Harga dari pengalihan (jika diterima dari produsen/bahan baku pengganti)
     *  3. Harga master bahan_baku_produsens
     */
    private function resolveHarga($penerimaan, $detail, Collection $pengalihans, Collection $hargaPivot): float
    {
        $pmd = $detail->permintaanBahanBakuDetail;

        if ($pmd
            && (int) $pmd->produsen_id === (int) $penerimaan->produsen_id
            && (int) $pmd->bahan_baku_id === (int) $detail->bahan_baku_id) {
            return (float) $pmd->harga_satuan;
        }

        if ($pmd) {
            $alih = ($pengalihans[$pmd->id] ?? collect())->first(function ($p) use ($penerimaan, $detail) {
                return (int) $p->produsen_pengganti_id === (int) $penerimaan->produsen_id
                    && (int) ($p->bahan_baku_pengganti_id ?? $p->bahan_baku_asal_id) === (int) $detail->bahan_baku_id
                    && $p->harga_satuan !== null;
            });

            if ($alih) {
                return (float) $alih->harga_satuan;
            }
        }

        $pivot = $hargaPivot[$penerimaan->produsen_id . '-' . $detail->bahan_baku_id] ?? null;

        return (float) ($pivot->harga ?? $pmd->harga_satuan ?? 0);
    }

    /**
     * Terjemahkan input filter menjadi rentang tanggal + label.
     */
    private function resolvePeriode(Request $request): array
    {
        $jenis = in_array($request->query('jenis'), self::JENIS, true) ? $request->query('jenis') : 'bulanan';
        $today = Carbon::today();

        $tanggal = $this->parse($request->query('tanggal'), 'Y-m-d') ?? $today->copy();
        $bulan   = $this->parse($request->query('bulan'), 'Y-m') ?? $today->copy()->startOfMonth();
        $tahun   = (int) $request->query('tahun', $today->year);
        $tahun   = ($tahun >= 2000 && $tahun <= 2100) ? $tahun : $today->year;
        $dari    = $this->parse($request->query('dari'), 'Y-m-d') ?? $today->copy()->startOfMonth();
        $sampai  = $this->parse($request->query('sampai'), 'Y-m-d') ?? $today->copy();

        if ($sampai->lt($dari)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        $id = fn (Carbon $c, string $f) => $c->copy()->locale('id')->translatedFormat($f);

        switch ($jenis) {
            case 'harian':
                $start = $tanggal->copy()->startOfDay();
                $end   = $tanggal->copy()->endOfDay();
                $label = $id($tanggal, 'l, j F Y');
                $jenisLabel = 'Laporan harian';
                $labelPeriode = 'Tanggal terima';
                break;

            case 'tahunan':
                $start = Carbon::create($tahun, 1, 1)->startOfDay();
                $end   = Carbon::create($tahun, 12, 31)->endOfDay();
                $label = 'Tahun ' . $tahun;
                $jenisLabel = 'Laporan tahunan';
                $labelPeriode = 'Periode terima';
                break;

            case 'rentang':
                $start = $dari->copy()->startOfDay();
                $end   = $sampai->copy()->endOfDay();
                $label = $dari->isSameDay($sampai)
                    ? $id($dari, 'j F Y')
                    : $id($dari, 'j F Y') . ' s.d. ' . $id($sampai, 'j F Y');
                $jenisLabel = 'Laporan per rentang tanggal';
                $labelPeriode = 'Periode terima';
                break;

            case 'bulanan':
            default:
                $start = $bulan->copy()->startOfMonth();
                $end   = $bulan->copy()->endOfMonth();
                $label = $id($bulan, 'F Y');
                $jenisLabel = 'Laporan bulanan';
                $labelPeriode = 'Periode terima';
                break;
        }

        return [
            'jenis'         => $jenis,
            'start'         => $start,
            'end'           => $end,
            'label'         => $label,
            'label_jenis'   => $jenisLabel,
            'label_periode' => $labelPeriode,
            'filter'        => [
                'jenis'   => $jenis,
                'tanggal' => $tanggal->format('Y-m-d'),
                'bulan'   => $bulan->format('Y-m'),
                'tahun'   => $tahun,
                'dari'    => $dari->format('Y-m-d'),
                'sampai'  => $sampai->format('Y-m-d'),
            ],
        ];
    }

    /** Parse tanggal dengan format ketat; null kalau tidak valid. */
    private function parse(?string $value, string $format): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!' . $format, $value);
        } catch (\Throwable $e) {
            return null;
        }

        return ($date && $date->format($format) === $value) ? $date : null;
    }

    private function qty(float $n): string
    {
        return number_format($n, 2, ',', '.');
    }

    private function rp(float $n): string
    {
        return 'Rp ' . number_format($n, fmod($n, 1) == 0 ? 0 : 2, ',', '.');
    }
}
