<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateController extends Controller
{
    /**
     * Certificates carry the dentist's name, so they are only served to their owner
     * (or an admin), never from a public URL.
     */
    public function show(Request $request, Certificate $certificate): StreamedResponse
    {
        $user = $request->user();

        abort_unless($certificate->user_id === $user->id || $user->is_admin, 403);

        $storage = $certificate->fileStorage();

        abort_unless($storage->exists($certificate->certificate_path), 404);

        $extension = pathinfo($certificate->certificate_path, PATHINFO_EXTENSION);
        $fileName = 'Certificado - '.Str::slug($certificate->course->title).'.'.$extension;

        return $storage->response($certificate->certificate_path, $fileName);
    }
}
