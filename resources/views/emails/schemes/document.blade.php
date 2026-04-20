@php
    $subjectName = $scheme->klassSubject?->gradeSubject?->subject?->name
        ?? ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? 'Unknown Subject');

    $gradeName = $scheme->klassSubject?->gradeSubject?->grade?->name
        ?? ($scheme->optionalSubject?->gradeSubject?->grade?->name ?? '—');

    $classLabel = $scheme->klassSubject?->klass?->name
        ?? ('Optional: ' . ($scheme->optionalSubject?->gradeSubject?->subject?->name ?? '—'));

    $sortedEntries = $scheme->entries->sortBy('week_number');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $mailSubject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f5f7fb;
            color: #1f2937;
            font-family: "Segoe UI", Arial, sans-serif;
            line-height: 1.55;
        }

        .container {
            max-width: 760px;
            margin: 0 auto;
            padding: 32px 20px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }

        .header {
            padding: 28px 28px 20px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }

        .header img {
            width: 72px;
            height: auto;
            margin-bottom: 14px;
        }

        .header h1 {
            margin: 0 0 6px;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .header p {
            margin: 0;
            font-size: 14px;
            color: #6b7280;
        }

        .content {
            padding: 24px 28px 32px;
        }

        .intro {
            margin-bottom: 22px;
        }

        .intro p {
            margin: 0 0 12px;
        }

        .note {
            margin: 16px 0 22px;
            padding: 14px 16px;
            background: #f8fafc;
            border-left: 3px solid #2563eb;
            border-radius: 4px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .summary td {
            padding: 10px 0;
            border-bottom: 1px solid #eef2f7;
            vertical-align: top;
            font-size: 14px;
        }

        .summary td:first-child {
            width: 140px;
            color: #6b7280;
            font-weight: 600;
        }

        .week {
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            margin-bottom: 18px;
            overflow: hidden;
        }

        .week-header {
            background: #f8fafc;
            padding: 12px 16px;
            font-size: 15px;
            font-weight: 700;
            color: #111827;
        }

        .week-body {
            padding: 16px;
        }

        .field {
            margin-bottom: 12px;
        }

        .field-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .field-value {
            font-size: 14px;
            color: #1f2937;
        }

        .lesson-plan {
            margin-top: 14px;
            padding: 14px 16px;
            background: #fafcff;
            border: 1px solid #dbeafe;
            border-left: 3px solid #2563eb;
            border-radius: 4px;
        }

        .lesson-plan h3 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #1d4ed8;
        }

        .lesson-meta {
            margin: 0 0 12px;
            font-size: 13px;
            color: #6b7280;
        }

        .footer {
            margin-top: 24px;
            font-size: 13px;
            color: #6b7280;
        }

        a {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <img src="{{ asset('assets/images/coat_of_arms.jpg') }}" alt="Botswana Coat of Arms">
                <h1>{{ $mailSubject }}</h1>
                <p>{{ $schoolSetup?->school_name ?? config('notifications.email.defaults.school_name', config('app.name')) }}</p>
            </div>

            <div class="content">
                <div class="intro">
                    <p>{{ trim($sender->full_name) }} has shared a scheme of work document with you.</p>
                    @if ($messageNote)
                        <div class="note">{!! nl2br(e($messageNote)) !!}</div>
                    @endif
                </div>

                <table class="summary" role="presentation">
                    <tr>
                        <td>Subject</td>
                        <td>{{ $subjectName }}</td>
                    </tr>
                    <tr>
                        <td>Class</td>
                        <td>{{ $classLabel }}</td>
                    </tr>
                    <tr>
                        <td>Teacher</td>
                        <td>{{ $scheme->teacher?->full_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td>Term</td>
                        <td>Term {{ $scheme->term?->term }}, {{ $scheme->term?->year }}</td>
                    </tr>
                    <tr>
                        <td>Grade</td>
                        <td>{{ $gradeName }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $scheme->status)) }}</td>
                    </tr>
                    <tr>
                        <td>Total Weeks</td>
                        <td>{{ $scheme->total_weeks }}</td>
                    </tr>
                </table>

                @forelse ($sortedEntries as $entry)
                    <div class="week">
                        <div class="week-header">
                            Week {{ $entry->week_number }}: {{ $entry->topic ?: 'No topic set' }}
                        </div>
                        <div class="week-body">
                            @if ($entry->sub_topic)
                                <div class="field">
                                    <div class="field-label">Sub-topic</div>
                                    <div class="field-value">{{ $entry->sub_topic }}</div>
                                </div>
                            @endif

                            @if ($entry->learning_objectives)
                                <div class="field">
                                    <div class="field-label">Learning Objectives</div>
                                    <div class="field-value">
                                        {!! strip_tags($entry->learning_objectives, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                    </div>
                                </div>
                            @endif

                            @foreach ($entry->lessonPlans as $plan)
                                <div class="lesson-plan">
                                    <h3>Lesson Plan: {{ $plan->topic }}</h3>
                                    <div class="lesson-meta">
                                        {{ $plan->date?->format('d M Y') ?? 'Date not set' }}
                                        @if ($plan->period)
                                            | {{ $plan->period }}
                                        @endif
                                        | {{ ucfirst(str_replace('_', ' ', $plan->status)) }}
                                    </div>

                                    @if ($plan->sub_topic)
                                        <div class="field">
                                            <div class="field-label">Sub-topic</div>
                                            <div class="field-value">{{ $plan->sub_topic }}</div>
                                        </div>
                                    @endif

                                    @if ($plan->learning_objectives)
                                        <div class="field">
                                            <div class="field-label">Learning Objectives</div>
                                            <div class="field-value">
                                                {!! strip_tags($plan->learning_objectives, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($plan->content)
                                        <div class="field">
                                            <div class="field-label">Content</div>
                                            <div class="field-value">
                                                {!! strip_tags($plan->content, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($plan->activities)
                                        <div class="field">
                                            <div class="field-label">Activities</div>
                                            <div class="field-value">
                                                {!! strip_tags($plan->activities, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($plan->resources)
                                        <div class="field">
                                            <div class="field-label">Resources</div>
                                            <div class="field-value">
                                                {!! strip_tags($plan->resources, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($plan->homework)
                                        <div class="field">
                                            <div class="field-label">Homework</div>
                                            <div class="field-value">
                                                {!! strip_tags($plan->homework, '<p><br><ul><ol><li><strong><b><em><i>') !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p>No weekly entries were added to this scheme yet.</p>
                @endforelse

                <div class="footer">
                    @if ($documentUrl)
                        <p>If you have system access, you can also view the live document here: <a href="{{ $documentUrl }}">{{ $documentUrl }}</a></p>
                    @endif
                    <p>{{ $schoolSetup?->physical_address ?: config('notifications.email.defaults.address', 'Gaborone, Botswana') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
