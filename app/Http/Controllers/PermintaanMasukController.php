<?php

namespace App\Http\Controllers;

use App\Models\PengalihanBahanBaku;
use App\Models\PermintaanBahanBaku;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sisi PRODUSEN: menerima permintaan bahan baku dari admin, lalu mengisi
 * jumlah yang sanggup disediakan per item.
 *
 * Aturan status (dihitung server, bukan dari input user):
 *   jumlah_disetujui = 0                  -> ditolak
 *   jumlah_disetujui = jumlah_diminta     -> disetujui
 *   0 < jumlah_disetujui < jumlah_diminta -> disetujui_sebagian
 *
 * Jika ada kekurangan (ditolak / disetujui_sebagian), otomatis dibuat baris
 * di pengalihan_bahan_bakus (status 'pending', produsen/bahan baku pengganti
 * masih kosong) sebagai pemicu menu pengalihan untuk admin.
 */
class PermintaanMasukController extends Controller
{
    private const STATUS = ['pending', 'disetujui', 'disetujui_sebagian', 'ditolak'];

    /**
     * Daftar permintaan (header) yang berisi minimal satu item untuk produsen ini.
     */
    public function index(Request $request)
    {
        $produsen = $this->produsenLogin();

        // Statistik per item (bukan per header), karena status dinilai per item.
        $counts = PermintaanBahanBakuDetail::where('produsen_id', $produsen->id)
            ->selectRaw('status_produsen, COUNT(*) as total')
            ->groupBy('status_produsen')
            ->pluck('total', 'status_produsen');

        $status = in_array($request->status, self::STATUS, true) ? $request->status : null;

        $permintaans = PermintaanBahanBaku::query()
            ->whereHas('permintaanBahanBakuDetails', function ($q) use ($produsen, $status) {
                $q->where('produsen_id', $produsen->id);
                if ($status) {
                    $q->where('status_produsen', $status);
                }
            })
            ->with([
                'admin.user',
                // hanya item milik produsen ini yang dimuat
                'permintaanBahanBakuDetails' => fn ($q) => $q
                    ->where('produsen_id', $produsen->id)
                    ->with('bahanBaku'),
            ])
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('nomor_transaksi', 'like', '%' . $request->search . '%');
            })
            ->latest('tanggal_permintaan')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.permintaan-masuk.index', [
            'permintaans'   => $permintaans,
            'totalPending'  => (int) ($counts['pending'] ?? 0),
            'totalSetuju'   => (int) ($counts['disetujui'] ?? 0),
            'totalSebagian' => (int) ($counts['disetujui_sebagian'] ?? 0),
            'totalTolak'    => (int) ($counts['ditolak'] ?? 0),
        ]);
    }

    /**
     * Detail satu permintaan: hanya item milik produsen yang login + form tanggapan.
     */
    public function show(PermintaanBahanBaku $permintaanBahanBaku)
    {
        $produsen = $this->produsenLogin();

        $permintaanBahanBaku->load([
            'admin.user',
            'permintaanBahanBakuDetails' => fn ($q) => $q
                ->where('produsen_id', $produsen->id)
                ->with(['bahanBaku.satuan', 'pengalihan'])
                ->orderBy('id'),
        ]);

        $details = $permintaanBahanBaku->permintaanBahanBakuDetails;

        // Produsen tidak boleh membuka permintaan yang tidak ditujukan kepadanya.
        abort_if($details->isEmpty(), 404);

        return view('pages.permintaan-masuk.show', [
            'permintaan' => $permintaanBahanBaku,
            'details'    => $details,
            'adaPending' => $details->contains('status_produsen', 'pending'),
        ]);
    }

    /**
     * Simpan tanggapan produsen. Item yang jumlahnya dikosongkan tetap 'pending'
     * sehingga bisa ditanggapi nanti.
     */
    public function respon(Request $request, PermintaanBahanBaku $permintaanBahanBaku): RedirectResponse
    {
        $produsen = $this->produsenLogin();

        $request->validate([
            'items'                    => ['required', 'array'],
            'items.*.jumlah_disetujui' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.alasan'           => ['nullable', 'string', 'max:200'],
        ], [
            'items.*.jumlah_disetujui.numeric' => 'Jumlah harus berupa angka.',
            'items.*.jumlah_disetujui.min'     => 'Jumlah tidak boleh negatif.',
            'items.*.alasan.max'               => 'Alasan maksimal 200 karakter.',
        ]);

        // Ambil hanya baris yang diisi (0 dianggap diisi = menolak item tsb).
        $input = collect($request->input('items', []))
            ->filter(fn ($row) => isset($row['jumlah_disetujui']) && $row['jumlah_disetujui'] !== '');

        if ($input->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Isi jumlah yang bisa disediakan minimal untuk satu item.',
            ]);
        }

        $jumlahDitanggapi = 0;

        DB::transaction(function () use ($permintaanBahanBaku, $produsen, $input, &$jumlahDitanggapi) {
            // Kunci baris pending milik produsen ini agar tidak double-submit / race.
            $details = PermintaanBahanBakuDetail::where('permintaan_bahan_baku_id', $permintaanBahanBaku->id)
                ->where('produsen_id', $produsen->id)
                ->where('status_produsen', 'pending')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($input as $detailId => $row) {
                $field  = "items.{$detailId}.jumlah_disetujui";
                $detail = $details->get((int) $detailId);

                if (! $detail) {
                    throw ValidationException::withMessages([
                        $field => 'Item tidak ditemukan atau sudah pernah ditanggapi.',
                    ]);
                }

                // Bandingkan dalam satuan sen agar bebas masalah floating point.
                $diminta   = (int) round((float) $detail->jumlah_diminta * 100);
                $disetujui = (int) round((float) $row['jumlah_disetujui'] * 100);

                if ($disetujui > $diminta) {
                    throw ValidationException::withMessages([
                        $field => 'Jumlah tidak boleh melebihi jumlah diminta ('
                            . rtrim(rtrim(number_format($diminta / 100, 2, ',', '.'), '0'), ',') . ').',
                    ]);
                }

                $status = match (true) {
                    $disetujui === 0         => 'ditolak',
                    $disetujui === $diminta  => 'disetujui',
                    default                  => 'disetujui_sebagian',
                };

                $detail->update([
                    'jumlah_disetujui' => $disetujui / 100,
                    'status_produsen'  => $status,
                ]);
                $jumlahDitanggapi++;

                // Ada kekurangan -> picu pengalihan.
                if ($disetujui < $diminta) {
                    $kurang = ($diminta - $disetujui) / 100;
                    $alasan = trim((string) ($row['alasan'] ?? ''));

                    if ($alasan === '') {
                        $alasan = $status === 'ditolak'
                            ? 'Ditolak produsen (tidak sanggup menyediakan).'
                            : 'Produsen hanya sanggup menyediakan sebagian dari jumlah yang diminta.';
                    }

                    // 1 item = maksimal 1 pengalihan (relasi hasOne).
                    PengalihanBahanBaku::updateOrCreate(
                        ['permintaan_bahan_baku_detail_id' => $detail->id],
                        [
                            'bahan_baku_asal_id'        => $detail->bahan_baku_id,
                            'jumlah_dialihkan'          => $kurang,
                            'status_produsen_pengganti' => 'pending',
                            'alasan_pengalihan'         => $alasan,
                            'tanggal'                   => now()->toDateString(),
                        ]
                    );
                }
            }
        });

        $pesan = "{$jumlahDitanggapi} item berhasil ditanggapi.";

        return redirect()
            ->route('produsen.permintaan.show', $permintaanBahanBaku)
            ->with('success', $pesan);
    }

    /**
     * Profil produsen dari user yang sedang login.
     */
    private function produsenLogin(): Produsen
    {
        return Produsen::where('user_id', auth()->id())->firstOrFail();
    }
}
