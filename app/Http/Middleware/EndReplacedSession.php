<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

/**
 * Ends this session once the account signs in on another device
 * (see AuthenticatedSessionController::store) and tells the user why.
 * It is a no-op for guests.
 */
class EndReplacedSession extends AuthenticateSession
{
    protected function redirectTo(Request $request): string
    {
        $request->session()->flash('warning', __('app.session_replaced'));

        return route('login');
    }
}
