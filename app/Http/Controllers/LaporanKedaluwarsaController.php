<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MenyiapkanLaporan;
use App\Models\KategoriBarang;
use App\Services\StokService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class LaporanKedaluwarsaController extends Controller
{
    use MenyiapkanLaporan;

    private const PER_PAGE = 15;

    /** Pilihan batas "mendekati kedaluwarsa" (hari ke depan). */
    private const PILIHAN_HARI = [7, 14, 30, 60, 90];

    private const PILIHAN_TAMPIL = [
        'semua'      => 'Kedaluwarsa & mendekati',
        'mendekati'  => 'Mendekati kedaluwarsa saja',
        'kadaluarsa' => 'Sudah kedaluwarsa saja',
    ];

    public function __construct(private StokService $stok)
    {
    }

    /** Halaman laporan (admin & pemilik). */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $hari = $this->hari($request);
        $tampil = $this->tampil($request);

        $rows = $this->stok->batchKedaluwarsa(today(), $hari, $tampil, $this->filter($request));

        $page = Paginator::resolveCurrentPage();
        $items = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.laporan.kedaluwarsa.index', [
            'items'         => $items,
            'ringkasan'     => $this->stok->ringkasanKedaluwarsa($rows),
            'kategoris'     => KategoriBarang::orderBy('nama_kategori')->get(),
            'hari'          => $hari,
            'tampil'        => $tampil,
            'pilihanHari'   => self::PILIHAN_HARI,
            'pilihanTampil' => self::PILIHAN_TAMPIL,
        ]);
    }

    /** Halaman cetak. */
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $hari = $this->hari($request);
        $tampil = $this->tampil($request);

        $rows = $this->stok->batchKedaluwarsa(today(), $hari, $tampil, $this->filter($request));

        // Rekap per bahan baku (rows sudah urut paling mendesak, jadi first() = batch terdekat).
        $rekap = $rows
            ->groupBy(fn ($r) => $r->bahan->id)
            ->map(fn ($group) => (object) [
                'bahan'          => $group->first()->bahan,
                'jumlah_batch'   => $group->count(),
                'total_sisa'     => round((float) $group->sum(fn ($r) => $r->batch->sisa), 2),
                'batch_terdekat' => $group->first()->batch,
            ])
            ->values();

        $totalPerSatuan = $rows
            ->groupBy(fn ($r) => mb_strtolower($r->bahan->satuan?->kode_satuan ?: ($r->bahan->satuan?->nama ?? '')))
            ->map(fn ($group) => round((float) $group->sum(fn ($r) => $r->batch->sisa), 2));

        return view('pages.laporan.kedaluwarsa.cetak', array_merge($this->dataKop($request), [
            'rows'           => $rows,
            'rekap'          => $rekap,
            'totalPerSatuan' => $totalPerSatuan,
            'ringkasan'      => $this->stok->ringkasanKedaluwarsa($rows),
            'per'            => today(),
            'hari'           => $hari,
            'filterLabel'    => $this->filterLabel($request, $tampil),
        ]));
    }

    /* ───────── helper ───────── */

    private function filter(Request $request): array
    {
        return $request->only(['search', 'kategori_barang_id']);
    }

    private function hari(Request $request): int
    {
        $h = (int) $request->input('hari', 30);

        return in_array($h, self::PILIHAN_HARI, true) ? $h : 30;
    }

    private function tampil(Request $request): string
    {
        $t = (string) $request->input('tampil', 'semua');

        return array_key_exists($t, self::PILIHAN_TAMPIL) ? $t : 'semua';
    }

    private function filterLabel(Request $request, string $tampil): string
    {
        return collect([
            self::PILIHAN_TAMPIL[$tampil],
            $request->filled('search') ? 'Kata kunci "' . $request->input('search') . '"' : null,
            $request->filled('kategori_barang_id')
                ? 'Kategori ' . (KategoriBarang::find($request->input('kategori_barang_id'))?->nama_kategori ?? '-')
                : null,
        ])->filter()->implode(' | ');
    }
}
