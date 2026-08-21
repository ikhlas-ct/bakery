<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\BahanBakuProdusen;
use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BahanBakuProdusenController extends Controller
{
    /**
     * Ambil data produsen milik user yang sedang login.
     * Semua query di controller ini SELALU di-scope ke produsen ini,
     * supaya satu produsen tidak bisa mengubah/menghapus data produsen lain.
     */
    private function produsenAktif()
    {
        return Auth::user()->produsen;
    }

    /**
     * Daftar bahan baku yang sudah dipilih/disediakan oleh produsen yang login.
     */
    public function index(Request $request): View
    {
        $produsen = $this->produsenAktif();

        $query = BahanBakuProdusen::with(['bahanBaku.kategoriBarang', 'bahanBaku.satuan'])
            ->where('produsen_id', $produsen->id);

        if ($search = $request->input('search')) {
            $query->whereHas('bahanBaku', function ($q) use ($search) {
                $q->where('nama_bahan_baku', 'like', "%{$search}%")
                    ->orWhere('kode_bahan_baku', 'like', "%{$search}%");
            });
        }

        if ($kategoriId = $request->input('kategori_barang_id')) {
            $query->whereHas('bahanBaku', function ($q) use ($kategoriId) {
                $q->where('kategori_barang_id', $kategoriId);
            });
        }

        $bahanBakuProdusens = $query->latest()->paginate(10)->withQueryString();

        $totalDisediakan = BahanBakuProdusen::where('produsen_id', $produsen->id)->count();
        $rataRataHarga = BahanBakuProdusen::where('produsen_id', $produsen->id)->avg('harga');
        $totalKategori = BahanBaku::whereHas('produsens', function ($q) use ($produsen) {
            $q->where('produsen_id', $produsen->id);
        })->distinct('kategori_barang_id')->count('kategori_barang_id');

        $kategoris = KategoriBarang::orderBy('nama_kategori')->get();

        return view('pages.produsenbahanbaku.index', compact(
            'bahanBakuProdusens',
            'totalDisediakan',
            'rataRataHarga',
            'totalKategori',
            'kategoris'
        ));
    }

    /**
     * Form pilih bahan baku baru yang bisa disediakan produsen ini.
     * Hanya menampilkan bahan baku aktif yang BELUM pernah dipilih produsen ini.
     */
    public function create(): View
    {
        $produsen = $this->produsenAktif();

        $sudahDipilih = BahanBakuProdusen::where('produsen_id', $produsen->id)->pluck('bahan_baku_id');

        $bahanBakus = BahanBaku::with(['kategoriBarang', 'satuan'])
            ->where('is_aktif', true)
            ->whereNotIn('id', $sudahDipilih)
            ->orderBy('nama_bahan_baku')
            ->get();

        return view('pages.produsenbahanbaku.create', compact('bahanBakus'));
    }

    public function store(Request $request): RedirectResponse
    {
        $produsen = $this->produsenAktif();

        $validated = $request->validate([
            'bahan_baku_id' => [
                'required',
                'exists:bahan_bakus,id',
                Rule::unique('bahan_baku_produsens')->where(
                    fn ($q) => $q->where('produsen_id', $produsen->id)
                ),
            ],
            'harga' => ['required', 'numeric', 'min:0'],
        ], [
            'bahan_baku_id.unique' => 'Bahan baku ini sudah ada di daftar yang Anda sediakan.',
        ]);

        BahanBakuProdusen::create([
            'bahan_baku_id' => $validated['bahan_baku_id'],
            'produsen_id' => $produsen->id,
            'harga' => $validated['harga'],
        ]);

        return redirect()
            ->route('produsen.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil ditambahkan ke daftar yang Anda sediakan.');
    }

    /**
     * Form ubah harga. Bahan baku & produsen tidak bisa diganti di sini,
     * hanya harga per satuan yang bisa diperbarui.
     */
    public function edit(int $id): View
    {
        $produsen = $this->produsenAktif();

        $bahanBakuProdusen = BahanBakuProdusen::with(['bahanBaku.kategoriBarang', 'bahanBaku.satuan'])
            ->where('produsen_id', $produsen->id)
            ->findOrFail($id);

        return view('pages.produsenbahanbaku.edit', compact('bahanBakuProdusen'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $produsen = $this->produsenAktif();

        $bahanBakuProdusen = BahanBakuProdusen::where('produsen_id', $produsen->id)->findOrFail($id);

        $validated = $request->validate([
            'harga' => ['required', 'numeric', 'min:0'],
        ]);

        $bahanBakuProdusen->update($validated);

        return redirect()
            ->route('produsen.bahan-baku.index')
            ->with('success', 'Harga bahan baku berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $produsen = $this->produsenAktif();

        $bahanBakuProdusen = BahanBakuProdusen::where('produsen_id', $produsen->id)->findOrFail($id);
        $bahanBakuProdusen->delete();

        return redirect()
            ->route('produsen.bahan-baku.index')
            ->with('success', 'Bahan baku berhasil dihapus dari daftar yang Anda sediakan.');
    }
}
