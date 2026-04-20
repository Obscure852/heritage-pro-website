<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model{
    use HasFactory;
    protected $table = 'account_balances';

    protected $fillable = [
        'sms_credits_package',
        'package_amount',
        'amount_used_bwp',
        'balance_bwp',
        'pending_amount'
    ];
}