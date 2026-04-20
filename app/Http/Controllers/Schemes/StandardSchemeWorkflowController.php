<?php

namespace App\Http\Controllers\Schemes;

use App\Http\Controllers\Controller;
use App\Models\Schemes\StandardScheme;
use App\Services\Schemes\StandardSchemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class StandardSchemeWorkflowController extends Controller {

    public function submit(Request $request, StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('submit', $standardScheme);

        try {
            $service->submitScheme($standardScheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', 'Standard scheme submitted for review.');
    }

    public function placeUnderReview(Request $request, StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('review', $standardScheme);

        try {
            $service->placeUnderReview($standardScheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', 'Standard scheme placed under review.');
    }

    public function approve(Request $request, StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('review', $standardScheme);

        try {
            $service->approveScheme($standardScheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', 'Standard scheme approved.');
    }

    public function returnForRevision(Request $request, StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('review', $standardScheme);

        $request->validate([
            'comments' => ['required', 'string', 'min:5'],
        ]);

        try {
            $service->returnForRevision($standardScheme, auth()->user(), $request->input('comments'));
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', 'Standard scheme returned for revision.');
    }

    public function publish(StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('publish', $standardScheme);

        try {
            $count = $service->publishScheme($standardScheme, auth()->user());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', "Standard scheme published and distributed to {$count} teacher(s).");
    }

    public function distribute(StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('publish', $standardScheme);

        try {
            $count = $service->distributeToTeachers($standardScheme, auth()->user());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', "Standard scheme distributed to {$count} teacher(s).");
    }

    public function unpublish(StandardScheme $standardScheme, StandardSchemeService $service): RedirectResponse {
        $this->authorize('unpublish', $standardScheme);

        try {
            $service->unpublishScheme($standardScheme, auth()->user());
        } catch (InvalidArgumentException $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('standard-schemes.show', $standardScheme)
            ->with('success', 'Standard scheme unpublished.');
    }
}
