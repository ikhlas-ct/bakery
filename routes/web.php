<?php

use App\Http\Controllers\Login\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Pemilik\AdminController;
use App\Http\Controllers\ProdusenController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BahanBakuProdusenController;
use App\Http\Controllers\PermintaanBahanBakuController;
use App\Http\Controllers\PermintaanMasukController;
use App\Http\Controllers\PengalihanBahanBakuController;
use App\Http\Controllers\PenerimaanBahanBakuController;
use App\Http\Controllers\PemakaianBahanBakuController;
use App\Http\Controllers\PermintaanTambahanController;
use App\Http\Controllers\PembayaranProdusenController;
use App\Http\Controllers\LaporanPenerimaanBahanBakuController;
use App\Http\Controllers\LaporanPemakaianBahanBakuController;
use App\Http\Controllers\LaporanReceiptPembayaranController;
use Illuminate\Support\Facades\Route;

// =================== Auth Routes ===================
Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'login_post'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =================== Dashboard per Role ===================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])->name('admin.dashboard');

    // =================== Pengaturan Website ===================
    Route::get('/setting/website', [SettingController::class, 'edit'])->name('setting.website.edit');
    Route::put('/setting/website', [SettingController::class, 'update'])->name('setting.website.update');

    // =================== CRUD Produsen (dikelola oleh Admin) ===================
    Route::get('/admin/produsen', [ProdusenController::class, 'index'])->name('admin.produsen.index');
    Route::get('/admin/produsen/create', [ProdusenController::class, 'create'])->name('admin.produsen.create');
    Route::post('/admin/produsen', [ProdusenController::class, 'store'])->name('admin.produsen.store');
    Route::get('/admin/produsen/{produsen}', [ProdusenController::class, 'show'])->name('admin.produsen.show');
    Route::get('/admin/produsen/{produsen}/edit', [ProdusenController::class, 'edit'])->name('admin.produsen.edit');
    Route::put('/admin/produsen/{produsen}', [ProdusenController::class, 'update'])->name('admin.produsen.update');
    Route::delete('/admin/produsen/{produsen}', [ProdusenController::class, 'destroy'])->name('admin.produsen.destroy');
});

Route::middleware(['auth', 'role:pemilik'])->group(function () {
    Route::get('/pemilik/dashboard', [DashboardController::class, 'pemilik'])->name('pemilik.dashboard');

    // =================== CRUD Admin (dikelola oleh Pemilik) ===================
    Route::get('/pemilik/admin', [AdminController::class, 'index'])->name('pemilik.admin.index');
    Route::get('/pemilik/admin/create', [AdminController::class, 'create'])->name('pemilik.admin.create');
    Route::post('/pemilik/admin', [AdminController::class, 'store'])->name('pemilik.admin.store');
    Route::get('/pemilik/admin/{admin}', [AdminController::class, 'show'])->name('pemilik.admin.show');
    Route::get('/pemilik/admin/{admin}/edit', [AdminController::class, 'edit'])->name('pemilik.admin.edit');
    Route::put('/pemilik/admin/{admin}', [AdminController::class, 'update'])->name('pemilik.admin.update');
    Route::delete('/pemilik/admin/{admin}', [AdminController::class, 'destroy'])->name('pemilik.admin.destroy');

    // =================== CRUD Produsen (dikelola oleh Pemilik) ===================
    Route::get('/pemilik/produsen', [ProdusenController::class, 'index'])->name('pemilik.produsen.index');
    Route::get('/pemilik/produsen/create', [ProdusenController::class, 'create'])->name('pemilik.produsen.create');
    Route::post('/pemilik/produsen', [ProdusenController::class, 'store'])->name('pemilik.produsen.store');
    Route::get('/pemilik/produsen/{produsen}', [ProdusenController::class, 'show'])->name('pemilik.produsen.show');
    Route::get('/pemilik/produsen/{produsen}/edit', [ProdusenController::class, 'edit'])->name('pemilik.produsen.edit');
    Route::put('/pemilik/produsen/{produsen}', [ProdusenController::class, 'update'])->name('pemilik.produsen.update');
    Route::delete('/pemilik/produsen/{produsen}', [ProdusenController::class, 'destroy'])->name('pemilik.produsen.destroy');
});

