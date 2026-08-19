<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriBarangController extends Controller
{
    /**
     * Tampilkan daftar kategori barang.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $kategoriBarangs = KategoriBarang::withCount('bahanBakus')
            ->when($search, function ($query) use ($search) {
                $query->where('nama_kategori', 'like', "%{$search}%");
            })
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        $totalKategori = KategoriBarang::count();
        $kategoriTerpakai = KategoriBarang::has('bahanBakus')->count();
        $kategoriBelumTerpakai = $totalKategori - $kategoriTerpakai;

        return view('pages.kategori.index', compact(
            'kategoriBarangs',
            'search',
            'totalKategori',
            'kategoriTerpakai',
            'kategoriBelumTerpakai',
        ));
    }

    /**
     * Tampilkan form tambah kategori barang.
     */
    public function create(): View
    {
        return view('pages.kategori.create');
    }

    /**
     * Simpan kategori barang baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100', 'unique:kategori_barangs,nama_kategori'],
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah digunakan, silakan gunakan nama lain.',
        ]);

        KategoriBarang::create($validated);

        return redirect()->route('kategori-barang.index')
            ->with('success', 'Kategori barang berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit kategori barang.
     */
    public function edit(KategoriBarang $kategoriBarang): View
    {
        return view('pages.kategori.edit', compact('kategoriBarang'));
    }

    /**
     * Perbarui kategori barang.
     */
    public function update(Request $request, KategoriBarang $kategoriBarang): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kategori' => [
                'required', 'string', 'max:100',
                'unique:kategori_barangs,nama_kategori,' . $kategoriBarang->id,
            ],
        ], [
            'nama_kategori.unique' => 'Nama kategori sudah digunakan, silakan gunakan nama lain.',
        ]);

        $kategoriBarang->update($validated);

        return redirect()->route('kategori-barang.index')
            ->with('success', 'Kategori barang berhasil diperbarui.');
    }

    /**
     * Hapus kategori barang.
     */
    public function destroy(KategoriBarang $kategoriBarang): RedirectResponse
    {
        if ($kategoriBarang->bahanBakus()->exists()) {
            return redirect()->route('kategori-barang.index')
                ->with('error', 'Kategori "' . $kategoriBarang->nama_kategori . '" tidak bisa dihapus karena masih dipakai oleh data bahan baku.');
        }

        $kategoriBarang->delete();

        return redirect()->route('kategori-barang.index')
            ->with('success', 'Kategori barang berhasil dihapus.');
    }
}
