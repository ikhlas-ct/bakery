<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\BahanBaku;
use App\Models\PenerimaanBahanBaku;
use App\Models\PenerimaanBahanBakuDetail;
use App\Models\PermintaanBahanBaku;
use App\Models\Pemilik;
use App\Models\PermintaanBahanBakuDetail;
use App\Models\Produsen;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Penerimaan Bahan Baku (dikelola Admin DAN Pemilik dengan hak akses yang sama).
 *
 * Alur:
 *  1. Produsen menyetujui permintaan bahan baku (penuh / sebagian). Kekurangan yang ditandai
 *     lewat pengalihan akan muncul sebagai permintaan baru, jadi penerimaan hanya dikaitkan
 *     ke permintaan yang disetujui.
 *  2. Produsen mengirim barang -> Admin mencatat penerimaan di sini (satu nota per produsen,
 *     boleh berisi barang dari BANYAK permintaan sekaligus). Hanya barang yang masih punya
 *     sisa (belum diterima penuh) yang bisa dicatat.
 *  3. Setiap penerimaan menambah stok bahan baku (stok_saat_ini) dan membuat batch FEFO
 *     (jumlah_tersisa + tanggal_kadaluarsa).
 *  4. Tagihan pada tabel `pembayarans` (per permintaan + produsen) disinkronkan otomatis
 *     = SUM(jumlah_diterima x harga_satuan).
 */
class PenerimaanBahanBakuController extends Controller implements HasMiddleware
{
    /** Peran yang boleh mengakses seluruh modul ini (tanpa pembatasan antar peran). */
    private const ROLE_BOLEH = ['admin', 'pemilik'];

