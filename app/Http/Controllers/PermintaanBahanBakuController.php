<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\BahanBakuProdusen;
use App\Models\PermintaanBahanBaku;
use App\Models\Produsen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PermintaanBahanBakuController extends Controller
{
    /**
     * Fitur ini khusus untuk ADMIN (belum termasuk respons produsen / pengalihan).
     *
     * Catatan: proteksi role admin sudah dihandle oleh middleware group
     * ['auth', 'role:admin'] di routes/web.php, jadi tidak perlu didaftarkan
     * lagi di sini. (Laravel 11+ juga sudah tidak punya method
     * $this->middleware() di controller biasa — kalau mau tetap didaftarkan
     * di controller, harus implement interface Illuminate\Routing\Controllers\HasMiddleware
     * dengan static method middleware().)
     */

    /**
     * Daftar semua permintaan bahan baku.
     */
    public function index(Request $request)
    {
        $permintaans = PermintaanBahanBaku::with(['admin', 'permintaanBahanBakuDetails'])
            ->withCount('permintaanBahanBakuDetails as jumlah_item')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('nomor_transaksi', 'like', '%' . $request->search . '%');
            })
            ->orderByDesc('tanggal_permintaan')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.permintaanbahanbaku.index', compact('permintaans'));
    }

    /**
     * Form "Permintaan Baru".
     */
    public function create()
    {
        $bahanBakus = BahanBaku::where('is_aktif', true)
            ->orderBy('nama_bahan_baku')
            ->get(['id', 'kode_bahan_baku', 'nama_bahan_baku', 'satuan_id'])
            ->load('satuan');

        return view('pages.permintaanbahanbaku.create', compact('bahanBakus'));
    }

    /**
     * Endpoint AJAX: ambil daftar produsen yang memasok sebuah bahan baku,
     * lengkap dengan harga (snapshot dari pivot bahan_baku_produsens).
     * Hanya produsen dengan status_mitra aktif yang ditampilkan.
     */
    public function produsenByBahanBaku(BahanBaku $bahanBaku)
    {
        $produsens = $bahanBaku->produsens()
            ->where('status_mitra', 'aktif')
            ->get()
            ->map(function ($produsen) {
                return [
                    'id' => $produsen->id,
                    'nama_produsen' => $produsen->nama_produsen,
                    'harga' => (float) $produsen->pivot->harga,
                ];
            })
            ->values();

        return response()->json($produsens);
    }

    /**
     * Simpan permintaan baru (header + detail item).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_permintaan' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.bahan_baku_id' => ['required', 'integer', 'exists:bahan_bakus,id'],
            'items.*.produsen_id' => ['required', 'integer', 'exists:produsens,id'],
            'items.*.jumlah_diminta' => ['required', 'numeric', 'min:0.01'],
        ], [
            'items.required' => 'Minimal harus ada 1 item bahan baku yang diminta.',
            'items.min' => 'Minimal harus ada 1 item bahan baku yang diminta.',
            'items.*.bahan_baku_id.required' => 'Bahan baku pada salah satu baris belum dipilih.',
            'items.*.produsen_id.required' => 'Produsen pada salah satu baris belum dipilih.',
            'items.*.jumlah_diminta.required' => 'Jumlah diminta pada salah satu baris belum diisi.',
            'items.*.jumlah_diminta.min' => 'Jumlah diminta harus lebih besar dari 0.',
        ]);

        // --- Cegah kombinasi (bahan_baku_id, produsen_id) duplikat dalam satu permintaan ---
        $combos = collect($validated['items'])
            ->map(fn($item) => $item['bahan_baku_id'] . '-' . $item['produsen_id']);

        if ($combos->count() !== $combos->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Kombinasi bahan baku dan produsen tidak boleh duplikat dalam satu permintaan yang sama.',
            ]);
        }

        // --- Validasi bisnis per baris: aktif, relasi pemasok, dan ambil snapshot harga ---
        $itemsToInsert = [];

        foreach ($validated['items'] as $item) {
            $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
            $produsen = Produsen::find($item['produsen_id']);

            if (! $bahanBaku || ! $bahanBaku->is_aktif) {
                throw ValidationException::withMessages([
                    'items' => "Bahan baku \"{$bahanBaku?->nama_bahan_baku}\" tidak aktif dan tidak bisa diminta.",
                ]);
            }

            if (! $produsen || $produsen->status_mitra !== 'aktif') {
                throw ValidationException::withMessages([
                    'items' => "Produsen \"{$produsen?->nama_produsen}\" tidak aktif sebagai mitra.",
                ]);
            }

            $pivot = BahanBakuProdusen::where('bahan_baku_id', $bahanBaku->id)
                ->where('produsen_id', $produsen->id)
                ->first();

            if (! $pivot) {
                throw ValidationException::withMessages([
                    'items' => "Produsen \"{$produsen->nama_produsen}\" tidak memasok bahan baku \"{$bahanBaku->nama_bahan_baku}\".",
                ]);
            }

            $itemsToInsert[] = [
                'bahan_baku_id' => $bahanBaku->id,
                'produsen_id' => $produsen->id,
                // Snapshot harga saat permintaan dibuat, BUKAN referensi live ke pivot.
                'harga_satuan' => $pivot->harga,
                'jumlah_diminta' => $item['jumlah_diminta'],
                'jumlah_disetujui' => null,
                'status_produsen' => 'pending',
            ];
        }

        $admin = auth()->user()->admin;

        if (! $admin) {
            throw ValidationException::withMessages([
                'items' => 'Akun anda tidak terdaftar sebagai admin. Hubungi pemilik untuk memperbaiki data akun.',
            ]);
        }

        $permintaan = DB::transaction(function () use ($validated, $itemsToInsert, $admin) {
            $nomorTransaksi = $this->generateNomorTransaksi();

            $permintaan = PermintaanBahanBaku::create([
                'admin_id' => $admin->id,
                'nomor_transaksi' => $nomorTransaksi,
                'tanggal_permintaan' => $validated['tanggal_permintaan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            foreach ($itemsToInsert as $item) {
                $permintaan->permintaanBahanBakuDetails()->create($item);
            }

            return $permintaan;
        });

        return redirect()
            ->route('permintaan-bahan-baku.show', $permintaan->id)
            ->with('success', "Permintaan bahan baku berhasil dibuat dengan nomor {$permintaan->nomor_transaksi}.");
    }

    /**
     * Generate nomor_transaksi format PBB-DDMMYYYY-XXX, reset harian.
     * Dijalankan di dalam DB transaction pemanggilnya, dengan lockForUpdate
     * untuk mencegah race condition saat 2 admin submit bersamaan.
     */
    private function generateNomorTransaksi(): string
    {
        $tanggalStr = now()->format('dmY');
        $prefix = "PBB-{$tanggalStr}-";

        $jumlahHariIni = PermintaanBahanBaku::where('nomor_transaksi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->count();

        $nomorUrut = str_pad((string) ($jumlahHariIni + 1), 3, '0', STR_PAD_LEFT);

        return $prefix . $nomorUrut;
    }

    /**
     * Detail satu permintaan.
     */
    public function show(PermintaanBahanBaku $permintaanBahanBaku)
    {
        $permintaanBahanBaku->load([
            'admin',
            'permintaanBahanBakuDetails.bahanBaku.satuan',
            'permintaanBahanBakuDetails.produsen',
        ]);

        return view('pages.permintaanbahanbaku.show', [
            'permintaan' => $permintaanBahanBaku,
        ]);
    }

    /**
     * Form edit.
     * - Header (tanggal_permintaan, keterangan) selalu bisa diubah.
     * - Item dengan status_produsen = 'pending' BISA diubah/dihapus, dan boleh
     *   tambah item baru (pending juga).
     * - Item dengan status_produsen != 'pending' (sudah direspons produsen)
     *   DIKUNCI — hanya ditampilkan sebagai referensi, tidak bisa diubah/dihapus.
     */
    public function edit(PermintaanBahanBaku $permintaanBahanBaku)
    {
        $permintaanBahanBaku->load(['permintaanBahanBakuDetails.bahanBaku.satuan', 'permintaanBahanBakuDetails.produsen']);

        $lockedItems = $permintaanBahanBaku->permintaanBahanBakuDetails
            ->where('status_produsen', '!=', 'pending')
            ->values();

        $editableItems = $permintaanBahanBaku->permintaanBahanBakuDetails
            ->where('status_produsen', 'pending')
            ->values();

        // Dropdown bahan baku di baris item harus tetap memuat bahan baku yang sedang
        // dipakai di item pending, walaupun bahan baku itu sekarang sudah non-aktif —
        // supaya baris lama tidak tiba-tiba kehilangan pilihannya.
        $bahanBakusForEdit = BahanBaku::where('is_aktif', true)
            ->orWhereIn('id', $editableItems->pluck('bahan_baku_id'))
            ->orderBy('nama_bahan_baku')
            ->get(['id', 'kode_bahan_baku', 'nama_bahan_baku'])
            ->map(fn($b) => [
                'id' => $b->id,
                'label' => $b->kode_bahan_baku . ' - ' . $b->nama_bahan_baku,
            ]);

        return view('pages.permintaanbahanbaku.edit', [
            'permintaan' => $permintaanBahanBaku,
            'lockedItems' => $lockedItems,
            'editableItems' => $editableItems,
            'bahanBakusForEdit' => $bahanBakusForEdit,
        ]);
    }

    public function update(Request $request, PermintaanBahanBaku $permintaanBahanBaku)
    {
        // id detail yang saat ini masih pending & boleh diedit/dihapus lewat form ini.
        $editableIds = $permintaanBahanBaku->permintaanBahanBakuDetails()
            ->where('status_produsen', 'pending')
            ->pluck('id')
            ->all();

        // id detail yang sudah direspons produsen — tidak boleh disentuh sama sekali.
        $lockedItems = $permintaanBahanBaku->permintaanBahanBakuDetails()
            ->where('status_produsen', '!=', 'pending')
            ->get(['bahan_baku_id', 'produsen_id']);

        $validated = $request->validate([
            'tanggal_permintaan' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.bahan_baku_id' => ['required_with:items.*.produsen_id,items.*.jumlah_diminta', 'integer', 'exists:bahan_bakus,id'],
            'items.*.produsen_id' => ['required_with:items.*.bahan_baku_id', 'integer', 'exists:produsens,id'],
            'items.*.jumlah_diminta' => ['required_with:items.*.bahan_baku_id', 'numeric', 'min:0.01'],
        ], [
            'items.*.jumlah_diminta.min' => 'Jumlah diminta harus lebih besar dari 0.',
        ]);

        $incomingItems = $validated['items'] ?? [];

        // Minimal harus ada 1 item total (item terkunci + item hasil edit/baru).
        if ($lockedItems->isEmpty() && count($incomingItems) === 0) {
            throw ValidationException::withMessages([
                'items' => 'Minimal harus ada 1 item bahan baku pada permintaan ini.',
            ]);
        }

        // --- Cegah kombinasi (bahan_baku_id, produsen_id) duplikat, gabungan locked + incoming ---
        $combos = $lockedItems
            ->map(fn($d) => $d->bahan_baku_id . '-' . $d->produsen_id)
            ->concat(collect($incomingItems)->map(fn($i) => $i['bahan_baku_id'] . '-' . $i['produsen_id']));

        if ($combos->count() !== $combos->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Kombinasi bahan baku dan produsen tidak boleh duplikat dalam satu permintaan yang sama.',
            ]);
        }

        // --- Validasi bisnis tiap baris yang dikirim (item pending yang diedit + item baru) ---
        $itemsToSave = [];

        foreach ($incomingItems as $item) {
            $detailId = $item['id'] ?? null;

            // Kalau baris ini punya id tapi id-nya bukan milik item pending permintaan ini,
            // berarti ada percobaan mengubah item yang sudah dikunci -> tolak.
            if ($detailId && ! in_array($detailId, $editableIds, true)) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu item yang dikirim sudah tidak bisa diubah karena sudah direspons produsen.',
                ]);
            }

            $bahanBaku = BahanBaku::find($item['bahan_baku_id']);
            $produsen = Produsen::find($item['produsen_id']);

            if (! $bahanBaku || ! $bahanBaku->is_aktif) {
                throw ValidationException::withMessages([
                    'items' => "Bahan baku \"{$bahanBaku?->nama_bahan_baku}\" tidak aktif dan tidak bisa diminta.",
                ]);
            }

            if (! $produsen || $produsen->status_mitra !== 'aktif') {
                throw ValidationException::withMessages([
                    'items' => "Produsen \"{$produsen?->nama_produsen}\" tidak aktif sebagai mitra.",
                ]);
            }

            $pivot = BahanBakuProdusen::where('bahan_baku_id', $bahanBaku->id)
                ->where('produsen_id', $produsen->id)
                ->first();

            if (! $pivot) {
                throw ValidationException::withMessages([
                    'items' => "Produsen \"{$produsen->nama_produsen}\" tidak memasok bahan baku \"{$bahanBaku->nama_bahan_baku}\".",
                ]);
            }

            $itemsToSave[] = [
                'id' => $detailId,
                'bahan_baku_id' => $bahanBaku->id,
                'produsen_id' => $produsen->id,
                // Re-snapshot harga terbaru dari pivot pada saat diedit.
                'harga_satuan' => $pivot->harga,
                'jumlah_diminta' => $item['jumlah_diminta'],
            ];
        }

        DB::transaction(function () use ($permintaanBahanBaku, $validated, $itemsToSave, $editableIds) {
            $permintaanBahanBaku->update([
                'tanggal_permintaan' => $validated['tanggal_permintaan'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            $keptIds = [];

            foreach ($itemsToSave as $item) {
                if ($item['id']) {
                    $permintaanBahanBaku->permintaanBahanBakuDetails()
                        ->where('id', $item['id'])
                        ->update([
                            'bahan_baku_id' => $item['bahan_baku_id'],
                            'produsen_id' => $item['produsen_id'],
                            'harga_satuan' => $item['harga_satuan'],
                            'jumlah_diminta' => $item['jumlah_diminta'],
                        ]);
                    $keptIds[] = $item['id'];
                } else {
                    $new = $permintaanBahanBaku->permintaanBahanBakuDetails()->create([
                        'bahan_baku_id' => $item['bahan_baku_id'],
                        'produsen_id' => $item['produsen_id'],
                        'harga_satuan' => $item['harga_satuan'],
                        'jumlah_diminta' => $item['jumlah_diminta'],
                        'jumlah_disetujui' => null,
                        'status_produsen' => 'pending',
                    ]);
                    $keptIds[] = $new->id;
                }
            }

            // Item pending yang tadinya ada tapi tidak dikirim lagi dari form -> dianggap dihapus admin.
            $idsToDelete = array_diff($editableIds, $keptIds);
            if (! empty($idsToDelete)) {
                $permintaanBahanBaku->permintaanBahanBakuDetails()->whereIn('id', $idsToDelete)->delete();
            }
        });

        return redirect()
            ->route('permintaan-bahan-baku.show', $permintaanBahanBaku->id)
            ->with('success', 'Permintaan bahan baku berhasil diperbarui.');
    }

    /**
     * Hapus permintaan — hanya diperbolehkan jika SEMUA item masih 'pending'
     * (belum ada respons dari produsen manapun).
     */
    public function destroy(PermintaanBahanBaku $permintaanBahanBaku)
    {
        $sudahDirespons = $permintaanBahanBaku->permintaanBahanBakuDetails()
            ->where('status_produsen', '!=', 'pending')
            ->exists();

        if ($sudahDirespons) {
            return back()->with('error', 'Permintaan tidak bisa dihapus karena sudah ada item yang direspons produsen.');
        }

        $permintaanBahanBaku->permintaanBahanBakuDetails()->delete();
        $permintaanBahanBaku->delete();

        return redirect()
            ->route('permintaan-bahan-baku.index')
            ->with('success', 'Permintaan bahan baku berhasil dihapus.');
    }
}
