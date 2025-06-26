<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dosen\DosenController;
use App\Http\Controllers\Mahasiswa\MahasiswaController;

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

// Landing page
Route::get('/', function () {
    return view('auth.login');
});

// Authentication routes
Auth::routes();

// Main dashboard route (redirects based on role)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Dosen routes
Route::prefix('dosen')->name('dosen.')->middleware(['auth', 'role:dosen'])->group(function () {
    Route::get('/dashboard', [DosenController::class, 'dashboard'])->name('dashboard');
    
    // Tugas routes
    Route::resource('tugas', \App\Http\Controllers\Dosen\TugasController::class)->parameters([
        'tugas' => 'tugas'
    ]);
    Route::patch('tugas/{tugas}/toggle-status', [\App\Http\Controllers\Dosen\TugasController::class, 'toggleStatus'])->name('tugas.toggle-status');
    
    // Penilaian routes
    Route::prefix('penilaian')->name('penilaian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dosen\PenilaianController::class, 'index'])->name('index');
        Route::get('/tugas/{tugas}', [\App\Http\Controllers\Dosen\PenilaianController::class, 'showTugas'])->name('tugas');
        Route::get('/jawaban/{jawaban}', [\App\Http\Controllers\Dosen\PenilaianController::class, 'showJawaban'])->name('jawaban');
        Route::get('/jawaban/{jawaban}/grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'grade'])->name('grade');
        Route::post('/jawaban/{jawaban}/grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'storeGrade'])->name('store-grade');
        Route::post('/tugas/{tugas}/auto-grade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'autoGrade'])->name('auto-grade');
        Route::post('/jawaban/{jawaban}/regrade', [\App\Http\Controllers\Dosen\PenilaianController::class, 'regrade'])->name('regrade');
        Route::get('/tugas/{tugas}/export', [\App\Http\Controllers\Dosen\PenilaianController::class, 'exportNilai'])->name('export');
    });
});

// Mahasiswa routes
Route::prefix('mahasiswa')->name('mahasiswa.')->middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/dashboard', [MahasiswaController::class, 'dashboard'])->name('dashboard');
    
    // Tugas routes
    Route::prefix('tugas')->name('tugas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'index'])->name('index');
        Route::get('/{tugas}', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'show'])->name('show');
        Route::post('/{tugas}/start', [\App\Http\Controllers\Mahasiswa\TugasController::class, 'start'])->name('start');
    });
    
    // Ujian routes
    Route::prefix('ujian')->name('ujian.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'index'])->name('index');
        Route::get('/{jawaban}/work', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'work'])->name('work');
        Route::post('/{jawaban}/save-draft', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{jawaban}/submit', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'submit'])->name('submit');
        Route::get('/{jawaban}/remaining-time', [\App\Http\Controllers\Mahasiswa\UjianController::class, 'getRemainingTime'])->name('remaining-time');
    });
    
    // Nilai routes
    Route::prefix('nilai')->name('nilai.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'index'])->name('index');
        Route::get('/{jawaban}', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'show'])->name('show');
        Route::get('/per/mata-kuliah', [\App\Http\Controllers\Mahasiswa\NilaiController::class, 'perMataKuliah'])->name('per-mata-kuliah');
    });
});

// Admin routes (Voyager)
Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

// Redirect after login
Route::get('/home', [DashboardController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function () {
    // Pendaftaran Dosen + Mata Kuliah
    Route::get('/dosen/create', [\App\Http\Controllers\Admin\AdminDosenController::class, 'create'])->name('admin.dosen.create');
    Route::post('/dosen', [\App\Http\Controllers\Admin\AdminDosenController::class, 'store'])->name('admin.dosen.store');
});
