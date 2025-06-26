<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tugas;
use App\Models\MataKuliah;
use App\Models\User;

echo "Cek data orphan tugas dan validasi relasi...\n";

// Cek tugas orphan (mata_kuliah_id tidak ada di tabel mata_kuliah)
$orphanTugas = Tugas::whereDoesntHave('mataKuliah')->get();
if ($orphanTugas->count() > 0) {
    echo "Tugas orphan (mata_kuliah_id tidak valid):\n";
    foreach ($orphanTugas as $t) {
        echo "- ID: {$t->id}, Judul: {$t->judul}, mata_kuliah_id: {$t->mata_kuliah_id}\n";
    }
} else {
    echo "Tidak ada tugas orphan (semua tugas punya mata kuliah valid).\n";
}

// Cek tugas yang dosen_id di mata_kuliah tidak valid
$tugas = Tugas::with('mataKuliah')->get();
foreach ($tugas as $t) {
    if ($t->mataKuliah && !User::where('id', $t->mataKuliah->dosen_id)->where('user_role', 'dosen')->exists()) {
        echo "Tugas ID: {$t->id}, Judul: {$t->judul}, mata_kuliah_id: {$t->mata_kuliah_id}, dosen_id di mata_kuliah: {$t->mataKuliah->dosen_id} (TIDAK VALID)\n";
    }
}
echo "Selesai cek.\n"; 