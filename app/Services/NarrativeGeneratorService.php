<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NarrativeGeneratorService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Generate narrative description for assessment
     *
     * @param string $studentName
     * @param string $aspectName
     * @param string $aspectCategory
     * @param string $score (BB, MB, BSH, BSB)
     * @param string|null $keywords
     * @return string|null
     */
    public function generateNarrative(
        string $studentName,
        string $aspectName,
        string $aspectCategory,
        string $score,
        ?string $keywords = null
    ): ?string {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API key not configured');
            return null;
        }

        $scoreLabel = $this->getScoreLabel($score);
        $prompt = $this->buildPrompt($studentName, $aspectName, $aspectCategory, $score, $scoreLabel, $keywords);

        try {
            $response = Http::timeout(30)
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'topK' => 40,
                        'topP' => 0.95,
                        'maxOutputTokens' => 200,
                    ],
                    'safetySettings' => [
                        [
                            'category' => 'HARM_CATEGORY_HARASSMENT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_HATE_SPEECH',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                            'threshold' => 'BLOCK_NONE'
                        ],
                        [
                            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                            'threshold' => 'BLOCK_NONE'
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $narrative = $data['candidates'][0]['content']['parts'][0]['text'];
                    return trim($narrative);
                }

                Log::warning('Unexpected Gemini API response structure', ['response' => $data]);
                return null;
            }

            // Handle specific HTTP error codes
            if ($response->status() === 401 || $response->status() === 403) {
                Log::error('Gemini API authentication failed', ['status' => $response->status()]);
                throw new \Exception('API key Gemini tidak valid. Periksa konfigurasi GEMINI_API_KEY.');
            }

            if ($response->status() === 429) {
                Log::warning('Gemini API rate limit exceeded');
                throw new \Exception('Rate limit API Gemini tercapai. Tunggu beberapa menit dan coba lagi.');
            }

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Gagal terhubung ke Gemini API. Periksa koneksi internet.');

        } catch (\Exception $e) {
            Log::error('Error generating narrative with Gemini', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to let controller handle the error
        }
    }

    /**
     * Build prompt for narrative generation
     */
    private function buildPrompt(
        string $studentName,
        string $aspectName,
        string $aspectCategory,
        string $score,
        string $scoreLabel,
        ?string $keywords
    ): string {
        $keywordsText = $keywords ? "Kata kunci: " . $keywords : "Tidak ada kata kunci khusus.";

        return <<<PROMPT
Kamu adalah seorang guru PAUD profesional yang sedang menulis raport siswa.
Buatlah narasi deskripsi perkembangan anak untuk raport PAUD dengan detail berikut:

Nama Anak: {$studentName}
Aspek Penilaian: {$aspectName} ({$aspectCategory})
Nilai: {$score} ({$scoreLabel})
{$keywordsText}

Tulis narasi deskriptif dalam 2-3 kalimat yang:
1. Menggunakan bahasa Indonesia formal dan profesional
2. Menggambarkan perkembangan anak secara spesifik sesuai nilai yang diberikan
3. Jika ada kata kunci, integrasikan kata kunci tersebut ke dalam narasi secara natural
4. Bersifat positif dan konstruktif
5. Fokus pada perkembangan anak, bukan perbandingan dengan anak lain
6. Gunakan nama anak ({$studentName}) di awal kalimat

Contoh format narasi:
- Untuk BSB: "{$studentName} menunjukkan perkembangan yang sangat baik dalam aspek {$aspectCategory}. [Deskripsi spesifik berdasarkan kata kunci]. [Pencapaian tambahan atau keunggulan]."
- Untuk BSH: "{$studentName} berkembang sesuai harapan dalam aspek {$aspectCategory}. [Deskripsi kemampuan yang sudah dikuasai]."
- Untuk MB: "{$studentName} mulai menunjukkan perkembangan dalam aspek {$aspectCategory}. [Deskripsi kemampuan yang mulai muncul]. Perlu didampingi untuk lebih optimal."
- Untuk BB: "{$studentName} masih memerlukan bimbingan dalam aspek {$aspectCategory}. [Deskripsi area yang perlu perhatian]. Disarankan untuk terus didampingi dan diberi stimulasi."

Tulis HANYA narasi deskripsi tanpa label atau penjelasan tambahan:
PROMPT;
    }

    /**
     * Get score label in Indonesian
     */
    private function getScoreLabel(string $score): string
    {
        return match ($score) {
            'BB' => 'Belum Berkembang',
            'MB' => 'Mulai Berkembang',
            'BSH' => 'Berkembang Sesuai Harapan',
            'BSB' => 'Berkembang Sangat Baik',
            default => '-',
        };
    }

    /**
     * Generate narratives for all assessments in a report card
     *
     * @param array $assessments Array of assessment data
     * @param string $studentName
     * @return array Array of generated narratives indexed by assessment_aspect_id
     */
    public function generateBulkNarratives(array $assessments, string $studentName): array
    {
        $narratives = [];

        foreach ($assessments as $assessment) {
            if (!isset($assessment['assessment_aspect_id'], $assessment['score'])) {
                continue;
            }

            $aspectId = $assessment['assessment_aspect_id'];
            $aspect = \App\Models\AssessmentAspect::find($aspectId);

            if (!$aspect) {
                continue;
            }

            $narrative = $this->generateNarrative(
                $studentName,
                $aspect->name,
                $aspect->category,
                $assessment['score'],
                $assessment['keywords'] ?? null
            );

            if ($narrative) {
                $narratives[$aspectId] = $narrative;
            }
        }

        return $narratives;
    }
}
