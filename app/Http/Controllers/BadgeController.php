<?php

namespace App\Http\Controllers;

use App\Models\Badge;

class BadgeController extends Controller
{
    public function show(Badge $badge)
    {
        $this->authorize('view', $badge);

        return view('badges.show', compact('badge'));
    }

    public function share(Badge $badge)
    {
        $this->authorize('view', $badge);

        // Simple share page with social links
        return view('badges.share', compact('badge'));
    }
}
