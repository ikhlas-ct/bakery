<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBarang;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BahanBakuController extends Controller
{
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategoriBarang', 'satuan']);

        // ── Pencarian (kode / nama) ──
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nama_bahan_baku', 'like', "%{$search}%");
            });
        }

        // ── Filter kategori ──
        if ($request->filled('kategori_barang_id')) {
            $query->where('kategori_barang_id', $request->kategori_barang_id);
        }

        // ── Filter status aktif/nonaktif ──
        if ($request->filled('status')) {
            $query->where('is_aktif', $request->status === 'aktif' ? 1 : 0);
        }

        // ── Filter stok kritis (stok saat ini <= stok minimum) ──
        if ($request->filled('stok') && $request->stok === 'kritis') {
            $query->whereColumn('stok_saat_ini', '<=', 'stok_minimum');
        }

        $bahanBakus = $query->orderBy('nama_bahan_baku')
            ->paginate(10)
            ->withQueryString();

        $kategoris = KategoriBarang::orderBy('nama_kategori')->get();

        $totalBahanBaku   = BahanBaku::count();
        $bahanBakuAktif   = BahanBaku::where('is_aktif', true)->count();
        $bahanBakuKritis  = BahanBaku::whereColumn('stok_saat_ini', '<=', 'stok_minimum')->count();

        return view('pages.bahanbaku.index', compact(
            'bahanBakus',
            'kategoris',
            'totalBahanBaku',
            'bahanBakuAktif',
            'bahanBakuKritis'
        ));
    }

    public function create()
    {
        $kategoris = KategoriBarang::orderBy('nama_kategori')->get();
        $satuans   = Satuan::orderBy('nama')->get();

        return view('pages.bahanbaku.create', compact('kategoris', 'satuans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_bahan_baku'    => 'required|string|max:20|unique:bahan_bakus,kode_bahan_baku',
            'nama_bahan_baku'    => 'required|string|max:100',
            'kategori_barang_id' => 'required|exists:kategori_barangs,id',
            'satuan_id'          => 'required|exists:satuans,id',
            'stok_minimum'       => 'required|numeric|min:0',
            'is_aktif'           => 'nullable|boolean',
        ], [
            'kode_bahan_baku.unique' => 'Kode bahan baku sudah dipakai, gunakan kode lain.',
        ]);

        $validated['is_aktif']    = $request->boolean('is_aktif');
        $validated['stok_saat_ini'] = 0; // stok awal, disinkronkan otomatis lewat penerimaan bahan baku

        BahanBaku::create($validated);

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku "' . $validated['nama_bahan_baku'] . '" berhasil ditambahkan.');
    }

    public function edit(BahanBaku $bahanBaku)
    {
        $kategoris = KategoriBarang::orderBy('nama_kategori')->get();
        $satuans   = Satuan::orderBy('nama')->get();

        return view('pages.bahanbaku.edit', compact('bahanBaku', 'kategoris', 'satuans'));
    }

    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $validated = $request->validate([
            'kode_bahan_baku'    => [
                'required', 'string', 'max:20',
                Rule::unique('bahan_bakus', 'kode_bahan_baku')->ignore($bahanBaku->id),
            ],
            'nama_bahan_baku'    => 'required|string|max:100',
            'kategori_barang_id' => 'required|exists:kategori_barangs,id',
            'satuan_id'          => 'required|exists:satuans,id',
            'stok_minimum'       => 'required|numeric|min:0',
            'is_aktif'           => 'nullable|boolean',
        ], [
            'kode_bahan_baku.unique' => 'Kode bahan baku sudah dipakai, gunakan kode lain.',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif');

        $bahanBaku->update($validated);

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku "' . $bahanBaku->nama_bahan_baku . '" berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahanBaku)
    {
        // Cegah hapus jika bahan baku masih terpakai di relasi lain (FK restrict)
        if (
            $bahanBaku->bahanBakuProdusens()->exists() ||
            $bahanBaku->permintaanBahanBakuDetails()->exists() ||
            $bahanBaku->penerimaanBahanBakuDetails()->exists() ||
            $bahanBaku->pemakaianBahanBakuDetails()->exists()
        ) {
            return redirect()->route('bahan-baku.index')
                ->with('error', 'Bahan baku "' . $bahanBaku->nama_bahan_baku . '" tidak bisa dihapus karena masih memiliki data terkait (produsen, permintaan, penerimaan, atau pemakaian).');
        }

        $nama = $bahanBaku->nama_bahan_baku;
        $bahanBaku->delete();

        return redirect()->route('bahan-baku.index')
            ->with('success', 'Bahan baku "' . $nama . '" berhasil dihapus.');
    }
}
