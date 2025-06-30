# Voyager Permissions Setup untuk Role Dosen dan Mahasiswa

## Overview
Sistem telah diubah untuk menggunakan Voyager's permission system untuk mengelola akses role dosen dan mahasiswa, menggantikan hardcoded role IDs dengan permission-based access control.

## Perubahan yang Dilakukan

### 1. Middleware Baru
- **VoyagerPermission**: Middleware baru yang menggunakan Voyager's permission system
- Menggantikan `CheckRole` middleware untuk route yang memerlukan permission-based access

### 2. Custom Permissions
Permissions yang dibuat untuk role dosen:
- `browse_dosen_dashboard` - Akses dashboard dosen
- `manage_mata_kuliah` - Kelola mata kuliah
- `manage_kelas` - Kelola kelas
- `manage_tugas` - Kelola tugas
- `grade_tugas` - Nilai tugas
- `view_penilaian` - Lihat penilaian
- `export_nilai` - Export nilai

Permissions yang dibuat untuk role mahasiswa:
- `browse_mahasiswa_dashboard` - Akses dashboard mahasiswa
- `view_tugas` - Lihat tugas
- `submit_tugas` - Submit tugas
- `view_nilai` - Lihat nilai
- `take_ujian` - Ambil ujian

### 3. Controller Updates
- **AdminDosenController**: Menggunakan role name 'dosen' instead of hardcoded ID
- **AdminMahasiswaController**: Menggunakan role name 'mahasiswa' instead of hardcoded ID
- **DashboardController**: Menggunakan helper methods `isAdmin()`, `isDosen()`, `isMahasiswa()`

### 4. Route Updates
Semua route telah diupdate untuk menggunakan `voyager.permission` middleware:
```php
// Contoh route dosen
Route::prefix('dosen')->middleware(['auth', 'voyager.permission:browse_dosen_dashboard'])

// Contoh route mahasiswa  
Route::prefix('mahasiswa')->middleware(['auth', 'voyager.permission:browse_mahasiswa_dashboard'])
```

### 5. User Model Updates
Helper methods di User model diupdate untuk menggunakan role names:
- `isAdmin()` - Check jika user adalah admin
- `isDosen()` - Check jika user adalah dosen  
- `isMahasiswa()` - Check jika user adalah mahasiswa

## Setup Instructions

### 1. Run Migrations dan Seeders
```bash
php artisan migrate:fresh --seed
```

### 2. Setup Voyager Permissions
```bash
php artisan voyager:setup-permissions
```

### 3. Setup Voyager BREAD (Optional)
```bash
php artisan voyager:setup-bread
```

## Mengelola Permissions melalui Voyager Admin

1. Login ke Voyager admin panel: `/admin`
2. Navigate ke **Tools > Roles**
3. Edit role **dosen** atau **mahasiswa**
4. Check/uncheck permissions sesuai kebutuhan
5. Save changes

## Keuntungan Sistem Baru

1. **Flexible**: Permissions dapat diubah melalui admin panel tanpa coding
2. **Granular**: Setiap fitur memiliki permission tersendiri
3. **Maintainable**: Tidak ada hardcoded role IDs
4. **Scalable**: Mudah menambah permission baru
5. **User-friendly**: Admin dapat mengelola permissions melalui UI

## Troubleshooting

### Permission tidak berfungsi
1. Pastikan command `voyager:setup-permissions` sudah dijalankan
2. Check apakah role memiliki permission yang benar di Voyager admin
3. Pastikan user memiliki role yang sesuai

### Role tidak ditemukan
1. Pastikan seeder `RolesTableSeeder` sudah dijalankan
2. Check database table `roles` untuk memastikan role ada

### Middleware error
1. Pastikan middleware `voyager.permission` sudah terdaftar di `app/Http/Kernel.php`
2. Restart server setelah perubahan middleware

## File yang Dimodifikasi

- `app/Http/Middleware/VoyagerPermission.php` (Baru)
- `app/Http/Kernel.php` - Menambah middleware alias
- `routes/web.php` - Update semua route untuk menggunakan permission middleware
- `app/Http/Controllers/Admin/AdminDosenController.php`
- `app/Http/Controllers/Admin/AdminMahasiswaController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Models/User.php`
- `database/seeders/CustomPermissionsSeeder.php` (Baru)
- `app/Console/Commands/SetupVoyagerPermissions.php` (Baru) 