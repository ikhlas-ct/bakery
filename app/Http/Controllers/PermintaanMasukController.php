<?php

namespace App\Http\Controllers;

use App\Models\PengalihanBahanBaku;
use App\Models\PermintaanBahanBaku;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
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
     * Daftar permintaan masuk untuk produsen ini. Berisi dua jenis baris dalam satu daftar:
     *  - permintaan biasa dari admin (header berisi item untuk produsen ini), dan
     *  - item yang diteruskan admin ke produsen ini sebagai pengganti
     *    (tampil sebagai permintaan masuk biasa, tanpa istilah pengalihan).
     */
    public function index(Request $request)
    {
        $produsen = $this->produsenLogin();
        $status   = in_array($request->status, self::STATUS, true) ? $request->status : null;
        $search   = trim((string) $request->search);

        // Statistik per item, gabungan permintaan biasa + item yang diteruskan.
        $counts = PermintaanBahanBakuDetail::where('produsen_id', $produsen->id)
            ->selectRaw('status_produsen as st, COUNT(*) as total')
            ->groupBy('status_produsen')
            ->pluck('total', 'st');
        $countsLanjutan = PengalihanBahanBaku::where('produsen_pengganti_id', $produsen->id)
            ->selectRaw('status_produsen_pengganti as st, COUNT(*) as total')
            ->groupBy('status_produsen_pengganti')
            ->pluck('total', 'st');
        $hitung = fn (string $st) => (int) ($counts[$st] ?? 0) + (int) ($countsLanjutan[$st] ?? 0);

        // 1) Permintaan biasa
        $permintaans = PermintaanBahanBaku::query()
            ->whereHas('permintaanBahanBakuDetails', function ($q) use ($produsen, $status) {
                $q->where('produsen_id', $produsen->id);
                if ($status) {
                    $q->where('status_produsen', $status);
                }
            })
            ->with([
                'admin.user',
                'permintaanBahanBakuDetails' => fn ($q) => $q
                    ->where('produsen_id', $produsen->id)
                    ->with('bahanBaku'),
            ])
            ->when($search !== '', fn ($q) => $q->where('nomor_transaksi', 'like', "%{$search}%"))
            ->get();

        // 2) Item yang diteruskan admin ke produsen ini
        $lanjutans = PengalihanBahanBaku::query()
            ->where('produsen_pengganti_id', $produsen->id)
            ->with([
                'permintaanBahanBakuDetail.permintaanBahanBaku.admin.user',
                'bahanBakuAsal',
                'bahanBakuPengganti',
            ])
            ->when($status, fn ($q) => $q->where('status_produsen_pengganti', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('permintaanBahanBakuDetail.permintaanBahanBaku',
                    fn ($h) => $h->where('nomor_transaksi', 'like', "%{$search}%"));
            })
            ->get();

        $rows = collect();

        foreach ($permintaans as $pm) {
            $items = $pm->permintaanBahanBakuDetails;
            $rows->push((object) [
                'nomor'      => $pm->nomor_transaksi,
                'tanggal'    => $pm->tanggal_permintaan,
                'dari'       => $pm->admin->user->username ?? '-',
                'jumlahItem' => $items->count(),
                'ringkasan'  => $items->pluck('bahanBaku.nama_bahan_baku')->take(2)->implode(', ')
                                . ($items->count() > 2 ? ', …' : ''),
                'pending'    => $items->where('status_produsen', 'pending')->count(),
                'disetujui'  => $items->where('status_produsen', 'disetujui')->count(),
                'sebagian'   => $items->where('status_produsen', 'disetujui_sebagian')->count(),
                'ditolak'    => $items->where('status_produsen', 'ditolak')->count(),
                'url'        => route('produsen.permintaan.show', $pm->id),
                'sortId'     => $pm->id,
            ]);
        }

        foreach ($lanjutans as $l) {
            $header = $l->permintaanBahanBakuDetail->permintaanBahanBaku;
            $st     = $l->status_produsen_pengganti;
            $rows->push((object) [
                'nomor'      => $header->nomor_transaksi,
                'tanggal'    => $l->tanggal,
                'dari'       => $header->admin->user->username ?? '-',
                'jumlahItem' => 1,
                'ringkasan'  => $l->bahan_baku_tujuan->nama_bahan_baku ?? '-',
                'pending'    => $st === 'pending' ? 1 : 0,
                'disetujui'  => $st === 'disetujui' ? 1 : 0,
                'sebagian'   => $st === 'disetujui_sebagian' ? 1 : 0,
                'ditolak'    => $st === 'ditolak' ? 1 : 0,
                'url'        => route('produsen.permintaan-tambahan.show', $l->id),
                'sortId'     => $l->id,
            ]);
        }

        // Terbaru di atas, lalu paginasi manual (dua sumber data digabung).
        $rows = $rows
            ->sortByDesc(fn ($r) => $r->tanggal->timestamp . str_pad((string) $r->sortId, 10, '0', STR_PAD_LEFT))
            ->values();

        $perPage = 10;
        $page    = LengthAwarePaginator::resolveCurrentPage();
        $paged   = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.permintaan-masuk.index', [
            'rows'          => $paged,
            'totalPending'  => $hitung('pending'),
            'totalSetuju'   => $hitung('disetujui'),
            'totalSebagian' => $hitung('disetujui_sebagian'),
            'totalTolak'    => $hitung('ditolak'),
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
                ->with('bahanBaku.satuan')
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