    /**
     * Rute modul ini hanya memakai middleware 'auth'; pembatasan peran dilakukan di sini
     * (admin & pemilik), sama seperti pada modul Laporan. Peran lain (produsen) mendapat 403.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function (Request $request, Closure $next) {
                abort_unless(
                    in_array(auth()->user()?->role, self::ROLE_BOLEH, true),
                    403,
                    'Anda tidak memiliki akses ke modul Penerimaan Bahan Baku.'
                );

                return $next($request);
            }),
        ];
    }

    private const STATUS_SETUJU = ['disetujui', 'disetujui_sebagian'];

    private const METODE_BAYAR = ['Tunai', 'Transfer'];

    private const MESSAGES = [
        'produsen_id.required' => 'Produsen wajib dipilih.',
        'produsen_id.exists' => 'Produsen tidak ditemukan.',
        'tanggal_terima.required' => 'Tanggal terima wajib diisi.',
        'tanggal_terima.date' => 'Tanggal terima tidak valid.',
        'tanggal_terima.before_or_equal' => 'Tanggal terima tidak boleh melebihi hari ini.',
        'items.required' => 'Tidak ada item yang diterima.',
        'items.array' => 'Format item tidak valid.',
        'items.*.jumlah_diterima.required' => 'Jumlah diterima wajib diisi.',
        'items.*.jumlah_diterima.numeric' => 'Jumlah diterima harus berupa angka.',
        'items.*.jumlah_diterima.min' => 'Jumlah diterima tidak valid.',
        'items.*.tanggal_kadaluarsa.date' => 'Tanggal kadaluarsa tidak valid.',
        'items.*.tanggal_kadaluarsa.after_or_equal' => 'Tanggal kadaluarsa tidak boleh sebelum tanggal terima.',
        'catatan.max' => 'Catatan maksimal 1000 karakter.',
    ];

    // =========================================================================
    // CRUD
    // =========================================================================

    public function index(Request $request): View
    {
        $query = PenerimaanBahanBaku::query()
            ->with(['produsen', 'penerimaanBahanBakuDetails.permintaanBahanBakuDetail.permintaanBahanBaku'])
            ->withCount('penerimaanBahanBakuDetails')
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('nomor_transaksi', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('produsen_id'), fn ($q) => $q->where('produsen_id', $request->produsen_id))
            ->when($request->filled('dari'), fn ($q) => $q->whereDate('tanggal_terima', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($q) => $q->whereDate('tanggal_terima', '<=', $request->sampai))
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('id');

        $penerimaans = $query->paginate(10)->withQueryString();

        $totalPenerimaan = PenerimaanBahanBaku::count();
        $penerimaanBulanIni = PenerimaanBahanBaku::query()
            ->whereYear('tanggal_terima', now()->year)
            ->whereMonth('tanggal_terima', now()->month)
            ->count();
        $belumLunas = DB::table('pembayarans')->where('status_bayar', 'belum')->count();

        $produsens = Produsen::orderBy('nama_produsen')->get();

        // Status pembayaran per (permintaan + produsen) untuk baris yang tampil.
        $permintaanIds = $penerimaans->getCollection()
            ->flatMap(fn ($p) => $p->penerimaanBahanBakuDetails->pluck('permintaanBahanBakuDetail.permintaan_bahan_baku_id'))
            ->filter()
            ->unique()
            ->values();

        $pembayaranMap = DB::table('pembayarans')
            ->whereIn('permintaan_bahan_baku_id', $permintaanIds)
            ->get()
            ->keyBy(fn ($r) => $r->permintaan_bahan_baku_id . '-' . $r->produsen_id);

        return view('pages.penerimaan-bahan-baku.index', compact(
            'penerimaans',
            'produsens',
            'totalPenerimaan',
            'penerimaanBulanIni',
            'belumLunas',
            'pembayaranMap'
        ));
    }

    public function create(): View
    {
        $produsens = Produsen::orderBy('nama_produsen')->get();

        return view('pages.penerimaan-bahan-baku.create', compact('produsens'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'produsen_id' => ['required', 'exists:produsens,id'],
            'tanggal_terima' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.permintaan_bahan_baku_detail_id' => ['required', 'integer'],
            'items.*.jumlah_diterima' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'items.*.tanggal_kadaluarsa' => ['nullable', 'date', 'after_or_equal:tanggal_terima'],
        ], self::MESSAGES);

        // Hanya baris yang diisi jumlahnya yang diproses.
        $items = collect($data['items'])
            ->filter(fn ($i) => (float) ($i['jumlah_diterima'] ?? 0) > 0)
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Isi jumlah diterima minimal untuk satu item.',
            ]);
        }

        if ($items->pluck('permintaan_bahan_baku_detail_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Ada item yang terkirim ganda, silakan muat ulang halaman.',
            ]);
        }

        $produsenId = (int) $data['produsen_id'];
        $pencatat = $this->pencatat();

        $penerimaan = DB::transaction(function () use ($data, $items, $produsenId, $pencatat) {
            $baris = $this->hitungBaris($produsenId);

            // Validasi tiap item terhadap batas yang disetujui produsen.
            $items = $items->map(function ($item) use ($baris) {
                $line = $baris[(int) $item['permintaan_bahan_baku_detail_id']] ?? null;
                if (! $line) {
                    throw ValidationException::withMessages([
                        'items' => 'Ada item yang tidak valid, bukan milik produsen ini, atau sudah diterima penuh.',
                    ]);
                }

                $jumlah = round((float) $item['jumlah_diterima'], 2);
                if ($jumlah > $line['sisa'] + 0.001) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah diterima untuk {$line['nama']} ({$line['nomor_permintaan']}) melebihi sisa yang disetujui produsen ({$line['sisa']}).",
                    ]);
                }

                return [
                    'line' => $line,
                    'jumlah' => $jumlah,
                    'kadaluarsa' => $item['tanggal_kadaluarsa'] ?? null,
                ];
            });

            $permintaanIds = $items->pluck('line.permintaan_id')->unique()->values()->all();
            if ($pesan = $this->pesanLunas($permintaanIds, $produsenId)) {
                throw ValidationException::withMessages(['items' => $pesan]);
            }

            $tanggal = Carbon::parse($data['tanggal_terima']);

            $penerimaan = PenerimaanBahanBaku::create([
                'admin_id' => $pencatat['admin_id'],
                'pemilik_id' => $pencatat['pemilik_id'],
                'produsen_id' => $produsenId,
                'nomor_transaksi' => $this->generateNomor($tanggal),
                'tanggal_terima' => $tanggal->toDateString(),
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($items as $item) {
                $penerimaan->penerimaanBahanBakuDetails()->create([
                    'permintaan_bahan_baku_detail_id' => $item['line']['id'],
                    'bahan_baku_id' => $item['line']['bahan_baku_id'],
                    'jumlah_diterima' => $item['jumlah'],
                    'jumlah_tersisa' => $item['jumlah'],
                    'tanggal_kadaluarsa' => $item['kadaluarsa'],
                ]);

                BahanBaku::whereKey($item['line']['bahan_baku_id'])
                    ->increment('stok_saat_ini', $item['jumlah']);
            }

            foreach ($permintaanIds as $permintaanId) {
                $this->sinkronPembayaran((int) $permintaanId, $produsenId);
            }

            return $penerimaan;
        });

        return redirect()
            ->route('penerimaan-bahan-baku.show', $penerimaan->id)
            ->with('success', "Penerimaan {$penerimaan->nomor_transaksi} berhasil dicatat, stok dan tagihan pembayaran diperbarui.");
    }

    public function show(PenerimaanBahanBaku $penerimaanBahanBaku): View
    {
        $penerimaanBahanBaku->load([
            'produsen',
            'penerimaanBahanBakuDetails.bahanBaku.satuan',
            'penerimaanBahanBakuDetails.permintaanBahanBakuDetail.permintaanBahanBaku',
        ]);

        $produsenId = (int) $penerimaanBahanBaku->produsen_id;
        $baris = $this->hitungBaris($produsenId, null, false);

        $permintaanIds = $this->permintaanIds($penerimaanBahanBaku->penerimaanBahanBakuDetails);
        $nomorPermintaan = PermintaanBahanBaku::whereIn('id', $permintaanIds)->pluck('nomor_transaksi', 'id');
        $sisaPerPermintaan = $this->hitungBaris($produsenId)
            ->groupBy('permintaan_id')
            ->map(fn ($g) => round($g->sum('sisa'), 2));

        // Tagihan per permintaan: total disatukan, tetapi dirinci per item dari semua nota produsen tsb.
        $tagihans = collect($permintaanIds)->map(function ($pid) use ($produsenId, $nomorPermintaan, $sisaPerPermintaan) {
            $rincian = $this->rincianPembayaran((int) $pid, $produsenId);

            return [
                'permintaan_id' => (int) $pid,
                'nomor' => $nomorPermintaan[$pid] ?? '-',
                'rincian' => $rincian,
                'total' => round($rincian->sum('subtotal'), 2),
                'sisa_belum' => $sisaPerPermintaan[$pid] ?? 0,
                'pembayaran' => DB::table('pembayarans')
                    ->where('permintaan_bahan_baku_id', $pid)
                    ->where('produsen_id', $produsenId)
                    ->first(),
            ];
        });

        $alasanTerkunci = $this->alasanTerkunci($penerimaanBahanBaku);

        return view('pages.penerimaan-bahan-baku.show', [
            'penerimaan' => $penerimaanBahanBaku,
            'baris' => $baris,
            'tagihans' => $tagihans,
            'alasanTerkunci' => $alasanTerkunci,
        ]);
    }

    // =========================================================================
    // Pembayaran (admin maupun pemilik boleh langsung membayar)
    // =========================================================================

    public function bayar(Request $request, PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        $data = $request->validate([
            'permintaan_bahan_baku_id' => ['required', 'integer'],
            'tanggal_bayar' => ['required', 'date', 'before_or_equal:today'],
            'metode_bayar' => ['required', Rule::in(self::METODE_BAYAR)],
            // Tunai maupun Transfer wajib disertai bukti (bukti transfer / bukti serah terima uang tunai).
            'bukti_bayar' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.before_or_equal' => 'Tanggal bayar tidak boleh melebihi hari ini.',
            'metode_bayar.required' => 'Metode bayar wajib diisi.',
            'metode_bayar.in' => 'Metode bayar harus Tunai atau Transfer.',
            'bukti_bayar.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_bayar.file' => 'Bukti pembayaran harus berupa file.',
            'bukti_bayar.mimes' => 'Bukti pembayaran harus berformat JPG, JPEG, PNG, atau WEBP.',
            'bukti_bayar.max' => 'Ukuran bukti pembayaran maksimal 4 MB.',
        ]);

        $permintaanId = (int) $data['permintaan_bahan_baku_id'];
        $produsenId = (int) $penerimaanBahanBaku->produsen_id;

        $pemilikId = $this->pemilikId();
        $pathBukti = null;

        try {
            DB::transaction(function () use ($data, $request, $penerimaanBahanBaku, $permintaanId, $produsenId, $pemilikId, &$pathBukti) {
                if (! in_array($permintaanId, $this->permintaanIds($penerimaanBahanBaku->penerimaanBahanBakuDetails), true)) {
                    throw ValidationException::withMessages(['pembayaran' => 'Permintaan tidak terkait dengan penerimaan ini.']);
                }

                $bayar = DB::table('pembayarans')
                    ->where('permintaan_bahan_baku_id', $permintaanId)
                    ->where('produsen_id', $produsenId)
                    ->lockForUpdate()
                    ->first();

                if ($bayar && $bayar->status_bayar === 'lunas') {
                    throw ValidationException::withMessages(['pembayaran' => 'Tagihan ini sudah lunas.']);
                }
                if ($bayar && $bayar->status_acc === 'ditolak') {
                    throw ValidationException::withMessages(['pembayaran' => 'Tagihan ini ditolak pemilik, sehingga tidak dapat dibayar.']);
                }

                // Pastikan nominal tagihan terbaru sebelum dilunasi.
                $this->sinkronPembayaran($permintaanId, $produsenId);

                $bayar = DB::table('pembayarans')
                    ->where('permintaan_bahan_baku_id', $permintaanId)
                    ->where('produsen_id', $produsenId)
                    ->first();

                if (! $bayar || (float) $bayar->jumlah_bayar <= 0) {
                    throw ValidationException::withMessages(['pembayaran' => 'Belum ada tagihan yang dapat dibayar.']);
                }

                // File baru disimpan setelah semua pengecekan lolos agar tidak ada file yatim.
                $pathBukti = $request->file('bukti_bayar')->store('bukti-pembayaran', 'public');

                $ubah = [
                    'status_acc' => 'disetujui', // dibayar langsung oleh admin / pemilik
                    'status_bayar' => 'lunas',
                    'tanggal_bayar' => $data['tanggal_bayar'],
                    'metode_bayar' => $data['metode_bayar'],
                    'bukti_bayar' => $pathBukti,
                    'updated_at' => now(),
                ];
                // pemilik_id hanya diisi bila pemilik yang membayar (null = diproses admin).
                if ($pemilikId) {
                    $ubah['pemilik_id'] = $pemilikId;
                }

                DB::table('pembayarans')->where('id', $bayar->id)->update($ubah);
            });
        } catch (ValidationException $e) {
            $this->hapusFile($pathBukti);

            return redirect()->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            $this->hapusFile($pathBukti);

            throw $e;
        }

        return redirect()
            ->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
            ->with('success', 'Pembayaran berhasil dicatat sebagai lunas.');
    }

    public function batalBayar(Request $request, PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        $permintaanId = (int) $request->input('permintaan_bahan_baku_id');
        $produsenId = (int) $penerimaanBahanBaku->produsen_id;

        if (! in_array($permintaanId, $this->permintaanIds($penerimaanBahanBaku->penerimaanBahanBakuDetails), true)) {
            return redirect()->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
                ->with('error', 'Permintaan tidak terkait dengan penerimaan ini.');
        }

        $buktiLama = DB::table('pembayarans')
            ->where('permintaan_bahan_baku_id', $permintaanId)
            ->where('produsen_id', $produsenId)
            ->where('status_bayar', 'lunas')
            ->value('bukti_bayar');

        $diubah = DB::table('pembayarans')
            ->where('permintaan_bahan_baku_id', $permintaanId)
            ->where('produsen_id', $produsenId)
            ->where('status_bayar', 'lunas')
            ->update([
                'status_bayar' => 'belum',
                'status_acc' => 'menunggu',
                'tanggal_bayar' => null,
                'metode_bayar' => null,
                'bukti_bayar' => null,
                'pemilik_id' => null,
                'updated_at' => now(),
            ]);

        if ($diubah) {
            $this->hapusFile($buktiLama);

            // Isi receipt berubah setelah pembayaran dibatalkan, jadi foto receipt lama tidak berlaku lagi.
            PenerimaanBahanBaku::where('produsen_id', $produsenId)
                ->whereNotNull('foto_receipt')
                ->whereHas('penerimaanBahanBakuDetails.permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $permintaanId))
                ->get()
                ->each(function (PenerimaanBahanBaku $p) {
                    $this->hapusFile($p->foto_receipt);
                    $p->update(['foto_receipt' => null]);
                });
        }

        return redirect()
            ->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
            ->with($diubah ? 'success' : 'error', $diubah ? 'Pembayaran dibatalkan, tagihan kembali berstatus belum dibayar.' : 'Tagihan tidak berstatus lunas.');
    }

    /**
     * Unggah foto/scan receipt yang sudah dicetak (dan ditandatangani).
     * Satu receipt mewakili seluruh tagihan lunas pada penerimaan ini, jadi disimpan di penerimaan.
     * Mengunggah lagi akan menggantikan file sebelumnya.
     */
    public function uploadReceipt(Request $request, PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        $request->validate([
            'foto_receipt' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'foto_receipt.required' => 'Pilih gambar receipt terlebih dahulu.',
            'foto_receipt.file' => 'Receipt harus berupa file.',
            'foto_receipt.mimes' => 'Receipt harus berformat JPG, JPEG, PNG, atau WEBP.',
            'foto_receipt.max' => 'Ukuran gambar receipt maksimal 4 MB.',
        ]);

        $permintaanIds = $this->permintaanIds($penerimaanBahanBaku->penerimaanBahanBakuDetails);

        $adaLunas = ! empty($permintaanIds) && DB::table('pembayarans')
            ->whereIn('permintaan_bahan_baku_id', $permintaanIds)
            ->where('produsen_id', $penerimaanBahanBaku->produsen_id)
            ->where('status_bayar', 'lunas')
            ->exists();

        if (! $adaLunas) {
            return redirect()->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
                ->with('error', 'Receipt hanya dapat diunggah setelah ada tagihan yang lunas.');
        }

        $lama = $penerimaanBahanBaku->foto_receipt;
        $baru = $request->file('foto_receipt')->store('receipt-pembayaran', 'public');

        try {
            $penerimaanBahanBaku->update(['foto_receipt' => $baru]);
        } catch (\Throwable $e) {
            $this->hapusFile($baru);

            throw $e;
        }

        $this->hapusFile($lama);

        return redirect()->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
            ->with('success', 'Gambar receipt berhasil diunggah.');
    }

