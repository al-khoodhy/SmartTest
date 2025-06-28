<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil dosen
        $dosen1 = User::where('email', 'ahmad.wijaya@univ.ac.id')->first();
        $dosen2 = User::where('email', 'siti.nurhaliza@univ.ac.id')->first();
        // Ambil mata kuliah
        $mk1 = \App\Models\MataKuliah::where('kode_mk', 'TI001')->first();
        $mk2 = \App\Models\MataKuliah::where('kode_mk', 'TI002')->first();
        $mk3 = \App\Models\MataKuliah::where('kode_mk', 'TI003')->first();
        $mk4 = \App\Models\MataKuliah::where('kode_mk', 'TI004')->first();
        $mk5 = \App\Models\MataKuliah::where('kode_mk', 'TI005')->first();

        // Buat kelas sample
        $kelasA = \App\Models\Kelas::create(['nama_kelas' => 'Web A', 'mata_kuliah_id' => $mk1->id]);
        $kelasB = \App\Models\Kelas::create(['nama_kelas' => 'Web B', 'mata_kuliah_id' => $mk1->id]);
        $kelasC = \App\Models\Kelas::create(['nama_kelas' => 'Basis Data A', 'mata_kuliah_id' => $mk2->id]);
        $kelasD = \App\Models\Kelas::create(['nama_kelas' => 'Algoritma A', 'mata_kuliah_id' => $mk3->id]);
        $kelasE = \App\Models\Kelas::create(['nama_kelas' => 'RPL A', 'mata_kuliah_id' => $mk4->id]);
        $kelasF = \App\Models\Kelas::create(['nama_kelas' => 'AI A', 'mata_kuliah_id' => $mk5->id]);

        // Assign dosen to classes using many-to-many relationship
        if ($dosen1 && $dosen2) {
            // Assign dosen1 to classes
            $dosen1->kelasAsDosen()->attach([$kelasA->id, $kelasB->id, $kelasC->id]);
            
            // Assign dosen2 to classes (including kelasA to show multiple dosen per class)
            $dosen2->kelasAsDosen()->attach([$kelasA->id, $kelasD->id, $kelasE->id, $kelasF->id]);
        }
    }
} 