<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ValidationRequest;
use Illuminate\Http\Request;

class ValidationRequestController extends Controller
{
    public function index()
    {
        $this->authorize('admin');

        $requests = ValidationRequest::with('user')->latest()->paginate(25);

        return view('admin.validation.index', compact('requests'));
    }

    public function show(ValidationRequest $validationRequest)
    {
        $this->authorize('admin');

        return view('admin.validation.show', ['request' => $validationRequest]);
    }

    public function review(Request $request, ValidationRequest $validationRequest)
    {
        $this->authorize('admin');

        $request->validate(['action' => 'required|in:approve,reject']);

        if ($request->input('action') === 'approve') {
            $validationRequest->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            // mark user validated
            $validationRequest->user->update(['is_validated' => true]);
        } else {
            $validationRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $validationRequest->user->update(['is_validated' => false]);
        }

        return back()->with('status', 'Revisión guardada.');
    }
}
