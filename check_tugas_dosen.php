<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Tugas;
use App\Models\User;
use TCG\Voyager\Models\Role;

echo "Checking tugas and dosen relationships...\n";

// Check tugas without dosen_id
$tugasWithoutDosen = Tugas::whereNull('dosen_id')->get();
echo "Tugas without dosen_id: " . $tugasWithoutDosen->count() . "\n";

if ($tugasWithoutDosen->count() > 0) {
    echo "Fixing tugas without dosen_id...\n";
    
    // Get first dosen
    $dosenRole = Role::where('name', 'dosen')->first();
    if ($dosenRole) {
        $firstDosen = User::where('role_id', $dosenRole->id)->first();
        if ($firstDosen) {
            Tugas::whereNull('dosen_id')->update(['dosen_id' => $firstDosen->id]);
            echo "Fixed " . $tugasWithoutDosen->count() . " tugas.\n";
        } else {
            echo "No dosen found!\n";
        }
    } else {
        echo "Dosen role not found!\n";
    }
}

// Check tugas with invalid dosen_id
$allDosenIds = User::whereHas('role', function($query) {
    $query->where('name', 'dosen');
})->pluck('id');

$tugasWithInvalidDosen = Tugas::whereNotIn('dosen_id', $allDosenIds)->whereNotNull('dosen_id')->get();
echo "Tugas with invalid dosen_id: " . $tugasWithInvalidDosen->count() . "\n";

if ($tugasWithInvalidDosen->count() > 0) {
    echo "Fixing tugas with invalid dosen_id...\n";
    
    $dosenRole = Role::where('name', 'dosen')->first();
    if ($dosenRole) {
        $firstDosen = User::where('role_id', $dosenRole->id)->first();
        if ($firstDosen) {
            Tugas::whereNotIn('dosen_id', $allDosenIds)->whereNotNull('dosen_id')->update(['dosen_id' => $firstDosen->id]);
            echo "Fixed " . $tugasWithInvalidDosen->count() . " tugas.\n";
        }
    }
}

// Check all tugas now
$allTugas = Tugas::all();
echo "\nTotal tugas: " . $allTugas->count() . "\n";

foreach ($allTugas as $tugas) {
    $dosenName = $tugas->dosen ? $tugas->dosen->name : 'No dosen';
    $kelasName = $tugas->kelas ? $tugas->kelas->nama_kelas : 'No kelas';
    echo "- Tugas ID {$tugas->id}: {$tugas->judul} | Dosen: {$dosenName} | Kelas: {$kelasName}\n";
}

echo "\nTugas check completed!\n"; 