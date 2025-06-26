<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;

class AdminDosenController extends Controller
{
    // Tampilkan form pendaftaran dosen + mata kuliah
    public function create()
    {
        $mataKuliah = MataKuliah::all();
        return view('admin.dosen.create', compact('mataKuliah'));
    }

    // Simpan data dosen + relasi mata kuliah
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nim_nip' => 'required|string|unique:users,nim_nip',
            'password' => 'required|string|min:6|confirmed',
            'nama_mk' => 'nullable|string|max:255',
            'kode_mk' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nim_nip' => $request->nim_nip,
                'user_role' => 'dosen',
                'is_active' => true,
                'password' => bcrypt($request->password),
            ]);

            // Jika form mata kuliah baru diisi, buat record baru dan relasikan ke dosen
            if ($request->filled('nama_mk') && $request->filled('kode_mk')) {
                $mkBaru = MataKuliah::create([
                    'nama_mk' => $request->nama_mk,
                    'kode_mk' => $request->kode_mk,
                ]);
                $user->mataKuliahDiampu()->sync([$mkBaru->id]);
            }
        });

        return redirect()->route('admin.dosen.create')->with('success', 'Dosen dan mata kuliah berhasil didaftarkan.');
    }
} 