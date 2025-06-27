<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tugas>
 */
class TugasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dosen = \App\Models\User::factory()->create(['role_id' => 2]);
        $mataKuliah = \App\Models\MataKuliah::factory()->create(['dosen_id' => $dosen->id]);
        
        return [
            'judul' => $this->faker->sentence(4),
            'deskripsi' => $this->faker->paragraph(),
            'soal_esai' => $this->faker->paragraphs(3, true),
            'kelas_id' => $mataKuliah->id,
            'dosen_id' => $dosen->id,
            'deadline' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'durasi_menit' => $this->faker->numberBetween(60, 180),
            'nilai_maksimal' => 100,
            'rubrik_penilaian' => $this->faker->paragraph(),
            'auto_grade' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
