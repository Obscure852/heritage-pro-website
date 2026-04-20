{{-- Calendar partial for public holidays --}}
<div class="holiday-calendar">
    <div class="calendar-legend mb-4">
        <div class="d-flex gap-4 justify-content-center">
            <div class="legend-item d-flex align-items-center gap-2">
                <span class="legend-color" style="background: #fee2e2; width: 20px; height: 20px; border-radius: 3px; border: 1px solid #fecaca;"></span>
                <span style="font-size: 13px; color: #374151;">One-time Holiday</span>
            </div>
            <div class="legend-item d-flex align-items-center gap-2">
                <span class="legend-color" style="background: #dbeafe; width: 20px; height: 20px; border-radius: 3px; border: 1px solid #bfdbfe;"></span>
                <span style="font-size: 13px; color: #374151;">Recurring Holiday</span>
            </div>
        </div>
    </div>

    <div class="calendar-grid">
        @php
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            $dayNames = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
        @endphp

        @foreach ($months as $monthNum => $monthName)
            @php
                $firstDay = \Carbon\Carbon::create($year, $monthNum, 1);
                $daysInMonth = $firstDay->daysInMonth;
                $startingDay = $firstDay->dayOfWeek; // 0 = Sunday

                $monthHolidays = $holidaysByMonth[$monthNum] ?? collect();
                $holidayDays = $monthHolidays->mapWithKeys(function ($holiday) use ($year) {
                    $displayDate = $holiday->display_date ?? $holiday->date;
                    return [$displayDate->day => $holiday];
                });
            @endphp

            <div class="calendar-month">
                <div class="month-header">{{ $monthName }} {{ $year }}</div>
                <div class="month-grid">
                    {{-- Day name headers --}}
                    @foreach ($dayNames as $dayName)
                        <div class="day-header">{{ $dayName }}</div>
                    @endforeach

                    {{-- Empty cells for days before start of month --}}
                    @for ($i = 0; $i < $startingDay; $i++)
                        <div class="day-cell empty"></div>
                    @endfor

                    {{-- Day cells --}}
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $holiday = $holidayDays->get($day);
                            $isHoliday = $holiday !== null;
                            $isRecurring = $isHoliday && $holiday->is_recurring;
                            $holidayClass = $isHoliday ? ($isRecurring ? 'holiday-recurring' : 'holiday-onetime') : '';
                            $title = $isHoliday ? $holiday->name : '';
                        @endphp
                        <div class="day-cell {{ $holidayClass }}"
                             @if ($isHoliday)
                                 title="{{ $title }}"
                                 data-bs-toggle="tooltip"
                                 data-bs-placement="top"
                             @endif>
                            <span class="day-number">{{ $day }}</span>
                            @if ($isHoliday)
                                <span class="holiday-indicator"></span>
                            @endif
                        </div>
                    @endfor
                </div>
                {{-- Holiday list for this month --}}
                @if ($monthHolidays->count() > 0)
                    <div class="month-holidays">
                        @foreach ($monthHolidays as $holiday)
                            @php
                                $displayDate = $holiday->display_date ?? $holiday->date;
                            @endphp
                            <div class="holiday-item {{ $holiday->is_recurring ? 'recurring' : 'onetime' }}">
                                <span class="holiday-date">{{ $displayDate->format('d') }}</span>
                                <span class="holiday-name">{{ $holiday->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<style>
    .holiday-calendar {
        padding: 20px 0;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    @media (max-width: 1200px) {
        .calendar-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 900px) {
        .calendar-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .calendar-grid {
            grid-template-columns: 1fr;
        }
    }

    .calendar-month {
        background: #f9fafb;
        border-radius: 6px;
        padding: 12px;
        border: 1px solid #e5e7eb;
    }

    .month-header {
        text-align: center;
        font-weight: 600;
        font-size: 14px;
        color: #1f2937;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 10px;
    }

    .month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
    }

    .day-header {
        text-align: center;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        padding: 4px 0;
    }

    .day-cell {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #374151;
        border-radius: 3px;
        position: relative;
        cursor: default;
        min-height: 28px;
    }

    .day-cell.empty {
        background: transparent;
    }

    .day-cell.holiday-onetime {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        cursor: pointer;
    }

    .day-cell.holiday-onetime:hover {
        background: #fecaca;
    }

    .day-cell.holiday-recurring {
        background: #dbeafe;
        color: #1e40af;
        font-weight: 600;
        cursor: pointer;
    }

    .day-cell.holiday-recurring:hover {
        background: #bfdbfe;
    }

    .day-number {
        position: relative;
        z-index: 1;
    }

    .holiday-indicator {
        position: absolute;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%);
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: currentColor;
    }

    .month-holidays {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
    }

    .holiday-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 4px 0;
        font-size: 11px;
    }

    .holiday-item.onetime .holiday-date {
        background: #fee2e2;
        color: #991b1b;
    }

    .holiday-item.recurring .holiday-date {
        background: #dbeafe;
        color: #1e40af;
    }

    .holiday-date {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 10px;
    }

    .holiday-name {
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips for holiday days
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
