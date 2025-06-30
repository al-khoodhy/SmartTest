<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'voyager.permission:browse_dosen_dashboard']);
    }
    
    /**
     * Display profile page
     */
    public function index()
    {
        $dosen = auth()->user();
        $dosen->load(['kelasAsDosen.mataKuliah', 'mataKuliahDiampu']);
        
        return view('dosen.profile.index', compact('dosen'));
    }
    
    /**
     * Show edit profile form
     */
    public function edit()
    {
        $dosen = auth()->user();
        
        return view('dosen.profile.edit', compact('dosen'));
    }
    
    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $dosen = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($dosen->id),
            ],
            'nim_nip' => [
                'required',
                'string',
                Rule::unique('users')->ignore($dosen->id),
            ],
        ]);
        
        $dosen->update([
            'name' => $request->name,
            'email' => $request->email,
            'nim_nip' => $request->nim_nip,
        ]);
        
        return redirect()->route('dosen.profile.index')
            ->with('success', 'Profil berhasil diperbarui.');
    }
    
    /**
     * Show change password form
     */
    public function changePassword()
    {
        return view('dosen.profile.change-password');
    }
    
    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ]);
        
        $dosen = auth()->user();
        $dosen->update([
            'password' => Hash::make($request->password),
        ]);
        
        return redirect()->route('dosen.profile.index')
            ->with('success', 'Password berhasil diubah.');
    }
} 