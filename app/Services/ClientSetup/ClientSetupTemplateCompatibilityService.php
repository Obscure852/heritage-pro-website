<?php

namespace App\Services\ClientSetup;

use Illuminate\Support\Str;

class ClientSetupTemplateCompatibilityService
{
    public function inspect(string $kind, array $headers): array
    {
        $definitions = config("client_setup.migration_template_compatibility.{$kind}.versions", []);
        $normalizedHeaders = $this->normalize($headers);
        $fingerprint = $this->makeFingerprint($normalizedHeaders);

        foreach ($definitions as $version => $definition) {
            $expected = $this->normalize($definition['headings'] ?? []);

            if ($normalizedHeaders !== $expected) {
                continue;
            }

            $current = (string) config('client_setup.template_version', '1.0');
            $isCurrent = (string) $version === $current;
            $legacyAllowed = (bool) config("client_setup.migration_template_compatibility.{$kind}.allow_legacy", false);

            if (! $isCurrent && ! $legacyAllowed) {
                return [
                    'status' => 'legacy_incompatible',
                    'matched_version' => (string) $version,
                    'fingerprint' => $fingerprint,
                    'errors' => [[
                        'row' => 1,
                        'messages' => ["Template version {$version} is recognized but is not accepted for new imports. Download version {$current}."],
                    ]],
                ];
            }

            return [
                'status' => $isCurrent ? 'compatible' : 'legacy_compatible',
                'matched_version' => (string) $version,
                'fingerprint' => $fingerprint,
                'errors' => [],
            ];
        }

        $currentDefinition = $definitions[(string) config('client_setup.template_version', '1.0')] ?? [];
        $expected = $this->normalize($currentDefinition['headings'] ?? []);
        $missing = array_values(array_diff($expected, $normalizedHeaders));
        $unexpected = array_values(array_diff($normalizedHeaders, $expected));
        $messages = [];

        if ($missing !== []) {
            $messages[] = 'Missing columns: ' . implode(', ', $missing) . '.';
        }
        if ($unexpected !== []) {
            $messages[] = 'Unexpected columns: ' . implode(', ', $unexpected) . '.';
        }
        if ($messages === []) {
            $messages[] = 'Column order or duplicate columns does not match a supported template version.';
        }

        return [
            'status' => 'incompatible',
            'matched_version' => null,
            'fingerprint' => $fingerprint,
            'errors' => [['row' => 1, 'messages' => $messages]],
        ];
    }

    public function fingerprint(array $headers): string
    {
        return $this->makeFingerprint($this->normalize($headers));
    }

    private function normalize(array $headers): array
    {
        return array_map(
            fn ($header): string => Str::lower(trim((string) $header)),
            array_values($headers)
        );
    }

    private function makeFingerprint(array $headers): string
    {
        return hash('sha256', implode('|', $headers));
    }
}
