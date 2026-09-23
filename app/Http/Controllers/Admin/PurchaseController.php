<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $this->authorize('admin');

        $purchases = Purchase::with('user', 'course')->latest()->paginate(25);

        return view('admin.purchases.index', compact('purchases'));
    }

    public function approve(Request $request, Purchase $purchase)
    {
        $this->authorize('admin');

        if ($purchase->status === 'completed') {
            return back()->with('status', 'Compra ya completada');
        }

        // The certificate is only earned by finishing every lesson in order — see
        // LessonController::complete() — never at payment/approval time.
        $purchase->markCompleted();

        return back()->with('status', 'Compra aprobada y usuario habilitado');
    }
}
