<?php

use App\Models\SMSApiSetting;

if (!function_exists('format_currency')) {
    /**
     * Format an amount with the configured currency symbol
     *
     * @param float|int|string $amount The amount to format
     * @param int $decimals Number of decimal places
     * @return string Formatted currency string
     */
    function format_currency($amount, int $decimals = 2): string
    {
        $symbol = get_currency_symbol();
        $position = SMSApiSetting::where('key', 'fee.currency_position')->value('value') ?? 'before';
        $formatted = number_format((float) $amount, $decimals);

        return $position === 'before'
            ? $symbol . ' ' . $formatted
            : $formatted . ' ' . $symbol;
    }
}

if (!function_exists('get_currency_symbol')) {
    /**
     * Get the configured currency symbol
     *
     * @return string Currency symbol
     */
    function get_currency_symbol(): string
    {
        return SMSApiSetting::where('key', 'fee.currency_symbol')->value('value') ?? 'P';
    }
}

if (!function_exists('get_currency_code')) {
    /**
     * Get the configured currency code (e.g., BWP, USD)
     *
     * @return string Currency code
     */
    function get_currency_code(): string
    {
        return SMSApiSetting::where('key', 'fee.currency_code')->value('value') ?? 'BWP';
    }
}
