<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Support\Arr;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $mahasiswa = User::where('user_role', 'mahasiswa')->get();
        $kelasList = Kelas::all();

        foreach ($mahasiswa as $mhs) {
            // Enroll ke 2-3 kelas random
            $kelasIds = $kelasList->pluck('id')->random(min(3, $kelasList->count()));
            foreach ($kelasIds as $kelasId) {
                $kelas = Kelas::find($kelasId);
                Enrollment::updateOrCreate([
                    'mahasiswa_id' => $mhs->id,
                    'mata_kuliah_id' => $kelas->mata_kuliah_id,
                ], [
                    'kelas_id' => $kelas->id,
                    'status' => 'active',
                    'tanggal_daftar' => now()->subDays(rand(1, 30)),
                    'enrolled_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
} 