<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin(Request $request)
    {
        return view('pages.admin.dashboard');
    }

    public function pemilik(Request $request)
    {
        return view('pages.pemilik.dashboard');
    }

    public function produsen(Request $request)
    {
        return view('pages.produsen.dashboard');
    }
}
