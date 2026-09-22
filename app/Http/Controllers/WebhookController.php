<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Services\BoldService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * Receive Bold payment notifications.
     *
     * Two shapes land here:
     * - Local simulation (no real Bold credentials configured): simple `purchase_id`/`status`
     *   query params, reached via BoldService's stub redirect — see BoldService::createPayment().
     * - Real Bold webhook: `type` (SALE_APPROVED/SALE_REJECTED/...), `subject` (Bold's
     *   transaction id), `data.metadata.reference` (our purchase id) — see
     *   https://developers.bold.co/webhook
     */
    public function bold(Request $request)
    {
        $boldService = new BoldService;

        if (! $boldService->verifyWebhook($request)) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        if ($request->boolean('simulate')) {
            return $this->handleSimulated($request);
        }

        return $this->handleReal($request);
    }

    private function handleSimulated(Request $request)
    {
        $purchase = Purchase::find($request->input('purchase_id'));

        if (! $purchase) {
            return response()->json(['message' => 'purchase not found'], 404);
        }

        if ($request->input('status') === 'paid') {
            $this->markPaid($purchase);
        }

        if ($request->isMethod('get')) {
            return redirect()->route('profile')->with('status', __('app.profile_status_completed'));
        }

        return response()->json(['ok' => true]);
    }

    private function handleReal(Request $request)
    {
        $payload = $request->json()->all();

        $reference = data_get($payload, 'data.metadata.reference') ?? data_get($payload, 'data.reference');
        $purchase = $reference ? Purchase::find($reference) : null;

        if (! $purchase) {
            return response()->json(['message' => 'purchase not found'], 404);
        }

        if (data_get($payload, 'type') === 'SALE_APPROVED') {
            $purchase->update(['bold_payment_id' => data_get($payload, 'subject')]);
            $this->markPaid($purchase);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Course access unlocks here (status paid/completed). The certificate is only
     * generated later, once the student finishes every lesson in order — see
     * LessonController::complete() — never at payment time.
     */
    private function markPaid(Purchase $purchase): void
    {
        $purchase->update(['status' => 'completed', 'paid_at' => now()]);
    }
}
