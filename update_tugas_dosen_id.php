<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Tugas;
use App\Models\Kelas;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating tugas with dosen_id based on kelas relationship...\n";

try {
    // Get all tugas without dosen_id
    $tugasWithoutDosen = Tugas::whereNull('dosen_id')->get();
    echo "Found " . $tugasWithoutDosen->count() . " tugas without dosen_id\n";
    
    foreach ($tugasWithoutDosen as $tugas) {
        // Get the first dosen for this kelas
        $dosen = $tugas->kelas->dosen()->first();
        
        if ($dosen) {
            $tugas->update(['dosen_id' => $dosen->id]);
            echo "Updated tugas ID {$tugas->id} ({$tugas->judul}) with dosen ID {$dosen->id} ({$dosen->name})\n";
        } else {
            echo "Warning: No dosen found for tugas ID {$tugas->id} (kelas ID {$tugas->kelas_id})\n";
        }
    }
    
    // Also update tugas that might have wrong dosen_id
    $allTugas = Tugas::whereNotNull('dosen_id')->get();
    echo "\nChecking " . $allTugas->count() . " existing tugas with dosen_id...\n";
    
    foreach ($allTugas as $tugas) {
        // Check if the dosen actually teaches this kelas
        $dosenTeachesKelas = $tugas->kelas->dosen()->where('dosen_id', $tugas->dosen_id)->exists();
        
        if (!$dosenTeachesKelas) {
            // Get the first dosen for this kelas
            $correctDosen = $tugas->kelas->dosen()->first();
            
            if ($correctDosen) {
                $oldDosenId = $tugas->dosen_id;
                $tugas->update(['dosen_id' => $correctDosen->id]);
                echo "Fixed tugas ID {$tugas->id} ({$tugas->judul}): dosen_id {$oldDosenId} -> {$correctDosen->id}\n";
            } else {
                echo "Warning: No dosen found for tugas ID {$tugas->id} (kelas ID {$tugas->kelas_id})\n";
            }
        }
    }
    
    echo "\nUpdate completed successfully!\n";

} catch (Exception $e) {
    echo "Error during update: " . $e->getMessage() . "\n";
} 