// =================== Master Data (dikelola oleh Admin saja) ===================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // ── CRUD Kategori Barang (tanpa show) ──
    Route::get('/kategori-barang', [KategoriBarangController::class, 'index'])->name('kategori-barang.index');
    Route::get('/kategori-barang/create', [KategoriBarangController::class, 'create'])->name('kategori-barang.create');
    Route::post('/kategori-barang', [KategoriBarangController::class, 'store'])->name('kategori-barang.store');
    Route::get('/kategori-barang/{kategoriBarang}/edit', [KategoriBarangController::class, 'edit'])->name('kategori-barang.edit');
    Route::put('/kategori-barang/{kategoriBarang}', [KategoriBarangController::class, 'update'])->name('kategori-barang.update');
    Route::delete('/kategori-barang/{kategoriBarang}', [KategoriBarangController::class, 'destroy'])->name('kategori-barang.destroy');

    // ── CRUD Satuan (tanpa show) ──
    Route::get('/satuan', [SatuanController::class, 'index'])->name('satuan.index');
    Route::get('/satuan/create', [SatuanController::class, 'create'])->name('satuan.create');
    Route::post('/satuan', [SatuanController::class, 'store'])->name('satuan.store');
    Route::get('/satuan/{satuan}/edit', [SatuanController::class, 'edit'])->name('satuan.edit');
    Route::put('/satuan/{satuan}', [SatuanController::class, 'update'])->name('satuan.update');
    Route::delete('/satuan/{satuan}', [SatuanController::class, 'destroy'])->name('satuan.destroy');

    // ── CRUD Bahan Baku (tanpa show) ──
    Route::get('/bahan-baku', [BahanBakuController::class, 'index'])->name('bahan-baku.index');
    Route::get('/bahan-baku/create', [BahanBakuController::class, 'create'])->name('bahan-baku.create');
    Route::post('/bahan-baku', [BahanBakuController::class, 'store'])->name('bahan-baku.store');
    Route::get('/bahan-baku/{bahanBaku}/edit', [BahanBakuController::class, 'edit'])->name('bahan-baku.edit');
    Route::put('/bahan-baku/{bahanBaku}', [BahanBakuController::class, 'update'])->name('bahan-baku.update');
    Route::delete('/bahan-baku/{bahanBaku}', [BahanBakuController::class, 'destroy'])->name('bahan-baku.destroy');
});

