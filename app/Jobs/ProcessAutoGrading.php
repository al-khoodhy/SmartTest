<?php

namespace App\Jobs;

use App\Models\JawabanMahasiswa;
use App\Services\AutoGradingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessAutoGrading implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jawabanId;

    // Jumlah maksimum percobaan ulang
    public $tries = 5;

    // Waktu tunggu (detik) sebelum retry
    public $backoff = 60; // 1 menit

    // Timeout total job
    public $timeout = 300; // 5 menit

    /**
     * Create a new job instance.
     */
    public function __construct($jawabanId)
    {
        $this->jawabanId = $jawabanId;
    }

    /**
     * Execute the job.
     */
    public function handle(AutoGradingService $autoGradingService): void
    {
        try {
            $jawaban = JawabanMahasiswa::find($this->jawabanId);
            
            if (!$jawaban) {
                Log::error("❌ Jawaban tidak ditemukan (ID: {$this->jawabanId})");
                return;
            }

            if ($jawaban->status !== 'submitted') {
                Log::warning("⚠️ Jawaban status bukan 'submitted' (ID: {$this->jawabanId})");
                return;
            }

            if ($jawaban->penilaian) {
                Log::info("ℹ️ Jawaban sudah pernah dinilai, lewati (ID: {$this->jawabanId})");
                return;
            }

            // Jalankan auto grading (batching + rate limiting di service)
            $autoGradingService->gradeJawaban($jawaban);

            Log::info("✅ Auto grading selesai untuk jawaban ID: {$this->jawabanId}");

        } catch (Exception $e) {

            // Jika error karena rate limit, tunda dan retry
            if (str_contains($e->getMessage(), 'Rate limit Gemini tercapai')) {
                Log::warning("🚦 Rate limit tercapai, menunda job (jawaban_id={$this->jawabanId}) selama {$this->backoff} detik...");
                $this->release($this->backoff);
                return;
            }

            Log::error("💥 Auto grading gagal untuk jawaban {$this->jawabanId}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Handle a job failure (setelah semua retry gagal).
     */
    public function failed(Exception $exception): void
    {
        Log::error("❌ Auto grading gagal permanen (ID: {$this->jawabanId}) — {$exception->getMessage()}");

        $jawaban = JawabanMahasiswa::find($this->jawabanId);
        if ($jawaban) {
            Log::error("🧑‍🏫 Jawaban ID {$jawaban->id} perlu penilaian manual oleh dosen.");
            // kamu bisa update status misalnya:
            // $jawaban->update(['status' => 'grading_failed']);
        }
    }
}
