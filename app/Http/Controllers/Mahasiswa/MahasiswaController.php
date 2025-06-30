<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\MataKuliah;
use App\Models\Kelas;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'voyager.permission:browse_mahasiswa_dashboard']);
    }
    
    public function dashboard()
    {
        $mahasiswa = auth()->user();
        
        // Get kelas IDs dari enrollment
        $kelasIds = Enrollment::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'active')
            ->pluck('kelas_id');
            
        $tugasTerbaru = Tugas::whereIn('kelas_id', $kelasIds)
            ->with('kelas.mataKuliah')
            ->active()
            ->available()
            ->latest()
            ->take(5)
            ->get();
        $tugasTersedia = $tugasTerbaru->count();
        
        // Get jawaban mahasiswa
        $tugasSelesai = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
            
        $rataRataNilai = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('penilaian')
            ->get()
            ->avg('nilai_akhir') ?? 0;
            
        $nilaiTerbaru = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->with(['tugas.kelas.mataKuliah', 'penilaian'])
            ->whereHas('penilaian')
            ->latest()
            ->take(5)
            ->get();
            
        $totalMataKuliah = Kelas::whereIn('id', $kelasIds)->distinct('mata_kuliah_id')->count('mata_kuliah_id');
        return view('mahasiswa.dashboard', compact(
            'totalMataKuliah',
            'tugasTersedia',
            'tugasTerbaru',
            'tugasSelesai',
            'rataRataNilai',
            'nilaiTerbaru'
        ));
    }
    
    private function getTugasAktif($mahasiswa)
    {
        $kelasIds = Enrollment::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'active')
            ->pluck('kelas_id');
        $tugasDikerjakan = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)->pluck('tugas_id');
        return Tugas::whereIn('kelas_id', $kelasIds)
            ->active()
            ->available()
            ->whereNotIn('id', $tugasDikerjakan);
    }
}
