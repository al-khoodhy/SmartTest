<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianSoal;

echo "=== TESTING FEEDBACK DISPLAY ===\n\n";

// Check jawaban with feedback
$jawabanWithFeedback = JawabanMahasiswa::with([
    'penilaian',
    'jawabanSoal.penilaian',
    'tugas.kelas.mataKuliah'
])
->whereHas('penilaian')
->first();

if ($jawabanWithFeedback) {
    echo "Sample Jawaban ID: {$jawabanWithFeedback->id}\n";
    echo "Tugas: {$jawabanWithFeedback->tugas->judul}\n";
    echo "Status: {$jawabanWithFeedback->status}\n";
    
    if ($jawabanWithFeedback->penilaian) {
        echo "\nMain Penilaian:\n";
        echo "- Nilai Final: {$jawabanWithFeedback->penilaian->nilai_final}\n";
        echo "- Status: {$jawabanWithFeedback->penilaian->status_penilaian}\n";
        echo "- Feedback AI: " . ($jawabanWithFeedback->penilaian->feedback_ai ? 'Ada' : 'Tidak ada') . "\n";
        echo "- Feedback Manual: " . ($jawabanWithFeedback->penilaian->feedback_manual ? 'Ada' : 'Tidak ada') . "\n";
    }
    
    echo "\nJawaban Per Soal:\n";
    foreach ($jawabanWithFeedback->jawabanSoal as $index => $js) {
        echo "- Soal " . ($index + 1) . ":\n";
        echo "  Pertanyaan: " . Str::limit($js->soal->pertanyaan, 50) . "\n";
        echo "  Jawaban: " . Str::limit($js->jawaban, 50) . "\n";
        
        if ($js->penilaian) {
            echo "  Nilai: {$js->penilaian->nilai_final}\n";
            echo "  Status: {$js->penilaian->status_penilaian}\n";
            echo "  Feedback AI: " . ($js->penilaian->feedback_ai ? 'Ada' : 'Tidak ada') . "\n";
            echo "  Feedback Manual: " . ($js->penilaian->feedback_manual ? 'Ada' : 'Tidak ada') . "\n";
        } else {
            echo "  Belum dinilai\n";
        }
    }
} else {
    echo "Tidak ada jawaban dengan penilaian yang ditemukan.\n";
}

echo "\n=== STATISTICS ===\n";
echo "Total JawabanMahasiswa: " . JawabanMahasiswa::count() . "\n";
echo "Jawaban dengan Penilaian: " . JawabanMahasiswa::whereHas('penilaian')->count() . "\n";
echo "PenilaianSoal dengan Feedback AI: " . PenilaianSoal::whereNotNull('feedback_ai')->count() . "\n";
echo "PenilaianSoal dengan Feedback Manual: " . PenilaianSoal::whereNotNull('feedback_manual')->count() . "\n";
echo "Penilaian dengan Feedback AI: " . Penilaian::whereNotNull('feedback_ai')->count() . "\n";
echo "Penilaian dengan Feedback Manual: " . Penilaian::whereNotNull('feedback_manual')->count() . "\n";

echo "\n=== END ===\n"; 