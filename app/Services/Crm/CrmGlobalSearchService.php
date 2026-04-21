<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use App\Models\Customer;
use App\Models\DevelopmentRequest;
use App\Models\DiscussionThread;
use App\Models\Integration;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CrmGlobalSearchService
{
    public function search(User $user, string $query): array
    {
        $query = trim($query);
        $minLength = (int) config('heritage_crm.search.min_query_length', 2);

        if (mb_strlen($query) < $minLength) {
            return [];
        }

        $sections = [
            $this->leadResults($user, $query),
            $this->customerResults($user, $query),
            $this->contactResults($user, $query),
            $this->requestResults($user, $query),
            $this->developmentResults($user, $query),
            $this->discussionResults($user, $query),
            $this->integrationResults($query),
            $this->userResults($user, $query),
        ];

        return array_values(array_filter($sections));
    }

    private function leadResults(User $user, string $query): ?array
    {
        $records = $this->scopeOwned($user, Lead::query())
            ->select(['id', 'company_name', 'country', 'status', 'owner_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('company_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('country', 'like', "%{$query}%");
            })
            ->orderBy('company_name')
            ->limit($this->limit())
            ->get();

        return $this->section('Leads', 'bx bxs-school', $records, function (Lead $lead) {
            return [
                'label' => $lead->company_name,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.lead_statuses.' . $lead->status, ucfirst($lead->status)),
                    $lead->country,
                ]))),
                'icon' => 'bx bxs-school',
                'url' => route('crm.leads.show', $lead),
            ];
        });
    }

    private function customerResults(User $user, string $query): ?array
    {
        $records = $this->scopeOwned($user, Customer::query())
            ->select(['id', 'company_name', 'country', 'status', 'owner_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('company_name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('country', 'like', "%{$query}%");
            })
            ->orderBy('company_name')
            ->limit($this->limit())
            ->get();

        return $this->section('Customers', 'bx bx-building-house', $records, function (Customer $customer) {
            return [
                'label' => $customer->company_name,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.customer_statuses.' . $customer->status, ucfirst($customer->status)),
                    $customer->country,
                ]))),
                'icon' => 'bx bx-building-house',
                'url' => route('crm.customers.show', $customer),
            ];
        });
    }

    private function contactResults(User $user, string $query): ?array
    {
        $records = $this->scopeOwned($user, Contact::query())
            ->with([
                'lead:id,company_name',
                'customer:id,company_name',
            ])
            ->select(['id', 'name', 'email', 'phone', 'job_title', 'lead_id', 'customer_id', 'owner_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%")
                    ->orWhere('job_title', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit($this->limit())
            ->get();

        return $this->section('Contacts', 'bx bx-user-pin', $records, function (Contact $contact) {
            return [
                'label' => $contact->name,
                'secondary' => trim(implode(' • ', array_filter([
                    $contact->job_title,
                    $contact->customer?->company_name ?: $contact->lead?->company_name,
                ]))),
                'icon' => 'bx bx-user-pin',
                'url' => route('crm.contacts.show', $contact),
            ];
        });
    }

    private function requestResults(User $user, string $query): ?array
    {
        $records = $this->scopeOwned($user, CrmRequest::query())
            ->with([
                'lead:id,company_name',
                'customer:id,company_name',
            ])
            ->select(['id', 'title', 'type', 'support_status', 'sales_stage_id', 'lead_id', 'customer_id', 'owner_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('next_action', 'like', "%{$query}%");
            })
            ->latest()
            ->limit($this->limit())
            ->get();

        return $this->section('Requests', 'bx bx-headphone', $records, function (CrmRequest $crmRequest) {
            return [
                'label' => $crmRequest->title,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.request_types.' . $crmRequest->type, ucfirst($crmRequest->type)),
                    $crmRequest->customer?->company_name ?: $crmRequest->lead?->company_name,
                ]))),
                'icon' => 'bx bx-headphone',
                'url' => route('crm.requests.show', $crmRequest),
            ];
        });
    }

    private function developmentResults(User $user, string $query): ?array
    {
        $records = $this->scopeOwned($user, DevelopmentRequest::query())
            ->select(['id', 'title', 'priority', 'status', 'owner_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('target_module', 'like', "%{$query}%")
                    ->orWhere('requested_by', 'like', "%{$query}%");
            })
            ->latest()
            ->limit($this->limit())
            ->get();

        return $this->section('Dev', 'bx bx-code-block', $records, function (DevelopmentRequest $item) {
            return [
                'label' => $item->title,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.development_priorities.' . $item->priority, ucfirst($item->priority)),
                    config('heritage_crm.development_statuses.' . $item->status, ucfirst(str_replace('_', ' ', $item->status))),
                ]))),
                'icon' => 'bx bx-code-block',
                'url' => route('crm.dev.show', $item),
            ];
        });
    }

    private function discussionResults(User $user, string $query): ?array
    {
        $records = DiscussionThread::query()
            ->with([
                'initiatedBy',
                'recipientUser',
            ])
            ->when($user->isRep(), function (Builder $builder) use ($user) {
                $builder->where(function (Builder $threadQuery) use ($user) {
                    $threadQuery->where('initiated_by_id', $user->id)
                        ->orWhere('recipient_user_id', $user->id)
                        ->orWhere('owner_id', $user->id);
                });
            })
            ->select(['id', 'subject', 'channel', 'delivery_status', 'owner_id', 'initiated_by_id', 'recipient_user_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('subject', 'like', "%{$query}%")
                    ->orWhere('recipient_email', 'like', "%{$query}%")
                    ->orWhere('recipient_phone', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->latest('last_message_at')
            ->limit($this->limit())
            ->get();

        return $this->section('Discussions', 'bx bx-chat', $records, function (DiscussionThread $thread) {
            return [
                'label' => $thread->subject,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.discussion_channels.' . $thread->channel, ucfirst($thread->channel)),
                    $thread->recipientUser?->name ?: $thread->recipient_email ?: $thread->recipient_phone,
                ]))),
                'icon' => 'bx bx-chat',
                'url' => route('crm.discussions.show', $thread),
            ];
        });
    }

    private function integrationResults(string $query): ?array
    {
        $records = Integration::query()
            ->select(['id', 'name', 'kind', 'status'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('school_code', 'like', "%{$query}%")
                    ->orWhere('base_url', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit($this->limit())
            ->get();

        return $this->section('Integrations', 'bx bx-plug', $records, function (Integration $integration) {
            return [
                'label' => $integration->name,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.integration_kinds.' . $integration->kind, ucfirst(str_replace('_', ' ', $integration->kind))),
                    config('heritage_crm.integration_statuses.' . $integration->status, ucfirst($integration->status)),
                ]))),
                'icon' => 'bx bx-plug',
                'url' => route('crm.integrations.show', $integration),
            ];
        });
    }

    private function userResults(User $user, string $query): ?array
    {
        if (! $user->canManageCrmUsers()) {
            return null;
        }

        $records = User::query()
            ->where('active', true)
            ->whereIn('role', array_keys(config('heritage_crm.roles', [])))
            ->where(function (Builder $builder) use ($query) {
                $columns = [];

                if (Schema::hasColumn('users', 'name')) {
                    $columns[] = 'name';
                }

                if (Schema::hasColumn('users', 'firstname')) {
                    $columns[] = 'firstname';
                }

                if (Schema::hasColumn('users', 'lastname')) {
                    $columns[] = 'lastname';
                }

                if (Schema::hasColumn('users', 'username')) {
                    $columns[] = 'username';
                }

                $columns[] = 'email';

                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', "%{$query}%");
                }
            })
            ->orderBy('email')
            ->limit($this->limit())
            ->get();

        return $this->section('Users', 'bx bx-group', $records, function (User $record) {
            return [
                'label' => $record->name,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.roles.' . $record->role, ucfirst($record->role)),
                    $record->email,
                ]))),
                'icon' => 'bx bx-group',
                'url' => route('crm.users.index') . '#crm-user-' . $record->id,
            ];
        });
    }

    private function section(string $label, string $icon, Collection $records, callable $mapper): ?array
    {
        if ($records->isEmpty()) {
            return null;
        }

        return [
            'label' => $label,
            'icon' => $icon,
            'items' => $records->map($mapper)->values()->all(),
        ];
    }

    private function scopeOwned(User $user, Builder $query, string $ownerColumn = 'owner_id'): Builder
    {
        if ($user->isRep()) {
            $query->where($ownerColumn, $user->id);
        }

        return $query;
    }

    private function limit(): int
    {
        return (int) config('heritage_crm.search.limit_per_group', 5);
    }
}
