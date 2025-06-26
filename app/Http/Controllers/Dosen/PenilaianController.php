<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Tugas;
use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Services\AutoGradingService;
use App\Jobs\ProcessAutoGrading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PenilaianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:dosen']);
    }
    
    /**
     * Display penilaian dashboard
     */
    public function index(Request $request)
    {
        $dosen = auth()->user();
        // Get tugas yang dibuat dosen (melalui mata kuliah)
        $tugasQuery = \App\Models\Tugas::whereHas('mataKuliah', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->with('mataKuliah');
        if ($request->mata_kuliah_id) {
            $tugasQuery->where('mata_kuliah_id', $request->mata_kuliah_id);
        }
        $tugas = $tugasQuery->latest()->paginate(10);
        // Get mata kuliah untuk filter dropdown
        $mataKuliah = \App\Models\MataKuliah::whereHas('dosen', function($q) use ($dosen) {
            $q->where('users.id', $dosen->id);
        })->where('is_active', true)->get();
        // Statistik penilaian
        $totalJawaban = \App\Models\JawabanMahasiswa::whereHas('tugas', function($query) use ($dosen) {
            $query->whereHas('mataKuliah', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            });
        })->where('status', 'submitted')->count();
        $sudahDinilai = \App\Models\JawabanMahasiswa::whereHas('tugas', function($query) use ($dosen) {
            $query->whereHas('mataKuliah', function($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            });
        })->where('status', 'graded')->count();
        $menungguPenilaian = $totalJawaban - $sudahDinilai;
        return view('dosen.penilaian.index', compact('tugas', 'mataKuliah', 'totalJawaban', 'sudahDinilai', 'menungguPenilaian'));
    }
    
    /**
     * Show jawaban untuk tugas tertentu
     */
    public function showTugas(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $jawaban = $tugas->jawabanMahasiswa()
            ->with(['mahasiswa', 'penilaian'])
            ->where('status', '!=', 'draft')
            ->latest()
            ->paginate(15);
        
        return view('dosen.penilaian.tugas', compact('tugas', 'jawaban'));
    }
    
    /**
     * Show detail jawaban mahasiswa
     */
    public function showJawaban(JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        
        $jawaban->load(['tugas', 'mahasiswa', 'penilaian']);
        
        return view('dosen.penilaian.jawaban', compact('jawaban'));
    }
    
    /**
     * Manual grading form
     */
    public function grade(JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        $jawaban->load(['tugas', 'mahasiswa', 'jawabanSoal.soal', 'jawabanSoal.penilaian']);
        return view('dosen.penilaian.grade', compact('jawaban'));
    }
    
    /**
     * Store manual grading
     */
    public function storeGrade(Request $request, JawabanMahasiswa $jawaban)
    {
        $this->authorize('view', $jawaban->tugas);
        $jawaban->load(['jawabanSoal.soal', 'jawabanSoal.penilaian']);
        $rules = [];
        foreach ($jawaban->jawabanSoal as $jawabanSoal) {
            $rules['nilai_manual.' . $jawabanSoal->id] = 'required|numeric|min:0|max:' . $jawaban->tugas->nilai_maksimal;
            $rules['feedback_manual.' . $jawabanSoal->id] = 'required|string|min:5';
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        foreach ($jawaban->jawabanSoal as $jawabanSoal) {
            $nilai = $request->input('nilai_manual.' . $jawabanSoal->id);
            $feedback = $request->input('feedback_manual.' . $jawabanSoal->id);
            $penilaian = $jawabanSoal->penilaian;
            if (!$penilaian) {
                $penilaian = \App\Models\PenilaianSoal::create([
                    'jawaban_soal_id' => $jawabanSoal->id,
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai,
                    'feedback_manual' => $feedback,
                    'status_penilaian' => 'final',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            } else {
                $penilaian->update([
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai,
                    'feedback_manual' => $feedback,
                    'status_penilaian' => 'final',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            }
        }
        // Update status jawaban jika semua soal sudah dinilai
        $allGraded = $jawaban->jawabanSoal->every(function($js) { return optional($js->penilaian)->status_penilaian === 'final'; });
        if ($allGraded) {
            $jawaban->update(['status' => 'graded']);
        }
        return redirect()->route('dosen.penilaian.tugas', $jawaban->tugas)
            ->with('success', 'Penilaian berhasil disimpan.');
    }
    
    /**
     * Trigger auto grading untuk tugas
     */
    public function autoGrade(Tugas $tugas, AutoGradingService $autoGradingService)
    {
        $this->authorize('view', $tugas);
        
        if (!$tugas->auto_grade) {
            return redirect()->back()
                ->with('error', 'Tugas ini tidak menggunakan auto grading.');
        }
        
        try {
            $result = $autoGradingService->gradeAllPendingForTugas($tugas);
            
            $message = "Auto grading selesai. ";
            $message .= "Berhasil: {$result['success_count']}, ";
            $message .= "Gagal: {$result['error_count']}";
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menjalankan auto grading: ' . $e->getMessage());
        }
    }
    
    /**
     * Re-grade jawaban dengan AI
     */
    public function regrade(JawabanMahasiswa $jawaban, AutoGradingService $autoGradingService)
    {
        $this->authorize('view', $jawaban->tugas);
        
        if (!$jawaban->tugas->auto_grade) {
            return redirect()->back()
                ->with('error', 'Tugas ini tidak menggunakan auto grading.');
        }
        
        try {
            $autoGradingService->regradeJawaban($jawaban);
            
            return redirect()->back()
                ->with('success', 'Jawaban berhasil dinilai ulang dengan AI.');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menilai ulang: ' . $e->getMessage());
        }
    }
    
    /**
     * Export nilai tugas
     */
    public function exportNilai(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $jawaban = $tugas->jawabanMahasiswa()
            ->with(['mahasiswa', 'penilaian'])
            ->where('status', 'graded')
            ->get();
        
        $filename = 'nilai_' . str_replace(' ', '_', $tugas->judul) . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($jawaban) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, ['NIM', 'Nama Mahasiswa', 'Nilai AI', 'Nilai Manual', 'Nilai Final', 'Status', 'Tanggal Submit']);
            
            foreach ($jawaban as $j) {
                fputcsv($file, [
                    $j->mahasiswa->nim_nip,
                    $j->mahasiswa->name,
                    $j->penilaian->nilai_ai ?? '-',
                    $j->penilaian->nilai_manual ?? '-',
                    $j->penilaian->nilai_final ?? '-',
                    $j->penilaian->status_penilaian,
                    $j->waktu_selesai ? $j->waktu_selesai->format('Y-m-d H:i:s') : '-'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
