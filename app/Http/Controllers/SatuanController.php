<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatuanController extends Controller
{
    /**
     * Tampilkan daftar satuan.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $satuans = Satuan::withCount('bahanBakus')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_satuan', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $totalSatuan = Satuan::count();
        $satuanTerpakai = Satuan::has('bahanBakus')->count();
        $satuanBelumTerpakai = $totalSatuan - $satuanTerpakai;

        return view('pages.satuan.index', compact(
            'satuans',
            'search',
            'totalSatuan',
            'satuanTerpakai',
            'satuanBelumTerpakai',
        ));
    }

    /**
     * Tampilkan form tambah satuan.
     */
    public function create(): View
    {
        return view('pages.satuan.create');
    }

    /**
     * Simpan satuan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:50', 'unique:satuans,nama'],
            'kode_satuan' => ['nullable', 'string', 'max:20'],
        ], [
            'nama.unique' => 'Nama satuan sudah digunakan, silakan gunakan nama lain.',
        ]);

        Satuan::create($validated);

        return redirect()->route('satuan.index')
            ->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit satuan.
     */
    public function edit(Satuan $satuan): View
    {
        return view('pages.satuan.edit', compact('satuan'));
    }

    /**
     * Perbarui satuan.
     */
    public function update(Request $request, Satuan $satuan): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required', 'string', 'max:50',
                'unique:satuans,nama,' . $satuan->id,
            ],
            'kode_satuan' => ['nullable', 'string', 'max:20'],
        ], [
            'nama.unique' => 'Nama satuan sudah digunakan, silakan gunakan nama lain.',
        ]);

        $satuan->update($validated);

        return redirect()->route('satuan.index')
            ->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Hapus satuan.
     */
    public function destroy(Satuan $satuan): RedirectResponse
    {
        if ($satuan->bahanBakus()->exists()) {
            return redirect()->route('satuan.index')
                ->with('error', 'Satuan "' . $satuan->nama . '" tidak bisa dihapus karena masih dipakai oleh data bahan baku.');
        }

        $satuan->delete();

        return redirect()->route('satuan.index')
            ->with('success', 'Satuan berhasil dihapus.');
    }
}
