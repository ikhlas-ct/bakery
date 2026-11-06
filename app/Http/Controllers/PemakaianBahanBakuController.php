<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\PemakaianBahanBaku;
use App\Models\PemakaianBahanBakuDetail;
use App\Models\PenerimaanBahanBakuDetail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * Pemakaian (pengeluaran) bahan baku dari gudang oleh Admin.
 *
 * Aturan pengambilan stok (FEFO versi bakery ini):
 *   1. Batch dengan TANGGAL PENERIMAAN paling lama dipakai lebih dulu.
 *   2. Jika tanggal penerimaan sama, batch dengan tanggal kadaluarsa lebih dekat didahulukan
 *      (batch tanpa tanggal kadaluarsa di urutan terakhir).
 *   3. Jika masih sama, batch dengan id lebih kecil didahulukan.
 *
 * Satu baris bahan baku pada form bisa diambil dari beberapa batch sekaligus;
 * hasilnya disimpan sebagai beberapa baris di pemakaian_bahan_baku_details (satu baris per batch).
 */
class PemakaianBahanBakuController extends Controller
{
    // =====================================================================
    //  CRUD
    // =====================================================================

    public function index(Request $request): View
    {
        $pemakaians = PemakaianBahanBaku::with([
                'admin.user',
                'pemakaianBahanBakuDetails.bahanBaku.satuan',
            ])
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $kata = trim($request->search);
                $q->where(function (Builder $w) use ($kata) {
                    $w->where('nomor_transaksi', 'like', "%{$kata}%")
                      ->orWhere('keterangan', 'like', "%{$kata}%");
                });
            })
            ->when($request->filled('bahan_baku_id'), function (Builder $q) use ($request) {
                $q->whereHas('pemakaianBahanBakuDetails', function (Builder $d) use ($request) {
                    $d->where('bahan_baku_id', $request->bahan_baku_id);
                });
            })
            ->when($request->filled('dari'), fn (Builder $q) => $q->whereDate('tanggal_pakai', '>=', $request->dari))
            ->when($request->filled('sampai'), fn (Builder $q) => $q->whereDate('tanggal_pakai', '<=', $request->sampai))
            ->orderByDesc('tanggal_pakai')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $totalTransaksi    = PemakaianBahanBaku::count();
        $transaksiBulanIni = PemakaianBahanBaku::whereYear('tanggal_pakai', now()->year)
            ->whereMonth('tanggal_pakai', now()->month)
            ->count();
        $transaksiHariIni  = PemakaianBahanBaku::whereDate('tanggal_pakai', today())->count();

        $bahanBakus = BahanBaku::orderBy('nama_bahan_baku')->get(['id', 'kode_bahan_baku', 'nama_bahan_baku']);

        return view('pages.pemakaian-bahan-baku.index', compact(
            'pemakaians',
            'totalTransaksi',
            'transaksiBulanIni',
            'transaksiHariIni',
            'bahanBakus'
        ));
    }

    public function create(): View
    {
        $bahanBakus = BahanBaku::with('satuan')
            ->where('is_aktif', true)
            ->orderBy('nama_bahan_baku')
            ->get();

        return view('pages.pemakaian-bahan-baku.create', compact('bahanBakus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages(), $this->attributes());

        $admin = auth()->user()->admin;
        if (! $admin) {
            return back()->withInput()->with('error', 'Akun Anda belum memiliki profil admin sehingga tidak dapat mencatat pemakaian.');
        }

        try {
            $pemakaian = DB::transaction(function () use ($data, $admin) {
                $pemakaian = PemakaianBahanBaku::create([
                    'admin_id'        => $admin->id,
                    'nomor_transaksi' => $this->nomorBaru($data['tanggal_pakai']),
                    'tanggal_pakai'   => $data['tanggal_pakai'],
                    'keterangan'      => $data['keterangan'] ?? null,
                ]);

                $this->alokasikanFefo($pemakaian, array_values($data['items']));

                return $pemakaian;
            });
        } catch (ValidationException $e) {
            throw $e; // stok tidak cukup, dll. -> kembali ke form dengan pesan per baris
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan pemakaian bahan baku.');
        }

        return redirect()
            ->route('pemakaian-bahan-baku.show', $pemakaian)
            ->with('success', "Pemakaian {$pemakaian->nomor_transaksi} berhasil dicatat dan stok sudah dikurangi (FEFO).");
    }

    public function show(PemakaianBahanBaku $pemakaianBahanBaku): View
    {
        $pemakaianBahanBaku->load([
            'admin.user',
            'pemakaianBahanBakuDetails.bahanBaku.satuan',
            'pemakaianBahanBakuDetails.penerimaanBahanBakuDetail.penerimaanBahanBaku.produsen',
        ]);

        $ringkasan = $pemakaianBahanBaku->pemakaianBahanBakuDetails
            ->groupBy('bahan_baku_id')
            ->map(function ($baris) {
                $bahanBaku = $baris->first()->bahanBaku;
                return (object) [
                    'bahanBaku'   => $bahanBaku,
                    'total'       => $baris->sum(fn ($d) => (float) $d->jumlah_dipakai),
                    'jumlahBatch' => $baris->count(),
                ];
            })
            ->values();

        return view('pages.pemakaian-bahan-baku.show', [
            'pemakaian' => $pemakaianBahanBaku,
            'ringkasan' => $ringkasan,
        ]);
    }

    public function edit(PemakaianBahanBaku $pemakaianBahanBaku): View
    {
        $pemakaianBahanBaku->load('pemakaianBahanBakuDetails');

        $dipakaiIds = $pemakaianBahanBaku->pemakaianBahanBakuDetails->pluck('bahan_baku_id')->unique()->all();

        // Bahan baku aktif + bahan baku yang sudah tercatat di transaksi ini (walau kini nonaktif).
        $bahanBakus = BahanBaku::with('satuan')
            ->where(function (Builder $q) use ($dipakaiIds) {
                $q->where('is_aktif', true)->orWhereIn('id', $dipakaiIds);
            })
            ->orderBy('nama_bahan_baku')
            ->get();

        // Gabungkan baris per-batch kembali menjadi satu baris per bahan baku untuk form.
        $initialItems = $pemakaianBahanBaku->pemakaianBahanBakuDetails
            ->groupBy('bahan_baku_id')
            ->map(fn ($baris, $bahanBakuId) => [
                'bahan_baku_id'  => $bahanBakuId,
                'jumlah_dipakai' => number_format($baris->sum(fn ($d) => (float) $d->jumlah_dipakai), 2, '.', ''),
            ])
            ->values()
            ->all();

        return view('pages.pemakaian-bahan-baku.edit', [
            'pemakaian'    => $pemakaianBahanBaku,
            'bahanBakus'   => $bahanBakus,
            'initialItems' => $initialItems,
        ]);
    }

    public function update(Request $request, PemakaianBahanBaku $pemakaianBahanBaku): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->messages(), $this->attributes());

        try {
            DB::transaction(function () use ($data, $pemakaianBahanBaku) {
                // Kunci header agar tidak diedit bersamaan.
                $pemakaian = PemakaianBahanBaku::lockForUpdate()->findOrFail($pemakaianBahanBaku->id);

                // Bahan baku lama boleh tetap dipakai walau sekarang nonaktif.
                $bolehNonaktif = $pemakaian->pemakaianBahanBakuDetails()->pluck('bahan_baku_id')->unique()->all();

                // 1) Kembalikan stok lama ke batch asalnya, 2) alokasikan ulang dengan FEFO.
                $this->kembalikanStok($pemakaian);

                $pemakaian->update([
                    'tanggal_pakai' => $data['tanggal_pakai'],
                    'keterangan'    => $data['keterangan'] ?? null,
                ]);

                $this->alokasikanFefo($pemakaian, array_values($data['items']), $bolehNonaktif);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui pemakaian bahan baku.');
        }

        return redirect()
            ->route('pemakaian-bahan-baku.show', $pemakaianBahanBaku)
            ->with('success', "Pemakaian {$pemakaianBahanBaku->nomor_transaksi} berhasil diperbarui dan stok dialokasikan ulang (FEFO).");
    }

    public function destroy(PemakaianBahanBaku $pemakaianBahanBaku): RedirectResponse
    {
        $nomor = $pemakaianBahanBaku->nomor_transaksi;

        try {
            DB::transaction(function () use ($pemakaianBahanBaku) {
                $pemakaian = PemakaianBahanBaku::lockForUpdate()->findOrFail($pemakaianBahanBaku->id);

                $this->kembalikanStok($pemakaian); // stok kembali ke batch asal
                $pemakaian->delete();               // hard delete (sesuai rancangan: tanpa soft delete)
            });
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Pemakaian bahan baku gagal dihapus.');
        }

        return redirect()
            ->route('pemakaian-bahan-baku.index')
            ->with('success', "Pemakaian {$nomor} berhasil dihapus dan stok dikembalikan ke batch asal.");
    }

    // =====================================================================
    //  AJAX: daftar batch dalam urutan FEFO (untuk pratinjau di form)
    // =====================================================================

    public function batch(Request $request, BahanBaku $bahanBaku): JsonResponse
    {
        $pemakaianId = (int) $request->query('pemakaian_id');

        // Saat edit: jumlah yang sudah diambil transaksi ini dianggap "tersedia" lagi,
        // karena akan dikembalikan terlebih dulu sebelum dialokasikan ulang.
        $dipakaiTransaksiIni = collect();
        if ($pemakaianId) {
            $dipakaiTransaksiIni = PemakaianBahanBakuDetail::query()
                ->where('pemakaian_bahan_baku_id', $pemakaianId)
                ->where('bahan_baku_id', $bahanBaku->id)
                ->whereNotNull('penerimaan_bahan_baku_detail_id')
                ->selectRaw('penerimaan_bahan_baku_detail_id, SUM(jumlah_dipakai) as total')
                ->groupBy('penerimaan_bahan_baku_detail_id')
                ->pluck('total', 'penerimaan_bahan_baku_detail_id');
        }

        $batches = $this->queryFefo($bahanBaku->id, $dipakaiTransaksiIni->keys()->all())
            ->with('penerimaanBahanBaku.produsen')
            ->get();

        $totalSen = 0;
        $daftar = $batches->map(function (PenerimaanBahanBakuDetail $b) use ($dipakaiTransaksiIni, &$totalSen) {
            $tersediaSen = $this->keSen($b->jumlah_tersisa) + $this->keSen($dipakaiTransaksiIni[$b->id] ?? 0);
            $totalSen += $tersediaSen;

            $kadaluarsa = $b->tanggal_kadaluarsa;

            return [
                'id'               => $b->id,
                'nomor'            => $b->penerimaanBahanBaku?->nomor_transaksi,
                'produsen'         => $b->penerimaanBahanBaku?->produsen?->nama_produsen,
                'tanggal_terima'   => $b->penerimaanBahanBaku?->tanggal_terima?->format('d/m/Y'),
                'tanggal_kadaluarsa' => $kadaluarsa?->format('d/m/Y'),
                'kadaluarsa'       => $kadaluarsa ? $kadaluarsa->lt(today()) : false,
                'tersedia'         => $tersediaSen / 100,
            ];
        })->filter(fn ($b) => $b['tersedia'] > 0)->values();

        $bahanBaku->loadMissing('satuan');

        return response()->json([
            'satuan'  => $bahanBaku->satuan?->kode_satuan ?: $bahanBaku->satuan?->nama,
            'total'   => $totalSen / 100,
            'batches' => $daftar,
        ]);
    }

    // =====================================================================
    //  INTI FEFO
    // =====================================================================

    /**
     * Query batch penerimaan satu bahan baku dalam URUTAN FEFO.
     * Dipakai bersama oleh alokasi stok dan endpoint pratinjau supaya urutannya selalu identik.
     *
     * @param  array<int>  $sertakanId  batch yang sudah habis tetap dimunculkan (khusus pratinjau edit)
     */
    private function queryFefo(int $bahanBakuId, array $sertakanId = []): Builder
    {
        return PenerimaanBahanBakuDetail::query()
            ->select('penerimaan_bahan_baku_details.*')
            ->join(
                'penerimaan_bahan_bakus',
                'penerimaan_bahan_bakus.id',
                '=',
                'penerimaan_bahan_baku_details.penerimaan_bahan_baku_id'
            )
            ->where('penerimaan_bahan_baku_details.bahan_baku_id', $bahanBakuId)
            ->where(function (Builder $q) use ($sertakanId) {
                $q->where('penerimaan_bahan_baku_details.jumlah_tersisa', '>', 0);
                if ($sertakanId) {
                    $q->orWhereIn('penerimaan_bahan_baku_details.id', $sertakanId);
                }
            })
            ->orderBy('penerimaan_bahan_bakus.tanggal_terima')                              // 1. terima paling lama
            ->orderByRaw('penerimaan_bahan_baku_details.tanggal_kadaluarsa IS NULL')        // 2. kadaluarsa kosong paling akhir
            ->orderBy('penerimaan_bahan_baku_details.tanggal_kadaluarsa')                   //    kadaluarsa terdekat dulu
            ->orderBy('penerimaan_bahan_baku_details.id');                                  // 3. pemecah seri
    }

    /**
     * Kurangi stok batch sesuai urutan FEFO lalu catat satu baris detail per batch yang terpakai.
     * Harus dipanggil di dalam DB::transaction().
     *
     * @param  array<int, array{bahan_baku_id:int|string, jumlah_dipakai:int|float|string}>  $items
     * @param  array<int>  $bolehNonaktif  id bahan baku nonaktif yang masih boleh dipakai (saat edit)
     *
     * @throws ValidationException bila stok tidak cukup / bahan baku nonaktif
     */
    private function alokasikanFefo(PemakaianBahanBaku $pemakaian, array $items, array $bolehNonaktif = []): void
    {
        foreach ($items as $index => $item) {
            /** @var BahanBaku $bahanBaku */
            $bahanBaku = BahanBaku::with('satuan')->lockForUpdate()->findOrFail($item['bahan_baku_id']);

            if (! $bahanBaku->is_aktif && ! in_array($bahanBaku->id, $bolehNonaktif, true)) {
                throw ValidationException::withMessages([
                    "items.{$index}.bahan_baku_id" => "Bahan baku {$bahanBaku->nama_bahan_baku} sudah nonaktif dan tidak bisa dipakai.",
                ]);
            }

            $kebutuhanSen = $this->keSen($item['jumlah_dipakai']);

            // lockForUpdate: cegah dua admin mengambil batch yang sama pada saat bersamaan.
            $batches = $this->queryFefo($bahanBaku->id)->lockForUpdate()->get();

            $tersediaSen = $batches->sum(fn ($b) => $this->keSen($b->jumlah_tersisa));
            if ($kebutuhanSen > $tersediaSen) {
                $satuan = $bahanBaku->satuan?->kode_satuan ?: $bahanBaku->satuan?->nama;
                throw ValidationException::withMessages([
                    "items.{$index}.jumlah_dipakai" => sprintf(
                        'Stok %s tidak mencukupi. Tersedia %s %s, diminta %s %s.',
                        $bahanBaku->nama_bahan_baku,
                        $this->tampilkan($tersediaSen),
                        $satuan,
                        $this->tampilkan($kebutuhanSen),
                        $satuan
                    ),
                ]);
            }

            $sisaKebutuhan = $kebutuhanSen;

            foreach ($batches as $batch) {
                if ($sisaKebutuhan <= 0) {
                    break;
                }

                $tersisaSen = $this->keSen($batch->jumlah_tersisa);
                $ambilSen   = min($sisaKebutuhan, $tersisaSen);

                $batch->jumlah_tersisa = $this->dariSen($tersisaSen - $ambilSen);
                $batch->save();

                $pemakaian->pemakaianBahanBakuDetails()->create([
                    'bahan_baku_id'                   => $bahanBaku->id,
                    'penerimaan_bahan_baku_detail_id' => $batch->id,
                    'jumlah_dipakai'                  => $this->dariSen($ambilSen),
                ]);

                $sisaKebutuhan -= $ambilSen;
            }

            $this->sinkronStok($bahanBaku->id);
        }
    }

    /**
     * Balikkan semua pemakaian suatu transaksi: jumlah_tersisa batch asal ditambah kembali,
     * baris detail dihapus, lalu cache stok bahan baku dihitung ulang.
     */
    private function kembalikanStok(PemakaianBahanBaku $pemakaian): void
    {
        $details = $pemakaian->pemakaianBahanBakuDetails()->get();

        foreach ($details as $detail) {
            if (! $detail->penerimaan_bahan_baku_detail_id) {
                continue; // baris lama tanpa batch: tidak ada yang bisa dikembalikan
            }

            $batch = PenerimaanBahanBakuDetail::lockForUpdate()->find($detail->penerimaan_bahan_baku_detail_id);
            if (! $batch) {
                continue;
            }

            $baru = $this->keSen($batch->jumlah_tersisa) + $this->keSen($detail->jumlah_dipakai);
            $baru = min($baru, $this->keSen($batch->jumlah_diterima)); // jangan melebihi jumlah diterima

            $batch->jumlah_tersisa = $this->dariSen($baru);
            $batch->save();
        }

        $bahanBakuIds = $details->pluck('bahan_baku_id')->unique();

        $pemakaian->pemakaianBahanBakuDetails()->delete();

        foreach ($bahanBakuIds as $id) {
            $this->sinkronStok($id);
        }
    }

    /**
     * bahan_bakus.stok_saat_ini adalah cache = SUM(jumlah_tersisa) seluruh batch bahan baku tersebut.
     */
    private function sinkronStok(int $bahanBakuId): void
    {
        $total = PenerimaanBahanBakuDetail::where('bahan_baku_id', $bahanBakuId)->sum('jumlah_tersisa');

        BahanBaku::whereKey($bahanBakuId)->update([
            'stok_saat_ini' => round((float) $total, 2),
        ]);
    }

    // =====================================================================
    //  Helper
    // =====================================================================

    /** Format nomor: PMK-ddmmYYYY-001 (urut per tanggal pakai), sejalan dengan PNB-ddmmYYYY-001 pada penerimaan. */
    private function nomorBaru(string $tanggalPakai): string
    {
        $prefix = 'PMK-' . Carbon::parse($tanggalPakai)->format('dmY') . '-';

        $terakhir = PemakaianBahanBaku::where('nomor_transaksi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $urut = $terakhir ? ((int) substr($terakhir, -3)) + 1 : 1;

        return $prefix . str_pad((string) $urut, 3, '0', STR_PAD_LEFT);
    }

    /** Hitung dalam satuan 1/100 (integer) agar bebas error pembulatan float untuk DECIMAL(10,2). */
    private function keSen(int|float|string|null $nilai): int
    {
        return (int) round(((float) $nilai) * 100);
    }

    private function dariSen(int $sen): string
    {
        return number_format($sen / 100, 2, '.', '');
    }

    private function tampilkan(int $sen): string
    {
        return rtrim(rtrim(number_format($sen / 100, 2, ',', '.'), '0'), ',');
    }

    private function rules(): array
    {
        return [
            'tanggal_pakai'          => ['required', 'date', 'before_or_equal:today'],
            'keterangan'             => ['nullable', 'string', 'max:255'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.bahan_baku_id'  => ['required', 'integer', 'distinct', 'exists:bahan_bakus,id'],
            'items.*.jumlah_dipakai' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
        ];
    }

    private function messages(): array
    {
        return [
            'items.required'                  => 'Tambahkan minimal satu bahan baku yang dipakai.',
            'items.min'                       => 'Tambahkan minimal satu bahan baku yang dipakai.',
            'items.*.bahan_baku_id.required'  => 'Pilih bahan baku.',
            'items.*.bahan_baku_id.distinct'  => 'Bahan baku ini sudah dipilih di baris lain. Gabungkan jumlahnya dalam satu baris.',
            'items.*.bahan_baku_id.exists'    => 'Bahan baku tidak ditemukan.',
            'items.*.jumlah_dipakai.required' => 'Jumlah dipakai wajib diisi.',
            'items.*.jumlah_dipakai.numeric'  => 'Jumlah dipakai harus berupa angka.',
            'items.*.jumlah_dipakai.gt'       => 'Jumlah dipakai harus lebih besar dari 0.',
            'tanggal_pakai.before_or_equal'   => 'Tanggal pakai tidak boleh melebihi hari ini.',
        ];
    }

    private function attributes(): array
    {
        return [
            'tanggal_pakai'          => 'tanggal pakai',
            'keterangan'             => 'keterangan',
            'items.*.bahan_baku_id'  => 'bahan baku',
            'items.*.jumlah_dipakai' => 'jumlah dipakai',
        ];
    }
}
