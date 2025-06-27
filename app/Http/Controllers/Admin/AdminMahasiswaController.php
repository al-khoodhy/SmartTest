<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class AdminMahasiswaController extends Controller
{
    // Tampilkan form pendaftaran mahasiswa
    public function create()
    {
        $kelasList = Kelas::with('mataKuliah')->get();
        return view('admin.mahasiswa.create', compact('kelasList'));
    }

    // Simpan data mahasiswa baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nim_nip' => 'required|string|unique:users,nim_nip',
            'password' => 'required|string|min:6|confirmed',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        DB::transaction(function () use ($request) {
            $mahasiswa = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nim_nip' => $request->nim_nip,
                'role_id' => 3,
                'password' => bcrypt($request->password),
            ]);
            foreach (array_filter($request->kelas_ids) as $kelasId) {
                Enrollment::create([
                    'mahasiswa_id' => $mahasiswa->id,
                    'kelas_id' => $kelasId,
                    'status' => 'active',
                    'tanggal_daftar' => now(),
                    'enrolled_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.mahasiswa.create')->with('success', 'Mahasiswa berhasil didaftarkan dan di-enroll ke kelas.');
    }
} 