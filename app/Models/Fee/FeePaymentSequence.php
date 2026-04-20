<?php

namespace App\Models\Fee;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FeePaymentSequence extends Model
{
    protected $table = 'fee_payment_sequences';
    protected $primaryKey = 'year';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'year',
        'last_invoice_sequence',
        'last_receipt_sequence',
        'last_refund_sequence',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_invoice_sequence' => 'integer',
        'last_receipt_sequence' => 'integer',
        'last_refund_sequence' => 'integer',
    ];

    /**
     * Get the next invoice number with database locking to prevent race conditions.
     */
    public static function getNextInvoiceNumber(int $year): string
    {
        return DB::transaction(function () use ($year) {
            $sequence = self::lockForUpdate()->find($year);

            if (!$sequence) {
                $sequence = self::create([
                    'year' => $year,
                    'last_invoice_sequence' => 0,
                    'last_receipt_sequence' => 0,
                ]);
            }

            $sequence->last_invoice_sequence++;
            $sequence->updated_at = now();
            $sequence->save();

            return sprintf('INV-%d-%04d', $year, $sequence->last_invoice_sequence);
        });
    }

    /**
     * Get the next receipt number with database locking to prevent race conditions.
     */
    public static function getNextReceiptNumber(int $year): string
    {
        return DB::transaction(function () use ($year) {
            $sequence = self::lockForUpdate()->find($year);

            if (!$sequence) {
                $sequence = self::create([
                    'year' => $year,
                    'last_invoice_sequence' => 0,
                    'last_receipt_sequence' => 0,
                ]);
            }

            $sequence->last_receipt_sequence++;
            $sequence->updated_at = now();
            $sequence->save();

            return sprintf('RCP-%d-%04d', $year, $sequence->last_receipt_sequence);
        });
    }

    /**
     * Get the next refund number with database locking to prevent race conditions.
     */
    public static function getNextRefundNumber(int $year): string
    {
        return DB::transaction(function () use ($year) {
            $sequence = self::lockForUpdate()->find($year);

            if (!$sequence) {
                $sequence = self::create([
                    'year' => $year,
                    'last_invoice_sequence' => 0,
                    'last_receipt_sequence' => 0,
                    'last_refund_sequence' => 0,
                ]);
            }

            $sequence->last_refund_sequence++;
            $sequence->updated_at = now();
            $sequence->save();

            return sprintf('REF-%d-%04d', $year, $sequence->last_refund_sequence);
        });
    }
}
