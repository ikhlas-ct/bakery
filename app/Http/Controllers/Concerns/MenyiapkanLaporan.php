<?php

namespace App\Http\Controllers\Concerns;

use App\Models\WebsiteSetting;
use Illuminate\Http\Request;

trait MenyiapkanLaporan
{
    /** Laporan stok & kartu stok hanya untuk admin dan pemilik. */
    protected function authorizeRole(): void
    {
        abort_unless(
            in_array(auth()->user()?->role, ['admin', 'pemilik'], true),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );
    }

    /** Data yang dibutuhkan kop & tanda tangan di halaman cetak. */
    protected function dataKop(Request $request): array
    {
        return [
            'setting'   => WebsiteSetting::getSetting(),
            'pencetak'  => $request->user(),
            // Ganti lewat config/app.php => 'kota' => 'Padang' bila perlu.
            'kota'      => config('app.kota', 'Padang'),
            // tambahkan ?auto_print=0 untuk pratinjau tanpa membuka dialog cetak
            'autoPrint' => $request->boolean('auto_print', true),
        ];
    }
}
