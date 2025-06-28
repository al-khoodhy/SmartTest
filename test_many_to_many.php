<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing many-to-many relationship between dosen and classes...\n\n";

try {
    // Get all dosen
    $dosen = User::where('role_id', 2)->get();
    echo "Found " . $dosen->count() . " dosen\n";
    
    foreach ($dosen as $d) {
        echo "\nDosen: {$d->name} ({$d->email})\n";
        echo "Classes taught:\n";
        
        $classes = $d->kelasAsDosen()->with('mataKuliah')->get();
        foreach ($classes as $kelas) {
            echo "  - {$kelas->nama_kelas} ({$kelas->mataKuliah->nama_mk})\n";
        }
    }
    
    echo "\n\nTesting classes with multiple dosen:\n";
    $classes = Kelas::with(['dosen', 'mataKuliah'])->get();
    
    foreach ($classes as $kelas) {
        echo "\nClass: {$kelas->nama_kelas} ({$kelas->mataKuliah->nama_mk})\n";
        echo "Dosen teaching this class:\n";
        
        foreach ($kelas->dosen as $dosen) {
            echo "  - {$dosen->name} ({$dosen->email})\n";
        }
    }
    
    echo "\nTest completed successfully!\n";

} catch (Exception $e) {
    echo "Error during test: " . $e->getMessage() . "\n";
} 