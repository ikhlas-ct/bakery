<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingController extends Controller
{
    /* ─────────────────────────────────────────────────────────── */
    /* EDIT (halaman utama setting)                               */
    /* ─────────────────────────────────────────────────────────── */
    public function edit()
    {
        // Ambil satu record, atau buat kosong jika belum ada
        $setting = WebsiteSetting::first() ?? new WebsiteSetting();

        return view('pages.setting.website', compact('setting'));
    }

    /* ─────────────────────────────────────────────────────────── */
    /* UPDATE                                                     */
    /* ─────────────────────────────────────────────────────────── */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama'               => 'nullable|string|max:150',
            'slogan'             => 'nullable|string|max:255',
            'alamat'             => 'nullable|string',
            'email'              => 'nullable|email|max:150',
            'nomor_telepon'      => 'nullable|string|max:20',
            'logo'               => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'social_facebook'    => 'nullable|url|max:255',
            'social_instagram'   => 'nullable|url|max:255',
            'social_twitter'     => 'nullable|url|max:255',
            'social_youtube'     => 'nullable|url|max:255',
            'title_pengantar'    => 'nullable|string|max:255',
            'paragraf_pengantar' => 'nullable|string',
            'gambar_pengantar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'about_us'           => 'nullable|string',
        ], [
            'email.email'           => 'Format email tidak valid.',
            'logo.image'            => 'Logo harus berupa gambar.',
            'logo.max'              => 'Ukuran logo maksimal 2 MB.',
            'gambar_pengantar.max'  => 'Gambar pengantar maksimal 3 MB.',
            'social_facebook.url'   => 'URL Facebook tidak valid.',
            'social_instagram.url'  => 'URL Instagram tidak valid.',
            'social_twitter.url'    => 'URL Twitter tidak valid.',
            'social_youtube.url'    => 'URL YouTube tidak valid.',
        ]);

        $setting = WebsiteSetting::first() ?? new WebsiteSetting();
        $data    = collect($validated)->except(['logo', 'gambar_pengantar'])->toArray();

        // ── Upload logo
        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('setting', 'public');
        }

        // ── Upload gambar pengantar
        if ($request->hasFile('gambar_pengantar')) {
            if ($setting->gambar_pengantar && Storage::disk('public')->exists($setting->gambar_pengantar)) {
                Storage::disk('public')->delete($setting->gambar_pengantar);
            }
            $data['gambar_pengantar'] = $request->file('gambar_pengantar')->store('setting', 'public');
        }

        $setting->fill($data)->save();

        return redirect()->route('setting.website.edit')
                         ->with('success', 'Pengaturan website berhasil disimpan.');
    }

    /* ─────────────────────────────────────────────────────────── */
    /* SUMMERNOTE – Upload gambar inline                          */
    /* ─────────────────────────────────────────────────────────── */
    public function uploadImage(Request $request)
    {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp,gif|max:3072']);

        $path = $request->file('image')->store('setting/isi', 'public');
        $url  = asset('storage/' . $path);

        return response()->json(['url' => $url]);
    }

    /* ─────────────────────────────────────────────────────────── */
    /* SUMMERNOTE – Hapus gambar inline                           */
    /* ─────────────────────────────────────────────────────────── */
    public function deleteImage(Request $request)
    {
        $src  = $request->input('src', '');
        $path = str_replace(asset('storage') . '/', '', $src);

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['success' => true]);
    }
}
