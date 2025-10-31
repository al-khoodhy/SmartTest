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
     * ===========================
     *  Fungsi Utama: gradeEssay
     * ===========================
     * - Mengirim prompt ke Gemini API
     * - Menghasilkan skor, feedback, dan rincian berbasis rubrik
     */
    public function gradeEssay($soal, $jawaban, $rubrikPenilaian = null, $nilaiMaksimal = 100, $kunciJawaban = null)
    {
        try {
            $prompt = $this->buildGradingPrompt(
                $soal,
                $jawaban,
                $rubrikPenilaian,
                $nilaiMaksimal,
                $kunciJawaban
            );

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature'      => 0.0,   // deterministik
                    'topK'             => 1,
                    'topP'             => 1.0,
                    'maxOutputTokens'  => 800,   // efisien
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $this->parseGradingResponse($result, $nilaiMaksimal);
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
     * ===========================
     *  Build Prompt Penilaian
     * ===========================
     * - Menghasilkan instruksi penilaian dengan format JSON-only
     * - Sesuai prinsip: objektif, konsisten, dan berbasis rubrik
     */
    private function buildGradingPrompt($soal, $jawaban, $rubrikPenilaian, $nilaiMaksimal, $kunciJawaban = null)
    {
        $S_MAX = 4; // Skala per kriteria (0–4)

        $prompt  = "PERAN: Anda penilai esai akademik. Nilai hanya berdasarkan SOAL, (opsional) KUNCI, dan RUBRIK.\n";
        $prompt .= "PRINSIP: objektif, konsisten, berbasis bukti, bebas bias identitas, fokus isi; hindari menilai panjang/gaya.\n";
        $prompt .= "SKALA: 0–{$S_MAX} per kriteria, total 0–{$nilaiMaksimal}.\n";
        $prompt .= "RUMUS TOTAL: round({$nilaiMaksimal} * Σ(bobot_i * skor_i/{$S_MAX}), 1).\n";
        $prompt .= "KETENTUAN:\n";
        $prompt .= "- Jika KUNCI tersedia: nilai kecocokan konsep, bukan sinonim literal.\n";
        $prompt .= "- Jika jawaban off-topic: beri skor rendah sesuai rubrik.\n";
        $prompt .= "- Sertakan EVIDENCE kutipan (≤25 kata) dari jawaban untuk skor < {$S_MAX}.\n";
        $prompt .= "- KELUARAN WAJIB BERFORMAT JSON VALID SAJA, TANPA TEKS TAMBAHAN.\n\n";

        $prompt .= "SOAL:\n{$soal}\n\n";
        if ($kunciJawaban) {
            $prompt .= "KUNCI:\n{$kunciJawaban}\n\n";
        }
        if ($rubrikPenilaian) {
            $prompt .= "RUBRIK (berbobot; skala 0–{$S_MAX}):\n{$rubrikPenilaian}\n\n";
        }
        $prompt .= "JAWABAN:\n{$jawaban}\n\n";

        // Template JSON wajib (deterministik)
        $template = [
            "criteria" => (object)[],
            "total"    => 0.0,
            "pos"      => [],
            "neg"      => [],
            "rec"      => []
        ];

        $prompt .= "FORMAT OUTPUT (WAJIB JSON SAJA):\n";
        $prompt .= json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        $prompt .= "ATURAN:\n";
        $prompt .= "- Isi semua KRITERIA dari RUBRIK dengan {score,evidence}.\n";
        $prompt .= "- Evidence boleh kosong hanya jika score={$S_MAX}.\n";
        $prompt .= "- Setiap daftar (pos/neg/rec) maks 3 item.\n";
        $prompt .= "- JSON valid tunggal, tanpa catatan tambahan.\n";

        return $prompt;
    }

    /**
     * ===========================
     *  Parse Hasil dari Gemini
     * ===========================
     * - Prioritas JSON parsing
     * - Fallback ke pola lama (NILAI:, FEEDBACK:, dst.)
     */
    private function parseGradingResponse($response, $nilaiMaksimal)
    {
        try {
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // === 1) Parse JSON langsung (prioritas utama)
            $json = json_decode($text, true);

            // Jika gagal parse, cari blok JSON pertama
            if (!is_array($json)) {
                if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $m)) {
                    $json = json_decode($m[0], true);
                }
            }

            if (is_array($json) && isset($json['total'])) {
                $total = (float) $json['total'];
                if ($total < 0) $total = 0.0;
                if ($total > $nilaiMaksimal) $total = (float) $nilaiMaksimal;

                $pos = $json['pos'] ?? [];
                $neg = $json['neg'] ?? [];
                $rec = $json['rec'] ?? [];

                $feedback = "Kelebihan: " . implode('; ', $pos)
                    . "\nKekurangan: " . implode('; ', $neg)
                    . "\nSaran: " . implode('; ', $rec);

                return [
                    'nilai'    => $total,
                    'feedback' => trim($feedback),
                    'detail'   => [
                        'criteria'     => $json['criteria'] ?? new \stdClass(),
                        'pos'          => $pos,
                        'neg'          => $neg,
                        'saran'        => $rec,
                        'raw_response' => $text,
                    ]
                ];
            }

            // === 2) Fallback ke format lama (NILAI:, FEEDBACK:, dst.)
            preg_match('/NILAI:\s*(\d+(?:\.\d+)?)/i', $text, $nilaiMatches);
            $nilai = isset($nilaiMatches[1]) ? (float) $nilaiMatches[1] : 0.0;
            if ($nilai > $nilaiMaksimal) $nilai = (float) $nilaiMaksimal;
            if ($nilai < 0) $nilai = 0.0;

            preg_match('/FEEDBACK:\s*(.*?)(?=KELEBIHAN:|$)/is', $text, $feedbackMatches);
            $feedback = isset($feedbackMatches[1]) ? trim($feedbackMatches[1]) : '';

            preg_match('/KELEBIHAN:\s*(.*?)(?=KEKURANGAN:|$)/is', $text, $kelebihanMatches);
            $kelebihan = isset($kelebihanMatches[1]) ? trim($kelebihanMatches[1]) : '';

            preg_match('/KEKURANGAN:\s*(.*?)(?=SARAN:|$)/is', $text, $kekuranganMatches);
            $kekurangan = isset($kekuranganMatches[1]) ? trim($kekuranganMatches[1]) : '';

            preg_match('/SARAN:\s*(.*?)$/is', $text, $saranMatches);
            $saran = isset($saranMatches[1]) ? trim($saranMatches[1]) : '';

            return [
                'nilai' => $nilai,
                'feedback' => $feedback,
                'detail' => [
                    'kelebihan'    => $kelebihan,
                    'kekurangan'   => $kekurangan,
                    'saran'        => $saran,
                    'raw_response' => $text,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error parsing Gemini response: ' . $e->getMessage());
            throw new Exception('Gagal memproses response dari Gemini API');
        }
    }

    /**
     * ===========================
     *  Test koneksi API Gemini
     * ===========================
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => 'Hello, this is a test message. Please respond with "Connection successful".']],
                ]]
            ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error('Gemini connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
