<?php

namespace App\Http\Requests\Activities;

use App\Models\Activities\ActivityEvent;
use App\Services\Activities\ActivitySettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settingsService = app(ActivitySettingsService::class);
        $event = $this->route('event');
        $currentEventType = $event instanceof ActivityEvent ? $event->event_type : null;

        return [
            'title' => ['required', 'string', 'max:255'],
            'event_type' => ['required', Rule::in($this->allowedOptionKeys($settingsService->activeEventTypeOptions(), $currentEventType))],
            'description' => ['nullable', 'string', 'max:5000'],
            'start_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_date' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'opponent_or_partner_name' => ['nullable', 'string', 'max:255'],
            'house_linked' => ['nullable', 'boolean'],
            'publish_to_calendar' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(array_keys(ActivityEvent::statuses()))],
        ];
    }

    protected function prepareForValidation(): void
    {
        $startDate = $this->input('start_date');
        $endTime = $this->input('end_time');
        $endDate = $this->input('end_date');

        $this->merge([
            'house_linked' => $this->boolean('house_linked'),
            'publish_to_calendar' => $this->boolean('publish_to_calendar'),
            'end_date' => $endTime && !$endDate ? $startDate : $endDate,
        ]);
    }

    private function allowedOptionKeys(array $activeOptions, ?string $currentKey = null): array
    {
        $keys = array_keys($activeOptions);

        if ($currentKey && !in_array($currentKey, $keys, true)) {
            $keys[] = $currentKey;
        }

        return $keys;
    }
}
