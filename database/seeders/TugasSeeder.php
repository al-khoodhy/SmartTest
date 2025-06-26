<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tugas;
use App\Models\Kelas;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Soal;

class TugasSeeder extends Seeder
{
    public function run(): void
    {
        $kelasList = Kelas::all();
        foreach ($kelasList as $kelas) {
            for ($i = 1; $i <= 2; $i++) {
                $tugas = Tugas::create([
                    'judul' => 'Tugas ' . $i . ' - ' . $kelas->nama_kelas,
                    'deskripsi' => 'Deskripsi tugas ' . $i . ' untuk ' . $kelas->nama_kelas,
                    'rubrik_penilaian' => null,
                    'mata_kuliah_id' => $kelas->mata_kuliah_id,
                    'dosen_id' => $kelas->dosen_id,
                    'kelas_id' => $kelas->id,
                    'deadline' => Carbon::now()->addDays(rand(7, 30)),
                    'durasi_menit' => 120,
                    'nilai_maksimal' => 100,
                    'is_active' => true,
                    'auto_grade' => true,
                ]);
                // Tambah soal untuk tugas ini
                for ($j = 1; $j <= 3; $j++) {
                    Soal::create([
                        'tugas_id' => $tugas->id,
                        'pertanyaan' => 'Soal ke-' . $j . ' untuk ' . $tugas->judul,
                        'bobot' => rand(1, 3),
                    ]);
                }
            }
        }
    }
} 