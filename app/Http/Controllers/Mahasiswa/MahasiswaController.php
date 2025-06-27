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
        $this->middleware(['auth', 'role:3']);
    }
    
    public function dashboard()
    {
        $mahasiswa = auth()->user();
        $kelasIds = $mahasiswa->enrollments()->active()->pluck('kelas_id');
        $tugasTerbaru = \App\Models\Tugas::whereIn('kelas_id', $kelasIds)
            ->with('kelas.mataKuliah')
            ->active()
            ->available()
            ->latest()
            ->take(5)
            ->get();
        $tugasTersedia = $tugasTerbaru->count();
        $tugasSelesai = $mahasiswa->jawabanMahasiswa()->whereIn('status', ['submitted', 'graded'])->count();
        $rataRataNilai = $mahasiswa->jawabanMahasiswa()
            ->whereHas('penilaian')
            ->join('penilaian', 'jawaban_mahasiswa.id', '=', 'penilaian.jawaban_id')
            ->avg('penilaian.nilai_final') ?? 0;
        $nilaiTerbaru = $mahasiswa->jawabanMahasiswa()
            ->with(['tugas.kelas.mataKuliah', 'penilaian'])
            ->whereHas('penilaian')
            ->latest()
            ->take(5)
            ->get();
        $totalMataKuliah = \App\Models\Kelas::whereIn('id', $kelasIds)->distinct('mata_kuliah_id')->count('mata_kuliah_id');
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
        $kelasIds = $mahasiswa->enrollments()->active()->pluck('kelas_id');
        $tugasDikerjakan = $mahasiswa->jawabanMahasiswa()->pluck('tugas_id');
        return Tugas::whereIn('kelas_id', $kelasIds)
            ->active()
            ->available()
            ->whereNotIn('id', $tugasDikerjakan);
    }
}