    public function hapusReceipt(PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        $lama = $penerimaanBahanBaku->foto_receipt;

        if ($lama) {
            $penerimaanBahanBaku->update(['foto_receipt' => null]);
            $this->hapusFile($lama);
        }

        return redirect()->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
            ->with($lama ? 'success' : 'error', $lama ? 'Gambar receipt dihapus.' : 'Belum ada gambar receipt.');
    }

    public function edit(PenerimaanBahanBaku $penerimaanBahanBaku): View
    {
        $penerimaanBahanBaku->load([
            'produsen',
            'penerimaanBahanBakuDetails.bahanBaku.satuan',
            'penerimaanBahanBakuDetails.permintaanBahanBakuDetail.permintaanBahanBaku',
        ]);

        // sisa = batas disetujui - penerimaan lain (tidak termasuk nota ini)
        $baris = $this->hitungBaris((int) $penerimaanBahanBaku->produsen_id, $penerimaanBahanBaku->id, false);
        $alasanTerkunci = $this->alasanTerkunci($penerimaanBahanBaku);

        return view('pages.penerimaan-bahan-baku.edit', [
            'penerimaan' => $penerimaanBahanBaku,
            'baris' => $baris,
            'alasanTerkunci' => $alasanTerkunci,
        ]);
    }

