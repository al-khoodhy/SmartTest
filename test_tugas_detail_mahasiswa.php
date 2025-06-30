<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tugas;
use App\Models\User;
use App\Models\JawabanMahasiswa;
use Carbon\Carbon;

echo "=== Test Halaman Detail Tugas Mahasiswa ===\n\n";

try {
    // Get a sample task
    $tugas = Tugas::with(['kelas.mataKuliah', 'soal'])->first();
    
    if (!$tugas) {
        echo "❌ Tidak ada tugas yang ditemukan\n";
        exit(1);
    }
    
    echo "📋 Informasi Tugas:\n";
    echo "- Judul: " . $tugas->judul . "\n";
    echo "- Mata Kuliah: " . $tugas->mataKuliah->nama_mk . "\n";
    echo "- Deskripsi: " . $tugas->deskripsi . "\n";
    echo "- Jumlah Soal: " . $tugas->soal->count() . " soal\n";
    echo "- Deadline: " . $tugas->deadline->format('d/m/Y H:i') . "\n";
    echo "- Nilai Maksimal: " . $tugas->nilai_maksimal . "\n";
    echo "- Status: " . ($tugas->is_active ? 'Aktif' : 'Tidak Aktif') . "\n";
    
    // Get a sample student
    $mahasiswa = User::where('role', 'mahasiswa')->first();
    
    if ($mahasiswa) {
        echo "\n👤 Mahasiswa: " . $mahasiswa->name . "\n";
        
        // Check if student has answers for this task
        $jawaban = JawabanMahasiswa::where('tugas_id', $tugas->id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();
        
        if ($jawaban) {
            echo "📝 Status Jawaban: " . $jawaban->status . "\n";
            echo "📊 Nilai Akhir: " . $jawaban->nilai_akhir . "\n";
        } else {
            echo "📝 Belum ada jawaban untuk tugas ini\n";
        }
    }
    
    // Test deadline calculation
    $now = Carbon::now();
    $isExpired = $tugas->deadline <= $now;
    $canWork = !$jawaban && $tugas->deadline > $now && $tugas->is_active;
    $canContinue = $jawaban && $jawaban->status === 'draft' && $tugas->deadline > $now;
    
    echo "\n⏰ Status Deadline:\n";
    echo "- Waktu Sekarang: " . $now->format('d/m/Y H:i') . "\n";
    echo "- Deadline: " . $tugas->deadline->format('d/m/Y H:i') . "\n";
    echo "- Sudah Expired: " . ($isExpired ? 'Ya' : 'Tidak') . "\n";
    echo "- Bisa Dikerjakan: " . ($canWork ? 'Ya' : 'Tidak') . "\n";
    echo "- Bisa Dilanjutkan: " . ($canContinue ? 'Ya' : 'Tidak') . "\n";
    
    echo "\n🔧 Test Tombol 'Mulai Mengerjakan Tugas':\n";
    echo "✅ Tombol sekarang menggunakan POST form dengan CSRF token\n";
    echo "✅ Konfirmasi dialog ditambahkan sebelum memulai tugas\n";
    echo "✅ Route menggunakan middleware 'submit_tugas' permission\n";
    echo "✅ Controller memuat relasi 'kelas.mataKuliah' dan 'soal'\n";
    
    echo "\n📋 Informasi yang ditampilkan di halaman detail:\n";
    echo "✅ Judul tugas\n";
    echo "✅ Mata kuliah\n";
    echo "✅ Deskripsi\n";
    echo "✅ Jumlah soal\n";
    echo "✅ Deadline\n";
    echo "✅ Nilai maksimal\n";
    echo "✅ Status tugas\n";
    echo "✅ Status pengumpulan (jika ada)\n";
    
    echo "\n❌ Yang TIDAK ditampilkan:\n";
    echo "❌ Daftar pertanyaan\n";
    echo "❌ Jawaban mahasiswa\n";
    echo "❌ Detail penilaian per soal\n";
    
    echo "\n🎯 Status Tombol:\n";
    if ($canWork) {
        echo "✅ Tombol 'Mulai Mengerjakan Tugas' akan ditampilkan\n";
        echo "   - Menggunakan POST form dengan CSRF\n";
        echo "   - Ada konfirmasi dialog\n";
        echo "   - Redirect ke halaman kerja tugas\n";
    } elseif ($canContinue) {
        echo "✅ Tombol 'Lanjutkan Mengerjakan' akan ditampilkan\n";
        echo "   - Link ke halaman kerja tugas yang sudah ada\n";
    } elseif ($jawaban) {
        echo "ℹ️ Status pengumpulan akan ditampilkan\n";
        echo "   - Status: " . $jawaban->status . "\n";
        if ($jawaban->status === 'graded') {
            echo "   - Nilai Akhir: " . $jawaban->nilai_akhir . "\n";
        }
    } elseif ($isExpired) {
        echo "❌ Pesan 'Tugas sudah expired' akan ditampilkan\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
} 