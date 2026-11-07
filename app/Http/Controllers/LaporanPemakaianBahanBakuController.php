<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\PemakaianBahanBaku;
use App\Models\PemakaianBahanBakuDetail;
use App\Models\WebsiteSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Laporan Pemakaian Bahan Baku (metode FEFO) per periode:
 *  - harian   : satu tanggal
 *  - bulanan  : satu bulan pada tahun tertentu
 *  - tahunan  : satu tahun penuh
 *  - rentang  : antara dua tanggal (dari – sampai)
 *
 * Bisa diakses admin & pemilik (dicek di authorizeRole).
 */
class LaporanPemakaianBahanBakuController extends Controller
{
    /** Kota yang dicetak di dekat tanggal tanda tangan. Ubah sesuai kebutuhan. */
    private const KOTA = 'Kaohsiung';

    private const JENIS = ['harian', 'bulanan', 'tahunan', 'rentang'];

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // ─────────────────────────────────────────────────────────────
    //  Halaman laporan (filter + pratinjau di layar)
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->authorizeRole();

        $periode = $this->resolvePeriode($request);
        $filter  = $this->resolveFilter($request);
        $base    = $this->queryPemakaian($periode, $filter);

        $pemakaians = (clone $base)
            ->with($this->eagerDetail($filter['bahan_baku_id']))
            ->paginate(10)
            ->withQueryString();

        $rekap          = $this->rekap($base, $filter['bahan_baku_id']);
        $totalPerSatuan = $this->totalPerSatuan($rekap);

        // Parameter yang sudah dinormalisasi → dipakai tombol Lihat / Cetak.
        $cetakParams = array_merge($periode['params'], array_filter([
            'bahan_baku_id' => $filter['bahan_baku_id'],
            'search'        => $filter['search'],
        ]));

