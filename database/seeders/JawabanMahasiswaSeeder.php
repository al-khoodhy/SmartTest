<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JawabanMahasiswa;
use App\Models\Tugas;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use App\Models\JawabanSoalMahasiswa;
use App\Models\Soal;

class JawabanMahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        $enrollments = Enrollment::all();
        $tugasList = Tugas::all();

        foreach ($enrollments as $enroll) {
            // Cari tugas yang sesuai dengan kelas yang diambil mahasiswa
            $tugasKelas = $tugasList->where('kelas_id', $enroll->kelas_id);
            foreach ($tugasKelas as $tugas) {
                $jawaban = JawabanMahasiswa::updateOrCreate([
                    'tugas_id' => $tugas->id,
                    'mahasiswa_id' => $enroll->mahasiswa_id,
                ], [
                    'waktu_mulai' => Carbon::now()->subDays(rand(1, 10)),
                    'waktu_selesai' => Carbon::now()->subDays(rand(0, 1)),
                    'status' => 'submitted',
                    'durasi_detik' => rand(1800, 7200),
                ]);
                // Buat jawaban per soal
                foreach ($tugas->soal as $soal) {
                    JawabanSoalMahasiswa::updateOrCreate([
                        'jawaban_mahasiswa_id' => $jawaban->id,
                        'soal_id' => $soal->id,
                    ], [
                        'jawaban' => 'Jawaban dummy mahasiswa ID ' . $enroll->mahasiswa_id . ' untuk soal ID ' . $soal->id,
                        'waktu_mulai' => Carbon::now()->subDays(rand(1, 10)),
                        'waktu_selesai' => Carbon::now()->subDays(rand(0, 1)),
                        'status' => 'submitted',
                    ]);
                }
            }
        }
    }
} 