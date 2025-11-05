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
use RealRashid\SweetAlert\Facades\Alert;

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

        return redirect()
        ->back()
        ->with('success', 'Mahasiswa berhasil didaftarkan');
    }

    // Import mahasiswa via CSV
public function import(Request $request)
{
    $request->validate([
        'csv_file'  => 'required|file|mimes:csv,txt',
        'kelas_ids' => 'required|array|min:1',
        'kelas_ids.*' => 'exists:kelas,id',
    ]);

    // Ambil role mahasiswa
    $mahasiswaRole = Role::where('name', 'mahasiswa')->first();
    if (!$mahasiswaRole) {
        return back()->withErrors(['csv_file' => 'Role mahasiswa tidak ditemukan']);
    }

    $filePath = $request->file('csv_file')->getRealPath();

    // Baca file menjadi array baris, abaikan baris kosong
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines || count($lines) === 0) {
        return back()->withErrors(['csv_file' => 'File CSV kosong atau tidak valid.']);
    }

    $successCount = 0;
    $failCount    = 0;
    $failMessages = [];

    DB::beginTransaction();
    try {
        foreach ($lines as $i => $line) {
            // Hilangkan BOM di kolom pertama (jika ada)
            if ($i === 0) {
                $line = ltrim($line, "\xEF\xBB\xBF");
            }

            // Parse CSV untuk baris ini
            $row = str_getcsv($line);

            // Lewati baris header (selalu baris pertama sesuai instruksi)
            if ($i === 0) {
                continue;
            }

            // Normalisasi: trim tiap kolom
            $row = array_map(function ($v) {
                return is_string($v) ? trim($v) : $v;
            }, $row);

            // Abaikan baris benar-benar kosong
            if (count(array_filter($row, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            // Dukungan dua format:
            // 1) nama,email,nim,password          => 4 kolom
            // 2) Timestamp,nama,email,nim,password => 5 kolom (hapus kolom pertama)
            if (count($row) === 5) {
                // Buang kolom Timestamp
                array_shift($row);
            } elseif (count($row) > 5) {
                // Jika lebih dari 5 (mis. kolom tambahan tak terduga), ambil 4 kolom terakhir
                $row = array_slice($row, -4);
            }

            if (count($row) !== 4) {
                $failCount++;
                $failMessages[] = "Baris ke-".($i+1).": format tidak sesuai (butuh 4 kolom: nama,email,nim,password).";
                continue;
            }

            [$name, $email, $nim, $password] = $row;

            // Validasi minimal per kolom
            if (!$name || !$email || !$nim || !$password) {
                $failCount++;
                $failMessages[] = "Baris ke-".($i+1).": terdapat kolom kosong.";
                continue;
            }

            // Normalisasi email
            $email = mb_strtolower($email);

            // Validasi email sederhana
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $failCount++;
                $failMessages[] = "Baris ke-".($i+1).": format email tidak valid.";
                continue;
            }

            // Cek unik email/NIM
            $emailExists = User::where('email', $email)->exists();
            $nimExists   = User::where('nim_nip', $nim)->exists();

            if ($emailExists || $nimExists) {
                $failCount++;
                $failMessages[] = "Baris ke-".($i+1).": email atau NIM sudah terdaftar.";
                continue;
            }

            try {
                // Buat user
                $mahasiswa = User::create([
                    'name'     => $name,
                    'email'    => $email,
                    'nim_nip'  => $nim,
                    'role_id'  => $mahasiswaRole->id,
                    'password' => bcrypt($password),
                ]);

                // Enroll ke setiap kelas yang dipilih pada form
                foreach (array_filter($request->kelas_ids) as $kelasId) {
                    Enrollment::create([
                        'mahasiswa_id'   => $mahasiswa->id,
                        'kelas_id'       => $kelasId,
                        'status'         => 'active',
                        'tanggal_daftar' => now(),
                        'enrolled_at'    => now(),
                    ]);
                }

                $successCount++;
            } catch (\Throwable $rowEx) {
                $failCount++;
                $failMessages[] = "Baris ke-".($i+1).": gagal disimpan ({$rowEx->getMessage()}).";
                // lanjut ke baris berikutnya tanpa menggagalkan keseluruhan proses
            }
        }

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['csv_file' => 'Terjadi error saat import: ' . $e->getMessage()]);
    }

    $message = "$successCount mahasiswa berhasil diimport.";
    if ($failCount > 0) {
        $message .= " $failCount gagal: " . implode(' ', $failMessages);
        // tampilkan sukses + error
        return back()->with('success', $message)->withErrors(['csv_file' => $message]);
    }

    return back()->with('success', $message);
}

} 