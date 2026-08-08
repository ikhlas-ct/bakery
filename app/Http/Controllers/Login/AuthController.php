<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('pages.login.login');
    }

    public function login_post(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan role
            if ($user->isAdminDinsos()) {
                return redirect()->route('dinsos.dashboard')->with('success', 'Login berhasil');
            } elseif ($user->isAdminPanti()) {
                return redirect()->route('admin_panti.dashboard')->with('success', 'Login berhasil');
            } elseif ($user->isDonatur()) {
                return redirect()->route('donatur.dashboard')->with('success', 'Login berhasil');
            }

            // Fallback jika role tidak dikenali
            Auth::logout();
            return back()->withErrors([
                'username' => 'Role tidak dikenali, hubungi administrator.',
            ]);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}
