<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $purchases = $user->purchases()->with('course')->latest()->get();
        $certificates = $user->certificates()->with('course')->latest()->get();
        $badges = $user->badges()->with('course')->latest()->get();
        $latestValidation = $user->validationRequests()->latest()->first();

        return view('profile.index', compact('user', 'purchases', 'certificates', 'badges', 'latestValidation'));
    }
}
