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
        $kelasList = \App\Models\Kelas::with('mataKuliah')->get();
        return view('admin.dosen.create', compact('mataKuliah', 'mahasiswa', 'kelasList'));
    }

    // Simpan data dosen + relasi mata kuliah
    public function store(Request $request)
    {
        $kelasMode = $request->input('kelas_mode', 'baru');
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nim_nip' => 'required|string|unique:users,nim_nip',
            'password' => 'required|string|min:6|confirmed',
            'nama_mk' => 'nullable|string|max:255',
            'kode_mk' => 'nullable|string|max:50',
            'mata_kuliah_id' => 'nullable|exists:mata_kuliah,id',
        ];
        if ($kelasMode === 'baru') {
            $rules['kelas'] = 'required|array|min:1';
            $rules['kelas.*.nama_kelas'] = 'required|string|max:255';
        } else {
            $rules['kelas_pilih'] = 'required|array|min:1';
            $rules['kelas_pilih.*'] = 'exists:kelas,id';
        }
        $request->validate($rules);

        DB::transaction(function () use ($request, $kelasMode) {
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

            if ($kelasMode === 'baru') {
                // Multi kelas baru
                foreach ($request->kelas as $kelasData) {
                    \App\Models\Kelas::create([
                        'nama_kelas' => $kelasData['nama_kelas'],
                        'mata_kuliah_id' => $mataKuliahId,
                        'dosen_id' => $user->id,
                    ]);
                }
            } else {
                // Assign kelas yang sudah ada ke dosen ini
                foreach ($request->kelas_pilih as $kelasId) {
                    $kelas = \App\Models\Kelas::find($kelasId);
                    if ($kelas) {
                        $kelas->dosen_id = $user->id;
                        $kelas->save();
                    }
                }
            }
        });

        return redirect()->route('admin.dosen.create')->with('success', 'Dosen, mata kuliah, dan kelas berhasil didaftarkan.');
    }
} 