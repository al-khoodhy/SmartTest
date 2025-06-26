<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_mk' => 'TI' . $this->faker->unique()->numberBetween(100, 999),
            'nama_mk' => $this->faker->words(3, true),
            'deskripsi' => $this->faker->paragraph(),
            'sks' => $this->faker->numberBetween(2, 4),
            'dosen_id' => \App\Models\User::factory()->create(['user_role' => 'dosen']),
            'is_active' => true,
        ];
    }
}
