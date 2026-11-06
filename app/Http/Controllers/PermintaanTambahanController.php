<?php

namespace App\Http\Controllers;

use App\Models\PengalihanBahanBaku;
use App\Models\Produsen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sisi PRODUSEN PENGGANTI. Daftarnya digabung ke Permintaan Masuk
 * (PermintaanMasukController@index); controller ini hanya menampilkan satu item
 * dan menerima tanggapannya. Bagi produsen ini ia permintaan masuk biasa:
 * tidak ada istilah pengalihan dan tidak ada info produsen asal.
 */
class PermintaanTambahanController extends Controller
{
    public function show(PengalihanBahanBaku $pengalihanBahanBaku)
    {
        $produsen = $this->produsenLogin();
        abort_unless((int) $pengalihanBahanBaku->produsen_pengganti_id === (int) $produsen->id, 404);

        $pengalihanBahanBaku->load([
            'permintaanBahanBakuDetail.permintaanBahanBaku',
            'bahanBakuAsal.satuan',
            'bahanBakuPengganti',
        ]);

        return view('pages.permintaan-tambahan.show', ['p' => $pengalihanBahanBaku]);
    }

    public function respon(Request $request, PengalihanBahanBaku $pengalihanBahanBaku): RedirectResponse
    {
        $produsen = $this->produsenLogin();
        abort_unless((int) $pengalihanBahanBaku->produsen_pengganti_id === (int) $produsen->id, 404);

        $request->validate([
            'jumlah_disetujui_pengganti' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ], [
            'jumlah_disetujui_pengganti.required' => 'Isi jumlah yang sanggup Anda sediakan (isi 0 bila tidak sanggup).',
            'jumlah_disetujui_pengganti.numeric'  => 'Jumlah harus berupa angka.',
            'jumlah_disetujui_pengganti.min'      => 'Jumlah tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($request, $pengalihanBahanBaku, $produsen) {
            $p = PengalihanBahanBaku::where('produsen_pengganti_id', $produsen->id)
                ->lockForUpdate()
                ->findOrFail($pengalihanBahanBaku->id);

            if ($p->status_produsen_pengganti !== 'pending') {
                throw ValidationException::withMessages([
                    'jumlah_disetujui_pengganti' => 'Permintaan ini sudah pernah ditanggapi.',
                ]);
            }

            // Bandingkan dalam sen agar bebas masalah floating point.
            $diminta   = (int) round((float) $p->jumlah_dialihkan * 100);
            $disetujui = (int) round((float) $request->jumlah_disetujui_pengganti * 100);

            if ($disetujui > $diminta) {
                throw ValidationException::withMessages([
                    'jumlah_disetujui_pengganti' => 'Jumlah tidak boleh melebihi jumlah diminta ('
                        . rtrim(rtrim(number_format($diminta / 100, 2, ',', '.'), '0'), ',') . ').',
                ]);
            }

            $status = match (true) {
                $disetujui === 0        => 'ditolak',
                $disetujui === $diminta => 'disetujui',
                default                 => 'disetujui_sebagian',
            };

            $p->update([
                'jumlah_disetujui_pengganti' => $disetujui / 100,
                'status_produsen_pengganti'  => $status,
            ]);

            // Pengganti pun kurang -> picu pengalihan berikutnya untuk sisa yang belum terpenuhi.
            // Baris lama tetap sebagai riwayat; admin menentukan pengganti baru di baris yang baru.
            if ($disetujui < $diminta) {
                PengalihanBahanBaku::create([
                    'permintaan_bahan_baku_detail_id' => $p->permintaan_bahan_baku_detail_id,
                    'bahan_baku_asal_id'              => $p->bahan_baku_asal_id,
                    'jumlah_dialihkan'                => ($diminta - $disetujui) / 100,
                    'status_produsen_pengganti'       => 'pending',
                    'alasan_pengalihan'               => $disetujui === 0
                        ? 'Produsen pengganti tidak sanggup menyediakan.'
                        : 'Produsen pengganti hanya sanggup menyediakan sebagian.',
                    'tanggal'                         => now()->toDateString(),
                ]);
            }
        });

        return redirect()
            ->route('produsen.permintaan-tambahan.show', $pengalihanBahanBaku)
            ->with('success', 'Tanggapan berhasil dikirim.');
    }

    private function produsenLogin(): Produsen
    {
        return Produsen::where('user_id', auth()->id())->firstOrFail();
    }
}
