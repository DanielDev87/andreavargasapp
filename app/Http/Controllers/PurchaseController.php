<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ValidationRequest;
use App\Services\BoldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function create(Request $request, Course $course)
    {
        $user = $request->user();

        if (! $this->isValidated($user)) {
            return redirect()->route('validation.create')->with('warning', 'Debe validar su diploma antes de comprar.');
        }

        if ($this->alreadyOwns($user, $course)) {
            return redirect()->route('courses.show', $course);
        }

        if (! $course->isPubliclyVisible()) {
            return redirect()->route('courses.show', $course)->with('warning', 'Este curso ya no está disponible para inscripción.');
        }

        if ($course->isSoldOut()) {
            return redirect()->route('courses.show', $course)->with('warning', 'Este curso presencial ya no tiene cupos disponibles.');
        }

        return view('courses.checkout', compact('course'));
    }

    public function initiate(Request $request, Course $course, BoldService $bold): RedirectResponse
    {
        $user = $request->user();

        if (! $this->isValidated($user)) {
            return redirect()->route('validation.create')->with('warning', 'Debe validar su diploma antes de comprar.');
        }

        if ($this->alreadyOwns($user, $course)) {
            return redirect()->route('courses.show', $course);
        }

        if (! $course->isPubliclyVisible()) {
            return redirect()->route('courses.show', $course)->with('warning', 'Este curso ya no está disponible para inscripción.');
        }

        if ($course->isSoldOut()) {
            return redirect()->route('courses.show', $course)->with('warning', 'Este curso presencial ya no tiene cupos disponibles.');
        }

        $data = $request->validate([
            'payment_method' => 'required|in:bold,transfer',
        ]);

        if ($data['payment_method'] === 'transfer') {
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'amount' => $course->price,
                'status' => 'pending',
                'payment_method' => 'transfer',
            ]);

            $message = "Hola, acabo de realizar el pago por transferencia para el curso: {$course->title} (compra #{$purchase->id}). Adjunto el comprobante.";

            return redirect()->away('https://wa.me/573115228585?text='.urlencode($message));
        }

        // Going back to Bold reuses the pending link (same amount) instead of opening a new sale
        $pendingPurchase = $user->purchases()
            ->where('course_id', $course->id)
            ->where('payment_method', 'bold')
            ->where('status', 'pending')
            ->whereNotNull('bold_payment_url')
            ->latest()
            ->first();

        if ($pendingPurchase && (int) round((float) $pendingPurchase->amount) === (int) round((float) $course->price)) {
            $linkStatus = $bold->isConfigured() ? $bold->paymentLinkStatus($pendingPurchase->bold_payment_link)['status'] ?? null : null;

            // ACTIVE also covers a rejected attempt: Bold lets the same link be paid again.
            // A payment in progress or already paid must not get a second link (double charge).
            if ($linkStatus === 'ACTIVE') {
                return redirect()->away($pendingPurchase->bold_payment_url);
            }

            if (in_array($linkStatus, ['PAID', 'PROCESSING'], true)) {
                return redirect()->route('purchases.result', $pendingPurchase);
            }

            // Expired, cancelled or unknown: that link can't be paid anymore, start a fresh sale
            $pendingPurchase->update(['status' => 'cancelled']);
        }

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'status' => 'pending',
            'payment_method' => 'bold',
        ]);

        if ($bold->isSimulated()) {
            return redirect()->route('webhooks.bold', ['simulate' => 1, 'status' => 'paid', 'purchase_id' => $purchase->id]);
        }

        $reference = $purchase->newBoldReference();
        $link = $bold->createPaymentLink(
            reference: $reference,
            amount: (int) round((float) $purchase->amount),
            description: Str::limit('Curso: '.$course->title, 97),
            payerEmail: $user->email,
            callbackUrl: route('purchases.result', $purchase),
        );

        if (! $link) {
            $purchase->delete();

            return back()->with('warning', __('app.checkout_bold_unavailable'));
        }

        $purchase->update([
            'bold_reference' => $reference,
            'bold_payment_link' => $link['payment_link'],
            'bold_payment_url' => $link['url'],
        ]);

        return redirect()->away($link['url']);
    }

    /**
     * Where Bold sends the buyer back. The webhook can take up to 10 minutes, so while the
     * purchase is pending we also ask Bold for the link status and apply it if it is paid.
     */
    public function result(Request $request, Purchase $purchase, BoldService $bold): View
    {
        abort_unless($purchase->user_id === $request->user()->id, 403);

        if ($purchase->status === 'pending' && $purchase->bold_payment_link && $bold->isConfigured()) {
            $linkStatus = $bold->paymentLinkStatus($purchase->bold_payment_link);

            if (($linkStatus['status'] ?? null) === 'PAID') {
                $purchase->confirmBoldPayment($linkStatus['total'], 'COP', $linkStatus['transaction_id']);
                $purchase->refresh();
            }
        }

        $checks = min((int) $request->query('check', 0), 12);
        $rejected = $purchase->status === 'pending' && $request->query('bold-tx-status') === 'rejected';

        return view('purchases.result', [
            'purchase' => $purchase->load('course'),
            'rejected' => $rejected,
            // Keep re-checking every 5 s for about a minute while the payment is confirmed
            'nextCheck' => $purchase->status === 'pending' && ! $rejected && $checks < 12 ? $checks + 1 : null,
        ]);
    }

    private function isValidated(User $user): bool
    {
        return ValidationRequest::where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    private function alreadyOwns(User $user, Course $course): bool
    {
        return $user->purchases()->where('course_id', $course->id)->whereIn('status', ['paid', 'completed'])->exists();
    }
}
