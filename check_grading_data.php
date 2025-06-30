<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianSoal;
use App\Models\JawabanSoalMahasiswa;

echo "=== CHECKING GRADING DATA ===\n\n";

// Check JawabanMahasiswa
echo "JawabanMahasiswa:\n";
echo "- Total: " . JawabanMahasiswa::count() . "\n";
echo "- Submitted: " . JawabanMahasiswa::where('status', 'submitted')->count() . "\n";
echo "- Graded: " . JawabanMahasiswa::where('status', 'graded')->count() . "\n\n";

// Check Penilaian
echo "Penilaian:\n";
echo "- Total: " . Penilaian::count() . "\n";
echo "- AI Graded: " . Penilaian::where('status_penilaian', 'ai_graded')->count() . "\n";
echo "- Final: " . Penilaian::where('status_penilaian', 'final')->count() . "\n\n";

// Check PenilaianSoal
echo "PenilaianSoal:\n";
echo "- Total: " . PenilaianSoal::count() . "\n";
echo "- AI Graded: " . PenilaianSoal::where('status_penilaian', 'ai_graded')->count() . "\n";
echo "- Final: " . PenilaianSoal::where('status_penilaian', 'final')->count() . "\n\n";

// Check JawabanSoalMahasiswa
echo "JawabanSoalMahasiswa:\n";
echo "- Total: " . JawabanSoalMahasiswa::count() . "\n\n";

// Check submitted jawaban without penilaian
$submittedWithoutPenilaian = JawabanMahasiswa::where('status', 'submitted')
    ->whereDoesntHave('penilaian')
    ->count();
echo "Submitted jawaban without penilaian: $submittedWithoutPenilaian\n\n";

// Check graded jawaban without penilaian
$gradedWithoutPenilaian = JawabanMahasiswa::where('status', 'graded')
    ->whereDoesntHave('penilaian')
    ->count();
echo "Graded jawaban without penilaian: $gradedWithoutPenilaian\n\n";

// Check specific examples
echo "=== SAMPLE DATA ===\n";
$sampleJawaban = JawabanMahasiswa::with(['penilaian', 'jawabanSoal.penilaian'])
    ->where('status', 'submitted')
    ->first();

if ($sampleJawaban) {
    echo "Sample submitted jawaban ID: " . $sampleJawaban->id . "\n";
    echo "- Has penilaian: " . ($sampleJawaban->penilaian ? 'Yes' : 'No') . "\n";
    echo "- JawabanSoal count: " . $sampleJawaban->jawabanSoal->count() . "\n";
    echo "- JawabanSoal with penilaian: " . $sampleJawaban->jawabanSoal->filter(function($js) {
        return $js->penilaian;
    })->count() . "\n";
}

echo "\n=== END ===\n"; 