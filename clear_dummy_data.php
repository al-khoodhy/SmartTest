<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;

// Jalankan dalam transaksi agar aman
DB::transaction(function () {
    echo "\n=== CLEARING DUMMY DATA ===\n";

    // Hapus data child terlebih dahulu
    DB::table('jawaban_soal_mahasiswa')->truncate();
    DB::table('penilaian_soal')->truncate();
    DB::table('jawaban_mahasiswa')->truncate();
    DB::table('penilaian')->truncate();
    DB::table('enrollments')->truncate();
    DB::table('tugas')->truncate();
    DB::table('kelas_user')->truncate();
    DB::table('dosen_kelas')->truncate();
    DB::table('kelas')->truncate();
    DB::table('mata_kuliah_user')->truncate();
    DB::table('mata_kuliah')->truncate();

    // Hapus semua user kecuali admin
    $admin = User::where('role_id', 1)->where('email', 'admin@admin.com')->first();
    if ($admin) {
        User::where('id', '!=', $admin->id)->delete();
        echo "\nUser admin dipertahankan: {$admin->email}\n";
    } else {
        // Jika tidak ada user admin, hapus semua user kecuali role_id=1
        User::where('role_id', '!=', 1)->delete();
        echo "\nUser dengan role_id=1 dipertahankan.\n";
    }

    echo "\n=== SEMUA DATA DUMMY TELAH DIHAPUS, HANYA USER ADMIN TERSISA ===\n";
}); 