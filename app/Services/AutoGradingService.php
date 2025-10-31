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
     * Grade jawaban mahasiswa secara otomatis (per-soal), lalu agregasi ke nilai akhir.
     * - Tidak mengubah nama fungsi / signature.
     * - Memastikan nilai ter-clamp ke [0, nilai_maksimal].
     * - Menandai status per-soal "ai_graded" dan total "graded" bila seluruh soal selesai.
     */
    public function gradeJawaban(JawabanMahasiswa $jawaban)
    {
        try {
            $tugas = $jawaban->tugas;

            // 1) Guard: pastikan tugas menggunakan auto grade
            if (!$tugas || !$tugas->auto_grade) {
                throw new Exception('Tugas ini tidak menggunakan auto grading');
            }

            // 2) Guard: hindari double grading di level jawaban utama
            if ($jawaban->penilaian) {
                throw new Exception('Jawaban sudah dinilai sebelumnya');
            }

            // 3) Muat relasi yang diperlukan
            $jawaban->load(['jawabanSoal.soal', 'jawabanSoal.penilaian', 'tugas.soal']);

            // 4) Grading per-soal
            foreach ($jawaban->jawabanSoal as $jawabanSoal) {
                // Skip jika sudah dinilai final
                if ($jawabanSoal->penilaian && $jawabanSoal->penilaian->status_penilaian === 'final') {
                    continue;
                }

                $soal = $jawabanSoal->soal;
                if (!$soal) {
                    Log::warning('Soal tidak ditemukan untuk jawaban_soal_id=' . $jawabanSoal->id);
                    continue;
                }

                // Panggil GeminiService dengan rubrik & kunci (bila ada)
                $result = $this->geminiService->gradeEssay(
                    $soal->pertanyaan,
                    $jawabanSoal->jawaban,
                    $tugas->rubrik_penilaian,
                    $tugas->nilai_maksimal,
                    $soal->kunci_jawaban // opsional; aman jika null
                );

                // Clamp nilai ke [0, nilai_maksimal]
                $nilai = (float) $result['nilai'];
                if ($nilai < 0) $nilai = 0.0;
                if ($nilai > (float) $tugas->nilai_maksimal) {
                    Log::warning("Nilai AI melebihi nilai maksimal, dibatasi ke: {$tugas->nilai_maksimal}");
                    $nilai = (float) $tugas->nilai_maksimal;
                }

                // Simpan / perbarui penilaian per-soal
                PenilaianSoal::updateOrCreate(
                    ['jawaban_soal_id' => $jawabanSoal->id],
                    [
                        'nilai_ai'         => $nilai,
                        'nilai_final'      => $nilai, // saat ini nilai AI menjadi final sampai ada override manual
                        'feedback_ai'      => $result['feedback'] ?? '',
                        'status_penilaian' => 'ai_graded',
                        'graded_at'        => now(),
                    ]
                );
            }

            // 5) Hitung nilai akhir berdasarkan agregasi PenilaianSoal (aksesornya di model)
            $jawaban->refresh();
            $nilaiAkhir = (float) $jawaban->nilai_akhir;

            // 6) Buat arsip penilaian utama
            Penilaian::create([
                'jawaban_id'       => $jawaban->id,
                'nilai_ai'         => $nilaiAkhir,
                'nilai_final'      => $nilaiAkhir,
                'feedback_ai'      => 'Nilai dihitung otomatis dari penilaian per soal dengan AI',
                'status_penilaian' => 'ai_graded',
                'graded_at'        => now(),
            ]);

            // 7) Tandai status jawaban jika seluruh soal sudah dinilai (AI/final)
            $allGraded = $jawaban->jawabanSoal->every(function ($js) {
                $status = optional($js->penilaian)->status_penilaian;
                return $status === 'ai_graded' || $status === 'final';
            });

            if ($allGraded) {
                $jawaban->update(['status' => 'graded']);
            }

            Log::info('Auto grading per-soal selesai. jawaban_id=' . $jawaban->id . ' final_score=' . $nilaiAkhir);
            return true;
        } catch (Exception $e) {
            Log::error('Auto grading per-soal gagal. jawaban_id=' . ($jawaban->id ?? 'null') . ' error=' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Grade semua jawaban yang belum dinilai untuk tugas tertentu.
     * - Tidak asumsi nilai balik dari gradeJawaban() (karena memang boolean).
     * - Mengambil nilai akhir dari accessor setelah refresh().
     */
    public function gradeAllPendingForTugas(Tugas $tugas)
    {
        $pendingJawaban = JawabanMahasiswa::where('tugas_id', $tugas->id)
            ->where('status', 'submitted')
            ->whereDoesntHave('penilaian')
            ->get();

        $results = [];
        $errors  = [];

        foreach ($pendingJawaban as $jawaban) {
            try {
                // Jalankan grading
                $this->gradeJawaban($jawaban);

                // Ambil nilai akhir aktual setelah tersimpan
                $jawaban->refresh();
                $nilaiAkhir = (float) $jawaban->nilai_akhir;

                $results[] = [
                    'jawaban_id' => $jawaban->id,
                    'mahasiswa'  => $jawaban->mahasiswa->name ?? '-',
                    'nilai'      => $nilaiAkhir,
                    'status'     => 'success',
                ];
            } catch (Exception $e) {
                $errors[] = [
                    'jawaban_id' => $jawaban->id,
                    'mahasiswa'  => $jawaban->mahasiswa->name ?? '-',
                    'error'      => $e->getMessage(),
                    'status'     => 'error',
                ];
            }
        }

        return [
            'success'         => $results,
            'errors'          => $errors,
            'total_processed' => count($results) + count($errors),
            'success_count'   => count($results),
            'error_count'     => count($errors),
        ];
    }

    /**
     * Re-grade jawaban dengan AI (per-soal), lalu perbarui agregasi.
     * - Mempertahankan nilai_manual bila sudah ada (final tetap manual).
     */
    public function regradeJawaban(JawabanMahasiswa $jawaban)
    {
        try {
            $tugas     = $jawaban->tugas;
            $penilaian = $jawaban->penilaian;

            if (!$penilaian) {
                throw new Exception('Jawaban belum pernah dinilai');
            }

            // Muat relasi yang diperlukan
            $jawaban->load(['jawabanSoal.soal', 'jawabanSoal.penilaian']);

            foreach ($jawaban->jawabanSoal as $jawabanSoal) {
                $soal = $jawabanSoal->soal;
                if (!$soal) {
                    Log::warning('Soal tidak ditemukan saat re-grade untuk jawaban_soal_id=' . $jawabanSoal->id);
                    continue;
                }

                $result = $this->geminiService->gradeEssay(
                    $soal->pertanyaan,
                    $jawabanSoal->jawaban,
                    $tugas->rubrik_penilaian,
                    $tugas->nilai_maksimal,
                    $soal->kunci_jawaban // gunakan kunci jika ada agar konsisten
                );

                // Clamp nilai
                $nilai = (float) $result['nilai'];
                if ($nilai < 0) $nilai = 0.0;
                if ($nilai > (float) $tugas->nilai_maksimal) {
                    $nilai = (float) $tugas->nilai_maksimal;
                }

                // Update penilaian per-soal
                $penilaianSoal = $jawabanSoal->penilaian;
                if ($penilaianSoal) {
                    $penilaianSoal->update([
                        'nilai_ai'   => $nilai,
                        'feedback_ai'=> $result['feedback'] ?? '',
                        'graded_at'  => now(),
                    ]);

                    // Jika belum ada nilai manual (override), sinkronkan nilai_final ke nilai AI
                    if (is_null($penilaianSoal->nilai_manual)) {
                        $penilaianSoal->update(['nilai_final' => $nilai]);
                    }
                } else {
                    // Jika belum ada record penilaianSoal, buat baru
                    PenilaianSoal::create([
                        'jawaban_soal_id'  => $jawabanSoal->id,
                        'nilai_ai'         => $nilai,
                        'nilai_final'      => $nilai,
                        'feedback_ai'      => $result['feedback'] ?? '',
                        'status_penilaian' => 'ai_graded',
                        'graded_at'        => now(),
                    ]);
                }
            }

            // Hitung ulang nilai akhir
            $jawaban->refresh();
            $nilaiAkhir = (float) $jawaban->nilai_akhir;

            // Perbarui penilaian utama (arsip)
            $penilaian->update([
                'nilai_ai'    => $nilaiAkhir,
                'feedback_ai' => 'Nilai dihitung ulang otomatis dari penilaian per-soal dengan AI',
                'graded_at'   => now(),
            ]);

            // Jika belum ada nilai_manual di penilaian utama, sinkronkan nilai_final ke nilaiAkhir
            if (is_null($penilaian->nilai_manual)) {
                $penilaian->update(['nilai_final' => $nilaiAkhir]);
            }

            Log::info('Re-grading selesai. jawaban_id=' . $jawaban->id . ' final_score=' . $nilaiAkhir);

            return $penilaian;
        } catch (Exception $e) {
            Log::error('Re-grading gagal. jawaban_id=' . ($jawaban->id ?? 'null') . ' error=' . $e->getMessage());
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
