<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\BoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Receive Bold payment notifications (https://developers.bold.co/webhook).
     *
     * Bold sends every event of the merchant account here (including in-store sales), with
     * `type` (SALE_APPROVED, SALE_REJECTED, VOID_APPROVED, VOID_REJECTED), `subject` (Bold's
     * transaction id) and `data.metadata.reference`, which for payment links is the LNK_ id.
     * It retries until it gets a 200, so events we don't act on are still acknowledged.
     *
     * Local development without Bold keys reaches this route with ?simulate=1 instead.
     */
    public function bold(Request $request, BoldService $bold): JsonResponse|RedirectResponse
    {
        if ($request->boolean('simulate')) {
            return $bold->isSimulated()
                ? $this->handleSimulated($request)
                : response()->json(['message' => 'simulation disabled'], 403);
        }

        if (! $request->isMethod('post')) {
            return response()->json(['message' => 'method not allowed'], 405);
        }

        if (! $bold->verifyWebhookSignature($request->getContent(), (string) $request->header('x-bold-signature'))) {
            Log::warning('Bold webhook with an invalid signature', ['ip' => $request->ip()]);

            return response()->json(['message' => 'invalid signature'], 400);
        }

        $reference = (string) $request->json('data.metadata.reference');
        $purchase = $reference === '' ? null : Purchase::where('bold_payment_link', $reference)
            ->orWhere('bold_reference', $reference)
            ->first();

        if (! $purchase) {
            // Keep the event (minus card and seller details) so a sale that should have matched can be traced
            $event = $request->json()->all();
            Arr::forget($event, ['data.card', 'data.seller', 'data.payer_email', 'seller']);
            Log::info('Bold webhook for a sale outside the platform', ['type' => $request->json('type'), 'reference' => $reference, 'payload' => $event]);

            return response()->json(['ok' => true]);
        }

        match ($request->json('type')) {
            'SALE_APPROVED' => $purchase->confirmBoldPayment(
                $request->json('data.amount.total'),
                $request->json('data.amount.currency'),
                $request->json('data.payment_id') ?? $request->json('subject'),
            ),
            'VOID_APPROVED' => $purchase->cancelBoldPayment(),
            default => Log::info('Bold webhook without action', ['type' => $request->json('type'), 'purchase_id' => $purchase->id]),
        };

        return response()->json(['ok' => true]);
    }

    private function handleSimulated(Request $request): JsonResponse|RedirectResponse
    {
        $purchase = Purchase::find($request->input('purchase_id'));

        if (! $purchase) {
            return response()->json(['message' => 'purchase not found'], 404);
        }

        if ($request->input('status') === 'paid') {
            $purchase->markCompleted();
        }

        if ($request->isMethod('get')) {
            return redirect()->route('purchases.result', $purchase);
        }

        return response()->json(['ok' => true]);
    }
}
