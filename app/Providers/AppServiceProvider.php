<?php

namespace App\Providers;

use App\Models\CrmCommercialSetting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider {
    public function register() {
        //
    }

    public function boot() {
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        View::composer(['layouts.app', 'layouts.auth', 'layouts.crm-topbar', 'auth.*', 'crm.onboarding.*'], function ($view) {
            $settings = null;

            if (Schema::hasTable('crm_commercial_settings')) {
                $settings = CrmCommercialSetting::query()->first();
            }

            $view->with('crmBrandingSettings', $settings);
        });

        $this->registerPasswordResetMailView();
    }

    private function registerPasswordResetMailView(): void {
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $settings = Schema::hasTable('crm_commercial_settings')
                ? CrmCommercialSetting::query()->first()
                : null;

            $companyName = $settings?->company_name ?: config('app.name', 'Heritage Pro');
            $companyWebsite = $this->normalizeWebsiteUrl($settings?->company_website);

            return (new MailMessage)
                ->subject('Create or reset your password for ' . $companyName)
                ->view([
                    'html' => 'emails.auth.reset-password',
                    'text' => 'emails.auth.reset-password-text',
                ], [
                    'companyName' => $companyName,
                    'resetUrl' => url(route('password.reset', [
                        'token' => $token,
                        'email' => $notifiable->getEmailForPasswordReset(),
                    ], false)),
                    'expireMinutes' => (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire'),
                    'recipientName' => trim((string) ($notifiable->name ?? '')),
                    'recipientEmail' => $notifiable->getEmailForPasswordReset(),
                    'companyEmail' => $settings?->company_email,
                    'companyPhone' => $settings?->company_phone,
                    'companyWebsiteUrl' => $companyWebsite,
                    'companyWebsiteLabel' => $settings?->company_website ?: $companyWebsite,
                ]);
        });
    }

    private function normalizeWebsiteUrl(?string $website): ?string {
        if (blank($website)) {
            return null;
        }

        return Str::startsWith($website, ['http://', 'https://'])
            ? $website
            : 'https://' . ltrim($website, '/');
    }
}
