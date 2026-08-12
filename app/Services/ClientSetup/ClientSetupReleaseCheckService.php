<?php

namespace App\Services\ClientSetup;

use App\Services\ClientSetup\Contracts\ClientSetupMigrationImporter;
use App\Services\ClientSetup\Contracts\ClientSetupScanner;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClientSetupReleaseCheckService
{
    public function check(bool $strict = false, ?bool $production = null): array
    {
        $production ??= app()->environment('production');
        $checks = [
            $this->applicationUrl($production),
            $this->mail($production),
            $this->queue($production),
            $this->privateDocumentsDisk($production),
            $this->tables(),
            $this->templates(),
            $this->rateLimits(),
            $this->crmPermissions(),
            $this->scanner($production),
            $this->importer($production),
            $this->backup($production),
            $this->pilot($production),
        ];

        $failures = collect($checks)->where('status', 'fail')->count();
        $warnings = collect($checks)->where('status', 'warn')->count();

        return [
            'production' => $production,
            'strict' => $strict,
            'passed' => $failures === 0 && (! $strict || $warnings === 0),
            'failures' => $failures,
            'warnings' => $warnings,
            'checks' => $checks,
        ];
    }

    private function applicationUrl(bool $production): array
    {
        $url = (string) config('app.url');
        $valid = filter_var($url, FILTER_VALIDATE_URL) !== false;
        $https = Str::startsWith(Str::lower($url), 'https://');
        $localHost = in_array(parse_url($url, PHP_URL_HOST), ['localhost', '127.0.0.1'], true);

        if ($production && (! $valid || ! $https || $localHost)) {
            return $this->fail('application_url', 'Production requires a valid HTTPS APP_URL on the intended public domain.');
        }

        return $valid
            ? $this->pass('application_url', 'Application URL is configured.')
            : $this->warn('application_url', 'APP_URL is not configured for this environment.');
    }

    private function mail(bool $production): array
    {
        $mailer = (string) config('mail.default');
        $from = (string) config('mail.from.address');
        $nonDeliveryMailer = in_array($mailer, ['array', 'log', 'null'], true);

        if ($production && ($nonDeliveryMailer || ! filter_var($from, FILTER_VALIDATE_EMAIL))) {
            return $this->fail('mail', 'Production mail must use a delivery-capable mailer and valid MAIL_FROM_ADDRESS.');
        }

        return $nonDeliveryMailer
            ? $this->warn('mail', 'This environment uses a non-delivery mailer.')
            : $this->pass('mail', 'Mail delivery configuration is present.');
    }

    private function queue(bool $production): array
    {
        $queue = (string) config('queue.default');

        if ($production && $queue === 'sync') {
            return $this->fail('queue', 'Production must use a durable queue worker for notifications and retries.');
        }

        return $queue === 'sync'
            ? $this->warn('queue', 'Queue is synchronous in this environment.')
            : $this->pass('queue', 'A durable queue connection is configured.');
    }

    private function privateDocumentsDisk(bool $production): array
    {
        $disk = config('filesystems.disks.documents', []);
        $private = is_array($disk) && ($disk['visibility'] ?? null) !== 'public';
        $root = is_array($disk) ? ($disk['root'] ?? null) : null;
        $rootReady = is_string($root) && is_dir($root) && is_writable($root);

        if ($production && (! $private || ! $rootReady)) {
            return $this->fail('private_documents', 'The documents disk must be private and its storage root must be available.');
        }

        return $private && $rootReady
            ? $this->pass('private_documents', 'Private documents storage is configured.')
            : $this->warn('private_documents', 'Private document storage still needs environment verification.');
    }

    private function tables(): array
    {
        $missing = collect(config('client_setup.release.required_tables', []))
            ->reject(fn (string $table): bool => Schema::hasTable($table))
            ->values()
            ->all();

        return $missing === []
            ? $this->pass('database_schema', 'All client-setup tables are present.')
            : $this->fail('database_schema', 'Missing client-setup tables: ' . implode(', ', $missing) . '.');
    }

    private function templates(): array
    {
        $missing = [];

        foreach (array_keys(config('client_setup.migration_templates', [])) as $kind) {
            try {
                app(ClientSetupMigrationService::class)->templatePath($kind);

                $currentVersion = (string) config('client_setup.template_version', '1.0');
                $versionDefinitions = config("client_setup.migration_template_compatibility.{$kind}.versions", []);
                $versionDefinition = is_array($versionDefinitions) ? ($versionDefinitions[$currentVersion] ?? null) : null;

                if (! is_array($versionDefinition) || empty($versionDefinition['headings'])) {
                    $missing[] = $kind . ' compatibility manifest for v' . $currentVersion;
                }
            } catch (\Throwable) {
                $missing[] = $kind;
            }
        }

        return $missing === []
            ? $this->pass('templates', 'All configured migration templates are present.')
            : $this->fail('templates', 'Missing migration templates: ' . implode(', ', $missing) . '.');
    }

    private function rateLimits(): array
    {
        $values = [
            config('client_setup.verification_max_attempts'),
            config('client_setup.verification_code_resend_cooldown_seconds'),
            config('client_setup.invitation_expires_days'),
        ];

        return collect($values)->every(fn ($value): bool => is_numeric($value) && (int) $value > 0)
            ? $this->pass('rate_limits', 'Invitation expiry and verification limits are positive.')
            : $this->fail('rate_limits', 'Invitation or verification rate-limit configuration is invalid.');
    }

    private function crmPermissions(): array
    {
        $module = config('heritage_crm.modules.client_setup', []);
        $matches = collect($module['match'] ?? []);
        $required = [
            'crm.client-setup.index',
            'crm.client-setup.show',
            'crm.client-setup.migration-uploads.validation-report',
            'crm.client-setup.revisions.compare',
            'crm.client-setup.migration-uploads.approve',
            'crm.client-setup.migration-uploads.import',
        ];

        $routePermissions = collect($module['route_permissions'] ?? [])
            ->pluck('match')
            ->flatten();

        return $matches->contains('crm.client-setup.*') && collect($required)->every(
            fn (string $route): bool => $routePermissions->contains($route)
        )
            ? $this->pass('crm_permissions', 'Client Setup CRM module and route patterns are registered.')
            : $this->warn('crm_permissions', 'Review Client Setup CRM route permission coverage.');
    }

    private function scanner(bool $production): array
    {
        $adapter = config('client_setup.release.scanner_adapter');

        if ($production && (! is_string($adapter) || $adapter === '' || ! class_exists($adapter) || ! is_a($adapter, ClientSetupScanner::class, true))) {
            return $this->fail('scanner', 'No production malware-scanner adapter is registered; manual approval is not a release substitute.');
        }

        return is_string($adapter) && $adapter !== '' && class_exists($adapter) && is_a($adapter, ClientSetupScanner::class, true)
            ? $this->pass('scanner', 'A scanner adapter class is registered.')
            : $this->warn('scanner', 'Scanner adapter is not configured in this environment.');
    }

    private function importer(bool $production): array
    {
        $adapter = config('client_setup.release.importer_adapter');

        if ($production && (! is_string($adapter) || $adapter === '' || ! class_exists($adapter) || ! is_a($adapter, ClientSetupMigrationImporter::class, true))) {
            return $this->fail('importer', 'No production staff/student importer adapter is registered.');
        }

        return is_string($adapter) && $adapter !== '' && class_exists($adapter) && is_a($adapter, ClientSetupMigrationImporter::class, true)
            ? $this->pass('importer', 'A production importer adapter class is registered.')
            : $this->warn('importer', 'Production import remains disabled until an approved adapter is registered.');
    }

    private function backup(bool $production): array
    {
        $verified = (bool) config('client_setup.release.backup_verified', false);

        if ($production && ! $verified) {
            return $this->fail('backup', 'A current database and private-document backup checkpoint is not marked verified.');
        }

        return $verified
            ? $this->pass('backup', 'Backup checkpoint is marked verified.')
            : $this->warn('backup', 'Backup verification is an environment release step.');
    }

    private function pilot(bool $production): array
    {
        $approved = (bool) config('client_setup.release.pilot_approved', false);

        if ($production && ! $approved) {
            return $this->fail('pilot', 'The pilot approval gate is not marked complete.');
        }

        return $approved
            ? $this->pass('pilot', 'Pilot approval is marked complete.')
            : $this->warn('pilot', 'Pilot approval is still outstanding.');
    }

    private function pass(string $key, string $message): array
    {
        return ['key' => $key, 'status' => 'pass', 'message' => $message];
    }

    private function warn(string $key, string $message): array
    {
        return ['key' => $key, 'status' => 'warn', 'message' => $message];
    }

    private function fail(string $key, string $message): array
    {
        return ['key' => $key, 'status' => 'fail', 'message' => $message];
    }
}
