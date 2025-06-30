<?php

require_once 'vendor/autoload.php';

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianSoal;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Debug Detail Penilaian ===\n\n";

// Cari jawaban yang memiliki PenilaianSoal tapi tidak ada Penilaian utama
$jawabanToFix = JawabanMahasiswa::whereHas('jawabanSoal.penilaian')
    ->whereDoesntHave('penilaian')
    ->with(['tugas', 'mahasiswa', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
    ->get();

foreach ($jawabanToFix as $jawaban) {
    echo "=== Jawaban ID {$jawaban->id} - {$jawaban->mahasiswa->name} - {$jawaban->tugas->judul} ===\n";
    
    foreach ($jawaban->jawabanSoal as $jawabanSoal) {
        echo "Soal ID: {$jawabanSoal->soal->id}, Bobot: {$jawabanSoal->soal->bobot}\n";
        
        if ($jawabanSoal->penilaian) {
            echo "  Status: {$jawabanSoal->penilaian->status_penilaian}\n";
            echo "  Nilai AI: " . ($jawabanSoal->penilaian->nilai_ai ?? 'null') . "\n";
            echo "  Nilai Manual: " . ($jawabanSoal->penilaian->nilai_manual ?? 'null') . "\n";
            echo "  Nilai Final: " . ($jawabanSoal->penilaian->nilai_final ?? 'null') . "\n";
        } else {
            echo "  Tidak ada penilaian\n";
        }
        echo "\n";
    }
    
    // Hitung nilai akhir dari PenilaianSoal
    $totalNilai = 0;
    $totalBobot = 0;
    $allGraded = true;
    
    foreach ($jawaban->jawabanSoal as $jawabanSoal) {
        if ($jawabanSoal->penilaian && $jawabanSoal->penilaian->status_penilaian === 'final') {
            $nilai = $jawabanSoal->penilaian->nilai_final ?? $jawabanSoal->penilaian->nilai_manual ?? $jawabanSoal->penilaian->nilai_ai;
            if ($nilai !== null) {
                $totalNilai += ($nilai * $jawabanSoal->soal->bobot);
                $totalBobot += $jawabanSoal->soal->bobot;
                echo "Soal {$jawabanSoal->soal->id}: Nilai = {$nilai}, Bobot = {$jawabanSoal->soal->bobot}, Total = " . ($nilai * $jawabanSoal->soal->bobot) . "\n";
            } else {
                $allGraded = false;
                echo "Soal {$jawabanSoal->soal->id}: Nilai null\n";
            }
        } else {
            $allGraded = false;
            echo "Soal {$jawabanSoal->soal->id}: Status bukan final atau tidak ada penilaian\n";
        }
    }
    
    echo "Total Nilai: {$totalNilai}, Total Bobot: {$totalBobot}, All Graded: " . ($allGraded ? 'true' : 'false') . "\n";
    
    if ($allGraded && $totalBobot > 0) {
        $nilaiAkhir = $totalNilai / $totalBobot;
        echo "Nilai Akhir: {$nilaiAkhir}\n";
    } else {
        echo "Tidak bisa menghitung nilai akhir\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

echo "=== Selesai ===\n"; 