// =================== Transaksi (dikelola oleh Admin saja) ===================
Route::middleware(['auth', 'role:admin'])->group(function () {

    // Endpoint AJAX: ambil produsen (+harga) yang memasok sebuah bahan baku.
    // Dipakai oleh dropdown produsen dinamis di form Permintaan Bahan Baku.
    Route::get('/bahan-baku/{bahanBaku}/produsens', [PermintaanBahanBakuController::class, 'produsenByBahanBaku'])->name('bahan-baku.produsens');

    // ── CRUD Permintaan Bahan Baku (tanpa create-only show; edit sekaligus kelola item pending) ──
    Route::get('/permintaan-bahan-baku', [PermintaanBahanBakuController::class, 'index'])->name('permintaan-bahan-baku.index');
    Route::get('/permintaan-bahan-baku/create', [PermintaanBahanBakuController::class, 'create'])->name('permintaan-bahan-baku.create');
    Route::post('/permintaan-bahan-baku', [PermintaanBahanBakuController::class, 'store'])->name('permintaan-bahan-baku.store');
    Route::get('/permintaan-bahan-baku/{permintaanBahanBaku}', [PermintaanBahanBakuController::class, 'show'])->name('permintaan-bahan-baku.show');
    Route::get('/permintaan-bahan-baku/{permintaanBahanBaku}/edit', [PermintaanBahanBakuController::class, 'edit'])->name('permintaan-bahan-baku.edit');
    Route::put('/permintaan-bahan-baku/{permintaanBahanBaku}', [PermintaanBahanBakuController::class, 'update'])->name('permintaan-bahan-baku.update');
    Route::delete('/permintaan-bahan-baku/{permintaanBahanBaku}', [PermintaanBahanBakuController::class, 'destroy'])->name('permintaan-bahan-baku.destroy');

    // ── Pengalihan Bahan Baku (baris dibuat otomatis saat produsen menyetujui sebagian/menolak; admin menentukan pengganti) ──
    Route::get('/pengalihan-bahan-baku', [PengalihanBahanBakuController::class, 'index'])->name('pengalihan-bahan-baku.index');
    Route::get('/pengalihan-bahan-baku/{pengalihanBahanBaku}', [PengalihanBahanBakuController::class, 'show'])->name('pengalihan-bahan-baku.show');
    Route::get('/pengalihan-bahan-baku/{pengalihanBahanBaku}/edit', [PengalihanBahanBakuController::class, 'edit'])->name('pengalihan-bahan-baku.edit');
    Route::put('/pengalihan-bahan-baku/{pengalihanBahanBaku}', [PengalihanBahanBakuController::class, 'update'])->name('pengalihan-bahan-baku.update');
    Route::delete('/pengalihan-bahan-baku/{pengalihanBahanBaku}', [PengalihanBahanBakuController::class, 'destroy'])->name('pengalihan-bahan-baku.destroy');

    // ── Penerimaan Bahan Baku (admin mencatat barang yang dikirim produsen; stok & tagihan pembayaran ikut diperbarui) ──
    // Endpoint AJAX: semua item (dari berbagai permintaan) yang disetujui produsen & belum diterima penuh (dipakai form tambah).
    Route::get('/penerimaan-bahan-baku/produsen/{produsen}/item', [PenerimaanBahanBakuController::class, 'itemProdusen'])->name('penerimaan-bahan-baku.item-produsen');

    Route::get('/penerimaan-bahan-baku', [PenerimaanBahanBakuController::class, 'index'])->name('penerimaan-bahan-baku.index');
    Route::get('/penerimaan-bahan-baku/create', [PenerimaanBahanBakuController::class, 'create'])->name('penerimaan-bahan-baku.create');
    Route::post('/penerimaan-bahan-baku', [PenerimaanBahanBakuController::class, 'store'])->name('penerimaan-bahan-baku.store');
    Route::get('/penerimaan-bahan-baku/{penerimaanBahanBaku}', [PenerimaanBahanBakuController::class, 'show'])->name('penerimaan-bahan-baku.show');
    Route::get('/penerimaan-bahan-baku/{penerimaanBahanBaku}/edit', [PenerimaanBahanBakuController::class, 'edit'])->name('penerimaan-bahan-baku.edit');
    Route::put('/penerimaan-bahan-baku/{penerimaanBahanBaku}', [PenerimaanBahanBakuController::class, 'update'])->name('penerimaan-bahan-baku.update');
    Route::delete('/penerimaan-bahan-baku/{penerimaanBahanBaku}', [PenerimaanBahanBakuController::class, 'destroy'])->name('penerimaan-bahan-baku.destroy');

    // Pembayaran tagihan produsen dari halaman detail penerimaan (admin bisa langsung melunasi / membatalkan pelunasan).
    Route::post('/penerimaan-bahan-baku/{penerimaanBahanBaku}/bayar', [PenerimaanBahanBakuController::class, 'bayar'])->name('penerimaan-bahan-baku.bayar');
    Route::post('/penerimaan-bahan-baku/{penerimaanBahanBaku}/batal-bayar', [PenerimaanBahanBakuController::class, 'batalBayar'])->name('penerimaan-bahan-baku.batal-bayar');

    // Gambar receipt yang sudah dicetak (diunggah admin setelah pembayaran lunas).
    Route::post('/penerimaan-bahan-baku/{penerimaanBahanBaku}/receipt', [PenerimaanBahanBakuController::class, 'uploadReceipt'])->name('penerimaan-bahan-baku.upload-receipt');
    Route::delete('/penerimaan-bahan-baku/{penerimaanBahanBaku}/receipt', [PenerimaanBahanBakuController::class, 'hapusReceipt'])->name('penerimaan-bahan-baku.hapus-receipt');

    // ── Pemakaian Bahan Baku (pengeluaran stok gudang oleh admin dengan metode FEFO) ──
    // Stok diambil otomatis dari batch penerimaan dengan tanggal terima paling lama; stok batch & cache stok bahan baku ikut berkurang.
    // Endpoint AJAX: daftar batch (urutan FEFO) untuk pratinjau di form. Harus berada SEBELUM route {pemakaianBahanBaku}.
    Route::get('/pemakaian-bahan-baku/bahan-baku/{bahanBaku}/batch', [PemakaianBahanBakuController::class, 'batch'])->name('pemakaian-bahan-baku.batch');

    Route::get('/pemakaian-bahan-baku', [PemakaianBahanBakuController::class, 'index'])->name('pemakaian-bahan-baku.index');
    Route::get('/pemakaian-bahan-baku/create', [PemakaianBahanBakuController::class, 'create'])->name('pemakaian-bahan-baku.create');
    Route::post('/pemakaian-bahan-baku', [PemakaianBahanBakuController::class, 'store'])->name('pemakaian-bahan-baku.store');
    Route::get('/pemakaian-bahan-baku/{pemakaianBahanBaku}', [PemakaianBahanBakuController::class, 'show'])->name('pemakaian-bahan-baku.show');
    Route::get('/pemakaian-bahan-baku/{pemakaianBahanBaku}/edit', [PemakaianBahanBakuController::class, 'edit'])->name('pemakaian-bahan-baku.edit');
    Route::put('/pemakaian-bahan-baku/{pemakaianBahanBaku}', [PemakaianBahanBakuController::class, 'update'])->name('pemakaian-bahan-baku.update');
    Route::delete('/pemakaian-bahan-baku/{pemakaianBahanBaku}', [PemakaianBahanBakuController::class, 'destroy'])->name('pemakaian-bahan-baku.destroy');
});

