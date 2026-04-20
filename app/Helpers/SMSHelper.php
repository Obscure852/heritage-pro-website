<?php

namespace App\Helpers;

use App\Models\AccountBalance;
use App\Models\Message;
use App\Services\Messaging\SmsCostCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SMSHelper{

    /**
     * Get package rates from database via SmsCostCalculator
     *
     * @return array [Basic => rate, Standard => rate, Premium => rate]
     */
    public static function getPackageRates(){
        return app(SmsCostCalculator::class)->getPackageRates();
    }

    public static function processMessageResponse($response, $httpCode, $messageDetails){
        $responseBody = json_decode($response, true);
        self::logResponseStructure($responseBody);
        if ($httpCode != 200) {
            Log::error('HTTP error sending SMS via Link SMS: ' . $httpCode);
            return false;
        }

        try {
            return self::processSuccessfulResponse($messageDetails);
        } catch (\Exception $e) {
            self::logError($e);
            throw $e;
        }
    }

    private static function processSuccessfulResponse($details){
        $accountBalance = self::validateAccountBalance();
        $priceInBWP = self::getPackageRate($accountBalance->sms_credits_package);
        $totalCost = self::calculateTotalCost($priceInBWP, $details['smsCount'], $details['numRecipients']);
        
        self::validateSufficientBalance($accountBalance, $totalCost);
        $message = self::createMessageRecord($details, $priceInBWP);
        self::updateAccountBalance($accountBalance, $totalCost);
        
        return $message;
    }

    private static function validateAccountBalance(){
        $accountBalance = AccountBalance::first();
        if (!$accountBalance) {
            throw new \Exception('No SMS package found. Please purchase a package first.');
        }
        return $accountBalance;
    }

    /**
     * Get rate for a specific package type
     *
     * @param string|null $packageType Package type (Basic, Standard, Premium)
     * @return float Cost per SMS unit in BWP
     */
    public static function getPackageRate($packageType = null){
        return app(SmsCostCalculator::class)->getRateForPackage($packageType);
    }

    private static function calculateTotalCost($priceInBWP, $smsCount, $numRecipients){
        return $priceInBWP * $smsCount * $numRecipients;
    }

    private static function validateSufficientBalance($accountBalance, $totalCost){
        if ($accountBalance->balance_bwp < $totalCost) {
            throw new \Exception('Insufficient balance to send messages. Please top up your account.');
        }
    }

    private static function createMessageRecord($details, $priceInBWP){
        $messageData = [
            'author' => $details['userId'],
            'term_id' => $details['termId'],
            'body' => $details['message'],
            'channel' => 'sms',
            'provider' => $details['provider'] ?? 'link_sms',
            'recipient_address' => $details['recipientAddress'] ?? null,
            'metadata' => [
                'provider_response' => $details['providerResponse'] ?? null,
            ],
            'sms_count' => $details['smsCount'],
            'type' => $details['type'],
            'status' => 'Delivered',
            'num_recipients' => $details['numRecipients'],
            'price' => $priceInBWP,
            'price_unit' => 'BWP',
            'external_message_id' => $details['externalMessageId'] ?? null,
            'delivery_status' => $details['deliveryStatus'] ?? 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($details['senderType'] === 'user') {
            $messageData['user_id'] = $details['senderId'];
        } elseif ($details['senderType'] === 'sponsor') {
            $messageData['sponsor_id'] = $details['senderId'];
        }

        $message = Message::create($messageData);
        self::logMessageCreation($message, $details, $priceInBWP);
        return $message;
    }

    private static function updateAccountBalance($accountBalance, $totalCost){
        DB::transaction(function () use ($accountBalance, $totalCost) {
            $accountBalance->amount_used_bwp += $totalCost;
            $accountBalance->balance_bwp -= $totalCost;
            $accountBalance->save();

            self::logBalanceUpdate($accountBalance, $totalCost);
        });
    }

    private static function logResponseStructure($responseBody){
        Log::info('Response structure:', [
            'full_response' => $responseBody,
            'success_exists' => isset($responseBody['success']),
            'success_value' => $responseBody['success'] ?? null,
            'status_code' => $responseBody['status_code'] ?? null
        ]);
    }

    private static function logMessageCreation($message, $details, $priceInBWP){
        Log::info('Message record created:', [
            'message_id' => $message->id,
            'total_cost' => $details['smsCount'] * $priceInBWP * $details['numRecipients'],
            'sms_count' => $details['smsCount'],
            'recipients' => $details['numRecipients']
        ]);
    }

    private static function logBalanceUpdate($accountBalance, $totalCost){
        Log::info('Balance updated:', [
            'package' => $accountBalance->sms_credits_package,
            'price_per_sms' => self::getPackageRate($accountBalance->sms_credits_package),
            'total_cost' => $totalCost,
            'new_balance' => $accountBalance->balance_bwp,
            'total_used' => $accountBalance->amount_used_bwp
        ]);
    }

    private static function logError(\Exception $e){
        Log::error('Error processing SMS:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }

  public static function getSMSMetrics($balance) {
      $packageType = optional($balance)->sms_credits_package;
      $cost = self::getPackageRate($packageType);
      $remainingUnits = floor((optional($balance)->balance_bwp ?? 0) / ($cost ?: 1));
      
      return [
          'cost' => $cost,
          'remainingUnits' => $remainingUnits,
          'alertLevel' => $remainingUnits < 50 ? 'danger' : ($remainingUnits < 100 ? 'warning' : 'success')
      ];
  }


    public static function getMessagesDashboardData($selectedTermId){
      $messages = Message::with(['user', 'sponsor', 'author'])->where('term_id', $selectedTermId)->get();

      $bulkMessages = $messages->where('type', 'bulk')->groupBy('body');
      $nonBulkMessages = $messages->where('type', '!=', 'bulk');
      $accountBalance = AccountBalance::first();
      
      $packageMetrics = self::calculatePackageMetrics($accountBalance);
      $totalBulkCost = self::calculateTotalBulkCost($bulkMessages);

      return [
          'messages' => $messages,
          'bulkMessages' => $bulkMessages,
          'nonBulkMessages' => $nonBulkMessages,
          'packageMetrics' => $packageMetrics,
          'totalBulkCost' => $totalBulkCost
      ];
    }

  private static function calculatePackageMetrics($balance){
      $packageType = optional($balance)->sms_credits_package;
      $cost = self::getPackageRate($packageType);
      $remainingUnits = $cost > 0 ? floor((optional($balance)->balance_bwp ?? 0) - $cost) : 0;

      return [
          'type' => $packageType ?: 'No Package',
          'amount' => optional($balance)->package_amount ?? 0,
          'balance' => optional($balance)->balance_bwp ?? 0,
          'amountUsed' => optional($balance)->amount_used_bwp ?? 0,
          'costPerSms' => $cost,
          'remainingUnits' => $remainingUnits,
          'alertLevel' => $remainingUnits < 50 ? 'danger' : ($remainingUnits < 100 ? 'warning' : 'success')
      ];
  }

  private static function calculateTotalBulkCost($bulkMessages){
      $totalBulkCost = 0;
      $accountBalance = AccountBalance::first();
      $packageType = optional($accountBalance)->sms_credits_package ?? 'Basic';
      $costPerUnit = self::getPackageRate($packageType);

      foreach ($bulkMessages as $group) {
          foreach ($group as $message) {
              $totalBulkCost += $message->num_recipients * $message->sms_count * $costPerUnit;
          }
      }
      return $totalBulkCost;
  }

  /**
   * Batch insert multiple SMS message records
   *
   * Use this instead of individual Message::create() calls for bulk operations
   * to reduce database load by 95%
   *
   * @param array $messageRecords Array of message data arrays
   * @return int Number of records inserted
   */
  public static function batchInsertMessages(array $messageRecords): int
  {
      if (empty($messageRecords)) {
          return 0;
      }

      // Add timestamps to all records
      $now = now();
      foreach ($messageRecords as &$record) {
          $record['created_at'] = $now;
          $record['updated_at'] = $now;
      }

      // Batch insert all records in one query
      Message::insert($messageRecords);

      return count($messageRecords);
  }

}
