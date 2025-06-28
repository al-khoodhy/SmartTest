<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Kelas;
use App\Models\User;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Starting migration of dosen-kelas data...\n";

try {
    // Get all classes with dosen_id
    $kelasWithDosen = DB::table('kelas')
        ->whereNotNull('dosen_id')
        ->where('dosen_id', '!=', 0)
        ->get();

    echo "Found " . $kelasWithDosen->count() . " classes with assigned dosen\n";

    foreach ($kelasWithDosen as $kelas) {
        // Check if the dosen exists
        $dosen = User::find($kelas->dosen_id);
        
        if ($dosen && $dosen->role_id == 2) { // Ensure it's a dosen
            // Check if the relationship already exists
            $existingRelation = DB::table('dosen_kelas')
                ->where('dosen_id', $kelas->dosen_id)
                ->where('kelas_id', $kelas->id)
                ->first();

            if (!$existingRelation) {
                // Insert into the pivot table
                DB::table('dosen_kelas')->insert([
                    'dosen_id' => $kelas->dosen_id,
                    'kelas_id' => $kelas->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Migrated: Dosen ID {$kelas->dosen_id} -> Kelas ID {$kelas->id}\n";
            } else {
                echo "Skipped: Relationship already exists for Dosen ID {$kelas->dosen_id} -> Kelas ID {$kelas->id}\n";
            }
        } else {
            echo "Warning: Dosen ID {$kelas->dosen_id} not found or not a dosen for Kelas ID {$kelas->id}\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
} 