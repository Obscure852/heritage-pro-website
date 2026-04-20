@php
    $hasAnyResults =
        $users->isNotEmpty() || $students->isNotEmpty() || $sponsors->isNotEmpty() || $admissions->isNotEmpty();
@endphp

@if ($hasAnyResults)
    @if (Gate::allows('access-hr') && $users->isNotEmpty())
        <div class="search-section">
            <div class="section-header">
                <i class="fas fa-user mr-2"></i> Staff Members
            </div>
            @foreach ($users as $user)
                <div class="result-item" data-type="user" data-id="{{ $user->id }}">
                    <div class="result-name">{{ $user->firstname }} {{ $user->lastname }}</div>
                    <div class="result-details">
                        @if ($user->position)
                            <span>{{ $user->position }}</span>
                        @endif
                        @if ($user->email)
                            <span>{{ $user->email }}</span>
                        @endif
                        @if ($user->phone)
                            <span>{{ $user->phone }}</span>
                        @endif
                        @if ($user->id_number)
                            <span>ID: {{ $user->id_number }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (Gate::allows('access-students') && $students->isNotEmpty())
        <div class="search-section">
            <div class="section-header">
                <i class="fas fa-graduation-cap mr-2"></i> Students
            </div>
            @foreach ($students as $student)
                <div class="result-item" data-type="student" data-id="{{ $student->id }}">
                    <div class="result-name">{{ $student->first_name }} {{ $student->last_name }}</div>
                    <div class="result-details">
                        @if ($student->id_number)
                            <span>ID: {{ $student->id_number }}</span>
                        @endif
                        @if ($student->status)
                            <span>{{ $student->status }}</span>
                        @endif
                        <span>Class: {{ $student->current_class->name }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (Gate::allows('access-sponsors') && $sponsors->isNotEmpty())
        <div class="search-section">
            <div class="section-header">
                <i class="fas fa-users mr-2"></i> Sponsors
            </div>
            @foreach ($sponsors as $sponsor)
                <div class="result-item" data-type="sponsor" data-id="{{ $sponsor->id }}">
                    <div class="result-name">{{ $sponsor->first_name }} {{ $sponsor->last_name }}</div>
                    <div class="result-details">
                        @if ($sponsor->email)
                            <span>{{ $sponsor->email }}</span>
                        @endif
                        @if ($sponsor->phone)
                            <span>{{ $sponsor->phone }}</span>
                        @endif
                        @if ($sponsor->id_number)
                            <span>ID: {{ $sponsor->id_number }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if (Gate::allows('access-admissions') && $admissions->isNotEmpty())
        <div class="search-section">
            <div class="section-header">
                <i class="fas fa-user-plus mr-2"></i> Admissions
            </div>
            @foreach ($admissions as $admission)
                <div class="result-item" data-type="admission" data-id="{{ $admission->id }}">
                    <div class="result-name">{{ $admission->first_name }} {{ $admission->last_name }}</div>
                    <div class="result-details">
                        @if ($admission->id_number)
                            <span>ID: {{ $admission->id_number }}</span>
                        @endif
                        @if ($admission->status)
                            <span>{{ $admission->status }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@else
    {{-- If no results are found or user lacks all permissions --}}
    {{-- This section is intentionally left empty; frontend will handle it --}}
@endif
