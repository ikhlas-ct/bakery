<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MenyiapkanLaporan;
use App\Models\BahanBaku;
use App\Services\StokService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanKartuStokController extends Controller
{
    use MenyiapkanLaporan;

    public function __construct(private StokService $stok)
    {
    }

    /** Halaman kartu stok per bahan baku (admin & pemilik). */
    public function index(Request $request)
    {
        $this->authorizeRole();

        $request->validate([
            'bahan_baku_id' => ['nullable', 'exists:bahan_bakus,id'],
            'dari'          => ['nullable', 'date'],
            'sampai'        => ['nullable', 'date'],
        ]);

        [$dari, $sampai] = $this->periode($request);

        $bahan = null;
        $kartu = null;
        $batches = collect();

        if ($request->filled('bahan_baku_id')) {
            $bahan = BahanBaku::with(['kategoriBarang', 'satuan'])->findOrFail($request->input('bahan_baku_id'));
            $kartu = $this->stok->kartuStok($bahan, $dari, $sampai);
            $batches = $this->stok->batchPerBahan([$bahan->id], $sampai)->get($bahan->id, collect());
        }

        return view('pages.laporan.kartu-stok.index', [
            'daftarBahan' => BahanBaku::with('satuan')->orderByDesc('is_aktif')->orderBy('nama_bahan_baku')->get(),
            'bahan'       => $bahan,
            'kartu'       => $kartu,
            'batches'     => $batches,
            'dari'        => $dari,
            'sampai'      => $sampai,
        ]);
    }

    /** Halaman cetak kartu stok. */
    public function cetak(Request $request)
    {
        $this->authorizeRole();

        $request->validate([
            'bahan_baku_id' => ['required', 'exists:bahan_bakus,id'],
            'dari'          => ['nullable', 'date'],
            'sampai'        => ['nullable', 'date'],
        ]);

        [$dari, $sampai] = $this->periode($request);

        $bahan = BahanBaku::with(['kategoriBarang', 'satuan'])->findOrFail($request->input('bahan_baku_id'));

        return view('pages.laporan.kartu-stok.cetak', array_merge($this->dataKop($request), [
            'bahan'   => $bahan,
            'kartu'   => $this->stok->kartuStok($bahan, $dari, $sampai),
            'batches' => $this->stok->batchPerBahan([$bahan->id], $sampai)->get($bahan->id, collect()),
            'dari'    => $dari,
            'sampai'  => $sampai,
        ]));
    }

    /** Default periode: awal bulan ini s/d hari ini. Bila terbalik, ditukar otomatis. */
    private function periode(Request $request): array
    {
        $dari = $request->filled('dari') ? Carbon::parse($request->input('dari'))->startOfDay() : today()->startOfMonth();
        $sampai = $request->filled('sampai') ? Carbon::parse($request->input('sampai'))->startOfDay() : today();

        return $dari->greaterThan($sampai) ? [$sampai, $dari] : [$dari, $sampai];
    }
}
