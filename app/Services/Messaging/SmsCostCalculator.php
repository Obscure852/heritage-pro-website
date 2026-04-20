<?php

namespace App\Services\Messaging;

use App\Models\AccountBalance;
use App\Models\SMSApiSetting;
use Illuminate\Support\Facades\Cache;

/**
 * SmsCostCalculator
 *
 * Centralized service for all SMS pricing calculations.
 * Reads rates from the s_m_s_api_settings table.
 * This is the ONLY place where SMS costs should be calculated.
 */
class SmsCostCalculator
{
    /**
     * Cache key for package rates
     */
    protected const RATES_CACHE_KEY = 'sms_package_rates';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected const RATES_CACHE_TTL = 3600;

    /**
     * Default rates if database values are not found
     */
    protected const DEFAULT_RATES = [
        'Basic' => 0.35,
        'Standard' => 0.30,
        'Premium' => 0.25,
    ];

    /**
     * Get all package rates from database
     *
     * @return array [Basic => rate, Standard => rate, Premium => rate]
     */
    public function getPackageRates(): array
    {
        return Cache::remember(self::RATES_CACHE_KEY, self::RATES_CACHE_TTL, function () {
            $rates = [];

            // Fetch rates from database
            $basicRate = SMSApiSetting::where('key', 'sms_rate_basic')->first();
            $standardRate = SMSApiSetting::where('key', 'sms_rate_standard')->first();
            $premiumRate = SMSApiSetting::where('key', 'sms_rate_premium')->first();

            $rates['Basic'] = $basicRate ? (float) $basicRate->value : self::DEFAULT_RATES['Basic'];
            $rates['Standard'] = $standardRate ? (float) $standardRate->value : self::DEFAULT_RATES['Standard'];
            $rates['Premium'] = $premiumRate ? (float) $premiumRate->value : self::DEFAULT_RATES['Premium'];

            return $rates;
        });
    }

    /**
     * Get rate for a specific package type
     *
     * @param string|null $packageType Package type (Basic, Standard, Premium)
     * @return float Cost per SMS unit in BWP
     */
    public function getRateForPackage(?string $packageType): float
    {
        $packageType = $packageType ?? 'Basic';
        $rates = $this->getPackageRates();

        return $rates[$packageType] ?? self::DEFAULT_RATES['Basic'];
    }

    /**
     * Get the cost per SMS unit based on current account package
     *
     * @return float Cost per SMS unit in BWP
     */
    public function getCostPerUnit(): float
    {
        $balance = AccountBalance::first();
        $packageType = optional($balance)->sms_credits_package ?? 'Basic';

        return $this->getRateForPackage($packageType);
    }

    /**
     * Calculate total cost for sending SMS
     *
     * @param int $recipients Number of recipients
     * @param int $smsUnits Number of SMS units per message (based on character count)
     * @return float Total cost in BWP
     */
    public function calculateTotalCost(int $recipients, int $smsUnits): float
    {
        return $recipients * $smsUnits * $this->getCostPerUnit();
    }

    /**
     * Calculate SMS units needed for a message
     *
     * @param string $message The SMS message text
     * @param int $charactersPerUnit Characters per SMS unit (default 160)
     * @return int Number of SMS units
     */
    public function calculateSmsUnits(string $message, int $charactersPerUnit = 160): int
    {
        return (int) ceil(strlen($message) / $charactersPerUnit);
    }

    /**
     * Get full cost breakdown for a bulk SMS operation
     *
     * @param string $message The SMS message text
     * @param int $recipientCount Number of recipients
     * @return array [sms_units, cost_per_unit, total_units, total_cost]
     */
    public function getFullCostBreakdown(string $message, int $recipientCount): array
    {
        $smsUnits = $this->calculateSmsUnits($message);
        $costPerUnit = $this->getCostPerUnit();
        $totalUnits = $smsUnits * $recipientCount;
        $totalCost = $totalUnits * $costPerUnit;

        return [
            'sms_units' => $smsUnits,
            'cost_per_unit' => $costPerUnit,
            'total_units' => $totalUnits,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Get the current package type
     *
     * @return string Package type (Basic, Standard, Premium)
     */
    public function getCurrentPackageType(): string
    {
        $balance = AccountBalance::first();
        return optional($balance)->sms_credits_package ?? 'Basic';
    }

    /**
     * Clear the rates cache (call this when rates are updated in settings)
     *
     * @return void
     */
    public function clearRatesCache(): void
    {
        Cache::forget(self::RATES_CACHE_KEY);
    }
}
