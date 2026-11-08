<?php

namespace Database\Seeders;

use App\Models\Pemilik;
use App\Models\Produsen;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Mengatur status tagihan di tabel pembayarans (tagihannya sudah dibuat PenerimaanBahanBakuSeeder
 * dengan status menunggu / belum), mengikuti alur controller:
 *
 *   menunggu  -> belum disetujui pemilik
 *   disetujui -> sudah disetujui pemilik, belum dibayar
 *   ditolak   -> ditolak pemilik, tidak bisa dibayar
 *   lunas     -> dibayar admin atau pemilik, wajib ada metode, tanggal, dan bukti bayar
 *
 * Tagihan hanya boleh lunas bila seluruh barang yang disetujui produsen sudah diterima
 * (di controller, penerimaan terkunci setelah lunas). Gambar bukti bayar dan receipt yang dibuat
 * hanya gambar contoh; ganti dengan unggahan asli bila perlu.
 *
 * Menjalankan seeder ini lagi akan mengembalikan status tagihan ke aturan di bawah.
 */
class PembayaranSeeder extends Seeder
{
    private const TANGGAL_AKHIR = '2026-11-08';

    private const PNG_BUKTI = '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';

    private const PNG_RECEIPT = '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';

    public function run(): void
    {
        $pemilikId = Pemilik::whereHas('user', fn ($q) => $q->where('username', 'pemilik'))->firstOrFail()->id;

        $usernameProdusen = Produsen::with('user:id,username')->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->user->username ?? ''])
            ->all();

        $urutPermintaan = [];
        foreach (PermintaanBahanBakuSeeder::daftarBernomor() as $no => $p) {
            $urutPermintaan[$p['nomor']] = $no + 1;
        }

        $nomorPermintaan = DB::table('permintaan_bahan_bakus')->pluck('nomor_transaksi', 'id')->all();

        $batas = Carbon::parse(self::TANGGAL_AKHIR);
        if (now()->lt($batas)) {
            $batas = now()->copy();
        }

        $urutLunas = 0;

        foreach (DB::table('pembayarans')->orderBy('permintaan_bahan_baku_id')->orderBy('produsen_id')->get() as $t) {
            $n = $urutPermintaan[$nomorPermintaan[$t->permintaan_bahan_baku_id] ?? ''] ?? 0;
            $username = $usernameProdusen[$t->produsen_id] ?? '';
            $lengkap = $this->diterimaLengkap((int) $t->permintaan_bahan_baku_id, (int) $t->produsen_id);

            $status = self::tentukanStatus($n, $username, $lengkap);

            // Kondisi dasar: menunggu persetujuan, belum dibayar.
            $ubah = [
                'pemilik_id' => null,
                'tanggal_bayar' => null,
                'status_acc' => 'menunggu',
                'status_bayar' => 'belum',
                'metode_bayar' => null,
                'bukti_bayar' => null,
            ];

            if ($status === 'disetujui' || $status === 'ditolak') {
                $ubah['pemilik_id'] = $pemilikId;
                $ubah['status_acc'] = $status;
            }

            if ($status === 'lunas') {
                $terakhir = DB::table('penerimaan_bahan_baku_details as d')
                    ->join('penerimaan_bahan_bakus as pn', 'pn.id', '=', 'd.penerimaan_bahan_baku_id')
                    ->join('permintaan_bahan_baku_details as pbd', 'pbd.id', '=', 'd.permintaan_bahan_baku_detail_id')
                    ->where('pbd.permintaan_bahan_baku_id', $t->permintaan_bahan_baku_id)
                    ->where('pn.produsen_id', $t->produsen_id)
                    ->orderByDesc('pn.tanggal_terima')
                    ->orderByDesc('pn.id')
                    ->first(['pn.id as penerimaan_id', 'pn.tanggal_terima']);

                $tanggalBayar = Carbon::parse($terakhir->tanggal_terima)->addDays(1 + ($urutLunas % 3));
                if ($tanggalBayar->gt($batas)) {
                    $tanggalBayar = $batas->copy();
                }

                $pathBukti = "bukti-pembayaran/seed-pembayaran-{$t->id}.png";
                Storage::disk('public')->put($pathBukti, base64_decode(self::PNG_BUKTI));

                $ubah = [
                    // Pemilik yang membayar bila urutannya kelipatan 3; selain itu admin (pemilik_id kosong).
                    'pemilik_id' => $urutLunas % 3 === 0 ? $pemilikId : null,
                    'tanggal_bayar' => $tanggalBayar->toDateString(),
                    'status_acc' => 'disetujui',
                    'status_bayar' => 'lunas',
                    'metode_bayar' => $urutLunas % 2 === 0 ? 'Transfer' : 'Tunai',
                    'bukti_bayar' => $pathBukti,
                ];

                // Receipt (foto/scan) diunggah pada nota penerimaan terakhir, sebagian saja.
                if ($urutLunas % 2 === 0) {
                    $pathReceipt = "receipt-pembayaran/seed-penerimaan-{$terakhir->penerimaan_id}.png";
                    Storage::disk('public')->put($pathReceipt, base64_decode(self::PNG_RECEIPT));

                    DB::table('penerimaan_bahan_bakus')
                        ->where('id', $terakhir->penerimaan_id)
                        ->whereNull('foto_receipt')
                        ->update(['foto_receipt' => $pathReceipt]);
                }

                $ubah['updated_at'] = $tanggalBayar->copy()->setTime(10, 30)->toDateTimeString();
                $urutLunas++;
            }

            DB::table('pembayarans')->where('id', $t->id)->update($ubah);
        }
    }

    /**
     * Aturan status tagihan:
     *  - barang belum diterima lengkap            -> menunggu
     *  - permintaan ke-10, produsen4              -> ditolak pemilik
     *  - permintaan ke-1 s.d. ke-11               -> lunas
     *  - permintaan ke-12 dan ke-14               -> disetujui pemilik, belum dibayar
     *  - selebihnya                               -> menunggu persetujuan
     */
    public static function tentukanStatus(int $urutPermintaan, string $produsen, bool $lengkap): string
    {
        if (! $lengkap) {
            return 'menunggu';
        }

        if ($urutPermintaan === 10 && $produsen === 'produsen4') {
            return 'ditolak';
        }

        if ($urutPermintaan >= 1 && $urutPermintaan <= 11) {
            return 'lunas';
        }

        if (in_array($urutPermintaan, [12, 14], true)) {
            return 'disetujui';
        }

        return 'menunggu';
    }

    /** Seluruh jumlah yang disetujui produsen untuk permintaan ini sudah diterima. */
    private function diterimaLengkap(int $permintaanId, int $produsenId): bool
    {
        $disetujui = (float) DB::table('permintaan_bahan_baku_details')
            ->where('permintaan_bahan_baku_id', $permintaanId)
            ->where('produsen_id', $produsenId)
            ->whereIn('status_produsen', ['disetujui', 'disetujui_sebagian'])
            ->sum('jumlah_disetujui');

        $diterima = (float) DB::table('penerimaan_bahan_baku_details as d')
            ->join('penerimaan_bahan_bakus as pn', 'pn.id', '=', 'd.penerimaan_bahan_baku_id')
            ->join('permintaan_bahan_baku_details as pbd', 'pbd.id', '=', 'd.permintaan_bahan_baku_detail_id')
            ->where('pbd.permintaan_bahan_baku_id', $permintaanId)
            ->where('pn.produsen_id', $produsenId)
            ->sum('d.jumlah_diterima');

        return round($diterima, 2) >= round($disetujui, 2);
    }
}
