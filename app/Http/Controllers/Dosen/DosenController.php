<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:2']);
    }
    
    public function dashboard()
    {
        $dosen = auth()->user();
        
        // Statistik untuk dashboard
        $totalMataKuliah = $dosen->kelasAsDosen()->distinct('mata_kuliah_id')->count('mata_kuliah_id');
        $totalTugas = Tugas::where('dosen_id', $dosen->id)->active()->count();
        $tugasAktif = Tugas::where('dosen_id', $dosen->id)->active()->available()->count();
        $jawabanMenunggu = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'submitted')->count();
        
        // Tugas terbaru
        $tugasTerbaru = Tugas::where('dosen_id', $dosen->id)
            ->with('kelas.mataKuliah')
            ->latest()
            ->take(5)
            ->get();
            
        // Jawaban terbaru yang perlu dinilai
        $jawabanTerbaru = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->with(['tugas.kelas.mataKuliah', 'mahasiswa'])
            ->where('status', 'submitted')
            ->latest()
            ->take(5)
            ->get();
            
        return view('dosen.dashboard', compact(
            'totalMataKuliah',
            'totalTugas', 
            'tugasAktif',
            'jawabanMenunggu',
            'tugasTerbaru',
            'jawabanTerbaru'
        ));
    }
}
