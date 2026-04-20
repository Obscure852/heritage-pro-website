<?php

namespace App\Services\Messaging;

use App\Models\AccountBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SmsBalanceService
 *
 * Handles all SMS balance operations with proper pessimistic locking
 * to prevent race conditions when multiple requests are processed concurrently.
 */
class SmsBalanceService
{
    protected SmsCostCalculator $costCalculator;

    public function __construct(SmsCostCalculator $costCalculator)
    {
        $this->costCalculator = $costCalculator;
    }

    /**
     * Reserve balance for a pending SMS job
     *
     * This uses pessimistic locking to prevent race conditions.
     * The reserved amount is held in pending_amount until confirmed or released.
     *
     * @param float $amount Amount to reserve in BWP
     * @return bool True if reservation successful, false if insufficient balance
     * @throws \Exception On database errors
     */
    public function reserveBalance(float $amount): bool
    {
        return DB::transaction(function () use ($amount) {
            // Lock the row for update to prevent concurrent modifications
            $balance = AccountBalance::lockForUpdate()->first();

            if (!$balance) {
                Log::error('SmsBalanceService: No account balance record found');
                throw new \Exception('No SMS account balance found. Please contact administrator.');
            }

            // Check available balance (current balance minus already reserved amounts)
            $availableBalance = $balance->balance_bwp - ($balance->pending_amount ?? 0);

            if ($availableBalance < $amount) {
                Log::warning('SmsBalanceService: Insufficient balance for reservation', [
                    'requested' => $amount,
                    'available' => $availableBalance,
                    'current_balance' => $balance->balance_bwp,
                    'pending_amount' => $balance->pending_amount ?? 0,
                ]);
                return false;
            }

            // Reserve the amount
            $balance->pending_amount = ($balance->pending_amount ?? 0) + $amount;
            $balance->save();

            Log::info('SmsBalanceService: Balance reserved', [
                'amount' => $amount,
                'new_pending' => $balance->pending_amount,
            ]);

            return true;
        });
    }

    /**
     * Confirm a reservation and deduct from actual balance
     *
     * Call this when the SMS job completes successfully.
     *
     * @param float $amount Amount to confirm in BWP
     * @return void
     * @throws \Exception On database errors or if reservation doesn't exist
     */
    public function confirmDeduction(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            $balance = AccountBalance::lockForUpdate()->first();

            if (!$balance) {
                throw new \Exception('No SMS account balance found.');
            }

            // Move from pending to actual deduction
            $balance->pending_amount = max(0, ($balance->pending_amount ?? 0) - $amount);
            $balance->balance_bwp -= $amount;
            $balance->amount_used_bwp += $amount;
            $balance->save();

            Log::info('SmsBalanceService: Deduction confirmed', [
                'amount' => $amount,
                'new_balance' => $balance->balance_bwp,
                'total_used' => $balance->amount_used_bwp,
            ]);
        });
    }

    /**
     * Release a reservation without deducting
     *
     * Call this when an SMS job fails or is cancelled.
     *
     * @param float $amount Amount to release in BWP
     * @return void
     */
    public function releaseReservation(float $amount): void
    {
        DB::transaction(function () use ($amount) {
            $balance = AccountBalance::lockForUpdate()->first();

            if (!$balance) {
                Log::warning('SmsBalanceService: Cannot release - no balance record');
                return;
            }

            $balance->pending_amount = max(0, ($balance->pending_amount ?? 0) - $amount);
            $balance->save();

            Log::info('SmsBalanceService: Reservation released', [
                'amount' => $amount,
                'new_pending' => $balance->pending_amount,
            ]);
        });
    }

    /**
     * Get current available balance (excluding pending reservations)
     *
     * @return float Available balance in BWP
     */
    public function getAvailableBalance(): float
    {
        $balance = AccountBalance::first();

        if (!$balance) {
            return 0.0;
        }

        return $balance->balance_bwp - ($balance->pending_amount ?? 0);
    }

    /**
     * Get current balance info
     *
     * @return array [balance_bwp, pending_amount, available_balance, package_type]
     */
    public function getBalanceInfo(): array
    {
        $balance = AccountBalance::first();

        if (!$balance) {
            return [
                'balance_bwp' => 0,
                'pending_amount' => 0,
                'available_balance' => 0,
                'package_type' => 'None',
                'amount_used_bwp' => 0,
            ];
        }

        $pendingAmount = $balance->pending_amount ?? 0;

        return [
            'balance_bwp' => $balance->balance_bwp,
            'pending_amount' => $pendingAmount,
            'available_balance' => $balance->balance_bwp - $pendingAmount,
            'package_type' => $balance->sms_credits_package ?? 'Basic',
            'amount_used_bwp' => $balance->amount_used_bwp,
        ];
    }

    /**
     * Check if there's sufficient balance for an operation
     *
     * @param float $amount Required amount in BWP
     * @return bool True if sufficient balance available
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->getAvailableBalance() >= $amount;
    }

    /**
     * Check sufficient balance and throw exception if not
     *
     * @param float $amount Required amount in BWP
     * @throws \Exception If insufficient balance
     */
    public function requireSufficientBalance(float $amount): void
    {
        $available = $this->getAvailableBalance();

        if ($available < $amount) {
            throw new \Exception(
                "Insufficient SMS balance. Required: BWP " . number_format($amount, 2) .
                ", Available: BWP " . number_format($available, 2)
            );
        }
    }
}
