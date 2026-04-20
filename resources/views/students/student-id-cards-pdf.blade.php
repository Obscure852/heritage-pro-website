<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student ID Cards</title>
    <style>
        @page {
            margin: 10mm;
            size: A4 portrait;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8px;
            color: #333;
            line-height: 1.3;
        }

        .page {
            width: 100%;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .cards-container {
            width: 100%;
        }

        .cards-row {
            width: 100%;
            margin-bottom: 5mm;
            overflow: hidden;
        }

        .card-wrapper {
            width: 50%;
            float: left;
            padding: 2mm;
        }

        /* ID Card Base Styles */
        .id-card {
            width: 85.6mm;
            height: 53.98mm;
            border: 1px solid #ccc;
            border-radius: 3mm;
            overflow: hidden;
            background: white;
            position: relative;
        }

        /* Front Card Styles */
        .card-front {
            background-color: #1e3a5f;
        }

        .card-front-header {
            background-color: #ffffff;
            padding: 3mm 4mm;
            display: table;
            width: 100%;
        }

        .logo-cell {
            display: table-cell;
            width: 12mm;
            vertical-align: middle;
        }

        .logo-cell img {
            width: 10mm;
            height: 10mm;
            object-fit: contain;
        }

        .logo-placeholder {
            width: 10mm;
            height: 10mm;
            background: #e5e7eb;
            border-radius: 2mm;
        }

        .school-name-cell {
            display: table-cell;
            vertical-align: middle;
            padding-left: 2mm;
        }

        .school-name {
            font-size: 9px;
            font-weight: bold;
            color: #1e3a5f;
            line-height: 1.2;
        }

        .card-type {
            font-size: 7px;
            color: #4a5568;
            margin-top: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-front-body {
            padding: 3mm 4mm;
            display: table;
            width: 100%;
            height: 35mm;
            background-color: #1e3a5f;
        }

        .photo-cell {
            display: table-cell;
            width: 22mm;
            vertical-align: top;
        }

        .student-photo {
            width: 20mm;
            height: 25mm;
            border: 2px solid white;
            border-radius: 2mm;
            object-fit: cover;
            background: #e5e7eb;
        }

        .photo-placeholder {
            width: 20mm;
            height: 25mm;
            border: 2px solid white;
            border-radius: 2mm;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 6px;
            text-align: center;
        }

        .info-cell {
            display: table-cell;
            vertical-align: top;
            padding-left: 3mm;
        }

        .student-name {
            font-size: 10px;
            font-weight: bold;
            color: white;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }

        .info-row {
            margin-bottom: 1.5mm;
        }

        .info-label {
            font-size: 6px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-value {
            font-size: 8px;
            color: white;
            font-weight: 500;
        }

        .card-front-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #152a45;
            padding: 2mm 4mm;
        }

        .academic-year {
            font-size: 7px;
            color: #e2e8f0;
            text-align: center;
        }

        /* Back Card Styles */
        .card-back {
            background: #f8f9fa;
        }

        .card-back-header {
            background: #1e3a5f;
            padding: 2mm 4mm;
            text-align: center;
        }

        .back-title {
            font-size: 8px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-back-body {
            padding: 3mm 4mm;
        }

        .back-section {
            margin-bottom: 2mm;
        }

        .back-section-title {
            font-size: 6px;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-bottom: 1mm;
            border-bottom: 0.5px solid #e5e7eb;
            padding-bottom: 0.5mm;
        }

        .back-info {
            font-size: 7px;
            color: #374151;
            line-height: 1.4;
        }

        .emergency-contact {
            background: #fff3cd;
            padding: 2mm;
            border-radius: 1mm;
            margin-bottom: 2mm;
        }

        .emergency-label {
            font-size: 6px;
            font-weight: bold;
            color: #856404;
            text-transform: uppercase;
        }

        .emergency-value {
            font-size: 7px;
            color: #856404;
        }

        .card-back-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: #1e3a5f;
            padding: 2mm 4mm;
        }

        .terms-text {
            font-size: 5px;
            color: #cbd5e0;
            text-align: center;
            line-height: 1.3;
        }

        .motto {
            font-size: 6px;
            font-style: italic;
            color: #6b7280;
            text-align: center;
            margin-top: 2mm;
        }

        /* Page Title */
        .page-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 5mm;
            padding-bottom: 2mm;
            border-bottom: 1px solid #e5e7eb;
        }

        .section-label {
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 3mm;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    @php
        $chunkedStudents = $students->chunk(10);
        $totalPages = $chunkedStudents->count();
    @endphp

    @foreach ($chunkedStudents as $pageIndex => $pageStudents)
        <!-- Front Cards Page -->
        <div class="page">
            <div class="page-title">{{ $school_data->school_name ?? 'School' }} - Student ID Cards</div>
            <div class="section-label">Front Side</div>

            <div class="cards-container">
                @foreach ($pageStudents->chunk(2) as $rowStudents)
                    <div class="cards-row clearfix">
                        @foreach ($rowStudents as $student)
                            <div class="card-wrapper">
                                <div class="id-card card-front">
                                    <div class="card-front-header">
                                        <div class="logo-cell">
                                            @if ($school_data->logo_path)
                                                <img src="{{ public_path($school_data->logo_path) }}"
                                                    alt="Logo">
                                            @else
                                                <div class="logo-placeholder"></div>
                                            @endif
                                        </div>
                                        <div class="school-name-cell">
                                            <div class="school-name">{{ $school_data->school_name ?? 'School Name' }}</div>
                                            <div class="card-type">Student Identification Card</div>
                                        </div>
                                    </div>

                                    <div class="card-front-body">
                                        <div class="photo-cell">
                                            @if ($student->photo_path)
                                                <img src="{{ public_path($student->photo_path) }}"
                                                    alt="Photo" class="student-photo">
                                            @else
                                                <div class="photo-placeholder">No Photo</div>
                                            @endif
                                        </div>
                                        <div class="info-cell">
                                            <div class="student-name">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </div>

                                            @php
                                                $currentClass = $student->currentClassRelation->first();
                                            @endphp

                                            <div class="info-row">
                                                <div class="info-label">Class</div>
                                                <div class="info-value">{{ $currentClass->name ?? 'N/A' }}</div>
                                            </div>

                                            <div class="info-row">
                                                <div class="info-label">Grade</div>
                                                <div class="info-value">{{ $currentClass->grade->name ?? 'N/A' }}</div>
                                            </div>

                                            <div class="info-row">
                                                <div class="info-label">Class Teacher</div>
                                                <div class="info-value">
                                                    {{ optional($currentClass->teacher)->first_name ?? '' }}
                                                    {{ optional($currentClass->teacher)->last_name ?? 'N/A' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-front-footer">
                                        <div class="academic-year">
                                            Academic Year: {{ $term->year ?? now()->year }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Back Cards Page -->
        <div class="page">
            <div class="page-title">{{ $school_data->school_name ?? 'School' }} - Student ID Cards</div>
            <div class="section-label">Back Side</div>

            <div class="cards-container">
                @foreach ($pageStudents->chunk(2) as $rowStudents)
                    <div class="cards-row clearfix">
                        @foreach ($rowStudents as $student)
                            <div class="card-wrapper">
                                <div class="id-card card-back">
                                    <div class="card-back-header">
                                        <div class="back-title">{{ $school_data->school_name ?? 'School' }}</div>
                                    </div>

                                    <div class="card-back-body">
                                        <div class="back-section">
                                            <div class="back-section-title">School Contact</div>
                                            <div class="back-info">
                                                {{ $school_data->physical_address ?? '' }}<br>
                                                Tel: {{ $school_data->telephone ?? 'N/A' }}
                                                @if ($school_data->email ?? false)
                                                    <br>{{ $school_data->email }}
                                                @endif
                                            </div>
                                        </div>

                                        <div class="emergency-contact">
                                            <div class="emergency-label">Emergency Contact</div>
                                            <div class="emergency-value">
                                                @if ($student->sponsor)
                                                    {{ $student->sponsor->title ?? '' }}
                                                    {{ $student->sponsor->first_name ?? '' }}
                                                    {{ $student->sponsor->last_name ?? '' }}<br>
                                                    Tel: {{ $student->sponsor->telephone ?? $student->sponsor->cell_phone ?? 'N/A' }}
                                                @else
                                                    Not specified
                                                @endif
                                            </div>
                                        </div>

                                        @if ($school_data->motto ?? false)
                                            <div class="motto">"{{ $school_data->motto }}"</div>
                                        @endif
                                    </div>

                                    <div class="card-back-footer">
                                        <div class="terms-text">
                                            This card is the property of {{ $school_data->school_name ?? 'the school' }}.
                                            If found, please return to the school office.
                                            Valid for {{ $term->year ?? now()->year }} academic year only.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</body>

</html>
