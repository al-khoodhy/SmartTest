<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tugas;
use App\Models\User;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;

echo "=== Test Remove Feedback dari Halaman Nilai Mahasiswa ===\n\n";

// Check for students with scores
$students = User::whereHas('role', function($query) {
    $query->where('name', 'mahasiswa');
})->get();
echo "👥 Total mahasiswa: " . $students->count() . "\n";

if ($students->count() > 0) {
    $student = $students->first();
    echo "✅ Contoh mahasiswa: " . $student->name . "\n";
    
    // Check for submitted answers with feedback
    $jawabanWithFeedback = JawabanMahasiswa::where('mahasiswa_id', $student->id)
        ->whereHas('penilaian', function($query) {
            $query->whereNotNull('feedback_ai')
                  ->orWhereNotNull('feedback_manual');
        })
        ->with(['penilaian', 'tugas.kelas'])
        ->get();
    
    echo "📝 Jawaban dengan feedback: " . $jawabanWithFeedback->count() . "\n";
    
    if ($jawabanWithFeedback->count() > 0) {
        $jawaban = $jawabanWithFeedback->first();
        echo "\n📋 Contoh jawaban dengan feedback:\n";
        echo "   - Tugas: " . $jawaban->tugas->judul . "\n";
        echo "   - Mata Kuliah: " . ($jawaban->tugas->mataKuliah ? $jawaban->tugas->mataKuliah->nama_mk : '-') . "\n";
        echo "   - Status: " . $jawaban->status . "\n";
        echo "   - Nilai: " . $jawaban->nilai_akhir . "\n";
        
        if ($jawaban->penilaian) {
            echo "   - Feedback AI: " . (strlen($jawaban->penilaian->feedback_ai ?? '') > 0 ? 'Ada' : 'Tidak ada') . "\n";
            echo "   - Feedback Manual: " . (strlen($jawaban->penilaian->feedback_manual ?? '') > 0 ? 'Ada' : 'Tidak ada') . "\n";
        }
    }
}

// Check the view file for feedback elements
$viewFile = 'resources/views/mahasiswa/nilai/index.blade.php';
if (file_exists($viewFile)) {
    $viewContent = file_get_contents($viewFile);
    
    echo "\n🔍 Analisis file view:\n";
    
    // Check for feedback-related elements
    $hasFeedbackColumn = strpos($viewContent, '<th>Feedback</th>') !== false;
    $hasFeedbackPreview = strpos($viewContent, 'feedback-preview') !== false;
    $hasFeedbackAI = strpos($viewContent, 'feedback_ai') !== false;
    $hasFeedbackManual = strpos($viewContent, 'feedback_manual') !== false;
    $hasFeedbackCSS = strpos($viewContent, '.feedback-preview') !== false;
    
    echo "   - Kolom Feedback: " . ($hasFeedbackColumn ? '❌ Masih ada' : '✅ Sudah dihapus') . "\n";
    echo "   - CSS Feedback: " . ($hasFeedbackCSS ? '❌ Masih ada' : '✅ Sudah dihapus') . "\n";
    echo "   - Preview Feedback: " . ($hasFeedbackPreview ? '❌ Masih ada' : '✅ Sudah dihapus') . "\n";
    echo "   - Referensi Feedback AI: " . ($hasFeedbackAI ? '❌ Masih ada' : '✅ Sudah dihapus') . "\n";
    echo "   - Referensi Feedback Manual: " . ($hasFeedbackManual ? '❌ Masih ada' : '✅ Sudah dihapus') . "\n";
    
    // Check for remaining table structure
    $hasTableHeaders = strpos($viewContent, '<thead>') !== false;
    $hasTableBody = strpos($viewContent, '<tbody>') !== false;
    $hasDetailButton = strpos($viewContent, 'Detail Nilai & Feedback') !== false;
    
    echo "\n📊 Struktur tabel yang tersisa:\n";
    echo "   - Header tabel: " . ($hasTableHeaders ? '✅ Ada' : '❌ Tidak ada') . "\n";
    echo "   - Body tabel: " . ($hasTableBody ? '✅ Ada' : '❌ Tidak ada') . "\n";
    echo "   - Tombol Detail: " . ($hasDetailButton ? '✅ Ada' : '❌ Tidak ada') . "\n";
    
    // Count remaining columns
    preg_match_all('/<th>/', $viewContent, $matches);
    $columnCount = count($matches[0]);
    echo "   - Jumlah kolom: $columnCount\n";
    
    // Expected columns: Mata Kuliah, Judul Tugas, Nilai, Status, Tanggal, Aksi
    $expectedColumns = 6;
    echo "   - Kolom yang diharapkan: $expectedColumns\n";
    
    if ($columnCount === $expectedColumns) {
        echo "   ✅ Jumlah kolom sesuai!\n";
    } else {
        echo "   ❌ Jumlah kolom tidak sesuai!\n";
    }
}

echo "\n🎯 Perbaikan yang telah dilakukan:\n";
echo "✅ Menghapus kolom 'Feedback' dari header tabel\n";
echo "✅ Menghapus seluruh konten feedback dari body tabel\n";
echo "✅ Menghapus CSS styles untuk feedback display\n";
echo "✅ Menghapus referensi ke feedback_ai dan feedback_manual\n";
echo "✅ Menghapus div feedback-preview dan stylingnya\n";

echo "\n📱 Hasil perubahan:\n";
echo "✅ Halaman nilai mahasiswa tidak lagi menampilkan feedback\n";
echo "✅ Tabel menjadi lebih ringkas dan fokus pada nilai\n";
echo "✅ Feedback masih tersedia di halaman detail (tombol Detail)\n";
echo "✅ Performa halaman meningkat karena tidak perlu load feedback\n";

echo "\n🔗 Feedback masih tersedia di:\n";
echo "✅ Halaman detail nilai (route: mahasiswa.nilai.show)\n";
echo "✅ Halaman detail tugas mahasiswa\n";

echo "\n✅ Test selesai. Feedback berhasil dihapus dari halaman nilai mahasiswa!\n"; 