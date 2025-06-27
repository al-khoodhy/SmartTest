<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDosenController extends Controller
{
    // Tampilkan form pendaftaran dosen + mata kuliah
    public function create()
    {
        $mataKuliah = \App\Models\MataKuliah::all();
        $mahasiswa = \App\Models\User::where('role_id', 3)->get();
        return view('admin.dosen.create', compact('mataKuliah', 'mahasiswa'));
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
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
            'kelas' => 'required|array|min:1',
            'kelas.*.nama_kelas' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nim_nip' => $request->nim_nip,
                'role_id' => 2,
                'password' => bcrypt($request->password),
            ]);

            // Mata kuliah: pilih yang ada atau buat baru
            $mataKuliahId = $request->mata_kuliah_id;
            if (!$mataKuliahId && $request->filled('nama_mk') && $request->filled('kode_mk')) {
                $mkBaru = \App\Models\MataKuliah::create([
                    'nama_mk' => $request->nama_mk,
                    'kode_mk' => $request->kode_mk,
                ]);
                $mataKuliahId = $mkBaru->id;
            }
            if ($mataKuliahId) {
                $user->mataKuliahDiampu()->sync([$mataKuliahId]);
            }

            // Multi kelas
            foreach ($request->kelas as $kelasData) {
                $kelas = \App\Models\Kelas::create([
                    'nama_kelas' => $kelasData['nama_kelas'],
                    'mata_kuliah_id' => $mataKuliahId,
                    'dosen_id' => $user->id,
                ]);
            }
        });

        return redirect()->route('admin.dosen.create')->with('success', 'Dosen, mata kuliah, dan kelas berhasil didaftarkan.');
    }
} 