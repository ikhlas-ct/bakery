<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $table = 'website_settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'slogan',
        'alamat',
        'email',
        'nomor_telepon',
        'logo',
        'social_facebook',
        'social_instagram',
        'social_twitter',
        'social_youtube',
        'title_pengantar',
        'paragraf_pengantar',
        'gambar_pengantar',
        'about_us',
        'why_choose_us',
    ];

    // ──────────────────────────────────────────────────────────
    //  ACCESSOR: URL logo (selalu benar, ada fallback default)
    // ──────────────────────────────────────────────────────────
    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo) {
            return asset('default-image/default_logo.png');
        }

        // Jika sudah berupa full URL (http/https), kembalikan apa adanya
        if (str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }

        return asset('storage/' . $this->logo);
    }

    // ──────────────────────────────────────────────────────────
    //  ACCESSOR: URL gambar pengantar
    // ──────────────────────────────────────────────────────────
    public function getGambarPengantarUrlAttribute(): string
    {
        if (!$this->gambar_pengantar) {
            return asset('default-image/default_banner.png');
        }

        if (str_starts_with($this->gambar_pengantar, 'http')) {
            return $this->gambar_pengantar;
        }

        return asset('storage/' . $this->gambar_pengantar);
    }

    // ──────────────────────────────────────────────────────────
    //  STATIC HELPER
    // ──────────────────────────────────────────────────────────
    public static function getSetting(): ?self
    {
        return static::first();
    }
}
