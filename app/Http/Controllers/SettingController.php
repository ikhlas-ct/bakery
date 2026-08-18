<?php

namespace App\Http\Controllers;

use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Tampilkan form pengaturan website.
     * Kalau belum ada data sama sekali, buat baris default kosong dulu.
     */
    public function edit()
    {
        $setting = WebsiteSetting::getSetting() ?? WebsiteSetting::create([]);

        return view('pages.setting.website', compact('setting'));
    }

    /**
     * Simpan perubahan pengaturan website.
     */
    public function update(Request $request)
    {
        $setting = WebsiteSetting::getSetting() ?? WebsiteSetting::create([]);

        $validated = $request->validate([
            'nama'              => 'nullable|string|max:255',
            'slogan'            => 'nullable|string|max:255',
            'alamat'            => 'nullable|string',
            'email'             => 'nullable|email|max:255',
            'nomor_telepon'     => 'nullable|string|max:20',
            'logo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'social_facebook'   => 'nullable|url|max:255',
            'social_instagram'  => 'nullable|url|max:255',
            'social_twitter'    => 'nullable|url|max:255',
            'social_youtube'    => 'nullable|url|max:255',
        ]);

        // Upload logo baru (kalau ada) & hapus logo lama
        if ($request->hasFile('logo')) {
            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            $validated['logo'] = $request->file('logo')->store('logo', 'public');
        } else {
            unset($validated['logo']);
        }

        $setting->update($validated);

        return back()->with('success', 'Pengaturan website berhasil disimpan.');
    }
}
