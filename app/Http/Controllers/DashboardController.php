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
        switch ($user->user_role) {
            case 'admin':
                return redirect()->route('voyager.dashboard');
            case 'dosen':
                return redirect()->route('dosen.dashboard');
            case 'mahasiswa':
                return redirect()->route('mahasiswa.dashboard');
            default:
                auth()->logout();
                return redirect()->route('login')->with('error', 'Role tidak valid.');
        }
    }
}
