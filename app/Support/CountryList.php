<?php

namespace App\Support;

class CountryList
{
    public static function all(): array
    {
        static $countries = null;

        if ($countries !== null) {
            return $countries;
        }

        $path = resource_path('data/countries.json');
        $decoded = json_decode((string) file_get_contents($path), true);

        $countries = is_array($decoded)
            ? array_values(array_filter($decoded, fn ($country) => is_array($country) && filled($country['name'] ?? null)))
            : [];

        usort($countries, fn ($first, $second) => strcmp((string) $first['name'], (string) $second['name']));

        return $countries;
    }

    public static function names(): array
    {
        return array_values(array_map(
            fn (array $country) => (string) $country['name'],
            self::all()
        ));
    }

    public static function normalizeName(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        foreach (self::all() as $country) {
            if (strcasecmp((string) $country['code'], $value) === 0 || strcasecmp((string) $country['name'], $value) === 0) {
                return (string) $country['name'];
            }
        }

        return $value;
    }
}
