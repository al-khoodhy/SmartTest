<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penilaian;
use App\Models\JawabanMahasiswa;
use App\Models\User;
use Carbon\Carbon;
use App\Models\JawabanSoalMahasiswa;
use App\Models\PenilaianSoal;

class PenilaianSeeder extends Seeder
{
    public function run(): void
    {
        $jawabanList = JawabanMahasiswa::all();
        $dosen = User::where('user_role', 'dosen')->first();

        foreach ($jawabanList as $jawaban) {
            Penilaian::updateOrCreate([
                'jawaban_id' => $jawaban->id,
            ], [
                'nilai_ai' => rand(60, 90),
                'nilai_manual' => rand(70, 100),
                'nilai_final' => rand(70, 100),
                'feedback_ai' => 'Feedback AI untuk jawaban ' . $jawaban->id,
                'feedback_manual' => 'Feedback dosen untuk jawaban ' . $jawaban->id,
                'detail_penilaian_ai' => json_encode(['rubrik1' => rand(10, 20), 'rubrik2' => rand(10, 20)]),
                'status_penilaian' => 'final',
                'graded_by' => $dosen ? $dosen->id : null,
                'graded_at' => Carbon::now()->subDays(rand(0, 2)),
            ]);
        }

        $jawabanSoalList = JawabanSoalMahasiswa::all();
        $dosen = User::where('user_role', 'dosen')->first();
        foreach ($jawabanSoalList as $jawabanSoal) {
            PenilaianSoal::updateOrCreate([
                'jawaban_soal_id' => $jawabanSoal->id,
            ], [
                'nilai_ai' => rand(60, 90),
                'nilai_manual' => rand(70, 100),
                'nilai_final' => rand(70, 100),
                'feedback_ai' => 'Feedback AI untuk jawaban soal ' . $jawabanSoal->id,
                'feedback_manual' => 'Feedback dosen untuk jawaban soal ' . $jawabanSoal->id,
                'status_penilaian' => 'final',
                'graded_by' => $dosen ? $dosen->id : null,
                'graded_at' => now()->subDays(rand(0, 2)),
            ]);
        }
    }
} 