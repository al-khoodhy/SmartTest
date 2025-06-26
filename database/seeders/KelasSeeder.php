<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kelas;
use App\Models\MataKuliah;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $mataKuliahs = MataKuliah::all();
        foreach ($mataKuliahs as $mk) {
            // Ambil dosen pertama dari relasi pivot
            $dosen = $mk->dosen()->first();
            if (!$dosen) continue; // skip jika tidak ada dosen
            for ($i = 1; $i <= 2; $i++) {
                Kelas::create([
                    'nama_kelas' => $mk->nama_mk . ' - Kelas ' . chr(64 + $i),
                    'mata_kuliah_id' => $mk->id,
                    'dosen_id' => $dosen->id,
                ]);
            }
        }
    }
} 