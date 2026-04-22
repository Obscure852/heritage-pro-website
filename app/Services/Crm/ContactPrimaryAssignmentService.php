<?php

namespace App\Services\Crm;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactPrimaryAssignmentService
{
    public function sync(Contact $contact): void
    {
        DB::transaction(function () use ($contact) {
            $contact = Contact::query()->lockForUpdate()->findOrFail($contact->id);

            if (! $contact->is_primary) {
                return;
            }

            $query = Contact::query()
                ->whereKeyNot($contact->id)
                ->when($contact->lead_id !== null, function ($builder) use ($contact) {
                    $builder->where('lead_id', $contact->lead_id);
                })
                ->when($contact->lead_id === null, function ($builder) {
                    $builder->whereNull('lead_id');
                })
                ->when($contact->customer_id !== null, function ($builder) use ($contact) {
                    $builder->where('customer_id', $contact->customer_id);
                })
                ->when($contact->customer_id === null, function ($builder) {
                    $builder->whereNull('customer_id');
                });

            $query->lockForUpdate()->get();
            $query->update(['is_primary' => false]);
        });
    }
}
