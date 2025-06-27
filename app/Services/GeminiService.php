<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    private $apiKey;
    private $apiUrl;
    
    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->apiUrl = config('services.gemini.api_url', env('GEMINI_API_URL'));
    }
    
    /**
     * Grade essay menggunakan Gemini AI
     */
    public function gradeEssay($soal, $jawaban, $rubrikPenilaian = null, $nilaiMaksimal = 100)
    {
        try {
            $prompt = $this->buildGradingPrompt($soal, $jawaban, $rubrikPenilaian, $nilaiMaksimal);
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => $prompt
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'topK' => 1,
                    'topP' => 1,
                    'maxOutputTokens' => 2048,
                ]
            ]);
            
            if ($response->successful()) {
                $result = $response->json();
                return $this->parseGradingResponse($result);
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                throw new Exception('Gagal menghubungi Gemini API: ' . $response->status());
            }
            
        } catch (Exception $e) {
            Log::error('Error grading essay: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Build prompt untuk grading essay
     */
    private function buildGradingPrompt($soal, $jawaban, $rubrikPenilaian, $nilaiMaksimal)
    {
        $prompt = "Anda adalah seorang dosen yang ahli dalam menilai esai mahasiswa. Tugas Anda adalah menilai jawaban esai berikut dengan objektif dan memberikan feedback yang konstruktif.\n\n";
        
        $prompt .= "SOAL ESAI:\n";
        $prompt .= $soal . "\n\n";
        
        $prompt .= "JAWABAN MAHASISWA:\n";
        $prompt .= $jawaban . "\n\n";
        
        if ($rubrikPenilaian) {
            $prompt .= "RUBRIK PENILAIAN:\n";
            $prompt .= $rubrikPenilaian . "\n\n";
        }
        
        $prompt .= "INSTRUKSI PENILAIAN:\n";
        $prompt .= "1. Nilai jawaban dari 0 sampai {$nilaiMaksimal}\n";
        $prompt .= "2. Berikan feedback yang detail dan konstruktif\n";
        $prompt .= "3. Jelaskan kelebihan dan kekurangan jawaban\n";
        $prompt .= "4. Berikan saran untuk perbaikan\n\n";
        
        $prompt .= "FORMAT RESPONSE (WAJIB IKUTI FORMAT INI):\n";
        $prompt .= "NILAI: [angka dari 0-{$nilaiMaksimal}]\n";
        $prompt .= "FEEDBACK: [feedback detail]\n";
        $prompt .= "KELEBIHAN: [poin-poin kelebihan]\n";
        $prompt .= "KEKURANGAN: [poin-poin kekurangan]\n";
        $prompt .= "SARAN: [saran perbaikan]\n";
        
        return $prompt;
    }
    
    /**
     * Parse response dari Gemini API
     */
    private function parseGradingResponse($response)
    {
        try {
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Parse nilai
            preg_match('/NILAI:\s*(\d+(?:\.\d+)?)/i', $text, $nilaiMatches);
            $nilai = isset($nilaiMatches[1]) ? (float) $nilaiMatches[1] : 0;
            
            // Parse feedback
            preg_match('/FEEDBACK:\s*(.*?)(?=KELEBIHAN:|$)/is', $text, $feedbackMatches);
            $feedback = isset($feedbackMatches[1]) ? trim($feedbackMatches[1]) : '';
            
            // Parse kelebihan
            preg_match('/KELEBIHAN:\s*(.*?)(?=KEKURANGAN:|$)/is', $text, $kelebihanMatches);
            $kelebihan = isset($kelebihanMatches[1]) ? trim($kelebihanMatches[1]) : '';
            
            // Parse kekurangan
            preg_match('/KEKURANGAN:\s*(.*?)(?=SARAN:|$)/is', $text, $kekuranganMatches);
            $kekurangan = isset($kekuranganMatches[1]) ? trim($kekuranganMatches[1]) : '';
            
            // Parse saran
            preg_match('/SARAN:\s*(.*?)$/is', $text, $saranMatches);
            $saran = isset($saranMatches[1]) ? trim($saranMatches[1]) : '';
            
            return [
                'nilai' => $nilai,
                'feedback' => $feedback,
                'detail' => [
                    'kelebihan' => $kelebihan,
                    'kekurangan' => $kekurangan,
                    'saran' => $saran,
                    'raw_response' => $text
                ]
            ];
            
        } catch (Exception $e) {
            Log::error('Error parsing Gemini response: ' . $e->getMessage());
            throw new Exception('Gagal memproses response dari Gemini API');
        }
    }
    
    /**
     * Test koneksi ke Gemini API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => 'Hello, this is a test message. Please respond with "Connection successful".'
                            ]
                        ]
                    ]
                ]
            ]);
            
            return $response->successful();
            
        } catch (Exception $e) {
            Log::error('Gemini connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}

