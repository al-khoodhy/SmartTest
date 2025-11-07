<?php

namespace App\Services;

use App\Models\JawabanMahasiswa;
use App\Models\Penilaian;
use App\Models\Tugas;
use App\Models\JawabanSoalMahasiswa;
use App\Models\PenilaianSoal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Exception;
use App\Jobs\AutoGradeJawabanJob;

class AutoGradingService
{
    private $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Wrapper rate limiting untuk panggilan ke Gemini
     * - Maksimal 15 request per 60 detik
     * - Jika limit tercapai: lempar exception supaya Queue bisa retry/delay
     */
    private function callGeminiWithRateLimit(callable $callback)
    {
        $key         = 'gemini:grading';
        $maxAttempts = 15;
        $decay       = 60; // detik

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // Biarkan Job me-release / retry
            throw new Exception('Rate limit Gemini tercapai, tunda dan coba lagi.');
        }

        RateLimiter::hit($key, $decay);

        return $callback();
    }

    /**
     * Grade jawaban mahasiswa (BATCH per mahasiswa)
     * - Tidak mengubah signature.
     * - Menggunakan 1 request Gemini untuk banyak soal.
     */
    public function gradeJawaban(JawabanMahasiswa $jawaban)
    {
        try {
            $tugas = $jawaban->tugas;

            if (!$tugas || !$tugas->auto_grade) {
                throw new Exception('Tugas ini tidak menggunakan auto grading');
            }

            if ($jawaban->penilaian) {
                throw new Exception('Jawaban sudah dinilai sebelumnya');
            }

            $jawaban->load(['jawabanSoal.soal', 'jawabanSoal.penilaian', 'tugas.soal']);

            // Siapkan batch items
            $items = [];
            foreach ($jawaban->jawabanSoal as $jawabanSoal) {
                $soal = $jawabanSoal->soal;
                if (!$soal) {
                    Log::warning('Soal tidak ditemukan untuk jawaban_soal_id=' . $jawabanSoal->id);
                    continue;
                }

                // Skip jika sudah final manual
                if ($jawabanSoal->penilaian && $jawabanSoal->penilaian->status_penilaian === 'final') {
                    continue;
                }

                $items[] = [
                    'soal_id'       => $soal->id,
                    'pertanyaan'    => $soal->pertanyaan,
                    'jawaban'       => $jawabanSoal->jawaban ?? '',
                    'kunci_jawaban' => $soal->kunci_jawaban ?? null,
                ];
            }

            if (empty($items)) {
                throw new Exception('Tidak ada soal yang perlu dinilai untuk jawaban ini.');
            }

            // Panggil Gemini (1 request untuk semua soal pada jawaban ini)
            $results = $this->callGeminiWithRateLimit(function () use ($items, $tugas) {
                return $this->geminiService->gradeMultiEssay(
                    $items,
                    $tugas->rubrik_penilaian,
                    $tugas->nilai_maksimal
                );
            });

            // Simpan hasil per-soal
            foreach ($jawaban->jawabanSoal as $jawabanSoal) {
                $soal = $jawabanSoal->soal;
                if (!$soal) {
                    continue;
                }

                // Jika sudah final manual, jangan sentuh
                if ($jawabanSoal->penilaian && $jawabanSoal->penilaian->status_penilaian === 'final') {
                    continue;
                }

                $soalId = $soal->id;

                if (!isset($results[$soalId])) {
                    Log::warning("Tidak ada hasil AI untuk soal_id={$soalId}, jawaban_soal_id={$jawabanSoal->id}");
                    continue;
                }

                $res   = $results[$soalId];
                $nilai = (float)$res['nilai'];

                if ($nilai < 0) $nilai = 0.0;
                if ($nilai > (float)$tugas->nilai_maksimal) {
                    Log::warning("Nilai AI melebihi nilai maksimal, dibatasi ke: {$tugas->nilai_maksimal}");
                    $nilai = (float)$tugas->nilai_maksimal;
                }

                PenilaianSoal::updateOrCreate(
                    ['jawaban_soal_id' => $jawabanSoal->id],
                    [
                        'nilai_ai'         => $nilai,
                        'nilai_final'      => $nilai,
                        'feedback_ai'      => $res['feedback'] ?? '',
                        'status_penilaian' => 'ai_graded',
                        'graded_at'        => now(),
                    ]
                );                
            }

            // Hitung nilai akhir (via accessor di model)
            $jawaban->refresh();
            $nilaiAkhir = (float)$jawaban->nilai_akhir;

            // Buat penilaian utama
            Penilaian::create([
                'jawaban_id'       => $jawaban->id,
                'nilai_ai'         => $nilaiAkhir,
                'nilai_final'      => $nilaiAkhir,
                'feedback_ai'      => 'Nilai dihitung otomatis (batched) dari penilaian per soal dengan AI',
                'status_penilaian' => 'ai_graded',
                'graded_at'        => now(),
            ]);

            // Cek semua soal graded
            $allGraded = $jawaban->jawabanSoal->every(function ($js) {
                $status = optional($js->penilaian)->status_penilaian;
                return in_array($status, ['ai_graded', 'final']);
            });

            if ($allGraded) {
                $jawaban->update(['status' => 'graded']);
            }

            Log::info('Auto grading (batch) selesai. jawaban_id=' . $jawaban->id . ' final_score=' . $nilaiAkhir);

            return true;
        } catch (Exception $e) {
            Log::error('Auto grading (batch) gagal. jawaban_id=' . ($jawaban->id ?? 'null') . ' error=' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Grade semua jawaban pending untuk satu tugas via QUEUE
     * - Tidak eksekusi langsung semuanya
     * - Mengantrikan ke Job agar aman terhadap rate limit
     */
    public function gradeAllPendingForTugas(Tugas $tugas)
    {
        $pendingJawaban = JawabanMahasiswa::where('tugas_id', $tugas->id)
            ->where('status', 'submitted')
            ->whereDoesntHave('penilaian')
            ->get();

        foreach ($pendingJawaban as $jawaban) {
            AutoGradeJawabanJob::dispatch($jawaban->id)->onQueue('grading');
        }

        return [
            'queued' => $pendingJawaban->count(),
        ];
    }

    /**
     * Re-grade masih bisa pakai pola lama (per-soal) atau di-upgrade ke batch serupa.
     * Untuk singkatnya, biarkan seperti versi awalmu atau nanti kita refactor.
     */
    public function regradeJawaban(JawabanMahasiswa $jawaban)
    {
        // (opsional: refactor ke batch juga dengan pola serupa)
        // Untuk sekarang bisa dibiarkan seperti implementasi lama.
        // ...
    }

    public function testGeminiConnection()
    {
        return $this->geminiService->testConnection();
    }
}
