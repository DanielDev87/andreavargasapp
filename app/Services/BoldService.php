<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Wraps Bold's "Link de Pagos" API and webhook signatures.
 *
 * Docs: https://developers.bold.co/pagos-en-linea/api-link-de-pagos
 *       https://developers.bold.co/pagos-en-linea/llaves-de-integracion
 *       https://developers.bold.co/webhook
 */
class BoldService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $webhookSecret,
        private readonly bool $sandbox,
    ) {}

    public function isConfigured(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * Local development without Bold keys: checkout jumps straight to a fake "paid" webhook.
     * Never available in production, where it would let anyone mark a purchase as paid.
     */
    public function isSimulated(): bool
    {
        return ! $this->isConfigured() && app()->environment('local', 'testing');
    }

    /**
     * Create a closed-amount payment link in COP.
     *
     * @param  string  $reference  Unique sale reference: letters, digits, "_" and "-", max 60 characters
     * @param  int  $amount  Whole pesos, no decimals
     * @param  string|null  $callbackUrl  Where Bold sends the buyer back; Bold rejects non-HTTPS URLs
     * @return array{payment_link: string, url: string}|null Null when Bold could not create the link
     */
    public function createPaymentLink(string $reference, int $amount, string $description, ?string $payerEmail, ?string $callbackUrl): ?array
    {
        $body = array_filter([
            'amount_type' => 'CLOSE',
            'amount' => [
                'currency' => 'COP',
                'total_amount' => $amount,
                'tip_amount' => 0,
            ],
            'reference' => $reference,
            'description' => $description,
            'payer_email' => $payerEmail,
            'callback_url' => $callbackUrl !== null && str_starts_with($callbackUrl, 'https://') ? $callbackUrl : null,
        ], fn ($value) => $value !== null);

        try {
            $response = $this->client()->post($this->baseUrl, $body);
        } catch (ConnectionException $e) {
            Log::error('Bold payment link request failed', ['reference' => $reference, 'message' => $e->getMessage()]);

            return null;
        }

        $paymentLink = $response->json('payload.payment_link');
        $url = $response->json('payload.url');

        if (! $response->successful() || ! $paymentLink || ! $url) {
            Log::warning('Bold rejected the payment link', ['reference' => $reference, 'status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return ['payment_link' => $paymentLink, 'url' => $url];
    }

    /**
     * Current state of a payment link, used as a backup when the webhook is late.
     *
     * @return array{status: string, total: int|float|null, transaction_id: string|null}|null
     */
    public function paymentLinkStatus(string $paymentLink): ?array
    {
        try {
            $response = $this->client()->get(rtrim($this->baseUrl, '/').'/'.rawurlencode($paymentLink));
        } catch (ConnectionException $e) {
            Log::warning('Bold payment link status request failed', ['payment_link' => $paymentLink, 'message' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful() || ! $response->json('status')) {
            return null;
        }

        return [
            'status' => (string) $response->json('status'),
            'total' => $response->json('total'),
            'transaction_id' => $response->json('transaction_id'),
        ];
    }

    /**
     * Bold signs the webhook as hex HMAC-SHA256 of base64(raw body) with the secret key
     * (x-bold-signature header). Test-mode events are signed with an empty key, which
     * anyone could reproduce, so that signature is only accepted in sandbox mode.
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        if ($signature === '') {
            return false;
        }

        $secrets = array_filter(
            $this->sandbox ? [$this->webhookSecret, ''] : [$this->webhookSecret],
            fn (string $secret) => $secret !== '' || $this->sandbox,
        );

        $encodedBody = base64_encode($rawBody);

        foreach ($secrets as $secret) {
            if (hash_equals(hash_hmac('sha256', $encodedBody, $secret), $signature)) {
                return true;
            }
        }

        return false;
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders(['Authorization' => 'x-api-key '.$this->apiKey])
            ->acceptJson()
            ->timeout(15);
    }
}
