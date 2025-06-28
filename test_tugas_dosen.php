<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Tugas;
use App\Models\User;
use App\Models\Kelas;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing tugas-dosen relationship...\n\n";

try {
    // Get all dosen
    $dosen = User::where('role_id', 2)->get();
    echo "Found " . $dosen->count() . " dosen\n";
    
    foreach ($dosen as $d) {
        echo "\nDosen: {$d->name} ({$d->email})\n";
        echo "Tugas created by this dosen:\n";
        
        $tugas = Tugas::where('dosen_id', $d->id)->with('kelas.mataKuliah')->get();
        foreach ($tugas as $t) {
            echo "  - {$t->judul} (Kelas: {$t->kelas->nama_kelas}, Mata Kuliah: {$t->kelas->mataKuliah->nama_mk})\n";
        }
        
        if ($tugas->count() == 0) {
            echo "  - No tugas created\n";
        }
    }
    
    echo "\n\nTesting all tugas:\n";
    $allTugas = Tugas::with(['dosen', 'kelas.mataKuliah'])->get();
    
    foreach ($allTugas as $tugas) {
        echo "\nTugas: {$tugas->judul}\n";
        echo "Created by: " . ($tugas->dosen ? $tugas->dosen->name : 'No dosen assigned') . "\n";
        echo "Kelas: {$tugas->kelas->nama_kelas} ({$tugas->kelas->mataKuliah->nama_mk})\n";
        
        // Check if the dosen actually teaches this kelas
        if ($tugas->dosen) {
            $dosenTeachesKelas = $tugas->kelas->dosen()->where('dosen_id', $tugas->dosen_id)->exists();
            echo "Dosen teaches this kelas: " . ($dosenTeachesKelas ? 'Yes' : 'No') . "\n";
        }
    }
    
    echo "\nTest completed successfully!\n";

} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
} 