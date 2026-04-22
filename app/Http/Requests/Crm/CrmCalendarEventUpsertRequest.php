<?php

namespace App\Http\Requests\Crm;

use App\Models\Contact;
use App\Models\CrmRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrmCalendarEventUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $defaultDuration = (int) config('heritage_crm.calendar.default_event_duration_minutes', 60);
        $startsAt = $this->input('starts_at');
        $endsAt = $this->input('ends_at');

        if (filled($startsAt) && blank($endsAt)) {
            $endsAt = Carbon::parse((string) $startsAt)->addMinutes($defaultDuration)->format('Y-m-d\TH:i');
        }

        $this->merge([
            'all_day' => $this->boolean('all_day'),
            'ends_at' => $endsAt,
            'attendee_user_ids' => collect((array) $this->input('attendee_user_ids', []))
                ->filter(fn ($userId) => filled($userId))
                ->map(fn ($userId) => (int) $userId)
                ->values()
                ->all(),
            'reminder_minutes' => collect((array) $this->input('reminder_minutes', []))
                ->filter(fn ($minutes) => filled($minutes))
                ->map(fn ($minutes) => (int) $minutes)
                ->values()
                ->all(),
        ]);
    }

    public function rules(): array
    {
        return [
            'calendar_id' => ['required', 'exists:crm_calendars,id'],
            'owner_id' => ['nullable', 'exists:users,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'request_id' => ['nullable', 'exists:requests,id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date'],
            'all_day' => ['required', 'boolean'],
            'status' => ['required', Rule::in(array_keys(config('heritage_crm.calendar_event_statuses', [])))],
            'visibility' => ['required', Rule::in(array_keys(config('heritage_crm.calendar_event_visibility', [])))],
            'timezone' => ['nullable', 'string', 'max:80'],
            'attendee_user_ids' => ['nullable', 'array', 'max:12'],
            'attendee_user_ids.*' => ['integer', 'exists:users,id'],
            'reminder_minutes' => ['nullable', 'array', 'max:6'],
            'reminder_minutes.*' => ['integer', Rule::in(array_map('intval', array_keys(config('heritage_crm.calendar_reminder_minutes', []))))],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (filled($this->input('lead_id')) && filled($this->input('customer_id'))) {
                $validator->errors()->add('customer_id', 'An event can be linked to a lead or a customer, not both.');
            }

            if (filled($this->input('starts_at')) && filled($this->input('ends_at'))) {
                $startsAt = Carbon::parse((string) $this->input('starts_at'));
                $endsAt = Carbon::parse((string) $this->input('ends_at'));

                if ($endsAt->lt($startsAt)) {
                    $validator->errors()->add('ends_at', 'The end time must be after the start time.');
                }
            }

            if (filled($this->input('contact_id'))) {
                /** @var Contact|null $contact */
                $contact = Contact::query()->find((int) $this->input('contact_id'));

                if ($contact !== null && filled($this->input('lead_id')) && (int) $contact->lead_id !== (int) $this->input('lead_id')) {
                    $validator->errors()->add('contact_id', 'The selected contact does not belong to the selected lead.');
                }

                if ($contact !== null && filled($this->input('customer_id')) && (int) $contact->customer_id !== (int) $this->input('customer_id')) {
                    $validator->errors()->add('contact_id', 'The selected contact does not belong to the selected customer.');
                }
            }

            if (filled($this->input('request_id'))) {
                /** @var CrmRequest|null $crmRequest */
                $crmRequest = CrmRequest::query()->find((int) $this->input('request_id'));

                if ($crmRequest !== null && filled($this->input('lead_id')) && (int) $crmRequest->lead_id !== (int) $this->input('lead_id')) {
                    $validator->errors()->add('request_id', 'The selected request does not belong to the selected lead.');
                }

                if ($crmRequest !== null && filled($this->input('customer_id')) && (int) $crmRequest->customer_id !== (int) $this->input('customer_id')) {
                    $validator->errors()->add('request_id', 'The selected request does not belong to the selected customer.');
                }
            }
        });
    }
}
