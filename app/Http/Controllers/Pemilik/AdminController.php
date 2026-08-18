<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    /**
     * Tampilkan daftar admin.
     */
    public function index(Request $request)
    {
        $query = Admin::with('user');

        // Pencarian (username, email, nip, no_telp)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                    ->orWhere('no_telp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q2) use ($search) {
                        $q2->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter status akun
        if ($request->filled('status')) {
            $isActive = $request->status === 'aktif';
            $query->whereHas('user', function ($q) use ($isActive) {
                $q->where('is_active', $isActive);
            });
        }

        $admins = $query->latest()->paginate(10)->withQueryString();

        // Statistik ringkas
        $totalAdmin      = Admin::count();
        $adminAktif      = Admin::whereHas('user', fn($q) => $q->where('is_active', true))->count();
        $adminNonaktif   = $totalAdmin - $adminAktif;
        $adminBaruBulan  = Admin::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('pages.admin.index', compact(
            'admins',
            'totalAdmin',
            'adminAktif',
            'adminNonaktif',
            'adminBaruBulan'
        ));
    }

    /**
     * Form tambah admin.
     */
    public function create()
    {
        return view('pages.admin.create');
    }

    /**
     * Simpan admin baru (sekaligus akun user role=admin).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'        => ['required', 'string', 'max:100'],
            'username'    => ['required', 'string', 'max:100', 'unique:users,username'],
            'email'       => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'    => ['required', 'string', 'min:8'],
            'nip'         => ['nullable', 'string', 'max:30', 'unique:admins,nip'],
            'no_telp'     => ['required', 'string', 'max:20'],
            'alamat'      => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $fotoPath = '';
            if ($request->hasFile('foto_profil')) {
                $fotoPath = $request->file('foto_profil')->store('foto_profil', 'public');
            }

            $user = User::create([
                'username'    => $validated['username'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
                'role'        => 'admin',
                'is_active'   => $request->boolean('is_active', true),
                'foto_profil' => $fotoPath,
            ]);

            Admin::create([
                'user_id' => $user->id,
                'nama'    => $validated['nama'],
                'nip'     => $validated['nip'] ?? null,
                'no_telp' => $validated['no_telp'],
                'alamat'  => $validated['alamat'] ?? null,
            ]);
        });

        return redirect()->route('pemilik.admin.index')
            ->with('success', 'Data admin berhasil ditambahkan.');
    }

    /**
     * Detail admin.
     */
    public function show(Admin $admin)
    {
        $admin->load('user');

            return view('pages.admin.show', compact('admin'));
        }

    /**
     * Form edit admin.
     */
    public function edit(Admin $admin)
    {
        $admin->load('user');

        return view('pages.admin.edit', compact('admin'));
    }

    /**
     * Update data admin.
     */
    public function update(Request $request, Admin $admin)
    {
        $userId = $admin->user_id;

        $validated = $request->validate([
            'nama'        => ['required', 'string', 'max:100'],
            'username'    => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'       => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($userId)],
            'password'    => ['nullable', 'string', 'min:8'],
            'nip'         => ['nullable', 'string', 'max:30', Rule::unique('admins', 'nip')->ignore($admin->id)],
            'no_telp'     => ['required', 'string', 'max:20'],
            'alamat'      => ['nullable', 'string', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        DB::transaction(function () use ($request, $validated, $admin) {
            $user = $admin->user;

            $userData = [
                'username'  => $validated['username'],
                'email'     => $validated['email'],
                'is_active' => $request->boolean('is_active', true),
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            if ($request->hasFile('foto_profil')) {
                if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                    Storage::disk('public')->delete($user->foto_profil);
                }
                $userData['foto_profil'] = $request->file('foto_profil')->store('foto_profil', 'public');
            }

            $user->update($userData);

            $admin->update([
                'nama'    => $validated['nama'],
                'nip'     => $validated['nip'] ?? null,
                'no_telp' => $validated['no_telp'],
                'alamat'  => $validated['alamat'] ?? null,
            ]);
        });

        return redirect()->route('pemilik.admin.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }

    /**
     * Hapus admin (beserta akun usernya).
     */
    public function destroy(Admin $admin)
    {
        DB::transaction(function () use ($admin) {
            $user = $admin->user;

            if ($user && $user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $admin->delete();
            $user?->delete();
        });

        return redirect()->route('pemilik.admin.index')
            ->with('success', 'Data admin berhasil dihapus.');
    }
}
