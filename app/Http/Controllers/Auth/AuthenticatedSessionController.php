<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // One active session per account: signing in here ends the session on any other device,
        // so paid course access cannot be shared by handing out the login.
        Auth::logoutOtherDevices($request->input('password'));

        // The guard stored the pre-rotation hash at login; refresh it or this device would log itself out too
        $request->session()->put(
            'password_hash_'.Auth::getDefaultDriver(),
            Auth::guard('web')->hashPasswordForCookie($request->user()->getAuthPassword()),
        );

        $destination = $request->user()->is_admin ? route('admin.dashboard') : route('profile');

        return redirect()->intended($destination);
    }

    /**
     * Heartbeat for long-lived pages such as the lesson player: AuthenticateSession answers
     * 401 here once the account has signed in on another device.
     */
    public function ping(): Response
    {
        return response()->noContent();
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
