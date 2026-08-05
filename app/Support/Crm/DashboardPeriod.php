<?php

namespace App\Support\Crm;

use Carbon\CarbonImmutable;

/**
 * A reporting window for the CRM dashboard, paired with the equally long window
 * immediately before it so every period metric can carry a comparison.
 */
final class DashboardPeriod
{
    public const DEFAULT_KEY = '30d';

    private function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly CarbonImmutable $start,
        public readonly CarbonImmutable $end,
        public readonly CarbonImmutable $previousStart,
        public readonly CarbonImmutable $previousEnd
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array {
        return [
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            'quarter' => 'This quarter',
            'ytd' => 'Year to date',
        ];
    }

    public static function fromKey(?string $key): self {
        $key = is_string($key) && array_key_exists($key, self::options())
            ? $key
            : self::DEFAULT_KEY;

        $now = CarbonImmutable::now();
        $end = $now;

        $start = match ($key) {
            '7d' => $now->startOfDay()->subDays(6),
            'quarter' => $now->startOfQuarter(),
            'ytd' => $now->startOfYear(),
            default => $now->startOfDay()->subDays(29),
        };

        // The comparison window is the same length, ending where this one begins.
        $length = $start->diffInSeconds($end);

        return new self(
            $key,
            self::options()[$key],
            $start,
            $end,
            $start->subSeconds($length),
            $start
        );
    }

    public function isDefault(): bool {
        return $this->key === self::DEFAULT_KEY;
    }

    public function rangeLabel(): string {
        return $this->start->format('d M') . ' – ' . $this->end->format('d M Y');
    }
}
