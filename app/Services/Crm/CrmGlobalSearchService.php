<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\CrmInvoice;
use App\Models\CrmProduct;
use App\Models\CrmQuote;
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
    public function __construct(
        private readonly DiscussionDeliveryService $discussionDeliveryService
    ) {
    }

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
            $this->productResults($user, $query),
            $this->quoteResults($user, $query),
            $this->invoiceResults($user, $query),
            $this->requestResults($user, $query),
            $this->developmentResults($user, $query),
            $this->discussionResults($user, $query),
            $this->integrationResults($user, $query),
            $this->userResults($user, $query),
        ];

        return array_values(array_filter($sections));
    }

    private function leadResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('customers', 'view')) {
            return null;
        }

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
        if (! $user->canAccessCrmModule('customers', 'view')) {
            return null;
        }

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
        if (! $user->canAccessCrmModule('contacts', 'view')) {
            return null;
        }

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

    private function productResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('products', 'view')) {
            return null;
        }

        $records = CrmProduct::query()
            ->select(['id', 'code', 'name', 'type', 'billing_frequency', 'active'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('type', 'like', "%{$query}%")
                    ->orWhere('billing_frequency', 'like', "%{$query}%");
            })
            ->orderByDesc('active')
            ->orderBy('name')
            ->limit($this->limit())
            ->get();

        return $this->section('Products', 'bx bx-package', $records, function (CrmProduct $product) {
            return [
                'label' => $product->name,
                'secondary' => trim(implode(' • ', array_filter([
                    $product->code,
                    $product->type ? ucfirst(str_replace('_', ' ', $product->type)) : null,
                    $product->billing_frequency ? ucfirst(str_replace('_', ' ', $product->billing_frequency)) : null,
                    $product->active ? 'Active' : 'Inactive',
                ]))),
                'icon' => 'bx bx-package',
                'url' => route('crm.products.catalog.show', $product),
            ];
        });
    }

    private function quoteResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('products', 'view')) {
            return null;
        }

        $records = CrmQuote::query()
            ->with([
                'lead:id,company_name',
                'customer:id,company_name',
                'contact:id,name',
            ])
            ->select(['id', 'quote_number', 'subject', 'status', 'lead_id', 'customer_id', 'contact_id', 'owner_id'])
            ->when($user->isRep(), function (Builder $builder) use ($user) {
                $builder->where('owner_id', $user->id);
            })
            ->where(function (Builder $builder) use ($query) {
                $builder->where('quote_number', 'like', "%{$query}%")
                    ->orWhere('subject', 'like', "%{$query}%")
                    ->orWhereHas('lead', fn (Builder $leadQuery) => $leadQuery->where('company_name', 'like', "%{$query}%"))
                    ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery->where('company_name', 'like', "%{$query}%"))
                    ->orWhereHas('contact', fn (Builder $contactQuery) => $contactQuery->where('name', 'like', "%{$query}%"));
            })
            ->latest('quote_date')
            ->latest('id')
            ->limit($this->limit())
            ->get();

        return $this->section('Quotes', 'bx bx-receipt', $records, function (CrmQuote $quote) {
            return [
                'label' => $quote->quote_number,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.quote_statuses.' . $quote->status, ucfirst($quote->status)),
                    $quote->customer?->company_name ?: $quote->lead?->company_name,
                    $quote->contact?->name,
                ]))),
                'icon' => 'bx bx-receipt',
                'url' => route('crm.products.quotes.show', $quote),
            ];
        });
    }

    private function invoiceResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('products', 'view')) {
            return null;
        }

        $records = CrmInvoice::query()
            ->with([
                'lead:id,company_name',
                'customer:id,company_name',
                'contact:id,name',
            ])
            ->select(['id', 'invoice_number', 'subject', 'status', 'lead_id', 'customer_id', 'contact_id', 'owner_id'])
            ->when($user->isRep(), function (Builder $builder) use ($user) {
                $builder->where('owner_id', $user->id);
            })
            ->where(function (Builder $builder) use ($query) {
                $builder->where('invoice_number', 'like', "%{$query}%")
                    ->orWhere('subject', 'like', "%{$query}%")
                    ->orWhereHas('lead', fn (Builder $leadQuery) => $leadQuery->where('company_name', 'like', "%{$query}%"))
                    ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery->where('company_name', 'like', "%{$query}%"))
                    ->orWhereHas('contact', fn (Builder $contactQuery) => $contactQuery->where('name', 'like', "%{$query}%"));
            })
            ->latest('invoice_date')
            ->latest('id')
            ->limit($this->limit())
            ->get();

        return $this->section('Invoices', 'bx bx-file', $records, function (CrmInvoice $invoice) {
            return [
                'label' => $invoice->invoice_number,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.invoice_statuses.' . $invoice->status, ucfirst($invoice->status)),
                    $invoice->customer?->company_name ?: $invoice->lead?->company_name,
                    $invoice->contact?->name,
                ]))),
                'icon' => 'bx bx-file',
                'url' => route('crm.products.invoices.show', $invoice),
            ];
        });
    }

    private function requestResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('requests', 'view')) {
            return null;
        }

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
        if (! $user->canAccessCrmModule('dev', 'view')) {
            return null;
        }

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
        if (! $user->canAccessCrmModule('discussions', 'view')) {
            return null;
        }

        $records = $this->discussionDeliveryService->threadQueryFor($user)
            ->with([
                'initiatedBy',
                'recipientUser',
                'participants.user',
            ])
            ->select(['id', 'subject', 'channel', 'kind', 'delivery_status', 'status', 'owner_id', 'initiated_by_id', 'recipient_user_id'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('subject', 'like', "%{$query}%")
                    ->orWhere('recipient_email', 'like', "%{$query}%")
                    ->orWhere('recipient_phone', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
            ->latest('last_message_at')
            ->limit($this->limit())
            ->get();

        return $this->section('Discussions', 'bx bx-chat', $records, function (DiscussionThread $thread) use ($user) {
            $secondaryLabel = null;

            if ($thread->channel === 'app' && $thread->kind === 'direct') {
                $secondaryLabel = $thread->counterpartFor($user)?->name;
            } elseif ($thread->channel === 'app' && $thread->kind === 'group') {
                $secondaryLabel = $thread->subject;
            }

            return [
                'label' => $thread->subject,
                'secondary' => trim(implode(' • ', array_filter([
                    config('heritage_crm.discussion_channels.' . $thread->channel, ucfirst($thread->channel)),
                    $secondaryLabel ?: $thread->recipientUser?->name ?: $thread->recipient_email ?: $thread->recipient_phone,
                ]))),
                'icon' => 'bx bx-chat',
                'url' => $this->discussionDeliveryService->threadRoute($thread),
            ];
        });
    }

    private function integrationResults(User $user, string $query): ?array
    {
        if (! $user->canAccessCrmModule('integrations', 'view')) {
            return null;
        }

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
        if (! $user->canAccessCrmModule('users', 'view')) {
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

                foreach (['phone', 'id_number', 'personal_payroll_number', 'nationality'] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $columns[] = $column;
                    }
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