        return view('pages.laporan.pemakaian-bahan-baku.index', [
            'pemakaians'     => $pemakaians,
            'rekap'          => $rekap,
            'totalPerSatuan' => $totalPerSatuan,
            'periode'        => $periode,
            'filter'         => $filter,
            'cetakParams'    => $cetakParams,
            'bahanBakus'     => BahanBaku::orderBy('nama_bahan_baku')->get(['id', 'kode_bahan_baku', 'nama_bahan_baku']),
            'bulans'         => self::BULAN,
            'tahuns'         => $this->daftarTahun(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Halaman cetak (format seperti PDF contoh)
    // ─────────────────────────────────────────────────────────────
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $periode = $this->resolvePeriode($request);
        $filter  = $this->resolveFilter($request);
        $base    = $this->queryPemakaian($periode, $filter);

        $pemakaians = (clone $base)
            ->with($this->eagerDetail($filter['bahan_baku_id']))
            ->get();

        $rekap = $this->rekap($base, $filter['bahan_baku_id']);

        $bahanBakuDipilih = $filter['bahan_baku_id']
            ? BahanBaku::find($filter['bahan_baku_id'])
            : null;

        return view('pages.laporan.pemakaian-bahan-baku.cetak', [
            'setting'          => WebsiteSetting::getSetting(),
            'pemakaians'       => $pemakaians,
            'rekap'            => $rekap,
            'totalPerSatuan'   => $this->totalPerSatuan($rekap),
            'periode'          => $periode,
            'bahanBakuDipilih' => $bahanBakuDipilih,
            'pencetak'         => auth()->user(),
            'kota'             => self::KOTA,
            'autoPrint'        => $request->boolean('cetak'),
            'transaksi'        => null,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Cetak untuk SATU transaksi pemakaian (tanpa filter periode)
    //  Dipakai tombol "Cetak" di halaman detail & daftar pemakaian.
    // ─────────────────────────────────────────────────────────────
    public function cetakTransaksi(Request $request, PemakaianBahanBaku $pemakaianBahanBaku)
    {
        $this->authorizeRole();

        $pemakaianBahanBaku->load(array_merge($this->eagerDetail(null), ['admin.user']));

        $rekap = $this->rekap(
            PemakaianBahanBaku::query()->whereKey($pemakaianBahanBaku->id),
            null
        );

        return view('pages.laporan.pemakaian-bahan-baku.cetak', [
            'setting'          => WebsiteSetting::getSetting(),
            'pemakaians'       => collect([$pemakaianBahanBaku]),
            'rekap'            => $rekap,
            'totalPerSatuan'   => $this->totalPerSatuan($rekap),
            'periode'          => [
                'jenis' => 'transaksi',
                'label' => $pemakaianBahanBaku->nomor_transaksi,
            ],
            'bahanBakuDipilih' => null,
            'pencetak'         => auth()->user(),
            'kota'             => self::KOTA,
            'autoPrint'        => $request->boolean('cetak'),
            'transaksi'        => $pemakaianBahanBaku,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  Helper
    // ─────────────────────────────────────────────────────────────

    /** Hanya admin & pemilik yang boleh membuka laporan. */
    private function authorizeRole(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['admin', 'pemilik'], true), 403);
    }

    /**
     * Tentukan rentang tanggal dari jenis periode yang dipilih.
     *
     * @return array{jenis:string, mulai:Carbon, selesai:Carbon, label:string, params:array,
     *               tanggal:string, bulan:int, tahun:int, dari:string, sampai:string}
     */
    private function resolvePeriode(Request $request): array
    {
        $now = Carbon::now();

        $jenis = $request->query('jenis');
        $jenis = in_array($jenis, self::JENIS, true) ? $jenis : 'bulanan';

        $tahun = (int) $request->query('tahun', $now->year);
        $tahun = ($tahun >= 2000 && $tahun <= 2100) ? $tahun : $now->year;

        $bulan = (int) $request->query('bulan', $now->month);
        $bulan = ($bulan >= 1 && $bulan <= 12) ? $bulan : $now->month;

        $tanggal = $this->parseTanggal($request->query('tanggal')) ?? $now->copy()->startOfDay();
        $dari    = $this->parseTanggal($request->query('dari')) ?? $now->copy()->startOfMonth();
        $sampai  = $this->parseTanggal($request->query('sampai')) ?? $now->copy()->startOfDay();
        if ($dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai, $dari];
        }

        switch ($jenis) {
            case 'harian':
                $mulai   = $tanggal->copy()->startOfDay();
                $selesai = $tanggal->copy()->endOfDay();
                $label   = $this->formatTanggal($mulai);
                $params  = ['jenis' => 'harian', 'tanggal' => $mulai->toDateString()];
                break;

            case 'tahunan':
                $mulai   = Carbon::create($tahun, 1, 1)->startOfDay();
                $selesai = Carbon::create($tahun, 12, 31)->endOfDay();
                $label   = 'Tahun ' . $tahun;
                $params  = ['jenis' => 'tahunan', 'tahun' => $tahun];
                break;

            case 'rentang':
                $mulai   = $dari->copy()->startOfDay();
                $selesai = $sampai->copy()->endOfDay();
                $label   = $this->formatTanggal($mulai) . ' s/d ' . $this->formatTanggal($selesai);
                $params  = [
                    'jenis'  => 'rentang',
                    'dari'   => $mulai->toDateString(),
                    'sampai' => $selesai->toDateString(),
                ];
                break;

            case 'bulanan':
            default:
                $mulai   = Carbon::create($tahun, $bulan, 1)->startOfMonth();
                $selesai = $mulai->copy()->endOfMonth();
                $label   = self::BULAN[$bulan] . ' ' . $tahun;
                $params  = ['jenis' => 'bulanan', 'bulan' => $bulan, 'tahun' => $tahun];
                break;
        }

        return [
            'jenis'   => $jenis,
            'mulai'   => $mulai,
            'selesai' => $selesai,
            'label'   => $label,
            'params'  => $params,
            // nilai bawaan untuk mengisi kembali input filter
            'tanggal' => $tanggal->toDateString(),
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'dari'    => $dari->toDateString(),
            'sampai'  => $sampai->toDateString(),
        ];
    }

    /** Filter tambahan: bahan baku tertentu & kata kunci. */
    private function resolveFilter(Request $request): array
    {
        $bahanBakuId = $request->query('bahan_baku_id');

        return [
            'bahan_baku_id' => ctype_digit((string) $bahanBakuId) && (int) $bahanBakuId > 0 ? (int) $bahanBakuId : null,
            'search'        => trim((string) $request->query('search', '')),
        ];
    }

    /** Query dasar: transaksi pemakaian dalam periode (+ filter). */
    private function queryPemakaian(array $periode, array $filter): Builder
    {
        $bahanBakuId = $filter['bahan_baku_id'];
        $search      = $filter['search'];

        return PemakaianBahanBaku::query()
            ->whereBetween('tanggal_pakai', [$periode['mulai']->toDateString(), $periode['selesai']->toDateString()])
            ->when($bahanBakuId, fn (Builder $q) => $q->whereHas(
                'pemakaianBahanBakuDetails',
                fn (Builder $d) => $d->where('bahan_baku_id', $bahanBakuId)
            ))
            ->when($search !== '', fn (Builder $q) => $q->where(
                fn (Builder $w) => $w->where('nomor_transaksi', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
            ))
            ->orderBy('tanggal_pakai')
            ->orderBy('id');
    }

    /** Eager load rincian pemakaian (ikut terfilter jika memilih satu bahan baku). */
    private function eagerDetail(?int $bahanBakuId): array
    {
        return [
            'pemakaianBahanBakuDetails' => fn ($q) => $q
                ->when($bahanBakuId, fn ($d) => $d->where('bahan_baku_id', $bahanBakuId))
                ->orderBy('id')
                ->with(['bahanBaku.satuan', 'penerimaanBahanBakuDetail.penerimaanBahanBaku']),
        ];
    }

    /**
     * Rekapitulasi per bahan baku untuk SELURUH periode (bukan hanya halaman aktif).
     * Tiap baris: bahan_baku_id, jumlah_transaksi, total_dipakai (+ relasi bahanBaku.satuan).
     */
    private function rekap(Builder $base, ?int $bahanBakuId)
    {
        return PemakaianBahanBakuDetail::query()
            ->whereIn('pemakaian_bahan_baku_id', (clone $base)->reorder()->select('id'))
            ->when($bahanBakuId, fn ($q) => $q->where('bahan_baku_id', $bahanBakuId))
            ->selectRaw('bahan_baku_id, COUNT(DISTINCT pemakaian_bahan_baku_id) AS jumlah_transaksi, SUM(jumlah_dipakai) AS total_dipakai')
            ->groupBy('bahan_baku_id')
            ->with('bahanBaku.satuan')
            ->get()
            ->sortBy(fn ($r) => $r->bahanBaku->kode_bahan_baku ?? '')
            ->values();
    }

    /**
     * Total keseluruhan dikelompokkan per satuan, supaya satuan berbeda
     * (kg, liter, pcs) tidak dijumlahkan menjadi satu angka yang menyesatkan.
     * Contoh hasil: ['kg' => 76.5, 'pcs' => 12.0]
     */
    private function totalPerSatuan($rekap)
    {
        return $rekap
            ->groupBy(fn ($r) => mb_strtolower($r->bahanBaku?->satuan?->kode_satuan ?: ($r->bahanBaku?->satuan?->nama ?? '-')))
            ->map(fn ($g) => $g->sum(fn ($r) => (float) $r->total_dipakai));
    }

    /** Daftar tahun untuk dropdown: dari tahun transaksi pertama sampai tahun ini. */
    private function daftarTahun(): array
    {
        $sekarang = Carbon::now()->year;
        $awal     = PemakaianBahanBaku::min('tanggal_pakai');
        $awal     = $awal ? min(Carbon::parse($awal)->year, $sekarang) : $sekarang;

        return range($sekarang, $awal);
    }

    private function parseTanggal(?string $value): ?Carbon
    {
        if (! $value || ! Carbon::canBeCreatedFromFormat($value, 'Y-m-d')) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
    }

    private function formatTanggal(Carbon $tanggal): string
    {
        return $tanggal->copy()->locale('id')->translatedFormat('d F Y');
    }
}
