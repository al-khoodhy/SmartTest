<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        // First, update any existing users to have a default role (admin) to avoid foreign key constraint issues
        DB::table('users')->update(['role_id' => 1]);
        
        // Now we can safely delete and recreate roles
        DB::table('roles')->delete();
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'display_name' => 'Admin'],
            ['id' => 2, 'name' => 'dosen', 'display_name' => 'Dosen'],
            ['id' => 3, 'name' => 'mahasiswa', 'display_name' => 'Mahasiswa'],
            ['id' => 4, 'name' => 'user', 'display_name' => 'User'],
        ]);
    }
}
