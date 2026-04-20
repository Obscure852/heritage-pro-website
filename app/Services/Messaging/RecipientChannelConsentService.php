<?php

namespace App\Services\Messaging;

use App\Models\RecipientChannelConsent;
use Illuminate\Database\Eloquent\Model;

class RecipientChannelConsentService
{
    public function getConsent(Model $recipient, string $channel): ?RecipientChannelConsent
    {
        return RecipientChannelConsent::query()
            ->where('recipient_type', $recipient->getMorphClass())
            ->where('recipient_id', $recipient->getKey())
            ->where('channel', $channel)
            ->first();
    }

    public function hasOptedIn(Model $recipient, string $channel): bool
    {
        return $this->getConsent($recipient, $channel)?->status === RecipientChannelConsent::STATUS_OPTED_IN;
    }

    public function recordStatus(
        Model $recipient,
        string $channel,
        string $status,
        ?int $recordedBy = null,
        ?string $source = null,
        ?string $notes = null
    ): RecipientChannelConsent {
        return RecipientChannelConsent::updateOrCreate(
            [
                'recipient_type' => $recipient->getMorphClass(),
                'recipient_id' => $recipient->getKey(),
                'channel' => $channel,
            ],
            [
                'status' => $status,
                'source' => $source,
                'recorded_by' => $recordedBy,
                'recorded_at' => now(),
                'opted_out_at' => $status === RecipientChannelConsent::STATUS_OPTED_IN ? null : now(),
                'notes' => $notes,
            ]
        );
    }
}
