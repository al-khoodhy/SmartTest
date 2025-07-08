<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Facades\Voyager;

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
            // Get mahasiswa role
            $mahasiswaRole = Role::where('name', 'mahasiswa')->first();
            if (!$mahasiswaRole) {
                throw new \Exception('Role mahasiswa tidak ditemukan');
            }

            $mahasiswa = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'nim_nip' => $request->nim_nip,
                'role_id' => $mahasiswaRole->id,
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

        Voyager::alert('success', 'Mahasiswa berhasil didaftarkan dan di-enroll ke kelas.');
        return redirect()->route('admin.mahasiswa.create');
    }

    // Import mahasiswa via CSV
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        if (empty($rows)) {
            return back()->withErrors(['csv_file' => 'File CSV kosong atau tidak valid.']);
        }

        $successCount = 0;
        $failCount = 0;
        $failMessages = [];

        // Get mahasiswa role
        $mahasiswaRole = Role::where('name', 'mahasiswa')->first();
        if (!$mahasiswaRole) {
            return back()->withErrors(['csv_file' => 'Role mahasiswa tidak ditemukan']);
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $i => $row) {
                if (count($row) < 4) {
                    $failCount++;
                    $failMessages[] = "Baris ke-".($i+1).": format tidak sesuai.";
                    continue;
                }
                [$name, $email, $nim, $password] = $row;
                // Cek email/nim unik
                if (User::where('email', $email)->exists() || User::where('nim_nip', $nim)->exists()) {
                    $failCount++;
                    $failMessages[] = "Baris ke-".($i+1).": email atau NIM sudah terdaftar.";
                    continue;
                }
                $mahasiswa = User::create([
                    'name' => $name,
                    'email' => $email,
                    'nim_nip' => $nim,
                    'role_id' => $mahasiswaRole->id,
                    'password' => bcrypt($password),
                ]);
                foreach ($request->kelas_ids as $kelasId) {
                    Enrollment::create([
                        'mahasiswa_id' => $mahasiswa->id,
                        'kelas_id' => $kelasId,
                        'status' => 'active',
                        'tanggal_daftar' => now(),
                        'enrolled_at' => now(),
                    ]);
                }
                $successCount++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['csv_file' => 'Terjadi error saat import: ' . $e->getMessage()]);
        }

        $message = "$successCount mahasiswa berhasil diimport.";
        if ($failCount > 0) {
            $message .= " $failCount gagal: ".implode(' ', $failMessages);
            return back()->with('success', $message)->withErrors(['csv_file' => $message]);
        }
        return back()->with('success', $message);
    }
} 