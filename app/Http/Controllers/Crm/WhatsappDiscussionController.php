<?php

namespace App\Http\Controllers\Crm;

class WhatsappDiscussionController extends ExternalDiscussionChannelController
{
    protected function channelKey(): string
    {
        return 'whatsapp';
    }

    protected function viewBase(): string
    {
        return 'crm.discussions.whatsapp';
    }

    protected function routeBase(): string
    {
        return 'crm.discussions.whatsapp';
    }

    protected function channelLabel(): string
    {
        return 'WhatsApp';
    }
}
