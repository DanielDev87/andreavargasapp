<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessValidationRequest;
use App\Models\ValidationRequest;
use App\Services\AIValidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ValidationRequestController extends Controller
{
    public function __construct(private readonly AIValidationService $ai) {}

    public function create(): View
    {
        return view('validation.create', ['aiAvailable' => $this->ai->isConfigured()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'diploma' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'review_method' => 'required|in:ai,manual',
            'data_consent' => 'accepted',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        // Identity documents are personal data: private disk, served only to admins
        $diplomaPath = $request->file('diploma')->store('validations', 'local');
        $idPath = $request->file('id_document')->store('validations', 'local');

        $wantsAi = $request->input('review_method') === 'ai';
        $aiUnavailable = $wantsAi && ! $this->ai->isConfigured();

        $vr = ValidationRequest::create([
            'user_id' => $request->user()->id,
            'course_id' => $request->input('course_id'),
            'diploma_path' => $diplomaPath,
            'id_document_path' => $idPath,
            'review_method' => $wantsAi && ! $aiUnavailable ? 'ai' : 'manual',
            'data_consent_at' => now(),
            'data_policy_version' => config('app.data_policy_version'),
            'status' => 'pending',
            'ai_report' => $aiUnavailable ? ['error' => 'not_configured'] : null,
        ]);

        if ($vr->review_method === 'manual') {
            return redirect()->route('profile')->with('status', __($aiUnavailable ? 'app.validation_ai_unavailable' : 'app.validation_sent_manual'));
        }

        // If the queue itself is down the upload must still go through, just to manual review
        try {
            ProcessValidationRequest::dispatch($vr);
        } catch (Throwable $e) {
            Log::error('Could not queue AI document review', ['validation_request_id' => $vr->id, 'message' => $e->getMessage()]);
            $vr->update(['review_method' => 'manual', 'ai_report' => ['error' => 'analysis_failed']]);

            return redirect()->route('profile')->with('status', __('app.validation_ai_unavailable'));
        }

        return redirect()->route('profile')->with('status', __('app.validation_sent_ai'));
    }
}
