<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanBakuProdusen;
use App\Models\PengalihanBahanBaku;
use App\Models\Produsen;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sisi ADMIN: menindaklanjuti kekurangan permintaan.
 *
 * Baris pengalihan dibuat otomatis saat produsen menanggapi dengan jumlah kurang
 * (lihat PermintaanMasukController). Admin di sini menentukan pengganti:
 *   (a) bahan baku SAMA ke produsen lain  -> bahan_baku_pengganti_id = NULL
 *   (b) bahan baku PENGGANTI setara       -> bahan_baku_pengganti_id terisi
 * Setelah ditentukan, status_produsen_pengganti = 'pending' menunggu respon
 * produsen pengganti (PermintaanTambahanController).
 */
class PengalihanBahanBakuController extends Controller
{
    private const FILTER_STATUS = ['belum', 'pending', 'disetujui', 'disetujui_sebagian', 'ditolak'];

    public function index(Request $request)
    {
        $status = in_array($request->status, self::FILTER_STATUS, true) ? $request->status : null;

        $pengalihans = PengalihanBahanBaku::query()
            ->with([
                'permintaanBahanBakuDetail.permintaanBahanBaku',
                'permintaanBahanBakuDetail.produsen',
                'bahanBakuAsal.satuan',
                'bahanBakuPengganti',
                'produsenPengganti',
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($w) use ($s) {
                    $w->whereHas('permintaanBahanBakuDetail.permintaanBahanBaku',
                            fn ($h) => $h->where('nomor_transaksi', 'like', "%{$s}%"))
                      ->orWhereHas('bahanBakuAsal',
                            fn ($b) => $b->where('nama_bahan_baku', 'like', "%{$s}%")
                                         ->orWhere('kode_bahan_baku', 'like', "%{$s}%"));
                });
            })
            ->when($status === 'belum', fn ($q) => $q->whereNull('produsen_pengganti_id'))
            ->when($status === 'pending', fn ($q) => $q->whereNotNull('produsen_pengganti_id')
                                                       ->where('status_produsen_pengganti', 'pending'))
            ->when(in_array($status, ['disetujui', 'disetujui_sebagian', 'ditolak'], true),
                fn ($q) => $q->where('status_produsen_pengganti', $status))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.pengalihan-bahan-baku.index', [
            'pengalihans'    => $pengalihans,
            'totalBelum'     => PengalihanBahanBaku::whereNull('produsen_pengganti_id')->count(),
            'totalMenunggu'  => PengalihanBahanBaku::whereNotNull('produsen_pengganti_id')
                                    ->where('status_produsen_pengganti', 'pending')->count(),
            'totalSelesai'   => PengalihanBahanBaku::whereIn('status_produsen_pengganti', ['disetujui', 'disetujui_sebagian'])->count(),
            'totalDitolak'   => PengalihanBahanBaku::where('status_produsen_pengganti', 'ditolak')->count(),
        ]);
    }

    public function show(PengalihanBahanBaku $pengalihanBahanBaku)
    {
        $pengalihanBahanBaku->load([
            'permintaanBahanBakuDetail.permintaanBahanBaku.admin.user',
            'permintaanBahanBakuDetail.produsen',
            'permintaanBahanBakuDetail.bahanBaku.satuan',
            'bahanBakuAsal.satuan',
            'bahanBakuPengganti',
            'produsenPengganti',
        ]);

        return view('pages.pengalihan-bahan-baku.show', [
            'p'          => $pengalihanBahanBaku,
            'detail'     => $pengalihanBahanBaku->permintaanBahanBakuDetail,
            'kekurangan' => $this->kekurangan($pengalihanBahanBaku),
            'bisaDiubah' => $this->bisaDiubah($pengalihanBahanBaku),
        ]);
    }

    public function edit(PengalihanBahanBaku $pengalihanBahanBaku)
    {
        if (! $this->bisaDiubah($pengalihanBahanBaku)) {
            return redirect()->route('pengalihan-bahan-baku.show', $pengalihanBahanBaku)
                ->with('error', 'Pengalihan ini sudah disetujui produsen pengganti sehingga tidak bisa diubah.');
        }

        $pengalihanBahanBaku->load([
            'permintaanBahanBakuDetail.permintaanBahanBaku',
            'permintaanBahanBakuDetail.produsen',
            'bahanBakuAsal.satuan',
        ]);

        $detail = $pengalihanBahanBaku->permintaanBahanBakuDetail;
        $asal   = $pengalihanBahanBaku->bahanBakuAsal;

        // Kandidat bahan baku: aktif & satuan sama (supaya jumlah tetap sebanding).
        $kandidat = BahanBaku::with('kategoriBarang')
            ->where('is_aktif', true)
            ->where('satuan_id', $asal->satuan_id)
            ->orderBy('nama_bahan_baku')
            ->get();

        // Produsen pemasok tiap kandidat (hanya mitra aktif) + harga dari pivot.
        $pemasok = BahanBakuProdusen::with('produsen')
            ->whereIn('bahan_baku_id', $kandidat->pluck('id'))
            ->whereHas('produsen', fn ($q) => $q->where('status_mitra', 'aktif'))
            ->get()
            ->groupBy('bahan_baku_id');

        $opsi = [];            // [bahan_baku_id => [ {produsen_id, nama, harga}, ... ]]
        foreach ($kandidat as $b) {
            foreach ($pemasok->get($b->id, collect()) as $row) {
                // Bahan baku yang sama: jangan tawarkan produsen yang baru saja kurang.
                if ((int) $b->id === (int) $asal->id && (int) $row->produsen_id === (int) $detail->produsen_id) {
                    continue;
                }
                $opsi[$b->id][] = [
                    'produsen_id' => $row->produsen_id,
                    'nama'        => $row->produsen->nama_produsen,
                    'harga'       => (float) $row->harga,
                ];
            }
        }

        // Dropdown bahan baku pengganti: hanya yang punya minimal 1 pemasok.
        $bahanPengganti = $kandidat
            ->filter(fn ($b) => (int) $b->id !== (int) $asal->id && ! empty($opsi[$b->id]))
            ->values();

        return view('pages.pengalihan-bahan-baku.edit', [
            'p'              => $pengalihanBahanBaku,
            'detail'         => $detail,
            'asal'           => $asal,
            'kekurangan'     => $this->kekurangan($pengalihanBahanBaku),
            'opsi'           => $opsi,
            'bahanPengganti' => $bahanPengganti,
        ]);
    }

    public function update(Request $request, PengalihanBahanBaku $pengalihanBahanBaku): RedirectResponse
    {
        $request->validate([
            'bahan_baku_pengganti_id' => ['nullable', 'integer', 'exists:bahan_bakus,id'],
            'produsen_pengganti_id'   => ['required', 'integer', 'exists:produsens,id'],
            'jumlah_dialihkan'        => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'alasan_pengalihan'       => ['required', 'string', 'max:255'],
        ], [
            'produsen_pengganti_id.required' => 'Pilih produsen pengganti.',
            'jumlah_dialihkan.required'      => 'Jumlah dialihkan wajib diisi.',
            'jumlah_dialihkan.min'           => 'Jumlah dialihkan minimal 0,01.',
            'alasan_pengalihan.required'     => 'Alasan pengalihan wajib diisi.',
        ]);

        DB::transaction(function () use ($request, $pengalihanBahanBaku) {
            $p = PengalihanBahanBaku::with(['permintaanBahanBakuDetail', 'bahanBakuAsal'])
                ->lockForUpdate()
                ->findOrFail($pengalihanBahanBaku->id);

            if (! $this->bisaDiubah($p)) {
                throw ValidationException::withMessages([
                    'produsen_pengganti_id' => 'Pengalihan ini sudah disetujui produsen pengganti sehingga tidak bisa diubah.',
                ]);
            }

            $detail = $p->permintaanBahanBakuDetail;
            $asal   = $p->bahanBakuAsal;

            // null = bahan baku yang sama dengan asal.
            $penggantiId = $request->filled('bahan_baku_pengganti_id')
                ? (int) $request->bahan_baku_pengganti_id : null;
            if ($penggantiId === (int) $asal->id) {
                $penggantiId = null;
            }
            $tujuanId   = $penggantiId ?? (int) $asal->id;
            $produsenId = (int) $request->produsen_pengganti_id;

            // Bahan baku pengganti harus aktif & satuannya sama dengan asal.
            if ($penggantiId) {
                $bb = BahanBaku::findOrFail($penggantiId);
                if (! $bb->is_aktif || (int) $bb->satuan_id !== (int) $asal->satuan_id) {
                    throw ValidationException::withMessages([
                        'bahan_baku_pengganti_id' => 'Bahan baku pengganti harus aktif dan bersatuan sama dengan bahan baku asal.',
                    ]);
                }
            }

            // Produsen pengganti harus mitra aktif.
            $produsen = Produsen::findOrFail($produsenId);
            if ($produsen->status_mitra !== 'aktif') {
                throw ValidationException::withMessages([
                    'produsen_pengganti_id' => 'Produsen pengganti harus berstatus mitra aktif.',
                ]);
            }

            // Bahan baku sama -> wajib produsen lain (yang tadi kurang tidak diulang).
            if (! $penggantiId && $produsenId === (int) $detail->produsen_id) {
                throw ValidationException::withMessages([
                    'produsen_pengganti_id' => 'Untuk bahan baku yang sama, pilih produsen lain.',
                ]);
            }

            // Produsen harus benar-benar memasok bahan baku tujuan (harga dari pivot).
            $pivot = BahanBakuProdusen::where('bahan_baku_id', $tujuanId)
                ->where('produsen_id', $produsenId)
                ->first();
            if (! $pivot) {
                throw ValidationException::withMessages([
                    'produsen_pengganti_id' => 'Produsen ini tidak memasok bahan baku yang dipilih.',
                ]);
            }

            // Batas jumlah = kekurangan di item asal (dalam sen).
            $maksSen = (int) round($this->kekurangan($p) * 100);
            $jumlahSen = (int) round((float) $request->jumlah_dialihkan * 100);
            if ($jumlahSen > $maksSen) {
                throw ValidationException::withMessages([
                    'jumlah_dialihkan' => 'Jumlah melebihi kekurangan di permintaan asal ('
                        . rtrim(rtrim(number_format($maksSen / 100, 2, ',', '.'), '0'), ',') . ').',
                ]);
            }

            $p->update([
                'bahan_baku_pengganti_id'    => $penggantiId,
                'produsen_pengganti_id'      => $produsenId,
                'jumlah_dialihkan'           => $jumlahSen / 100,
                'harga_satuan'               => $pivot->harga,
                'alasan_pengalihan'          => $request->alasan_pengalihan,
                'tanggal'                    => now()->toDateString(),
                // (re)mulai siklus persetujuan pengganti
                'status_produsen_pengganti'  => 'pending',
                'jumlah_disetujui_pengganti' => null,
            ]);
        });

        return redirect()
            ->route('pengalihan-bahan-baku.show', $pengalihanBahanBaku)
            ->with('success', 'Pengganti berhasil ditentukan. Menunggu respon produsen pengganti.');
    }

    /**
     * Hard delete, hanya untuk pengalihan yang belum direspon pengganti.
     */
    public function destroy(PengalihanBahanBaku $pengalihanBahanBaku): RedirectResponse
    {
        if ($pengalihanBahanBaku->status_produsen_pengganti !== 'pending') {
            return redirect()->route('pengalihan-bahan-baku.index')
                ->with('error', 'Pengalihan yang sudah direspon produsen pengganti tidak bisa dihapus.');
        }

        try {
            $pengalihanBahanBaku->delete();
        } catch (QueryException $e) {
            return redirect()->route('pengalihan-bahan-baku.index')
                ->with('error', 'Pengalihan tidak dapat dihapus karena masih terhubung dengan data lain.');
        }

        return redirect()->route('pengalihan-bahan-baku.index')
            ->with('success', 'Pengalihan berhasil dihapus.');
    }

    /**
     * Kekurangan di item permintaan asal = diminta - disetujui produsen asal.
     */
    private function kekurangan(PengalihanBahanBaku $p): float
    {
        $d = $p->permintaanBahanBakuDetail;

        return max(0, (float) $d->jumlah_diminta - (float) ($d->jumlah_disetujui ?? 0));
    }

    /**
     * Boleh ditentukan/diubah selama belum disetujui (pending) atau ditolak pengganti
     * (admin memilih pengganti lain pada baris yang sama).
     */
    private function bisaDiubah(PengalihanBahanBaku $p): bool
    {
        return in_array($p->status_produsen_pengganti, ['pending', 'ditolak'], true);
    }
}
