<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::updateOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin',
            'role_id' => 1,
            'password' => bcrypt('password123'),
        ]);

        // Dosen
        User::updateOrCreate([
            'email' => 'ahmad.wijaya@univ.ac.id',
        ], [
            'name' => 'Dr. Ahmad Wijaya',
            'role_id' => 2,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'siti.nurhaliza@univ.ac.id',
        ], [
            'name' => 'Prof. Siti Nurhaliza',
            'role_id' => 2,
            'password' => bcrypt('password123'),
        ]);

        // Mahasiswa
        User::updateOrCreate([
            'email' => 'budi.santoso@student.univ.ac.id',
        ], [
            'name' => 'Budi Santoso',
            'role_id' => 3,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'andi.pratama@student.univ.ac.id',
        ], [
            'name' => 'Andi Pratama',
            'role_id' => 3,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'sari.dewi@student.univ.ac.id',
        ], [
            'name' => 'Sari Dewi',
            'role_id' => 3,
            'password' => bcrypt('password123'),
        ]);
    }
}
