<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\JawabanMahasiswa;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:3']);
    }
    
    /**
     * Display kumpulan nilai mahasiswa
     */
    public function index(Request $request)
    {
        $mahasiswa = auth()->user();
        
        // Get mata kuliah yang diambil mahasiswa
        $kelasIds = $mahasiswa->enrollments()->active()->pluck('kelas_id');
        
        $query = $mahasiswa->jawabanMahasiswa()
            ->with(['tugas.kelas.mataKuliah', 'penilaian'])
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
                    $query->whereHas('penilaian');
                    break;
                case 'pending':
                    $query->whereDoesntHave('penilaian');
                    break;
            }
        }
        
        $jawaban = $query->latest()->paginate(10);
        
        // Get mata kuliah untuk filter
        $kelas = $mahasiswa->enrollments()->active()->get();
        
        // Statistik nilai
        $totalTugas = $mahasiswa->jawabanMahasiswa()
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->whereIn('status', ['submitted', 'graded'])
            ->count();
        
        $sudahDinilai = $mahasiswa->jawabanMahasiswa()
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->whereHas('penilaian')
            ->count();
        
        $menungguPenilaian = $totalTugas - $sudahDinilai;
        
        // Rata-rata nilai
        $rataRataNilai = $mahasiswa->jawabanMahasiswa()
            ->whereHas('tugas', function($q) use ($kelasIds) {
                $q->whereIn('kelas_id', $kelasIds);
            })
            ->whereHas('penilaian')
            ->join('penilaian', 'jawaban_mahasiswa.id', '=', 'penilaian.jawaban_id')
            ->avg('penilaian.nilai_final');
        
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
        
        $jawaban->load(['tugas.kelas.mataKuliah', 'penilaian']);
        
        return view('mahasiswa.nilai.show', compact('jawaban'));
    }
    
    /**
     * Get nilai per mata kuliah
     */
    public function perMataKuliah(Request $request)
    {
        $mahasiswa = auth()->user();
        
        $kelasIds = $mahasiswa->enrollments()->active()->pluck('kelas_id');
        
        $nilaiPerMK = [];
        
        foreach ($kelasIds as $kelasId) {
            $kelas = $mahasiswa->enrollments()->find($kelasId);
            
            $jawaban = $mahasiswa->jawabanMahasiswa()
                ->whereHas('tugas', function($q) use ($kelasId) {
                    $q->where('kelas_id', $kelasId);
                })
                ->whereHas('penilaian')
                ->with(['tugas', 'penilaian'])
                ->get();
            
            if ($jawaban->count() > 0) {
                $totalNilai = $jawaban->sum(function($j) {
                    return $j->penilaian->nilai_final;
                });
                
                $rataRata = $totalNilai / $jawaban->count();
                
                $nilaiPerMK[] = [
                    'kelas' => $kelas,
                    'total_tugas' => $jawaban->count(),
                    'rata_rata' => round($rataRata, 2),
                    'nilai_tertinggi' => $jawaban->max(function($j) {
                        return $j->penilaian->nilai_final;
                    }),
                    'nilai_terendah' => $jawaban->min(function($j) {
                        return $j->penilaian->nilai_final;
                    })
                ];
            }
        }
        
        return view('mahasiswa.nilai.per-kelas', compact('nilaiPerMK'));
    }
}
