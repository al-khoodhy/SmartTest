<?php

require_once 'vendor/autoload.php';

use App\Models\JawabanMahasiswa;
use App\Models\User;
use App\Models\Tugas;
use App\Models\JawabanSoalMahasiswa;
use App\Models\PenilaianSoal;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST NILAI OTOMATIS PENILAIAN ===\n\n";

// Ambil user mahasiswa pertama (role_id = 3 untuk mahasiswa)
$mahasiswa = User::where('role_id', 3)->first();
if (!$mahasiswa) {
    echo "Tidak ada user mahasiswa ditemukan!\n";
    exit;
}

echo "Mahasiswa: {$mahasiswa->name} (ID: {$mahasiswa->id})\n";
echo "Role: " . ($mahasiswa->role ? $mahasiswa->role->name : 'No role') . "\n\n";

// Ambil semua jawaban mahasiswa dengan relasi
$jawaban = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
    ->with([
        'tugas.kelas.mataKuliah', 
        'penilaian', 
        'jawabanSoal.soal', 
        'jawabanSoal.penilaian'
    ])
    ->whereIn('status', ['submitted', 'graded'])
    ->get();

echo "Total jawaban: {$jawaban->count()}\n\n";

foreach ($jawaban as $n) {
    echo "=== TUGAS: {$n->tugas->judul} ===\n";
    echo "Status: {$n->status}\n";
    echo "Auto Grade: " . ($n->tugas->auto_grade ? 'Ya' : 'Tidak') . "\n";
    echo "Nilai Akhir: {$n->nilai_akhir}\n";
    echo "Nilai AI: {$n->nilai_ai}\n";
    echo "Nilai Manual: {$n->nilai_manual}\n";
    
    // Cek penilaian di level tugas
    if ($n->penilaian) {
        echo "Penilaian Tugas: Ada (Status: {$n->penilaian->status_penilaian})\n";
    } else {
        echo "Penilaian Tugas: Tidak ada\n";
    }
    
    // Cek penilaian per soal
    $jawabanSoalCount = $n->jawabanSoal->count();
    $aiGradedCount = $n->jawabanSoal->filter(function($js) {
        return $js->penilaian && $js->penilaian->status_penilaian == 'ai_graded';
    })->count();
    $finalGradedCount = $n->jawabanSoal->filter(function($js) {
        return $js->penilaian && $js->penilaian->status_penilaian == 'final';
    })->count();
    
    echo "Total Soal: {$jawabanSoalCount}\n";
    echo "AI Graded: {$aiGradedCount}\n";
    echo "Final Graded: {$finalGradedCount}\n";
    
    // Test logika tampilan
    echo "\n--- LOGIKA TAMPILAN ---\n";
    
    // Test nilai yang ditampilkan
    if ($n->status === 'graded' && $n->nilai_akhir > 0) {
        echo "NILAI: {$n->nilai_akhir} (Graded)\n";
    } elseif ($n->tugas->auto_grade && $n->nilai_ai > 0) {
        echo "NILAI: {$n->nilai_ai} (AI)\n";
    } elseif ($n->status === 'submitted') {
        echo "NILAI: Menunggu Penilaian\n";
    } else {
        echo "NILAI: -\n";
    }
    
    // Test status yang ditampilkan
    if ($n->status === 'graded') {
        echo "STATUS: Sudah Dinilai\n";
    } elseif ($n->tugas->auto_grade && $n->nilai_ai > 0) {
        echo "STATUS: AI Graded\n";
    } elseif ($n->status === 'submitted') {
        echo "STATUS: Menunggu Penilaian\n";
    } else {
        echo "STATUS: Belum Dikerjakan\n";
    }
    
    // Test tombol detail
    if ($n->penilaian || ($n->tugas->auto_grade && $n->nilai_ai > 0)) {
        echo "TOMBOL DETAIL: Tampil\n";
    } else {
        echo "TOMBOL DETAIL: Tidak tampil\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Test statistik
echo "=== STATISTIK ===\n";

$totalTugas = $jawaban->count();
$sudahDinilai = $jawaban->filter(function($n) {
    return $n->status === 'graded' || 
           ($n->tugas->auto_grade && $n->nilai_ai > 0);
})->count();
$menungguPenilaian = $totalTugas - $sudahDinilai;
$rataRataNilai = $jawaban->filter(function($n) {
    return $n->status === 'graded' || 
           ($n->tugas->auto_grade && $n->nilai_ai > 0);
})->avg('nilai_akhir');

echo "Total Tugas: {$totalTugas}\n";
echo "Sudah Dinilai: {$sudahDinilai}\n";
echo "Menunggu Penilaian: {$menungguPenilaian}\n";
echo "Rata-rata Nilai: " . number_format($rataRataNilai, 1) . "\n";

echo "\n=== SELESAI ===\n"; 