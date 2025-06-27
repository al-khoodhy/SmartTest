<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tugas;
use App\Models\MataKuliah;
use App\Models\User;

echo "Cek data orphan tugas dan validasi relasi...\n";

// Cek tugas orphan (kelas_id tidak ada di tabel kelas atau kelas->mataKuliah null)
$orphanTugas = Tugas::whereDoesntHave('kelas')->orWhereHas('kelas', function($q) {
    $q->whereNull('mata_kuliah_id');
})->get();
if ($orphanTugas->count() > 0) {
    echo "Tugas orphan (kelas_id tidak valid atau kelas tidak punya mata kuliah):\n";
    foreach ($orphanTugas as $t) {
        echo "- ID: {$t->id}, Judul: {$t->judul}, kelas_id: {$t->kelas_id}\n";
    }
} else {
    echo "Tidak ada tugas orphan (semua tugas punya kelas dan mata kuliah valid).\n";
}

// Cek tugas yang dosen_id di mata_kuliah tidak valid
$tugas = Tugas::with('kelas.mataKuliah')->get();
foreach ($tugas as $t) {
    if ($t->kelas->mataKuliah && !User::where('id', $t->kelas->mataKuliah->dosen_id)->where('role_id', 2)->exists()) {
        echo "Tugas ID: {$t->id}, Judul: {$t->judul}, mata_kuliah_id: {$t->mata_kuliah_id}, dosen_id di mata_kuliah: {$t->kelas->mataKuliah->dosen_id} (TIDAK VALID)\n";
    }
}
echo "Selesai cek.\n"; 