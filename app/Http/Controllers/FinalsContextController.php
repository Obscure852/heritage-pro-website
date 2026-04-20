<?php

namespace App\Http\Controllers;

use App\Services\SchoolModeResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinalsContextController extends Controller {
    public function __construct(private readonly SchoolModeResolver $schoolModeResolver) {
        $this->middleware('auth');
    }

    /**
     * Persist the user's selected Finals context (Junior/Senior) in session
     * and redirect back to the page they came from. Mirrors the assessment
     * gradebook context switching pattern.
     */
    public function switch(Request $request, string $context): RedirectResponse {
        $resolved = $this->schoolModeResolver->resolveFinalsContext($context);

        if ($resolved === null) {
            abort(404);
        }

        session([SchoolModeResolver::FINALS_CONTEXT_SESSION_KEY => $resolved]);

        $redirectTo = $request->query('redirect');

        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect()->to($redirectTo);
        }

        return back();
    }
}
