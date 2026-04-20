@php
    $schoolModeResolver = $schoolModeResolver ?? app(\App\Services\SchoolModeResolver::class);
    $finalsContext = $finalsContext ?? $schoolModeResolver->currentFinalsContext();
    $availableFinalsContexts = $schoolModeResolver->availableFinalsContexts();
@endphp

@if ($schoolModeResolver->isCombinedFinalsMode())
    <div class="finals-context-toggle" role="tablist" aria-label="Finals context selector">
        @foreach ($availableFinalsContexts as $context)
            @php
                $isActive = $context === $finalsContext;
                $switchUrl = route('finals.context.switch', ['context' => $context, 'redirect' => request()->fullUrl()]);
            @endphp
            <a href="{{ $switchUrl }}"
               class="finals-context-pill {{ $isActive ? 'active' : '' }}"
               role="tab"
               aria-selected="{{ $isActive ? 'true' : 'false' }}"
               title="{{ $schoolModeResolver->finalsContextDescription($context) }}">
                <i class="bx {{ $context === \App\Services\SchoolModeResolver::FINALS_CONTEXT_SENIOR ? 'bx-graduation' : 'bx-book' }} me-1"></i>
                {{ $schoolModeResolver->finalsContextLabel($context) }}
            </a>
        @endforeach
    </div>
@endif

@once
    <style>
        .finals-context-toggle {
            display: inline-flex;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 999px;
            padding: 4px;
            gap: 4px;
        }

        .finals-context-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .finals-context-pill:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .finals-context-pill.active {
            background: white;
            color: #2563eb;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
        }
    </style>
@endonce