    public function update(Request $request, PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        $request->validate([
            'tanggal_terima' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.jumlah_diterima' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'items.*.tanggal_kadaluarsa' => ['nullable', 'date', 'after_or_equal:tanggal_terima'],
        ], self::MESSAGES);

        DB::transaction(function () use ($request, $penerimaanBahanBaku) {
            $penerimaan = PenerimaanBahanBaku::whereKey($penerimaanBahanBaku->id)->lockForUpdate()->firstOrFail();
            $details = $penerimaan->penerimaanBahanBakuDetails()->lockForUpdate()->get();

            if ($alasan = $this->alasanTerkunci($penerimaan, $details)) {
                throw ValidationException::withMessages(['items' => $alasan]);
            }

            $produsenId = (int) $penerimaan->produsen_id;
            $baris = $this->hitungBaris($produsenId, $penerimaan->id, false);
            $items = $request->input('items', []);

            foreach ($details as $detail) {
                $input = $items[$detail->id] ?? null;
                if (! $input) {
                    throw ValidationException::withMessages([
                        'items' => 'Data item tidak lengkap, silakan muat ulang halaman.',
                    ]);
                }

                $baru = round((float) $input['jumlah_diterima'], 2);
                $line = $detail->permintaan_bahan_baku_detail_id
                    ? ($baris[$detail->permintaan_bahan_baku_detail_id] ?? null)
                    : null;

                if ($line && $baru > $line['sisa'] + 0.001) {
                    throw ValidationException::withMessages([
                        'items' => "Jumlah untuk {$line['nama']} melebihi batas yang disetujui produsen (maks. {$line['sisa']}).",
                    ]);
                }

                $selisih = round($baru - (float) $detail->jumlah_diterima, 2);

                // Batch belum dipakai, jadi sisa batch = jumlah diterima.
                $detail->update([
                    'jumlah_diterima' => $baru,
                    'jumlah_tersisa' => $baru,
                    'tanggal_kadaluarsa' => $input['tanggal_kadaluarsa'] ?? null,
                ]);

                if (abs($selisih) > 0.0001) {
                    BahanBaku::whereKey($detail->bahan_baku_id)->increment('stok_saat_ini', $selisih);
                }
            }

            $penerimaan->update([
                'tanggal_terima' => $request->input('tanggal_terima'),
                'catatan' => $request->input('catatan'),
            ]);

            foreach ($this->permintaanIds($details) as $permintaanId) {
                $this->sinkronPembayaran((int) $permintaanId, $produsenId);
            }
        });

        return redirect()
            ->route('penerimaan-bahan-baku.show', $penerimaanBahanBaku->id)
            ->with('success', 'Penerimaan bahan baku berhasil diperbarui.');
    }

