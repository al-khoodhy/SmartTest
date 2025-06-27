<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\User;

class MataKuliahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mataKuliahData = [
            [
                'kode_mk' => 'TI001',
                'nama_mk' => 'Pemrograman Web',
                'deskripsi' => 'Mata kuliah yang membahas tentang pengembangan aplikasi web menggunakan teknologi modern.',
                'sks' => 3,
                'is_active' => true,
            ],
            [
                'kode_mk' => 'TI002',
                'nama_mk' => 'Basis Data',
                'deskripsi' => 'Mata kuliah yang membahas tentang perancangan dan implementasi sistem basis data.',
                'sks' => 3,
                'is_active' => true,
            ],
            [
                'kode_mk' => 'TI003',
                'nama_mk' => 'Algoritma dan Struktur Data',
                'deskripsi' => 'Mata kuliah yang membahas tentang algoritma dan struktur data fundamental.',
                'sks' => 4,
                'is_active' => true,
            ],
            [
                'kode_mk' => 'TI004',
                'nama_mk' => 'Rekayasa Perangkat Lunak',
                'deskripsi' => 'Mata kuliah yang membahas tentang metodologi pengembangan perangkat lunak.',
                'sks' => 3,
                'is_active' => true,
            ],
            [
                'kode_mk' => 'TI005',
                'nama_mk' => 'Kecerdasan Buatan',
                'deskripsi' => 'Mata kuliah yang membahas tentang konsep dan implementasi kecerdasan buatan.',
                'sks' => 3,
                'is_active' => true,
            ]
        ];
        $mkIds = [];
        foreach ($mataKuliahData as $i => $mk) {
            $mataKuliah = MataKuliah::updateOrCreate($mk);
            $mkIds[] = $mataKuliah->id;
        }
        // Assign dosen ke mata kuliah via pivot
        $dosen1 = User::where('email', 'ahmad.wijaya@univ.ac.id')->first();
        $dosen2 = User::where('email', 'siti.nurhaliza@univ.ac.id')->first();
        if ($dosen1) {
            $dosen1->mataKuliahDiampu()->syncWithoutDetaching([$mkIds[0], $mkIds[1], $mkIds[2]]);
        }
        if ($dosen2) {
            $dosen2->mataKuliahDiampu()->syncWithoutDetaching([$mkIds[3], $mkIds[4]]);
        }
    }
}
