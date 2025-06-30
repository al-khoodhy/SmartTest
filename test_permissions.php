<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use TCG\Voyager\Models\Role;

echo "Testing user permissions...\n";

// Get a mahasiswa user
$mahasiswa = User::whereHas('role', function($query) {
    $query->where('name', 'mahasiswa');
})->first();

if ($mahasiswa) {
    echo "Testing mahasiswa user: {$mahasiswa->name} (ID: {$mahasiswa->id})\n";
    echo "Role: " . ($mahasiswa->role ? $mahasiswa->role->name : 'No role') . "\n";
    
    // Test permissions
    $permissions = [
        'browse_mahasiswa_dashboard',
        'view_tugas',
        'submit_tugas',
        'view_nilai',
        'take_ujian'
    ];
    
    foreach ($permissions as $permission) {
        $hasPermission = $mahasiswa->hasPermission($permission);
        echo "- {$permission}: " . ($hasPermission ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "No mahasiswa user found!\n";
}

echo "\n";

// Get a dosen user
$dosen = User::whereHas('role', function($query) {
    $query->where('name', 'dosen');
})->first();

if ($dosen) {
    echo "Testing dosen user: {$dosen->name} (ID: {$dosen->id})\n";
    echo "Role: " . ($dosen->role ? $dosen->role->name : 'No role') . "\n";
    
    // Test permissions
    $permissions = [
        'browse_dosen_dashboard',
        'manage_mata_kuliah',
        'manage_kelas',
        'manage_tugas',
        'grade_tugas',
        'view_penilaian',
        'export_nilai'
    ];
    
    foreach ($permissions as $permission) {
        $hasPermission = $dosen->hasPermission($permission);
        echo "- {$permission}: " . ($hasPermission ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "No dosen user found!\n";
}

echo "\nPermission test completed!\n"; 