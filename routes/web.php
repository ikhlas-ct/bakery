<?php

use App\Http\Controllers\Login\AuthController;
use Illuminate\Support\Facades\Route;












// =================== Auth Routes ===================
Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'login_post'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');





    Route::middleware('role:admin_dinsos')->group(function () {

    });


