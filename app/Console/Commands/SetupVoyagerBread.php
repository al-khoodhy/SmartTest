<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;

class SetupVoyagerBread extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:setup-bread';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup BREAD for custom models in Voyager admin panel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up Voyager BREAD for custom models...');
        
        // Setup BREAD for each model
        $this->setupMataKuliahBread();
        $this->setupTugasBread();
        $this->setupJawabanMahasiswaBread();
        $this->setupPenilaianBread();
        $this->setupEnrollmentBread();
        
        // Update Users BREAD to include custom fields
        $this->updateUsersBread();
        
        $this->info('Voyager BREAD setup completed!');
    }
    
    private function setupMataKuliahBread()
    {
        $this->info('Setting up Mata Kuliah BREAD...');
        
        // Create DataType
        $dataType = DataType::firstOrCreate([
            'slug' => 'mata-kuliah'
        ], [
            'name' => 'mata_kuliah',
            'display_name_singular' => 'Mata Kuliah',
            'display_name_plural' => 'Mata Kuliah',
            'icon' => 'voyager-book',
            'model_name' => 'App\\Models\\MataKuliah',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Manajemen data mata kuliah',
            'generate_permissions' => 1,
            'server_side' => 0,
            'details' => json_encode([
                'order_column' => 'id',
                'order_display_column' => 'nama_mk',
                'order_direction' => 'asc',
                'default_search_key' => 'nama_mk'
            ])
        ]);
        
        // Add to menu
        $menu = \TCG\Voyager\Models\Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Mata Kuliah',
                'url' => '',
                'route' => 'voyager.mata-kuliah.index',
                'target' => '_self',
                'icon_class' => 'voyager-book',
                'color' => null,
                'parent_id' => null,
                'order' => 10
            ]);
        }
        
        // Create permissions
        Permission::generateFor('mata-kuliah');
    }
    
    private function setupTugasBread()
    {
        $this->info('Setting up Tugas BREAD...');
        
        $dataType = DataType::firstOrCreate([
            'slug' => 'tugas'
        ], [
            'name' => 'tugas',
            'display_name_singular' => 'Tugas',
            'display_name_plural' => 'Tugas',
            'icon' => 'voyager-file-text',
            'model_name' => 'App\\Models\\Tugas',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Manajemen tugas esai',
            'generate_permissions' => 1,
            'server_side' => 0,
            'details' => json_encode([
                'order_column' => 'created_at',
                'order_display_column' => 'judul',
                'order_direction' => 'desc',
                'default_search_key' => 'judul'
            ])
        ]);
        
        $menu = \TCG\Voyager\Models\Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Tugas',
                'url' => '',
                'route' => 'voyager.tugas.index',
                'target' => '_self',
                'icon_class' => 'voyager-file-text',
                'color' => null,
                'parent_id' => null,
                'order' => 11
            ]);
        }
        
        Permission::generateFor('tugas');
    }
    
    private function setupJawabanMahasiswaBread()
    {
        $this->info('Setting up Jawaban Mahasiswa BREAD...');
        
        $dataType = DataType::firstOrCreate([
            'slug' => 'jawaban-mahasiswa'
        ], [
            'name' => 'jawaban_mahasiswa',
            'display_name_singular' => 'Jawaban Mahasiswa',
            'display_name_plural' => 'Jawaban Mahasiswa',
            'icon' => 'voyager-edit',
            'model_name' => 'App\\Models\\JawabanMahasiswa',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Manajemen jawaban mahasiswa',
            'generate_permissions' => 1,
            'server_side' => 1,
            'details' => json_encode([
                'order_column' => 'created_at',
                'order_display_column' => 'id',
                'order_direction' => 'desc',
                'default_search_key' => null
            ])
        ]);
        
        $menu = \TCG\Voyager\Models\Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Jawaban Mahasiswa',
                'url' => '',
                'route' => 'voyager.jawaban-mahasiswa.index',
                'target' => '_self',
                'icon_class' => 'voyager-edit',
                'color' => null,
                'parent_id' => null,
                'order' => 12
            ]);
        }
        
        Permission::generateFor('jawaban-mahasiswa');
    }
    
    private function setupPenilaianBread()
    {
        $this->info('Setting up Penilaian BREAD...');
        
        $dataType = DataType::firstOrCreate([
            'slug' => 'penilaian'
        ], [
            'name' => 'penilaian',
            'display_name_singular' => 'Penilaian',
            'display_name_plural' => 'Penilaian',
            'icon' => 'voyager-star',
            'model_name' => 'App\\Models\\Penilaian',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Manajemen penilaian tugas',
            'generate_permissions' => 1,
            'server_side' => 1,
            'details' => json_encode([
                'order_column' => 'graded_at',
                'order_display_column' => 'nilai_final',
                'order_direction' => 'desc',
                'default_search_key' => null
            ])
        ]);
        
        $menu = \TCG\Voyager\Models\Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Penilaian',
                'url' => '',
                'route' => 'voyager.penilaian.index',
                'target' => '_self',
                'icon_class' => 'voyager-star',
                'color' => null,
                'parent_id' => null,
                'order' => 13
            ]);
        }
        
        Permission::generateFor('penilaian');
    }
    
    private function setupEnrollmentBread()
    {
        $this->info('Setting up Enrollment BREAD...');
        
        $dataType = DataType::firstOrCreate([
            'slug' => 'enrollments'
        ], [
            'name' => 'enrollments',
            'display_name_singular' => 'Enrollment',
            'display_name_plural' => 'Enrollments',
            'icon' => 'voyager-group',
            'model_name' => 'App\\Models\\Enrollment',
            'policy_name' => null,
            'controller' => null,
            'description' => 'Manajemen pendaftaran mahasiswa ke mata kuliah',
            'generate_permissions' => 1,
            'server_side' => 0,
            'details' => json_encode([
                'order_column' => 'tanggal_daftar',
                'order_display_column' => 'id',
                'order_direction' => 'desc',
                'default_search_key' => null
            ])
        ]);
        
        $menu = \TCG\Voyager\Models\Menu::where('name', 'admin')->first();
        if ($menu) {
            MenuItem::firstOrCreate([
                'menu_id' => $menu->id,
                'title' => 'Enrollments',
                'url' => '',
                'route' => 'voyager.enrollments.index',
                'target' => '_self',
                'icon_class' => 'voyager-group',
                'color' => null,
                'parent_id' => null,
                'order' => 14
            ]);
        }
        
        Permission::generateFor('enrollments');
    }
    
    private function updateUsersBread()
    {
        $this->info('Updating Users BREAD...');
        
        // Update existing users DataType to include custom fields
        $dataType = DataType::where('slug', 'users')->first();
        if ($dataType) {
            $dataType->update([
                'description' => 'Manajemen pengguna sistem (Admin, Dosen, Mahasiswa)'
            ]);
        }
    }
}
