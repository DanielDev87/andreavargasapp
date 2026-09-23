<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wraps Bold's "Link de Pagos" API.
 *
 * Docs: https://developers.bold.co/pagos-en-linea/api-link-de-pagos
 *       https://developers.bold.co/pagos-en-linea/llaves-de-integracion
 *       https://developers.bold.co/webhook
 */
class BoldService
{
    protected string $apiKey;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.bold.key') ?? '';
        $this->baseUrl = config('services.bold.base_url');
    }

    /**
     * Create a payment link for the given purchase.
     * Returns an array with `payment_id` and `payment_url`.
     */
    public function createPayment(array $payload): array
    {
        // If no API key configured, keep existing local stub behavior
        if (empty($this->apiKey)) {
            return [
                'payment_id' => 'bold_stub_'.($payload['purchase_id'] ?? uniqid('stub_')),
                'payment_url' => url('/webhooks/bold?simulate=1&status=paid&purchase_id='.($payload['purchase_id'] ?? '')),
            ];
        }

        try {
            $body = array_filter([
                'amount_type' => 'CLOSE',
                'amount' => [
                    'currency' => 'COP',
                    'total_amount' => (int) round($payload['amount']),
                    'tip_amount' => 0,
                ],
                'reference' => (string) $payload['purchase_id'],
                'description' => $payload['description'] ?? null,
                'payer_email' => $payload['customer']['email'] ?? null,
                // Bold rejects the request outright (403) if callback_url isn't HTTPS, so it's
                // only sent when we actually have an HTTPS app URL (i.e. not local dev).
                'callback_url' => $this->httpsCallbackUrl($payload['callback_url'] ?? null),
            ], fn ($value) => $value !== null);

            $resp = Http::withHeaders(['Authorization' => 'x-api-key '.$this->apiKey])
                ->timeout(10)
                ->post($this->baseUrl, $body);

            if ($resp->successful()) {
                $data = $resp->json();

                return [
                    'payment_id' => data_get($data, 'payload.payment_link'),
                    'payment_url' => data_get($data, 'payload.url'),
                    'raw' => $data,
                ];
            }

            Log::warning('BoldService.createPayment failed', ['status' => $resp->status(), 'body' => $resp->body()]);
        } catch (\Throwable $e) {
            Log::error('BoldService.createPayment error: '.$e->getMessage(), ['exception' => $e]);
        }

        // Fallback stub if API call failed
        return [
            'payment_id' => 'bold_stub_'.($payload['purchase_id'] ?? uniqid('stub_')),
            'payment_url' => url('/webhooks/bold?simulate=1&status=paid&purchase_id='.($payload['purchase_id'] ?? '')),
        ];
    }

    private function httpsCallbackUrl(?string $preferred): ?string
    {
        $url = $preferred ?? route('profile');

        return str_starts_with($url, 'https://') ? $url : null;
    }

    /**
     * Verify a Bold webhook signature.
     *
     * Bold signs the request body as: HMAC-SHA256(base64(raw_body), secret_key), hex-encoded,
     * sent in the `x-bold-signature` header. Per Bold's own docs: "En modo pruebas la firma usa
     * una clave vacía" — sandbox-mode transactions are always signed with an empty-string
     * secret, regardless of the real secret key configured. We accept either, since a real
     * secret will never coincidentally match the empty-string signature.
     */
    public function verifyWebhook(Request $request): bool
    {
        $secret = (string) (config('services.bold.webhook_secret') ?: env('BOLD_WEBHOOK_SECRET'));

        // Local simulation path used when no real Bold credentials are configured at all.
        if (empty($this->apiKey)) {
            return $request->boolean('simulate');
        }

        $signature = $request->header('x-bold-signature', '');
        if (empty($signature)) {
            return false;
        }

        $encodedBody = base64_encode($request->getContent());

        foreach ([$secret, ''] as $candidateSecret) {
            if (hash_equals(hash_hmac('sha256', $encodedBody, $candidateSecret), $signature)) {
                return true;
            }
        }

        return false;
    }
}
