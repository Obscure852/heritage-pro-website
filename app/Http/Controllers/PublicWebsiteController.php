<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookDemoRequest;
use App\Mail\BookDemoInquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublicWebsiteController extends Controller
{
    public function home(): View
    {
        return $this->renderPage('home');
    }

    public function page(string $page): View
    {
        abort_unless(in_array($page, $this->supportedPages(), true), 404);

        return $this->renderPage($page);
    }

    public function bookDemo(BookDemoRequest $request): RedirectResponse
    {
        $submission = $request->validated();
        $recipientAddress = (string) config('mail.demo_recipient.address');
        $recipientName = (string) config('mail.demo_recipient.name');
        $previousUrl = strtok(url()->previous(), '#') ?: route('website.home');

        try {
            Mail::to($recipientName !== '' ? [$recipientAddress => $recipientName] : $recipientAddress)
                ->send(new BookDemoInquiry($submission));
        } catch (Throwable $exception) {
            Log::error('Website demo request email failed.', [
                'exception' => $exception,
                'work_email' => $submission['work_email'],
                'institution' => $submission['institution'],
            ]);

            return redirect($previousUrl . '#contact')
                ->withInput()
                ->with('book_demo_error', 'Your request could not be sent right now. Please try again shortly.');
        }

        return redirect($previousUrl . '#contact')
            ->with('book_demo_success', 'Your demo request has been sent. We will respond within one business day.');
    }

    private function renderPage(string $page): View
    {
        $site = config('heritage_website');
        $pageConfig = $site['pages'][$page] ?? [];

        return view('website.pages.' . $page, [
            'page' => $page,
            'pageConfig' => $pageConfig,
            'pageTitle' => $pageConfig['title'] ?? $site['meta']['default_title'],
            'site' => $site,
        ]);
    }

    private function supportedPages(): array
    {
        return ['products', 'features', 'customers', 'pricing', 'about', 'faq', 'team'];
    }
}
