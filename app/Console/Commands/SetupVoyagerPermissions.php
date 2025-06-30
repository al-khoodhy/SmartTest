<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class SetupVoyagerPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voyager:setup-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup custom permissions for dosen and mahasiswa roles in Voyager';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up custom permissions for dosen and mahasiswa roles...');
        
        // Create custom permissions for dosen
        $dosenPermissions = [
            'browse_dosen_dashboard',
            'manage_mata_kuliah',
            'manage_kelas',
            'manage_tugas',
            'grade_tugas',
            'view_penilaian',
            'export_nilai',
        ];
        
        // Create custom permissions for mahasiswa
        $mahasiswaPermissions = [
            'browse_mahasiswa_dashboard',
            'view_tugas',
            'submit_tugas',
            'view_nilai',
            'take_ujian',
        ];
        
        // Create permissions
        foreach ($dosenPermissions as $permission) {
            Permission::firstOrCreate([
                'key' => $permission,
                'table_name' => null,
            ]);
            $this->info("Created permission: {$permission}");
        }
        
        foreach ($mahasiswaPermissions as $permission) {
            Permission::firstOrCreate([
                'key' => $permission,
                'table_name' => null,
            ]);
            $this->info("Created permission: {$permission}");
        }
        
        // Assign permissions to dosen role
        $dosenRole = Role::where('name', 'dosen')->first();
        if ($dosenRole) {
            $dosenPerms = Permission::whereIn('key', $dosenPermissions)->get();
            $dosenRole->permissions()->sync($dosenPerms->pluck('id')->all());
            $this->info('Permissions assigned to dosen role');
        } else {
            $this->error('Role dosen not found');
        }
        
        // Assign permissions to mahasiswa role
        $mahasiswaRole = Role::where('name', 'mahasiswa')->first();
        if ($mahasiswaRole) {
            $mahasiswaPerms = Permission::whereIn('key', $mahasiswaPermissions)->get();
            $mahasiswaRole->permissions()->sync($mahasiswaPerms->pluck('id')->all());
            $this->info('Permissions assigned to mahasiswa role');
        } else {
            $this->error('Role mahasiswa not found');
        }
        
        // Also assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPerms = Permission::all();
            $adminRole->permissions()->sync($allPerms->pluck('id')->all());
            $this->info('All permissions assigned to admin role');
        } else {
            $this->error('Role admin not found');
        }
        
        $this->info('Custom permissions setup completed!');
        $this->info('You can now manage these permissions through Voyager admin panel at /admin/roles');
    }
} 