// =================== Laporan (Admin & Pemilik) ===================
// Akses dibatasi ke admin & pemilik di dalam controller (authorizeRole), jadi middleware cukup 'auth'.
Route::middleware(['auth'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/penerimaan-bahan-baku', [LaporanPenerimaanBahanBakuController::class, 'index'])->name('penerimaan-bahan-baku.index');
    Route::get('/penerimaan-bahan-baku/cetak', [LaporanPenerimaanBahanBakuController::class, 'cetak'])->name('penerimaan-bahan-baku.cetak');

    // Laporan Pemakaian Bahan Baku (FEFO) per periode: harian, bulanan, tahunan, atau antara dua tanggal.
    Route::get('/pemakaian-bahan-baku', [LaporanPemakaianBahanBakuController::class, 'index'])->name('pemakaian-bahan-baku.index');
    Route::get('/pemakaian-bahan-baku/cetak', [LaporanPemakaianBahanBakuController::class, 'cetak'])->name('pemakaian-bahan-baku.cetak');
    // Cetak satu transaksi pemakaian saja (tombol Cetak di halaman daftar & detail pemakaian).
    Route::get('/pemakaian-bahan-baku/{pemakaianBahanBaku}/cetak', [LaporanPemakaianBahanBakuController::class, 'cetakTransaksi'])->name('pemakaian-bahan-baku.cetak-transaksi');

    // Receipt Pembayaran (satu receipt per penerimaan, hanya yang lunas).
    Route::get('/receipt-pembayaran', [LaporanReceiptPembayaranController::class, 'index'])->name('receipt-pembayaran.index');
    Route::get('/receipt-pembayaran/{penerimaan}', [LaporanReceiptPembayaranController::class, 'show'])->name('receipt-pembayaran.show');
});

