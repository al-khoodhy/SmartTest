<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MahasiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:mahasiswa']);
    }
    
    public function dashboard()
    {
        $mahasiswa = auth()->user();
        
        // Statistik untuk dashboard
        $totalMataKuliah = $mahasiswa->enrollments()->active()->count();
        $tugasTerbaru = $this->getTugasAktif($mahasiswa)
            ->with(['mataKuliah', 'dosen'])
            ->take(5)
            ->get();
        $tugasTersedia = $tugasTerbaru->count();
        $tugasSelesai = $mahasiswa->jawabanMahasiswa()->whereIn('status', ['submitted', 'graded'])->count();
        // Rata-rata nilai
        $rataRataNilai = $mahasiswa->jawabanMahasiswa()
            ->whereHas('penilaian')
            ->join('penilaian', 'jawaban_mahasiswa.id', '=', 'penilaian.jawaban_id')
            ->avg('penilaian.nilai_final') ?? 0;
        // Nilai terbaru
        $nilaiTerbaru = $mahasiswa->jawabanMahasiswa()
            ->with(['tugas.mataKuliah', 'penilaian'])
            ->whereHas('penilaian')
            ->latest()
            ->take(5)
            ->get();
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
        // Get mata kuliah yang diambil mahasiswa
        $mataKuliahIds = $mahasiswa->enrollments()
            ->active()
            ->pluck('mata_kuliah_id');
        
        // Get tugas yang belum dikerjakan
        $tugasDikerjakan = $mahasiswa->jawabanMahasiswa()
            ->pluck('tugas_id');
        
        return Tugas::whereIn('mata_kuliah_id', $mataKuliahIds)
            ->active()
            ->available()
            ->whereNotIn('id', $tugasDikerjakan);
    }
}
