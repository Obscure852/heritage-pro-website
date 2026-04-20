@php
    $metaItems = collect($metaItems ?? [])
        ->filter(fn ($item) => filled($item['text'] ?? null))
        ->values();

    $contactLines = collect([
        $school?->physical_address,
        $school?->postal_address,
        collect([
            filled($school?->telephone) ? 'Tel: ' . $school->telephone : null,
            filled($school?->fax) ? 'Fax: ' . $school->fax : null,
            filled($school?->email_address) ? 'Email: ' . $school->email_address : null,
        ])->filter()->implode(' | '),
    ])->filter(fn ($line) => filled($line));
@endphp

<div class="doc-letterhead">
    <div class="doc-letterhead-crest">
        <img src="{{ asset('assets/images/coat_of_arms.jpg') }}" alt="Botswana Coat of Arms">
    </div>
    <div class="doc-letterhead-body">
        @if (filled($school?->school_name))
            <div class="doc-letterhead-school">{{ $school->school_name }}</div>
        @endif

        @foreach ($contactLines as $line)
            <div class="doc-letterhead-contact">{{ $line }}</div>
        @endforeach

        @if (filled($subtitle ?? null))
            <div class="doc-letterhead-subtitle">{{ $subtitle }}</div>
        @endif
    </div>
</div>

@if (filled($title ?? null))
    <h2 class="doc-header-title">{{ $title }}</h2>
@endif

@if ($metaItems->isNotEmpty())
    <div class="doc-header-meta">
        @foreach ($metaItems as $item)
            <span>
                @if (!empty($item['icon']))
                    <i class="{{ $item['icon'] }}"></i>
                @endif
                {{ $item['text'] }}
            </span>
        @endforeach
    </div>
@endif
