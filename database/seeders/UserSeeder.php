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
            'user_role' => 'admin',
            'nim_nip' => 'ADM001',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        // Dosen
        User::updateOrCreate([
            'email' => 'ahmad.wijaya@univ.ac.id',
        ], [
            'name' => 'Dr. Ahmad Wijaya',
            'user_role' => 'dosen',
            'nim_nip' => '198501012010011001',
            'phone' => '081234567890',
            'address' => 'Jl. Pendidikan No. 123, Jakarta',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'npm ',
        ], [
            'name' => 'Prof. Siti Nurhaliza',
            'user_role' => 'dosen',
            'nim_nip' => '197803152005012002',
            'phone' => '081234567891',
            'address' => 'Jl. Akademik No. 456, Jakarta',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);

        // Mahasiswa
        User::updateOrCreate([
            'email' => 'budi.santoso@student.univ.ac.id',
        ], [
            'name' => 'Budi Santoso',
            'user_role' => 'mahasiswa',
            'nim_nip' => '2021001001',
            'phone' => '081234567892',
            'address' => 'Jl. Mahasiswa No. 789, Jakarta',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'andi.pratama@student.univ.ac.id',
        ], [
            'name' => 'Andi Pratama',
            'user_role' => 'mahasiswa',
            'nim_nip' => '2021001002',
            'phone' => '081234567893',
            'address' => 'Jl. Kampus No. 101, Jakarta',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);
        User::updateOrCreate([
            'email' => 'sari.dewi@student.univ.ac.id',
        ], [
            'name' => 'Sari Dewi',
            'user_role' => 'mahasiswa',
            'nim_nip' => '2021001003',
            'phone' => '081234567894',
            'address' => 'Jl. Universitas No. 202, Jakarta',
            'is_active' => true,
            'password' => bcrypt('password123'),
        ]);
    }
}
