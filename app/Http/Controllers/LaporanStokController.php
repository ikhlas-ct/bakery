<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MenyiapkanLaporan;
use App\Models\KategoriBarang;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class LaporanStokController extends Controller
{
    use MenyiapkanLaporan;

    private const PER_PAGE = 10;

    public function __construct(private StokService $stok)
    {
    }

    /** Halaman laporan stok (admin & pemilik). */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $per = $this->tanggalPosisi($request);
        $rows = $this->stok->posisiStok($per, $this->filter($request));
        $ringkasan = $this->stok->ringkasan($rows);

        $page = Paginator::resolveCurrentPage();
        $bahanBakus = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.laporan.stok.index', [
            'bahanBakus' => $bahanBakus,
            'ringkasan'  => $ringkasan,
            'kategoris'  => KategoriBarang::orderBy('nama_kategori')->get(),
            'per'        => $per,
        ]);
    }

    /** Halaman cetak laporan stok. */
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $per = $this->tanggalPosisi($request);
        $rows = $this->stok->posisiStok($per, $this->filter($request));

        $totalPerSatuan = $rows
            ->groupBy(fn ($r) => mb_strtolower($r->bahan->satuan?->kode_satuan ?: ($r->bahan->satuan?->nama ?? '')))
            ->map(fn ($group) => round((float) $group->sum('stok'), 2));

        return view('pages.laporan.stok.cetak', array_merge($this->dataKop($request), [
            'rows'           => $rows,
            'per'            => $per,
            'totalPerSatuan' => $totalPerSatuan,
            'filterLabel'    => $this->filterLabel($request),
        ]));
    }

    /* ───────── helper ───────── */

    private function filter(Request $request): array
    {
        return $request->only(['search', 'kategori_barang_id', 'status', 'stok']);
    }

    /** Tanggal posisi stok; default hari ini dan tidak boleh melewati hari ini. */
    private function tanggalPosisi(Request $request): Carbon
    {
        $request->validate(['tanggal' => ['nullable', 'date']]);

        $tgl = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal'))->startOfDay() : today();

        return $tgl->greaterThan(today()) ? today() : $tgl;
    }

    private function filterLabel(Request $request): string
    {
        $stokLabel = [
            'kritis'   => 'Stok kritis / habis',
            'habis'    => 'Stok habis',
            'expiring' => 'Ada batch segera kadaluarsa / kadaluarsa',
        ];

        return collect([
            $request->filled('search') ? 'Kata kunci "' . $request->input('search') . '"' : null,
            $request->filled('kategori_barang_id')
                ? 'Kategori ' . (KategoriBarang::find($request->input('kategori_barang_id'))?->nama_kategori ?? '-')
                : null,
            $request->filled('status') ? 'Status ' . ucfirst($request->input('status')) : null,
            $stokLabel[$request->input('stok')] ?? null,
        ])->filter()->implode(' | ') ?: 'Semua bahan baku';
    }
}
