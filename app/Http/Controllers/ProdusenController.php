<?php

namespace App\Http\Controllers;

use App\Models\Produsen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProdusenController extends Controller
{
    /**
     * Prefix route dinamis sesuai role user yang login (admin / pemilik),
     * supaya satu controller + satu set view bisa dipakai oleh dua role sekaligus.
     */
    protected function routePrefix(): string
    {
        return auth()->user()->role . '.produsen';
    }

    protected function dashboardRoute(): string
    {
        return auth()->user()->role . '.dashboard';
    }

    public function index(Request $request)
    {
        $query = Produsen::with('user');

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produsen', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->status) {
            $query->where('status_mitra', $status);
        }

        $produsens = $query->latest('id')->paginate(10)->withQueryString();

        $totalProdusen     = Produsen::count();
        $produsenAktif     = Produsen::where('status_mitra', 'aktif')->count();
        $produsenNonaktif  = Produsen::where('status_mitra', 'nonaktif')->count();
        $produsenBaruBulan = Produsen::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('pages.produsen.index', [
            'produsens'         => $produsens,
            'totalProdusen'     => $totalProdusen,
            'produsenAktif'     => $produsenAktif,
            'produsenNonaktif'  => $produsenNonaktif,
            'produsenBaruBulan' => $produsenBaruBulan,
            'routePrefix'       => $this->routePrefix(),
            'dashboardRoute'    => $this->dashboardRoute(),
        ]);
    }

    public function create()
    {
        return view('pages.produsen.create', [
            'routePrefix'    => $this->routePrefix(),
            'dashboardRoute' => $this->dashboardRoute(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'      => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'         => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'is_active'     => ['nullable', 'boolean'],
            'nama_produsen' => ['required', 'string', 'max:100'],
            'alamat'        => ['required', 'string', 'max:255'],
            'no_telp'       => ['required', 'string', 'max:20'],
            'status_mitra'  => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto_profil'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            $fotoPath = null;
            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto_profil', 'public');
            }

            $user = User::create([
                'username'    => $validated['username'],
                'email'       => $validated['email'],
                'password'    => $validated['password'],
                'role'        => 'produsen',
                'is_active'   => $request->boolean('is_active'),
                'foto_profil' => $fotoPath,
            ]);

            Produsen::create([
                'user_id'       => $user->id,
                'nama_produsen' => $validated['nama_produsen'],
                'alamat'        => $validated['alamat'],
                'no_telp'       => $validated['no_telp'],
                'status_mitra'  => $validated['status_mitra'],
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($fotoPath) && $fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            return back()->withInput()->with('error', 'Gagal menyimpan data produsen: ' . $e->getMessage());
        }

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', 'Data produsen berhasil ditambahkan.');
    }

    public function show(Produsen $produsen)
    {
        $produsen->load('user');

        return view('pages.produsen.show', [
            'produsen'       => $produsen,
            'routePrefix'    => $this->routePrefix(),
            'dashboardRoute' => $this->dashboardRoute(),
        ]);
    }

    public function edit(Produsen $produsen)
    {
        $produsen->load('user');

        return view('pages.produsen.edit', [
            'produsen'       => $produsen,
            'routePrefix'    => $this->routePrefix(),
            'dashboardRoute' => $this->dashboardRoute(),
        ]);
    }

    public function update(Request $request, Produsen $produsen)
    {
        $validated = $request->validate([
            'username'      => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($produsen->user_id)],
            'email'         => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($produsen->user_id)],
            'password'      => ['nullable', 'string', 'min:8'],
            'is_active'     => ['nullable', 'boolean'],
            'nama_produsen' => ['required', 'string', 'max:100'],
            'alamat'        => ['required', 'string', 'max:255'],
            'no_telp'       => ['required', 'string', 'max:20'],
            'status_mitra'  => ['required', Rule::in(['aktif', 'nonaktif'])],
            'foto_profil'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            $user     = $produsen->user;
            $oldFoto  = $user->foto_profil;
            $fotoPath = $oldFoto;

            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto_profil', 'public');
            }

            $userData = [
                'username'    => $validated['username'],
                'email'       => $validated['email'],
                'is_active'   => $request->boolean('is_active'),
                'foto_profil' => $fotoPath,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $user->update($userData);

            $produsen->update([
                'nama_produsen' => $validated['nama_produsen'],
                'alamat'        => $validated['alamat'],
                'no_telp'       => $validated['no_telp'],
                'status_mitra'  => $validated['status_mitra'],
            ]);

            if ($request->hasFile('foto_profil') && $oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memperbarui data produsen: ' . $e->getMessage());
        }

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', 'Data produsen berhasil diperbarui.');
    }

    public function destroy(Produsen $produsen)
    {
        try {
            DB::beginTransaction();

            $user    = $produsen->user;
            $oldFoto = $user?->foto_profil;

            $produsen->delete();
            $user?->delete();

            if ($oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Data produsen tidak bisa dihapus karena masih memiliki transaksi terkait (bahan baku, permintaan, pembayaran, dll).');
        }

        return redirect()
            ->route($this->routePrefix() . '.index')
            ->with('success', 'Data produsen berhasil dihapus.');
    }

    /* =====================================================================
     |  PROFIL PRODUSEN (self-service, diakses oleh user berrole 'produsen')
     |  Status mitra & status aktif akun TIDAK bisa diubah sendiri di sini —
     |  itu tetap dikontrol oleh Admin/Pemilik lewat CRUD di atas.
     * ===================================================================== */

    public function profile()
    {
        $produsen = auth()->user()->produsen()->with('user')->firstOrFail();

        return view('pages.produsen.profile', [
            'produsen' => $produsen,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $produsen = auth()->user()->produsen()->firstOrFail();
        $user     = $produsen->user;

        $validated = $request->validate([
            'username'      => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email'         => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'password'      => ['nullable', 'string', 'min:8'],
            'nama_produsen' => ['required', 'string', 'max:100'],
            'alamat'        => ['required', 'string', 'max:255'],
            'no_telp'       => ['required', 'string', 'max:20'],
            'foto_profil'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::beginTransaction();
        try {
            $oldFoto  = $user->foto_profil;
            $fotoPath = $oldFoto;

            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto_profil', 'public');
            }

            $userData = [
                'username'    => $validated['username'],
                'email'       => $validated['email'],
                'foto_profil' => $fotoPath,
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = $validated['password'];
            }

            $user->update($userData);

            $produsen->update([
                'nama_produsen' => $validated['nama_produsen'],
                'alamat'        => $validated['alamat'],
                'no_telp'       => $validated['no_telp'],
            ]);

            if ($request->hasFile('foto_profil') && $oldFoto) {
                Storage::disk('public')->delete($oldFoto);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }

        return redirect()
            ->route('produsen.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
