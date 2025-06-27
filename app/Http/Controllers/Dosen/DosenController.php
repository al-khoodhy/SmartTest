<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
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
        $totalMataKuliah = \App\Models\Kelas::where('dosen_id', $dosen->id)->distinct('mata_kuliah_id')->count('mata_kuliah_id');
        $totalTugas = \App\Models\Tugas::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->active()->count();
        $tugasAktif = \App\Models\Tugas::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->active()->available()->count();
        $jawabanMenunggu = \App\Models\JawabanMahasiswa::whereHas('tugas.kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'submitted')->count();
        // Tugas terbaru
        $tugasTerbaru = \App\Models\Tugas::whereHas('kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->with('kelas.mataKuliah')->latest()->take(5)->get();
        // Jawaban terbaru yang perlu dinilai
        $jawabanTerbaru = \App\Models\JawabanMahasiswa::whereHas('tugas.kelas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->with(['tugas.kelas.mataKuliah', 'mahasiswa'])->where('status', 'submitted')->latest()->take(5)->get();
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
