<?php

use App\Http\Controllers\Login\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Pemilik\AdminController;
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
});

Route::middleware(['auth', 'role:produsen'])->group(function () {
    Route::get('/produsen/dashboard', [DashboardController::class, 'produsen'])->name('produsen.dashboard');
});
