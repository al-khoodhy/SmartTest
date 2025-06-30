<?php

require_once 'vendor/autoload.php';

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\PenilaianSoal;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Pemeriksaan Data Penilaian ===\n\n";

// 1. Cek jawaban yang sudah submitted tapi tidak ada penilaian
echo "1. Jawaban yang sudah submitted tapi tidak ada penilaian:\n";
$jawabanWithoutPenilaian = JawabanMahasiswa::where('status', 'submitted')
    ->whereDoesntHave('penilaian')
    ->with(['tugas', 'mahasiswa'])
    ->get();

foreach ($jawabanWithoutPenilaian as $jawaban) {
    echo "- {$jawaban->mahasiswa->name} - {$jawaban->tugas->judul} (ID: {$jawaban->id})\n";
}

if ($jawabanWithoutPenilaian->count() == 0) {
    echo "Tidak ada jawaban yang submitted tanpa penilaian.\n";
}

echo "\n";

// 2. Cek jawaban yang sudah graded tapi tidak ada penilaian
echo "2. Jawaban yang sudah graded tapi tidak ada penilaian:\n";
$gradedWithoutPenilaian = JawabanMahasiswa::where('status', 'graded')
    ->whereDoesntHave('penilaian')
    ->with(['tugas', 'mahasiswa'])
    ->get();

foreach ($gradedWithoutPenilaian as $jawaban) {
    echo "- {$jawaban->mahasiswa->name} - {$jawaban->tugas->judul} (ID: {$jawaban->id})\n";
}

if ($gradedWithoutPenilaian->count() == 0) {
    echo "Tidak ada jawaban yang graded tanpa penilaian.\n";
}

echo "\n";

// 3. Cek jawaban yang memiliki PenilaianSoal tapi tidak ada Penilaian utama
echo "3. Jawaban yang memiliki PenilaianSoal tapi tidak ada Penilaian utama:\n";
$withPenilaianSoal = JawabanMahasiswa::whereHas('jawabanSoal.penilaian')
    ->whereDoesntHave('penilaian')
    ->with(['tugas', 'mahasiswa', 'jawabanSoal.penilaian'])
    ->get();

foreach ($withPenilaianSoal as $jawaban) {
    echo "- {$jawaban->mahasiswa->name} - {$jawaban->tugas->judul} (ID: {$jawaban->id})\n";
    echo "  Memiliki " . $jawaban->jawabanSoal->count() . " jawaban soal dengan penilaian\n";
}

if ($withPenilaianSoal->count() == 0) {
    echo "Tidak ada jawaban dengan PenilaianSoal tanpa Penilaian utama.\n";
}

echo "\n";

// 4. Cek penilaian yang tidak memiliki nilai
echo "4. Penilaian yang tidak memiliki nilai final:\n";
$penilaianWithoutNilai = Penilaian::whereNull('nilai_final')
    ->with(['jawaban.tugas', 'jawaban.mahasiswa'])
    ->get();

foreach ($penilaianWithoutNilai as $penilaian) {
    echo "- {$penilaian->jawaban->mahasiswa->name} - {$penilaian->jawaban->tugas->judul} (ID: {$penilaian->id})\n";
    echo "  Status: {$penilaian->status_penilaian}, Nilai AI: {$penilaian->nilai_ai}, Nilai Manual: {$penilaian->nilai_manual}\n";
}

if ($penilaianWithoutNilai->count() == 0) {
    echo "Tidak ada penilaian tanpa nilai final.\n";
}

echo "\n";

// 5. Cek deadline yang mungkin bermasalah
echo "5. Deadline tugas yang sudah lewat:\n";
$expiredTugas = \App\Models\Tugas::where('deadline', '<', now())
    ->with(['kelas.mataKuliah'])
    ->get();

foreach ($expiredTugas as $tugas) {
    echo "- {$tugas->judul} - {$tugas->kelas->mataKuliah->nama_mk}\n";
    echo "  Deadline: {$tugas->deadline->format('d/m/Y H:i')} ({$tugas->deadline->diffForHumans()})\n";
}

echo "\n=== Selesai ===\n"; 