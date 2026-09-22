<?php

namespace App\Jobs;

use App\Models\ValidationRequest;
use App\Services\AIValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessValidationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ValidationRequest $validationRequest;

    public function __construct(ValidationRequest $validationRequest)
    {
        $this->validationRequest = $validationRequest;
    }

    public function handle(AIValidationService $ai)
    {
        $vr = $this->validationRequest->fresh();
        $vr->update(['status' => 'processing']);

        $diplomaPath = storage_path('app/public/'.$vr->diploma_path);
        $idPath = storage_path('app/public/'.$vr->id_document_path);

        $result = $ai->analyze($diplomaPath, $idPath);

        $score = $result['score'] ?? null;
        $report = $result['report'] ?? null;

        $vr->update(['ai_score' => $score, 'ai_report' => $report]);

        // Simple threshold logic: 0.75+ approved, 0.6- rejected, otherwise pending/manual review
        if ($score >= 0.75) {
            $vr->update(['status' => 'approved']);
            $vr->user->update(['is_validated' => true]);
        } elseif ($score < 0.6) {
            $vr->update(['status' => 'rejected']);
            $vr->user->update(['is_validated' => false]);
        } else {
            $vr->update(['status' => 'processing']);
        }
    }
}
