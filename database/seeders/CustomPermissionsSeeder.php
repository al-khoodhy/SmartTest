<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class CustomPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run()
    {
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
        }
        
        foreach ($mahasiswaPermissions as $permission) {
            Permission::firstOrCreate([
                'key' => $permission,
                'table_name' => null,
            ]);
        }
        
        // Assign permissions to dosen role
        $dosenRole = Role::where('name', 'dosen')->first();
        if ($dosenRole) {
            $dosenPerms = Permission::whereIn('key', $dosenPermissions)->get();
            $dosenRole->permissions()->sync($dosenPerms->pluck('id')->all());
        }
        
        // Assign permissions to mahasiswa role
        $mahasiswaRole = Role::where('name', 'mahasiswa')->first();
        if ($mahasiswaRole) {
            $mahasiswaPerms = Permission::whereIn('key', $mahasiswaPermissions)->get();
            $mahasiswaRole->permissions()->sync($mahasiswaPerms->pluck('id')->all());
        }
        
        // Also assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPerms = Permission::all();
            $adminRole->permissions()->sync($allPerms->pluck('id')->all());
        }
    }
} 