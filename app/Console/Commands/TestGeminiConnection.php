<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;

class TestGeminiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test connection to Gemini API';

    /**
     * Execute the console command.
     */
    public function handle(GeminiService $geminiService)
    {
        $this->info('Testing Gemini API connection...');
        
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            $this->error('Gemini API key not configured. Please set GEMINI_API_KEY in your .env file.');
            return 1;
        }
        
        try {
            $isConnected = $geminiService->testConnection();
            
            if ($isConnected) {
                $this->info('✅ Gemini API connection successful!');
                
                // Test grading functionality
                $this->info('Testing essay grading functionality...');
                
                $testResult = $geminiService->gradeEssay(
                    'Jelaskan pentingnya pendidikan dalam pembangunan bangsa.',
                    'Pendidikan sangat penting untuk pembangunan bangsa karena dapat meningkatkan kualitas sumber daya manusia, menciptakan inovasi, dan membangun karakter bangsa yang baik.',
                    'Penilaian berdasarkan: 1) Pemahaman konsep (40%), 2) Analisis (30%), 3) Struktur penulisan (30%)',
                    100
                );
                
                $this->info('✅ Essay grading test successful!');
                $this->line('Sample result:');
                $this->line('Nilai: ' . $testResult['nilai']);
                $this->line('Feedback: ' . substr($testResult['feedback'], 0, 100) . '...');
                
                return 0;
                
            } else {
                $this->error('❌ Gemini API connection failed!');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error testing Gemini API: ' . $e->getMessage());
            return 1;
        }
    }
}
