<?php

namespace App\Http\Controllers\Crm;

class EmailDiscussionController extends ExternalDiscussionChannelController
{
    protected function channelKey(): string
    {
        return 'email';
    }

    protected function viewBase(): string
    {
        return 'crm.discussions.email';
    }

    protected function routeBase(): string
    {
        return 'crm.discussions.email';
    }

    protected function channelLabel(): string
    {
        return 'Email';
    }
}
