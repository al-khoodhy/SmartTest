<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $user = auth()->user();
        
        // Redirect berdasarkan role user
        if ($user->isAdmin()) {
            return redirect()->route('voyager.dashboard');
        } elseif ($user->isDosen()) {
            return redirect()->route('dosen.dashboard');
        } elseif ($user->isMahasiswa()) {
            return redirect()->route('mahasiswa.dashboard');
        } else {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Role tidak valid.');
        }
    }
}
