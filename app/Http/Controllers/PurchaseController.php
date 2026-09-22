<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\ValidationRequest;
use App\Services\BoldService;
use Illuminate\Http\Request;

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

        return view('courses.checkout', compact('course'));
    }

    public function initiate(Request $request, Course $course)
    {
        $user = $request->user();

        if (! $this->isValidated($user)) {
            return redirect()->route('validation.create')->with('warning', 'Debe validar su diploma antes de comprar.');
        }

        if ($this->alreadyOwns($user, $course)) {
            return redirect()->route('courses.show', $course);
        }

        $data = $request->validate([
            'payment_method' => 'required|in:bold,transfer',
        ]);

        $purchase = Purchase::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => $course->price,
            'status' => 'pending',
            'payment_method' => $data['payment_method'],
        ]);

        if ($data['payment_method'] === 'transfer') {
            $message = "Hola, acabo de realizar el pago por transferencia para el curso: {$course->title} (compra #{$purchase->id}). Adjunto el comprobante.";

            return redirect()->away('https://wa.me/573115228585?text='.urlencode($message));
        }

        $boldService = new BoldService;

        $payment = $boldService->createPayment([
            'purchase_id' => $purchase->id,
            'amount' => (float) $purchase->amount,
            'description' => 'Compra curso: '.$course->title,
            'customer' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);

        if (! empty($payment['payment_id'])) {
            $purchase->update(['bold_payment_id' => $payment['payment_id']]);
        }

        if (! empty($payment['payment_url'])) {
            return redirect()->away($payment['payment_url']);
        }

        return view('purchases.checkout', compact('purchase', 'course'));
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