    public function destroy(PenerimaanBahanBaku $penerimaanBahanBaku): RedirectResponse
    {
        try {
            DB::transaction(function () use ($penerimaanBahanBaku) {
                $penerimaan = PenerimaanBahanBaku::whereKey($penerimaanBahanBaku->id)->lockForUpdate()->firstOrFail();
                $details = $penerimaan->penerimaanBahanBakuDetails()->lockForUpdate()->get();

                if ($alasan = $this->alasanTerkunci($penerimaan, $details)) {
                    throw ValidationException::withMessages(['items' => $alasan]);
                }

                $produsenId = (int) $penerimaan->produsen_id;
                $permintaanIds = $this->permintaanIds($details);

                foreach ($details as $detail) {
                    BahanBaku::whereKey($detail->bahan_baku_id)
                        ->decrement('stok_saat_ini', (float) $detail->jumlah_diterima);
                    $detail->delete();
                }
                $penerimaan->delete();

                foreach ($permintaanIds as $permintaanId) {
                    $this->sinkronPembayaran((int) $permintaanId, $produsenId);
                }
            });
        } catch (ValidationException $e) {
            return redirect()->route('penerimaan-bahan-baku.index')
                ->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()
            ->route('penerimaan-bahan-baku.index')
            ->with('success', 'Penerimaan bahan baku dihapus, stok dan tagihan pembayaran disesuaikan.');
    }

    // =========================================================================
    // AJAX: item yang masih bisa diterima dari satu produsen
    // =========================================================================

    public function itemProdusen(Produsen $produsen): JsonResponse
    {
        return response()->json(
            $this->hitungBaris((int) $produsen->id)->values()
        );
    }

    // =========================================================================
    // Helper
    // =========================================================================

    /**
     * Baris permintaan yang sudah disetujui produsen (disetujui / disetujui_sebagian).
     * Mengembalikan batas jumlah & harga satuan, atau null bila belum/tidak disetujui.
     *
     * Pengalihan tidak dilibatkan: ia hanya penanda kekurangan, dan kekurangannya akan
     * muncul lagi sebagai permintaan bahan baku baru yang disetujui terpisah.
     */
    private function resolveBaris(PermintaanBahanBakuDetail $d, int $produsenId): ?array
    {
        if ((int) $d->produsen_id !== $produsenId || ! in_array($d->status_produsen, self::STATUS_SETUJU, true)) {
            return null;
        }

        $batas = $d->jumlah_disetujui ?? ($d->status_produsen === 'disetujui' ? $d->jumlah_diminta : 0);

        return [
            'bahan_baku_id' => (int) $d->bahan_baku_id,
            'harga' => (float) $d->harga_satuan,
            'batas' => (float) $batas,
        ];
    }

    /**
     * Daftar baris yang disetujui produsen, lengkap dengan jumlah yang sudah diterima & sisa.
     * Hasil di-key dengan id permintaan_bahan_baku_detail.
     *
     * @param  int|null  $kecuali  id penerimaan yang tidak dihitung (dipakai saat edit)
     * @param  bool  $hanyaSisa  true = hanya baris yang masih punya sisa
     */
    private function hitungBaris(int $produsenId, ?int $kecuali = null, bool $hanyaSisa = true): Collection
    {
        $details = PermintaanBahanBakuDetail::query()
            ->with('permintaanBahanBaku:id,nomor_transaksi')
            ->where('produsen_id', $produsenId)
            ->whereIn('status_produsen', self::STATUS_SETUJU)
            ->orderBy('permintaan_bahan_baku_id')
            ->orderBy('id')
            ->get();

        if ($details->isEmpty()) {
            return collect();
        }

        $diterima = PenerimaanBahanBakuDetail::query()
            ->whereIn('permintaan_bahan_baku_detail_id', $details->pluck('id'))
            ->whereHas('penerimaanBahanBaku', function ($q) use ($produsenId, $kecuali) {
                $q->where('produsen_id', $produsenId);
                if ($kecuali) {
                    $q->where('id', '!=', $kecuali);
                }
            })
            ->selectRaw('permintaan_bahan_baku_detail_id, SUM(jumlah_diterima) as total')
            ->groupBy('permintaan_bahan_baku_detail_id')
            ->pluck('total', 'permintaan_bahan_baku_detail_id');

        $tmp = [];
        foreach ($details as $d) {
            $line = $this->resolveBaris($d, $produsenId);
            if (! $line) {
                continue;
            }

            $sudah = (float) ($diterima[$d->id] ?? 0);
            $sisa = round($line['batas'] - $sudah, 2);
            if ($hanyaSisa && $sisa <= 0) {
                continue;
            }

            $tmp[$d->id] = $line + [
                'id' => $d->id,
                'permintaan_id' => (int) $d->permintaan_bahan_baku_id,
                'nomor_permintaan' => $d->permintaanBahanBaku->nomor_transaksi ?? '-',
                'diterima' => round($sudah, 2),
                'sisa' => $sisa,
            ];
        }

        $bahans = BahanBaku::with('satuan')
            ->whereIn('id', collect($tmp)->pluck('bahan_baku_id')->unique())
            ->get()
            ->keyBy('id');

        return collect($tmp)->map(function ($line) use ($bahans) {
            $b = $bahans[$line['bahan_baku_id']] ?? null;

            return $line + [
                'kode' => $b->kode_bahan_baku ?? '-',
                'nama' => $b->nama_bahan_baku ?? '-',
                'satuan' => $b?->satuan?->kode_satuan ?: ($b?->satuan?->nama ?? ''),
            ];
        });
    }

    /**
     * Rincian tagihan satu permintaan + produsen: satu baris per item yang diterima
     * (dari semua nota), lengkap dengan harga dan subtotal.
     */
    private function rincianPembayaran(int $permintaanId, int $produsenId): Collection
    {
        return PenerimaanBahanBakuDetail::query()
            ->whereHas('penerimaanBahanBaku', fn ($q) => $q->where('produsen_id', $produsenId))
            ->whereHas('permintaanBahanBakuDetail', fn ($q) => $q->where('permintaan_bahan_baku_id', $permintaanId))
            ->with([
                'penerimaanBahanBaku:id,nomor_transaksi,tanggal_terima',
                'permintaanBahanBakuDetail:id,harga_satuan',
                'bahanBaku.satuan',
            ])
            ->get()
            ->sortBy(fn ($d) => $d->penerimaanBahanBaku->tanggal_terima->format('Ymd') . str_pad((string) $d->id, 10, '0', STR_PAD_LEFT))
            ->map(function ($d) {
                $harga = (float) $d->permintaanBahanBakuDetail->harga_satuan;
                $jumlah = (float) $d->jumlah_diterima;

                return [
                    'penerimaan_id' => $d->penerimaan_bahan_baku_id,
                    'nomor' => $d->penerimaanBahanBaku->nomor_transaksi,
                    'tanggal' => $d->penerimaanBahanBaku->tanggal_terima,
                    'kode' => $d->bahanBaku->kode_bahan_baku,
                    'nama' => $d->bahanBaku->nama_bahan_baku,
                    'satuan' => $d->bahanBaku->satuan->kode_satuan ?: ($d->bahanBaku->satuan->nama ?? ''),
                    'jumlah' => $jumlah,
                    'harga' => $harga,
                    'subtotal' => round($jumlah * $harga, 2),
                ];
            })
            ->values();
    }

    /** Total tagihan = SUM(jumlah_diterima x harga) untuk satu permintaan + produsen. */
    private function hitungTotal(int $permintaanId, int $produsenId): float
    {
        return round((float) $this->rincianPembayaran($permintaanId, $produsenId)->sum('subtotal'), 2);
    }

    /**
     * Buat / perbarui tagihan di tabel pembayarans (unik per permintaan + produsen).
     * Pelunasan (status_bayar) bisa dilakukan admin maupun pemilik di modul Pembayaran.
     */
    private function sinkronPembayaran(int $permintaanId, int $produsenId): void
    {
        $total = $this->hitungTotal($permintaanId, $produsenId);
        $now = now();

        $existing = DB::table('pembayarans')
            ->where('permintaan_bahan_baku_id', $permintaanId)
            ->where('produsen_id', $produsenId)
            ->first();

        if ($total <= 0) {
            if ($existing && $existing->status_bayar === 'belum') {
                DB::table('pembayarans')->where('id', $existing->id)->delete();
            }

            return;
        }

        if ($existing) {
            $ubah = ['jumlah_bayar' => $total, 'updated_at' => $now];
            if (abs((float) $existing->jumlah_bayar - $total) > 0.004) {
                $ubah['status_acc'] = 'menunggu'; // nominal berubah -> perlu persetujuan ulang
            }
            DB::table('pembayarans')->where('id', $existing->id)->update($ubah);

            return;
        }

        DB::table('pembayarans')->insert([
            'permintaan_bahan_baku_id' => $permintaanId,
            'produsen_id' => $produsenId,
            'pemilik_id' => null, // diisi bila pemilik yang memproses pembayaran (admin atau pemilik boleh)
            'jumlah_bayar' => $total,
            'tanggal_bayar' => null,
            'status_acc' => 'menunggu',
            'status_bayar' => 'belum',
            'metode_bayar' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Siapa yang mencatat penerimaan: admin (admin_id) atau pemilik (pemilik_id).
     *
     * @return array{admin_id: int|null, pemilik_id: int|null}
     */
    private function pencatat(): array
    {
        if (auth()->user()->role === 'pemilik') {
            $pemilik = Pemilik::where('user_id', auth()->id())->firstOrFail();

            return ['admin_id' => null, 'pemilik_id' => (int) $pemilik->id];
        }

        $admin = Admin::where('user_id', auth()->id())->firstOrFail();

        return ['admin_id' => (int) $admin->id, 'pemilik_id' => null];
    }

    /** id pemilik yang sedang login (null bila yang login admin). */
    private function pemilikId(): ?int
    {
        if (auth()->user()->role !== 'pemilik') {
            return null;
        }

        return (int) Pemilik::where('user_id', auth()->id())->firstOrFail()->id;
    }

    /** Hapus file dari disk public bila ada (aman dipanggil dengan null). */
    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function generateNomor(Carbon $tanggal): string
    {
        $prefix = 'PNB-' . $tanggal->format('dmY') . '-';

        $last = PenerimaanBahanBaku::where('nomor_transaksi', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('nomor_transaksi')
            ->value('nomor_transaksi');

        $next = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    /** @return array<int> id permintaan_bahan_baku dari kumpulan detail penerimaan */
    private function permintaanIds($details): array
    {
        $detailIds = collect($details)->pluck('permintaan_bahan_baku_detail_id')->filter()->unique();

        if ($detailIds->isEmpty()) {
            return [];
        }

        return PermintaanBahanBakuDetail::whereIn('id', $detailIds)
            ->pluck('permintaan_bahan_baku_id')
            ->unique()
            ->values()
            ->all();
    }

    private function pesanLunas(array $permintaanIds, int $produsenId): ?string
    {
        if (empty($permintaanIds)) {
            return null;
        }

        $lunas = DB::table('pembayarans')
            ->whereIn('permintaan_bahan_baku_id', $permintaanIds)
            ->where('produsen_id', $produsenId)
            ->where('status_bayar', 'lunas')
            ->pluck('permintaan_bahan_baku_id');

        if ($lunas->isEmpty()) {
            return null;
        }

        $nomor = PermintaanBahanBaku::whereIn('id', $lunas)->pluck('nomor_transaksi')->implode(', ');

        return "Pembayaran untuk permintaan {$nomor} sudah lunas, sehingga penerimaan terkait tidak dapat ditambah/diubah/dihapus.";
    }

    /** Alasan penerimaan tidak boleh diubah/dihapus (null = boleh). */
    private function alasanTerkunci(PenerimaanBahanBaku $penerimaan, $details = null): ?string
    {
        $details ??= $penerimaan->penerimaanBahanBakuDetails;

        foreach ($details as $d) {
            if (round((float) $d->jumlah_tersisa, 2) < round((float) $d->jumlah_diterima, 2)) {
                return 'Sebagian bahan baku dari penerimaan ini sudah dipakai, sehingga tidak dapat diubah atau dihapus.';
            }
        }

        $detailIds = collect($details)->pluck('id');
        if ($detailIds->isNotEmpty()
            && DB::table('pemakaian_bahan_baku_details')->whereIn('penerimaan_bahan_baku_detail_id', $detailIds)->exists()
        ) {
            return 'Penerimaan ini sudah tercatat di pemakaian bahan baku, sehingga tidak dapat diubah atau dihapus.';
        }

        return $this->pesanLunas($this->permintaanIds($details), (int) $penerimaan->produsen_id);
    }
}
