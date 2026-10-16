<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Purchase $purchase;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    public function handle(): void
    {
        $user = $this->purchase->user;
        $course = $this->purchase->course;
        $issuedAt = now();
        $certificateNumber = 'AV-'.str_pad((string) $this->purchase->id, 6, '0', STR_PAD_LEFT).'-'.Str::upper(Str::random(4));

        $path = $this->generatePdf($user, $course, $issuedAt, $certificateNumber)
            ?? $this->generateTextFallback($user, $course, $issuedAt, $certificateNumber);

        Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_path' => $path,
            'data' => ['certificate_number' => $certificateNumber],
            'issued_at' => $issuedAt,
        ]);
    }

    private function generatePdf($user, $course, $issuedAt, string $certificateNumber): ?string
    {
        try {
            $html = view('certificates.template', [
                'user' => $user,
                'course' => $course,
                'issuedAt' => $issuedAt,
                'certificateNumber' => $certificateNumber,
            ])->render();

            $pdfData = Pdf::loadHTML($html)->setPaper('letter', 'landscape')->output();

            $path = 'certificates/certificate_'.$this->purchase->id.'.pdf';
            Storage::disk('local')->put($path, $pdfData);

            return $path;
        } catch (\Throwable $e) {
            Log::error('GenerateCertificate.pdf_failed', ['purchase_id' => $this->purchase->id, 'message' => $e->getMessage()]);

            return null;
        }
    }

    private function generateTextFallback($user, $course, $issuedAt, string $certificateNumber): string
    {
        $content = "Certificado de finalización\nNúmero: {$certificateNumber}\nUsuario: {$user->name}\nCurso: {$course->title}\nFecha: ".$issuedAt->toDateString()."\n";
        $path = 'certificates/certificate_'.$this->purchase->id.'.txt';
        Storage::disk('local')->put($path, $content);

        return $path;
    }
}
