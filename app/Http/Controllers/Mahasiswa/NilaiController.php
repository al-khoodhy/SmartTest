<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\JawabanMahasiswa;
use App\Models\Enrollment;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'voyager.permission:view_nilai']);
    }
    
    /**
     * Display kumpulan nilai mahasiswa
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();
        
        // Get mata kuliah yang diambil mahasiswa
        $kelasIds = Enrollment::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'active')
            ->pluck('kelas_id');
        
        $query = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->with(['tugas.kelas.mataKuliah', 'penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->whereIn('status', ['submitted', 'graded']);
        
        // Filter berdasarkan kelas
        if ($request->kelas_id) {
            $query->whereHas('tugas', function($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }
        
        // Filter berdasarkan status penilaian
        if ($request->status_penilaian) {
            switch ($request->status_penilaian) {
                case 'graded':
                    $query->where(function($q) {
                        $q->where('status', 'graded')
                          ->orWhereHas('jawabanSoal.penilaian', function($subQ) {
                              $subQ->whereIn('status_penilaian', ['ai_graded', 'final']);
                          });
                    });
                    break;
                case 'pending':
                    $query->whereDoesntHave('jawabanSoal.penilaian', function($q) {
                        $q->whereIn('status_penilaian', ['ai_graded', 'final']);
                    });
                    break;
            }
        }
        
        $jawaban = $query->latest()->paginate(10);
        
        // Get mata kuliah untuk filter
        $kelas = Enrollment::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'active')
            ->with('kelas.mataKuliah')
            ->get();
        
        // Statistik nilai - perbaiki logika untuk auto-grading
        $totalTugas = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
        
        $sudahDinilai = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->where(function($q) {
                $q->where('status', 'graded')
                  ->orWhereHas('jawabanSoal.penilaian', function($subQ) {
                      $subQ->whereIn('status_penilaian', ['ai_graded', 'final']);
                  });
            })
            ->count();
        
        $menungguPenilaian = $totalTugas - $sudahDinilai;
        
        // Rata-rata nilai - include auto-graded answers
        $jawabanUntukRataRata = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->where(function($q) {
                $q->where('status', 'graded')
                  ->orWhereHas('jawabanSoal.penilaian', function($subQ) {
                      $subQ->whereIn('status_penilaian', ['ai_graded', 'final']);
                  });
            })
            ->with(['jawabanSoal.soal', 'jawabanSoal.penilaian'])
            ->get();
        
        $rataRataNilai = $jawabanUntukRataRata->avg('nilai_akhir');
        
        return view('mahasiswa.nilai.index', compact(
            'jawaban', 
            'kelas', 
            'totalTugas', 
            'sudahDinilai', 
            'menungguPenilaian', 
            'rataRataNilai'
        ));
    }
    
    /**
     * Show detail nilai dan feedback
     */
    public function show(JawabanMahasiswa $jawaban)
    {
        $mahasiswa = auth()->user();
        
        // Check ownership
        if ($jawaban->mahasiswa_id !== $mahasiswa->id) {
            abort(403, 'Anda tidak memiliki akses ke jawaban ini.');
        }
        
        // Check if submitted
        if (!in_array($jawaban->status, ['submitted', 'graded'])) {
            return redirect()->route('mahasiswa.nilai.index')
                ->with('error', 'Jawaban belum disubmit.');
        }
        
        $jawaban->load([
            'tugas.kelas.mataKuliah', 
            'penilaian',
            'jawabanSoal.soal',
            'jawabanSoal.penilaian'
        ]);
        
        return view('mahasiswa.nilai.show', compact('jawaban'));
    }
    
    /**
     * Get nilai per mata kuliah
     */
    public function perMataKuliah(Request $request)
    {
        $mahasiswa = auth()->user();
        
        $kelasIds = Enrollment::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'active')
            ->pluck('kelas_id');
        
        $nilaiPerMK = [];
        
        foreach ($kelasIds as $kelasId) {
            $kelas = Enrollment::where('mahasiswa_id', $mahasiswa->id)
                ->where('kelas_id', $kelasId)
                ->where('status', 'active')
                ->with('kelas.mataKuliah')
                ->first();
            
            $jawaban = JawabanMahasiswa::where('mahasiswa_id', $mahasiswa->id)
                ->whereHas('tugas', function($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                })
                ->where('status', 'graded')
                ->with(['tugas'])
                ->get();
            
            if ($jawaban->count() > 0) {
                $totalNilai = $jawaban->sum('nilai_akhir');
                
                $rataRata = $totalNilai / $jawaban->count();
                
                $nilaiPerMK[] = [
                    'kelas' => $kelas,
                    'total_tugas' => $jawaban->count(),
                    'rata_rata' => round($rataRata, 2),
                    'nilai_tertinggi' => $jawaban->max('nilai_akhir'),
                    'nilai_terendah' => $jawaban->min('nilai_akhir')
                ];
            }
        }
        
        return view('mahasiswa.nilai.per-kelas', compact('nilaiPerMK'));
    }
}
