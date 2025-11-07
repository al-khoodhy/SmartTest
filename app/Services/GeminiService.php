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
     *  Single Essay Grading (legacy)
     * ===========================
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
                    'temperature'     => 0.0,
                    'topK'            => 1,
                    'topP'            => 1.0,
                    'maxOutputTokens' => 800,
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
     *  Multi Essay Grading (Batching)
     * ===========================
     * - items: array of [
     *      'soal_id'       => int,
     *      'pertanyaan'    => string,
     *      'jawaban'       => string,
     *      'kunci_jawaban' => ?string
     *   ]
     * - Output: [ soal_id => [ 'nilai' => float, 'feedback' => string, 'detail' => [...] ], ... ]
     */
    public function gradeMultiEssay(array $items, $rubrikPenilaian = null, $nilaiMaksimal = 100)
    {
        if (empty($items)) {
            throw new Exception('Tidak ada item untuk dinilai.');
        }

        try {
            $prompt = $this->buildMultiGradingPrompt($items, $rubrikPenilaian, $nilaiMaksimal);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '?key=' . $this->apiKey, [
                'contents' => [[
                    'role'  => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'temperature'     => 0.0,
                    'topK'            => 1,
                    'topP'            => 1.0,
                    'maxOutputTokens' => 1200, // cukup untuk banyak soal
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $this->parseMultiGradingResponse($result, $nilaiMaksimal);
            } else {
                Log::error('Gemini API Error (multi): ' . $response->body());
                throw new Exception('Gagal menghubungi Gemini API (multi): ' . $response->status());
            }
        } catch (Exception $e) {
            Log::error('Error grading multi essay: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build prompt single-essay (sudah ada, tidak diubah)
     */
    private function buildGradingPrompt($soal, $jawaban, $rubrikPenilaian, $nilaiMaksimal, $kunciJawaban = null)
    {
        $S_MAX = 4;

        $prompt  = "PERAN: Anda penilai esai akademik. Nilai hanya berdasarkan SOAL, (opsional) KUNCI, dan RUBRIK.\n";
        $prompt .= "PRINSIP: objektif, konsisten, berbasis bukti, bebas bias identitas, fokus isi.\n";
        $prompt .= "SKALA: 0–{$S_MAX} per kriteria, total 0–{$nilaiMaksimal}.\n";
        $prompt .= "RUMUS TOTAL: round({$nilaiMaksimal} * Σ(bobot_i * skor_i/{$S_MAX}), 1).\n";
        $prompt .= "KELUARAN WAJIB JSON VALID SAJA.\n\n";

        $prompt .= "SOAL:\n{$soal}\n\n";
        if ($kunciJawaban) {
            $prompt .= "KUNCI:\n{$kunciJawaban}\n\n";
        }
        if ($rubrikPenilaian) {
            $prompt .= "RUBRIK:\n{$rubrikPenilaian}\n\n";
        }
        $prompt .= "JAWABAN:\n{$jawaban}\n\n";

        $template = [
            "criteria" => (object)[],
            "total"    => 0.0,
            "pos"      => [],
            "neg"      => [],
            "rec"      => []
        ];

        $prompt .= "FORMAT OUTPUT (WAJIB JSON SAJA):\n";
        $prompt .= json_encode($template, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";

        return $prompt;
    }

    /**
     * Build prompt untuk multi-essay (batch)
     */
    private function buildMultiGradingPrompt(array $items, $rubrikPenilaian, $nilaiMaksimal)
    {
        $S_MAX = 4;

        $prompt  = "PERAN: Anda penilai esai akademik.\n";
        $prompt .= "TUGAS: Nilai beberapa jawaban sekaligus berdasarkan SOAL, (opsional) KUNCI, dan RUBRIK.\n";
        $prompt .= "PRINSIP: objektif, konsisten, berbasis bukti, bebas bias identitas, fokus isi.\n";
        $prompt .= "SKALA: 0–{$S_MAX} per kriteria, total 0–{$nilaiMaksimal} per soal.\n";
        $prompt .= "KELUARAN: JSON valid dengan struktur:\n";
        $prompt .= json_encode([
            "items" => [
                [
                    "soal_id"  => 1,
                    "total"    => 0.0,
                    "pos"      => [],
                    "neg"      => [],
                    "rec"      => [],
                    "criteria" => (object)[]
                ]
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        $prompt .= "ATURAN:\n";
        $prompt .= "- Gunakan field soal_id persis seperti input.\n";
        $prompt .= "- total: 0–{$nilaiMaksimal}, dibulatkan 1 desimal.\n";
        $prompt .= "- Evidence singkat bila skor < {$S_MAX}.\n";
        $prompt .= "- Hanya 1 JSON, tanpa teks tambahan.\n\n";

        if ($rubrikPenilaian) {
            $prompt .= "RUBRIK GLOBAL:\n{$rubrikPenilaian}\n\n";
        }

        $prompt .= "DATA:\n";
        foreach ($items as $i => $item) {
            $prompt .= "ITEM #" . ($i + 1) . "\n";
            $prompt .= "soal_id: " . $item['soal_id'] . "\n";
            $prompt .= "SOAL: " . $item['pertanyaan'] . "\n";
            if (!empty($item['kunci_jawaban'])) {
                $prompt .= "KUNCI: " . $item['kunci_jawaban'] . "\n";
            }
            $prompt .= "JAWABAN: " . $item['jawaban'] . "\n\n";
        }

        return $prompt;
    }

        /**
     * Bangun teks feedback terstruktur dari array pos/neg/rec
     */
    private function buildFeedbackText(array $pos, array $neg, array $rec): string
    {
        // Bersihkan elemen kosong
        $pos = array_values(array_filter($pos, fn($v) => trim($v) !== ''));
        $neg = array_values(array_filter($neg, fn($v) => trim($v) !== ''));
        $rec = array_values(array_filter($rec, fn($v) => trim($v) !== ''));

        $lines = [];

        // Kelebihan
        $lines[] = 'Kelebihan:';
        if (!empty($pos)) {
            foreach ($pos as $p) {
                $lines[] = '- ' . $p;
            }
        } else {
            $lines[] = '- (Belum tampak kelebihan yang menonjol pada jawaban ini.)';
        }

        $lines[] = ''; // baris kosong

        // Hal yang perlu diperbaiki
        $lines[] = 'Hal yang perlu diperbaiki:';
        if (!empty($neg)) {
            foreach ($neg as $n) {
                $lines[] = '- ' . $n;
            }
        } else {
            $lines[] = '- Tidak ada kekurangan yang signifikan terkait pemenuhan indikator utama.';
        }

        $lines[] = '';

        // Saran pengembangan
        $lines[] = 'Saran pengembangan:';
        if (!empty($rec)) {
            foreach ($rec as $r) {
                $lines[] = '- ' . $r;
            }
        } else {
            $lines[] = '- Pertahankan kualitas jawaban dan tetap sesuaikan dengan rubrik penilaian.';
        }

        return trim(implode("\n", $lines));
    }


    /**
     * Parse single-essay (sudah ada)
     */
    private function parseGradingResponse($response, $nilaiMaksimal)
    {
        try {
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

            $json = json_decode($text, true);
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

                // 🔹 gunakan helper baru
                $feedback = $this->buildFeedbackText($pos, $neg, $rec);

                return [
                    'nilai'    => $total,
                    'feedback' => $feedback,
                    'detail'   => [
                        'criteria'     => $json['criteria'] ?? new \stdClass(),
                        'pos'          => $pos,
                        'neg'          => $neg,
                        'saran'        => $rec,
                        'raw_response' => $text,
                    ]
                ];
            }

            // fallback lama (NILAI:, FEEDBACK:, ...)
            // ... (biarkan seperti kodenmu semula)
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
                'nilai'    => $nilai,
                'feedback' => $feedback,
                'detail'   => [
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
     * Parse multi-essay response
     */
    private function parseMultiGradingResponse($response, $nilaiMaksimal)
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';

        $json = json_decode($text, true);
        if (!is_array($json)) {
            if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $text, $m)) {
                $json = json_decode($m[0], true);
            }
        }

        if (!is_array($json) || !isset($json['items']) || !is_array($json['items'])) {
            Log::error('Format response multi tidak valid: ' . $text);
            throw new Exception('Response Gemini multi tidak valid.');
        }

        $results = [];

        foreach ($json['items'] as $item) {
            if (!isset($item['soal_id'])) {
                continue;
            }

            $soalId = (int)$item['soal_id'];
            $total  = isset($item['total']) ? (float)$item['total'] : 0.0;
            $total  = max(0.0, min($total, (float)$nilaiMaksimal));

            $pos = $item['pos'] ?? [];
            $neg = $item['neg'] ?? [];
            $rec = $item['rec'] ?? [];

            // 🔹 feedback terstruktur
            $feedback = $this->buildFeedbackText($pos, $neg, $rec);

            $results[$soalId] = [
                'nilai'    => $total,
                'feedback' => $feedback,
                'detail'   => [
                    'criteria'     => $item['criteria'] ?? new \stdClass(),
                    'pos'          => $pos,
                    'neg'          => $neg,
                    'saran'        => $rec,
                    'raw_response' => $text,
                ],
            ];
        }


        if (empty($results)) {
            throw new Exception('Tidak ada item yang berhasil diparse dari response Gemini.');
        }

        return $results;
    }

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
