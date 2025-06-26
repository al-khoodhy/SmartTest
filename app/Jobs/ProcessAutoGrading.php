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
    public $tries = 3;
    public $timeout = 300; // 5 minutes

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
                Log::error('Jawaban not found for auto grading: ' . $this->jawabanId);
                return;
            }
            
            if ($jawaban->status !== 'submitted') {
                Log::warning('Jawaban status is not submitted: ' . $this->jawabanId);
                return;
            }
            
            if ($jawaban->penilaian) {
                Log::warning('Jawaban already graded: ' . $this->jawabanId);
                return;
            }
            
            $penilaian = $autoGradingService->gradeJawaban($jawaban);
            
            Log::info('Auto grading job completed successfully for jawaban: ' . $this->jawabanId);
            
        } catch (Exception $e) {
            Log::error('Auto grading job failed for jawaban ' . $this->jawabanId . ': ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error('Auto grading job failed permanently for jawaban ' . $this->jawabanId . ': ' . $exception->getMessage());
        
        // Optionally, you can update the jawaban status or send notification
        $jawaban = JawabanMahasiswa::find($this->jawabanId);
        if ($jawaban) {
            // Could add a flag to indicate auto grading failed
            Log::error('Auto grading failed for jawaban ID: ' . $jawaban->id . ' - Manual grading required');
        }
    }
}
