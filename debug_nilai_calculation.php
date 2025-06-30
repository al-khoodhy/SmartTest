<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JawabanMahasiswa;
use App\Models\PenilaianSoal;

echo "=== DEBUGGING NILAI CALCULATION ===\n\n";

$jawaban = JawabanMahasiswa::with(['jawabanSoal.soal', 'jawabanSoal.penilaian', 'tugas'])
    ->where('id', 19)
    ->first();

if ($jawaban) {
    echo "Jawaban ID: {$jawaban->id}\n";
    echo "Tugas nilai_maksimal: " . ($jawaban->tugas->nilai_maksimal ?? 'N/A') . "\n\n";
    
    echo "JawabanSoal details:\n";
    foreach ($jawaban->jawabanSoal as $js) {
        echo "- Soal ID: {$js->soal->id}\n";
        echo "  Bobot: " . ($js->soal->bobot ?? 'N/A') . "\n";
        echo "  Nilai final: " . (optional($js->penilaian)->nilai_final ?? 'N/A') . "\n";
        echo "  Status: " . (optional($js->penilaian)->status_penilaian ?? 'N/A') . "\n";
    }
    
    echo "\nCalculation breakdown:\n";
    $totalBobot = $jawaban->jawabanSoal->sum(function($js) { return $js->soal->bobot; });
    echo "Total bobot: $totalBobot\n";
    
    $total = $jawaban->jawabanSoal->sum(function($js) {
        $nilai = optional($js->penilaian)->nilai_final;
        $bobot = $js->soal->bobot;
        $result = ($nilai ?? 0) * $bobot;
        echo "  Soal {$js->soal->id}: nilai={$nilai} * bobot={$bobot} = {$result}\n";
        return $result;
    });
    echo "Total weighted score: $total\n";
    
    $nilaiAkhir = $jawaban->nilai_akhir;
    echo "Final nilai (model attribute): $nilaiAkhir\n";
}

echo "\n=== END ===\n"; 