Route::middleware(['auth', 'role:produsen'])->group(function () {
    Route::get('/produsen/dashboard', [DashboardController::class, 'produsen'])->name('produsen.dashboard');

    // =================== Profil Produsen (dikelola oleh Produsen sendiri) ===================
    Route::get('/produsen/profile', [ProdusenController::class, 'profile'])->name('produsen.profile');
    Route::put('/produsen/profile', [ProdusenController::class, 'updateProfile'])->name('produsen.profile.update');

    // =================== Bahan Baku yang Disediakan Produsen (tabel bahan_baku_produsens) ===================
    // Produsen memilih dari bahan baku master (dibuat admin) mana saja yang bisa ia sediakan + harganya.
    Route::get('/produsen/bahan-baku', [BahanBakuProdusenController::class, 'index'])->name('produsen.bahan-baku.index');
    Route::get('/produsen/bahan-baku/create', [BahanBakuProdusenController::class, 'create'])->name('produsen.bahan-baku.create');
    Route::post('/produsen/bahan-baku', [BahanBakuProdusenController::class, 'store'])->name('produsen.bahan-baku.store');
    Route::get('/produsen/bahan-baku/{bahanBakuProdusen}/edit', [BahanBakuProdusenController::class, 'edit'])->name('produsen.bahan-baku.edit');
    Route::put('/produsen/bahan-baku/{bahanBakuProdusen}', [BahanBakuProdusenController::class, 'update'])->name('produsen.bahan-baku.update');
    Route::delete('/produsen/bahan-baku/{bahanBakuProdusen}', [BahanBakuProdusenController::class, 'destroy'])->name('produsen.bahan-baku.destroy');

    // =================== Permintaan Bahan Baku Masuk (dari Admin ke Produsen) ===================
    // Produsen melihat permintaan yang ditujukan kepadanya, lalu mengisi jumlah yang sanggup disediakan.
    // Jika jumlah kurang dari yang diminta (atau ditolak), kekurangan otomatis memicu data pengalihan.
    Route::get('/produsen/permintaan', [PermintaanMasukController::class, 'index'])->name('produsen.permintaan.index');
    Route::get('/produsen/permintaan/{permintaanBahanBaku}', [PermintaanMasukController::class, 'show'])->name('produsen.permintaan.show');
    Route::put('/produsen/permintaan/{permintaanBahanBaku}', [PermintaanMasukController::class, 'respon'])->name('produsen.permintaan.respon');

    // =================== Permintaan Tambahan (produsen pengganti menanggapi) ===================
    Route::get('/produsen/permintaan-tambahan', [PermintaanTambahanController::class, 'index'])->name('produsen.permintaan-tambahan.index');
    Route::get('/produsen/permintaan-tambahan/{pengalihanBahanBaku}', [PermintaanTambahanController::class, 'show'])->name('produsen.permintaan-tambahan.show');
    Route::put('/produsen/permintaan-tambahan/{pengalihanBahanBaku}', [PermintaanTambahanController::class, 'respon'])->name('produsen.permintaan-tambahan.respon');

    // =================== Pembayaran Saya (hanya baca; milik produsen yang login) ===================
    Route::get('/produsen/pembayaran', [PembayaranProdusenController::class, 'index'])->name('produsen.pembayaran.index');
    Route::get('/produsen/pembayaran/{pembayaran}', [PembayaranProdusenController::class, 'show'])->name('produsen.pembayaran.show');
});
