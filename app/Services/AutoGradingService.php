<?php

namespace App\Services;

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\Tugas;
use App\Models\JawabanSoalMahasiswa;
use App\Models\PenilaianSoal;
use Illuminate\Support\Facades\Log;
use Exception;

class AutoGradingService
{
    private $geminiService;
    
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }
    
    /**
     * Grade jawaban mahasiswa secara otomatis
     */
    public function gradeJawaban(JawabanMahasiswa $jawaban)
    {
        try {
            $tugas = $jawaban->tugas;
            
            // Check apakah tugas menggunakan auto grade
            if (!$tugas->auto_grade) {
                throw new Exception('Tugas ini tidak menggunakan auto grading');
            }
            
            // Check apakah jawaban sudah dinilai
            if ($jawaban->penilaian) {
                throw new Exception('Jawaban sudah dinilai sebelumnya');
            }
            
            $jawaban->load(['jawabanSoal', 'tugas.soal']);
            foreach ($jawaban->jawabanSoal as $jawabanSoal) {
                // Skip jika sudah dinilai
                if ($jawabanSoal->penilaian && $jawabanSoal->penilaian->status_penilaian === 'final') continue;
                $soal = $jawabanSoal->soal;
                $result = $this->geminiService->gradeEssay(
                    $soal->pertanyaan,
                    $jawabanSoal->jawaban,
                    $tugas->rubrik_penilaian,
                    $tugas->nilai_maksimal
                );
                PenilaianSoal::updateOrCreate(
                    ['jawaban_soal_id' => $jawabanSoal->id],
                    [
                        'nilai_ai' => $result['nilai'],
                        'nilai_final' => $result['nilai'],
                        'feedback_ai' => $result['feedback'],
                        'status_penilaian' => 'ai_graded',
                        'graded_at' => now(),
                    ]
                );
            }
            // Update status jawaban jika semua soal sudah dinilai
            $allGraded = $jawaban->jawabanSoal->every(function($js) { return optional($js->penilaian)->status_penilaian === 'ai_graded' || optional($js->penilaian)->status_penilaian === 'final'; });
            if ($allGraded) {
                $jawaban->update(['status' => 'graded']);
            }
            Log::info('Auto grading per soal completed for jawaban ID: ' . $jawaban->id);
            return true;
        } catch (Exception $e) {
            Log::error('Auto grading per soal failed for jawaban ID ' . $jawaban->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Grade semua jawaban yang belum dinilai untuk tugas tertentu
     */
    public function gradeAllPendingForTugas(Tugas $tugas)
    {
        $pendingJawaban = JawabanMahasiswa::where('tugas_id', $tugas->id)
            ->where('status', 'submitted')
            ->whereDoesntHave('penilaian')
            ->get();
        
        $results = [];
        $errors = [];
        
        foreach ($pendingJawaban as $jawaban) {
            try {
                $penilaian = $this->gradeJawaban($jawaban);
                $results[] = [
                    'jawaban_id' => $jawaban->id,
                    'mahasiswa' => $jawaban->mahasiswa->name,
                    'nilai' => $penilaian->nilai_ai,
                    'status' => 'success'
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'jawaban_id' => $jawaban->id,
                    'mahasiswa' => $jawaban->mahasiswa->name,
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }
        
        return [
            'success' => $results,
            'errors' => $errors,
            'total_processed' => count($results) + count($errors),
            'success_count' => count($results),
            'error_count' => count($errors)
        ];
    }
    
    /**
     * Re-grade jawaban yang sudah dinilai AI
     */
    public function regradeJawaban(JawabanMahasiswa $jawaban)
    {
        try {
            $tugas = $jawaban->tugas;
            $penilaian = $jawaban->penilaian;
            
            if (!$penilaian) {
                throw new Exception('Jawaban belum pernah dinilai');
            }
            
            // Grade ulang menggunakan Gemini
            $result = $this->geminiService->gradeEssay(
                $tugas->soal_esai,
                $jawaban->jawaban,
                $tugas->rubrik_penilaian,
                $tugas->nilai_maksimal
            );
            
            // Update penilaian
            $penilaian->update([
                'nilai_ai' => $result['nilai'],
                'feedback_ai' => $result['feedback'],
                'detail_penilaian_ai' => $result['detail'],
                'graded_at' => now(),
            ]);
            
            // Update nilai final jika belum ada review manual
            if (!$penilaian->nilai_manual) {
                $penilaian->update(['nilai_final' => $result['nilai']]);
            }
            
            Log::info('Re-grading completed for jawaban ID: ' . $jawaban->id);
            
            return $penilaian;
            
        } catch (Exception $e) {
            Log::error('Re-grading failed for jawaban ID ' . $jawaban->id . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Test Gemini API connection
     */
    public function testGeminiConnection()
    {
        return $this->geminiService->testConnection();
    }
}

