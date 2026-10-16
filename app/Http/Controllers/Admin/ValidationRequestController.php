<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ValidationRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * Streams an uploaded document from private storage; there is no public URL for it.
     */
    public function document(ValidationRequest $validationRequest, string $type): StreamedResponse
    {
        $this->authorize('admin');

        $path = $type === 'diploma' ? $validationRequest->diploma_path : $validationRequest->id_document_path;

        return $validationRequest->documentStorage($path)->response($path);
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
            $validationRequest->user->forceFill(['is_validated' => true])->save();
        } else {
            $validationRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $validationRequest->user->forceFill(['is_validated' => false])->save();
        }

        return back()->with('status', 'Revisión guardada.');
    }
}
