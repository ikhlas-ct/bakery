<?php

use App\Http\Controllers\Login\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Pemilik\AdminController;
use App\Http\Controllers\ProdusenController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\SatuanController;
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
});

Route::middleware(['auth', 'role:produsen'])->group(function () {
    Route::get('/produsen/dashboard', [DashboardController::class, 'produsen'])->name('produsen.dashboard');

    // =================== Profil Produsen (dikelola oleh Produsen sendiri) ===================
    Route::get('/produsen/profile', [ProdusenController::class, 'profile'])->name('produsen.profile');
    Route::put('/produsen/profile', [ProdusenController::class, 'updateProfile'])->name('produsen.profile.update');
});
