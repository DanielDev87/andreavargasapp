<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIValidationService
{
    /**
     * Analyze the diploma and ID document using Google Gemini Vision.
     *
     * @param  string  $diplomaPath  Storage path (disk 'public')
     * @param  string  $idPath  Storage path (disk 'public')
     * @return array{score:float,report:array}
     */
    public function analyze(string $diplomaPath, string $idPath): array
    {
        $geminiKey = config('services.gemini.api_key');

        if (! empty($geminiKey)) {
            try {
                return $this->analyzeWithGemini($geminiKey, $diplomaPath, $idPath);
            } catch (\Throwable $e) {
                Log::warning('AIValidationService.gemini_error', ['message' => $e->getMessage()]);
            }
        }

        return $this->fallbackAnalysis($diplomaPath, $idPath);
    }

    /**
     * Send images to Gemini 1.5 Flash for document verification.
     */
    private function analyzeWithGemini(string $key, string $diplomaPath, string $idPath): array
    {
        $diplomaAbs = Storage::disk('public')->path($diplomaPath);
        $idAbs = Storage::disk('public')->path($idPath);

        $diplomaB64 = base64_encode(file_get_contents($diplomaAbs));
        $idB64 = base64_encode(file_get_contents($idAbs));

        $diplomaMime = $this->mimeType($diplomaAbs);
        $idMime = $this->mimeType($idAbs);

        $prompt = <<<'PROMPT'
Eres un sistema de verificación profesional para una plataforma de cursos odontológicos.

Tu tarea es analizar dos documentos:
1. Un diploma o título universitario de odontología / medicina dental
2. Un documento de identidad (cédula, pasaporte, tarjeta profesional)

Debes determinar:
- ¿El primer documento parece un diploma/título de odontología, medicina dental o especialidad relacionada?
- ¿Los datos (nombre, número de documento) son consistentes entre los dos documentos?
- ¿Los documentos parecen auténticos y sin alteraciones obvias?

Responde ÚNICAMENTE con un JSON válido sin texto adicional, con esta estructura exacta:
{
  "score": 0.85,
  "report": {
    "is_dental_title": true,
    "names_match": true,
    "appears_authentic": true,
    "extracted_name": "Nombre del profesional",
    "extracted_title": "Título o especialidad encontrada",
    "document_id": "Número de documento",
    "observations": "Observaciones breves sobre los documentos analizados"
  }
}

El campo "score" debe estar entre 0.0 y 1.0:
- 0.85-1.0: Verificación exitosa, documentos válidos y coherentes
- 0.60-0.84: Requiere revisión manual, hay dudas menores
- 0.0-0.59: Rechazado, documentos inválidos, inconsistentes o ilegibles
PROMPT;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => $diplomaMime, 'data' => $diplomaB64]],
                        ['inline_data' => ['mime_type' => $idMime, 'data' => $idB64]],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 512,
            ],
        ];

        $response = Http::timeout(30)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$key}", $payload);

        if (! $response->successful()) {
            Log::warning('AIValidationService.gemini_http_error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API error: '.$response->status());
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');

        // Strip markdown code fences if present
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = str_replace('```', '', $text);
        $text = trim($text);

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! isset($decoded['score'])) {
            Log::warning('AIValidationService.gemini_parse_error', ['raw' => $text]);
            throw new \RuntimeException('Could not parse Gemini JSON response');
        }

        return [
            'score' => (float) $decoded['score'],
            'report' => $decoded['report'] ?? $decoded,
        ];
    }

    /**
     * Fallback when Gemini is not configured: try Tesseract OCR, then stub.
     */
    private function fallbackAnalysis(string $diplomaPath, string $idPath): array
    {
        try {
            if (function_exists('shell_exec')) {
                $diplomaAbs = Storage::disk('public')->path($diplomaPath);
                $idAbs = Storage::disk('public')->path($idPath);

                $diplomaText = @trim((string) shell_exec('tesseract '.escapeshellarg($diplomaAbs).' stdout 2>/dev/null'));
                $idText = @trim((string) shell_exec('tesseract '.escapeshellarg($idAbs).' stdout 2>/dev/null'));
                $combined = $diplomaText."\n".$idText;

                if (! empty(trim($combined))) {
                    $score = min(0.95, max(0.2, strlen($combined) / 2000));

                    return [
                        'score' => $score,
                        'report' => [
                            'method' => 'tesseract_ocr',
                            'diploma_text_sample' => substr($diplomaText, 0, 500),
                            'id_text_sample' => substr($idText, 0, 500),
                        ],
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info('AIValidationService.tesseract_failed', ['message' => $e->getMessage()]);
        }

        // Final stub — queued for manual review
        return [
            'score' => 0.65,
            'report' => [
                'method' => 'stub_fallback',
                'summary' => 'Configure GEMINI_API_KEY en .env para análisis automático por IA.',
            ],
        ];
    }

    private function mimeType(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/jpeg',
        };
    }
}
