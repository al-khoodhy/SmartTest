<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use TCG\Voyager\Models\Role;
use App\Models\User;

echo "Checking roles and users...\n";

// Check roles
$roles = Role::all();
echo "Found " . $roles->count() . " roles:\n";
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}

// Check users without roles
$usersWithoutRoles = User::whereNull('role_id')->get();
echo "\nUsers without roles: " . $usersWithoutRoles->count() . "\n";

if ($usersWithoutRoles->count() > 0) {
    echo "Fixing users without roles...\n";
    
    // Get default role (mahasiswa)
    $defaultRole = Role::where('name', 'mahasiswa')->first();
    if (!$defaultRole) {
        echo "Error: mahasiswa role not found!\n";
        exit(1);
    }
    
    // Assign default role to users without roles
    User::whereNull('role_id')->update(['role_id' => $defaultRole->id]);
    echo "Fixed " . $usersWithoutRoles->count() . " users.\n";
}

// Check if all users have valid roles
$usersWithInvalidRoles = User::whereNotIn('role_id', $roles->pluck('id'))->get();
echo "\nUsers with invalid roles: " . $usersWithInvalidRoles->count() . "\n";

if ($usersWithInvalidRoles->count() > 0) {
    echo "Fixing users with invalid roles...\n";
    
    $defaultRole = Role::where('name', 'mahasiswa')->first();
    if ($defaultRole) {
        User::whereNotIn('role_id', $roles->pluck('id'))->update(['role_id' => $defaultRole->id]);
        echo "Fixed " . $usersWithInvalidRoles->count() . " users.\n";
    }
}

echo "\nRole check completed!\n"; 
 