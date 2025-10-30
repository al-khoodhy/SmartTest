<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dosen\DosenController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;
use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Auth\CustomVoyagerAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role_id == 2) { // Dosen
            return redirect()->route('dosen.dashboard');
        } elseif ($user->role_id == 3) { // Mahasiswa
            return redirect()->route('mahasiswa.dashboard');
        } elseif ($user->role_id == 1) { // Admin
            return redirect()->route('voyager.dashboard');
        }
    }
    return redirect()->route('voyager.login');
});


// Main dashboard route (redirects based on role)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Dosen routes - using Voyager permissions
Route::prefix('dosen')->name('dosen.')->middleware(['auth', 'voyager.permission:browse_dosen_dashboard'])->group(function () {
    Route::get('/dashboard', [DosenController::class, 'dashboard'])->name('dashboard');
    
    // Mata Kuliah routes
    Route::resource('mata-kuliah', \App\Http\Controllers\Dosen\MataKuliahController::class)->parameters([
        'mata-kuliah' => 'mataKuliah'
    ])->middleware('voyager.permission:manage_mata_kuliah');
    
    // Kelas routes
    Route::resource('kelas', \App\Http\Controllers\Dosen\KelasController::class)->parameters([
        'kelas' => 'kelas'
    ])->middleware('voyager.permission:manage_kelas');
    
    // Tugas routes
    Route::resource('tugas', \App\Http\Controllers\Dosen\TugasController::class)->parameters([
        'tugas' => 'tugas'
    ])->middleware('voyager.permission:manage_tugas');
    Route::patch('tugas/{tugas}/toggle-status', [\App\Http\Controllers\Dosen\TugasController::class, 'toggleStatus'])->name('tugas.toggle-status');
    
    // Penilaian routes
    Route::prefix('penilaian')->name('penilaian.')->middleware('voyager.permission:view_penilaian')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dosen\PenilaianController::class, 'index'])->name('index');
        Route::get('/tugas/{tugas}', [\App\Http\Controllers\Dosen\PenilaianController::class, 'showTugas'])->name('tugas');
        Route::get('/jawaban/{jawaban}', [\App\Http\Controllers\Dosen\PenilaianController::class, 'showJawaban'])->name('jawaban');
        Route::get('/jawaban/{jawaban}/grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'grade'])->name('grade');
        Route::post('/jawaban/{jawaban}/grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'storeGrade'])->name('store-grade');
        Route::post('/tugas/{tugas}/auto-grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'autoGrade'])->name('auto-grade');
        Route::post('/jawaban/{jawaban}/regrade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'regrade'])->name('regrade');
        Route::get('/tugas/{tugas}/export', [\App\Http\Controllers\Dosen\PenilaianController::class, 'exportNilai'])->name('export')->middleware('voyager.permission:export_nilai');
    });

    Route::get('profile', [\App\Http\Controllers\Dosen\ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [\App\Http\Controllers\Dosen\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile/update', [\App\Http\Controllers\Dosen\ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [\App\Http\Controllers\Dosen\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('profile/update-password', [\App\Http\Controllers\Dosen\ProfileController::class, 'updatePassword'])->name('profile.update-password');
});
Auth::routes();
// Mahasiswa routes - using Voyager permissions
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'voyager.permission:browse_mahasiswa_dashboard'])->group(function () {
    
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/scalper', [MahasiswaController::class, 'scalper'])->name('scalper');
    // Profile routes
    Route::get('profile', [\App\Http\Controllers\Mahasiswa\ProfileController::class, 'index'])->name('profile.index');
    Route::get('profile/edit', [\App\Http\Controllers\Mahasiswa\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('profile/update', [\App\Http\Controllers\Mahasiswa\ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/change-password', [\App\Http\Controllers\Mahasiswa\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('profile/update-password', [\App\Http\Controllers\Mahasiswa\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    
    // Tugas routes
    Route::prefix('tugas')->name('tugas.')->middleware('voyager.permission:view_tugas')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'index'])->name('index');
        Route::get('/{tugas}', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'show'])->name('show');
        Route::post('/{tugas}/start', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'start'])->name('start')->middleware('voyager.permission:submit_tugas');
    });
    
    // Ujian routes
    Route::prefix('ujian')->name('ujian.')->middleware('voyager.permission:take_ujian')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'index'])->name('index');
        Route::get('/{jawaban}/work', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'work'])->name('work');
        Route::post('/{jawaban}/save-draft', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{jawaban}/submit', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'submit'])->name('submit');
        Route::get('/{jawaban}/get-remaining-time', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'getRemainingTime'])->name('get-remaining-time');
    });
    
    // Nilai routes
    Route::prefix('nilai')->name('nilai.')->middleware('voyager.permission:view_nilai')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'index'])->name('index');
        Route::get('/{jawaban}', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'show'])->name('show');
        Route::get('/per/mata-kuliah', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'perMataKuliah'])->name('per-mata-kuliah');
    });
});

// Admin routes (Voyager)
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    // Override POST login agar redirect sesuai role
    Route::post('login', [CustomVoyagerAuthController::class, 'postLogin'])->name('voyager.postlogin');
});

// Redirect after login
Route::get('/home', [DashboardController::class, 'index'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin routes for managing users - using Voyager permissions
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'voyager.permission:browse_users,add_users,edit_users']], function () {
    // Pendaftaran Dosen + Mata Kuliah
    Route::get('/dosen/create', [\App\Http\Controllers\Admin\AdminDosenController::class, 'create'])->name('admin.dosen.create');
    Route::post('/dosen', [\App\Http\Controllers\Admin\AdminDosenController::class, 'store'])->name('admin.dosen.store');
    Route::get('/mahasiswa/create', [\App\Http\Controllers\Admin\AdminMahasiswaController::class, 'create'])->name('admin.mahasiswa.create');
    Route::post('/mahasiswa', [\App\Http\Controllers\Admin\AdminMahasiswaController::class, 'store'])->name('admin.mahasiswa.store');
    Route::post('/mahasiswa/import', [\App\Http\Controllers\Admin\AdminMahasiswaController::class, 'import'])->name('admin.mahasiswa.import');
});
