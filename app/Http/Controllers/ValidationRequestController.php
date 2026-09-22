<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessValidationRequest;
use App\Models\ValidationRequest;
use Illuminate\Http\Request;

class ValidationRequestController extends Controller
{
    public function create()
    {
        return view('validation.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'diploma' => 'required|file|mimes:pdf,jpg,jpeg,png',
            'id_document' => 'required|file|mimes:pdf,jpg,jpeg,png',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $diplomaPath = $request->file('diploma')->store('validations', 'public');
        $idPath = $request->file('id_document')->store('validations', 'public');

        $vr = ValidationRequest::create([
            'user_id' => $request->user()->id,
            'course_id' => $request->input('course_id'),
            'diploma_path' => $diplomaPath,
            'id_document_path' => $idPath,
            'status' => 'pending',
        ]);

        // Dispatch AI validation job
        ProcessValidationRequest::dispatch($vr);

        return redirect()->route('profile')->with('status', 'Solicitud enviada. Se procesará automáticamente.');
    }
}
