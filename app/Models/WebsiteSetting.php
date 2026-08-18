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
    //  STATIC HELPER
    // ──────────────────────────────────────────────────────────
    public static function getSetting(): ?self
    {
        return static::first();
    }
}
