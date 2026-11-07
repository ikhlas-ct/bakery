<?php

namespace App\View\Composers;

use App\Models\Pembayaran;
use App\Models\PengalihanBahanBaku;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\User;
use App\Services\StokService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Menyiapkan angka badge untuk menu sidebar (variabel $sidebarBadges).
 *
 * - Hanya query milik role yang sedang login yang dijalankan; role lain tidak menyentuh database.
 * - Angka 0 berarti badge tidak ditampilkan (diatur di sidebar.blade.php).
 * - Bila satu query gagal, badge itu jatuh ke 0 dan errornya dicatat di log, sehingga sidebar tetap tampil.
 *
 * Didaftarkan di AppServiceProvider::boot() untuk partial sidebar saja.
 */
class SidebarBadgeComposer
{
    public function __construct(private StokService $stok)
    {
    }

    public function compose(View $view): void
    {
        $user = Auth::user();

        $view->with('sidebarBadges', match ($user?->role) {
            'admin'    => $this->admin(),
            'pemilik'  => $this->pemilik(),
            'produsen' => $this->produsen($user),
            default    => [],
        });
    }

    /* ───────── per role ───────── */

    /** Admin: tagihan belum lunas, pengalihan menunggu penentuan pengganti, batch kedaluwarsa/mendesak. */
    private function admin(): array
    {
        return [
            'tagihan'     => $this->tagihanMenunggu(),
            'pengalihan'  => $this->pengalihanMenungguAdmin(),
            'kedaluwarsa' => $this->batchPerluTindakan(),
        ];
    }

    /** Pemilik: tagihan belum lunas dan batch kedaluwarsa/mendesak. */
    private function pemilik(): array
    {
        return [
            'tagihan'     => $this->tagihanMenunggu(),
            'kedaluwarsa' => $this->batchPerluTindakan(),
        ];
    }

    /** Produsen: permintaan masuk yang belum direspon. */
    private function produsen(User $user): array
    {
        $produsenId = $user->produsen?->id;

        return [
            'permintaan_masuk' => $produsenId ? $this->permintaanMasuk((int) $produsenId) : 0,
        ];
    }

    /* ───────── penghitung ───────── */

    /**
     * Tagihan produsen yang masih menunggu (belum lunas dan tidak ditolak).
     * Mencakup status_acc "menunggu" maupun "disetujui" selama status_bayar masih "belum".
     */
    private function tagihanMenunggu(): int
    {
        return rescue(fn () => Pembayaran::query()
            ->where('status_bayar', 'belum')
            ->where('status_acc', '!=', 'ditolak')
            ->count(), 0);
    }

    /** Pengalihan yang belum ditentukan produsen penggantinya oleh admin. */
    private function pengalihanMenungguAdmin(): int
    {
        return rescue(fn () => PengalihanBahanBaku::query()
            ->whereNull('produsen_pengganti_id')
            ->where('status_produsen_pengganti', 'pending')
            ->count(), 0);
    }

    /** Batch yang sudah kedaluwarsa atau kedaluwarsa dalam <= BATAS_MENDESAK hari dan masih punya sisa stok. */
    private function batchPerluTindakan(): int
    {
        return rescue(fn () => $this->stok
            ->batchKedaluwarsa(today(), StokService::BATAS_MENDESAK, 'semua')
            ->count(), 0);
    }

    /**
     * Permintaan masuk yang belum direspon produsen:
     *  - permintaan unik (permintaan_bahan_baku_id) yang punya detail milik produsen ini berstatus pending, ditambah
     *  - pengalihan dengan produsen ini sebagai produsen pengganti yang berstatus pending.
     */
    private function permintaanMasuk(int $produsenId): int
    {
        return rescue(function () use ($produsenId) {
            $permintaan = PermintaanBahanBakuDetail::query()
                ->where('produsen_id', $produsenId)
                ->where('status_produsen', 'pending')
                ->distinct()
                ->count('permintaan_bahan_baku_id');

            $pengalihan = PengalihanBahanBaku::query()
                ->where('produsen_pengganti_id', $produsenId)
                ->where('status_produsen_pengganti', 'pending')
                ->count();

            return $permintaan + $pengalihan;
        }, 0);
    }
}
