<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            PermissionRoleTableSeeder::class,
            MenusTableSeeder::class,
            MenuItemsTableSeeder::class,
            DataTypesTableSeeder::class,
            DataRowsTableSeeder::class,
            UsersTableSeeder::class,
            UserSeeder::class,
            MataKuliahSeeder::class,
            KelasSeeder::class,
            EnrollmentSeeder::class,
            TugasSeeder::class,
            JawabanMahasiswaSeeder::class,
            PenilaianSeeder::class,
            CategoriesTableSeeder::class,
            SettingsTableSeeder::class,
            PagesTableSeeder::class,
            PostsTableSeeder::class,
            TranslationsTableSeeder::class,
            VoyagerDummyDatabaseSeeder::class,
        ]);
    }
}
