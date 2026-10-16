<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        // An unknown email gets the same answer as a known one, so the form can't be used
        // to find out who has an account
        return in_array($status, [Password::RESET_LINK_SENT, Password::INVALID_USER], true)
            ? back()->with('status', __(Password::RESET_LINK_SENT))
            : back()->withErrors(['email' => __($status)]);
    }
}
