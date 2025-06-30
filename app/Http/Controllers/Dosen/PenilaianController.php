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
        $this->middleware(['auth', 'role:2']);
    }
    
    /**
     * Display penilaian dashboard
     */
    public function index(Request $request)
    {
        $dosen = auth()->user();
        
        $tugasQuery = Tugas::where('dosen_id', $dosen->id)
            ->with(['kelas.mataKuliah', 'jawabanMahasiswa.penilaian']);
        if ($request->kelas_id) {
            $tugasQuery->where('kelas_id', $request->kelas_id);
        }
        $tugas = $tugasQuery->latest()->paginate(10);
        $kelas = $dosen->kelasAsDosen()->with('mataKuliah')->get();
        $totalJawaban = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'submitted')->count();
        $sudahDinilai = JawabanMahasiswa::whereHas('tugas', function($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id);
        })->where('status', 'graded')->count();
        $menungguPenilaian = $totalJawaban - $sudahDinilai;
        return view('dosen.penilaian.index', compact('tugas', 'kelas', 'totalJawaban', 'sudahDinilai', 'menungguPenilaian'));
    }
    
    /**
     * Show jawaban untuk tugas tertentu
     */
    public function showTugas(Tugas $tugas)
    {
        $this->authorize('view', $tugas);
        
        $jawaban = $tugas->jawabanMahasiswa()
            ->with(['mahasiswa', 'penilaian', 'jawabanSoal.soal', 'jawabanSoal.penilaian'])
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
            $rules['nilai_manual.' . $jawabanSoal->id] = 'nullable|numeric|max:' . $jawaban->tugas->nilai_maksimal;
            $rules['feedback_manual.' . $jawabanSoal->id] = 'nullable|string';
        }
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // Update penilaian per soal
        $hasManualGrade = false;
        foreach ($jawaban->jawabanSoal as $jawabanSoal) {
            $nilai = $request->input('nilai_manual.' . $jawabanSoal->id);
            $feedback = $request->input('feedback_manual.' . $jawabanSoal->id);
            
            // Jika nilai kosong, set ke null
            if ($nilai === null || $nilai === '') {
                $nilai = null;
            } else {
                // Pastikan nilai tidak melebihi nilai maksimal
                $nilai = min($nilai, $jawaban->tugas->nilai_maksimal);
                $hasManualGrade = true; // Ada nilai manual yang diinput
            }
            
            // Jika feedback kosong, set ke null
            if ($feedback === null || $feedback === '') {
                $feedback = null;
            }
            
            $penilaian = $jawabanSoal->penilaian;
            if (!$penilaian) {
                $penilaian = \App\Models\PenilaianSoal::create([
                    'jawaban_soal_id' => $jawabanSoal->id,
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai, // Nilai manual menjadi nilai final
                    'feedback_manual' => $feedback,
                    'status_penilaian' => $nilai !== null ? 'final' : 'pending',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            } else {
                $penilaian->update([
                    'nilai_manual' => $nilai,
                    'nilai_final' => $nilai, // Nilai manual menjadi nilai final
                    'feedback_manual' => $feedback,
                    'status_penilaian' => $nilai !== null ? 'final' : 'pending',
                    'graded_by' => auth()->id(),
                    'graded_at' => now(),
                ]);
            }
        }
        
        // Hitung nilai akhir berdasarkan PenilaianSoal
        $nilaiAkhir = $jawaban->nilai_akhir;
        
        // Update atau buat Penilaian utama sebagai backup/arsip
        $penilaianUtama = $jawaban->penilaian;
        if (!$penilaianUtama) {
            $penilaianUtama = \App\Models\Penilaian::create([
                'jawaban_id' => $jawaban->id,
                'nilai_manual' => $nilaiAkhir,
                'nilai_final' => $nilaiAkhir,
                'feedback_manual' => 'Nilai dihitung otomatis dari penilaian per soal',
                'status_penilaian' => 'final',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        } else {
            $penilaianUtama->update([
                'nilai_manual' => $nilaiAkhir,
                'nilai_final' => $nilaiAkhir,
                'feedback_manual' => 'Nilai dihitung otomatis dari penilaian per soal',
                'status_penilaian' => 'final',
                'graded_by' => auth()->id(),
                'graded_at' => now(),
            ]);
        }
        
        // Update status jawaban menjadi graded jika semua soal sudah dinilai (manual/AI)
        $isAllGraded = $jawaban->jawabanSoal->every(function($js) {
            $penilaian = $js->penilaian;
            return $penilaian && in_array($penilaian->status_penilaian, ['final', 'ai_graded']);
        });
        if ($isAllGraded && $jawaban->status !== 'graded') {
            $jawaban->update(['status' => 'graded']);
        }
        
        return redirect()->route('dosen.penilaian.tugas', $jawaban->tugas)
            ->with('success', 'Penilaian berhasil disimpan. Nilai akhir: ' . $nilaiAkhir);
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
            fputcsv($file, ['Nama Mahasiswa', 'Nilai AI', 'Nilai Manual', 'Nilai Final', 'Status', 'Tanggal Submit']);
            
            foreach ($jawaban as $j) {
                fputcsv($file, [
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
