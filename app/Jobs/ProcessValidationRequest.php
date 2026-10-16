<?php

namespace App\Jobs;

use App\Models\ValidationRequest;
use App\Services\AIValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Runs the AI document review. Clear-cut cases are approved automatically; everything
 * else (doubts, failed checks, AI unavailable) is left as "pending" for an admin.
 * The AI never rejects on its own.
 */
class ProcessValidationRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(public ValidationRequest $validationRequest) {}

    public function handle(AIValidationService $ai): void
    {
        $vr = $this->validationRequest->fresh();

        // An admin may have decided while the job was queued, or the dentist chose manual review
        if ($vr->review_method !== 'ai' || ! in_array($vr->status, ['pending', 'processing'], true)) {
            return;
        }

        if (! $ai->isConfigured()) {
            $vr->update(['status' => 'pending', 'review_method' => 'manual', 'ai_report' => ['error' => 'not_configured']]);

            return;
        }

        $vr->update(['status' => 'processing']);

        $report = $ai->analyze(
            $vr->documentStorage($vr->diploma_path)->path($vr->diploma_path),
            $vr->documentStorage($vr->id_document_path)->path($vr->id_document_path),
            $vr->user->name,
        );

        $approved = $ai->shouldAutoApprove($report);

        $vr->update([
            'status' => $approved ? 'approved' : 'pending',
            'ai_score' => $report['confidence'],
            'ai_report' => $report,
        ]);

        // is_validated is deliberately not mass assignable, so it is set explicitly
        if ($approved) {
            $vr->user->forceFill(['is_validated' => true])->save();
        }
    }

    /**
     * After the last retry the request goes to manual review instead of staying "processing".
     */
    public function failed(?Throwable $exception): void
    {
        $this->validationRequest->fresh()?->update([
            'status' => 'pending',
            'review_method' => 'manual',
            'ai_report' => ['error' => 'analysis_failed'],
        ]);
    }
}
