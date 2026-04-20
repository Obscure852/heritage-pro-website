@extends('layouts.master-sponsor-portal')
@section('title', 'Student Report Cards Timeline')

@section('css')
    <style>
        /* Timeline Container */
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        /* Vertical Line */
        .timeline::before {
            content: '';
            position: absolute;
            width: 2px;
            background: #3498db;
            top: 0;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Timeline Item */
        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }

        /* Alternate Sides for Odd/Even Items */
        .timeline-item:nth-child(odd) .timeline-content {
            margin-left: 60px;
            text-align: left;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-right: 60px;
            text-align: right;
        }

        /* Timeline Dot */
        .timeline-dot {
            width: 16px;
            height: 16px;
            background: #3498db;
            border-radius: 50%;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        /* Content Box */
        .timeline-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 45%;
        }

        .timeline-content h5 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .timeline-content small {
            color: #6b7280;
        }

        /* Responsive Design */
        @media (max-width: 767px) {
            .timeline::before {
                left: 20px;
            }

            .timeline-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .timeline-item:nth-child(odd) .timeline-content,
            .timeline-item:nth-child(even) .timeline-content {
                margin-left: 40px;
                margin-right: 0;
                text-align: left;
                width: 100%;
            }

            .timeline-dot {
                left: 20px;
                transform: translateX(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Report Cards Timeline</h4>
                        <p class="text-muted">View your children's report cards up to the current term.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                @forelse ($students as $student)
                    <h3 class="mt-4">{{ $student->getFullNameAttribute() }}</h3>
                    <div class="timeline">
                        @forelse ($terms as $term)
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-content">
                                    <h5>Term {{ $term->term }}, {{ $term->year }}</h5>
                                    <small>Start Date: {{ $term->start_date->format('M d, Y') }}</small><br>
                                    <small>End Date: {{ $term->end_date->format('M d, Y') }}</small><br>
                                    <a href="javascript:void(0);"
                                        onclick="pdfReportCardPopupJuniorParentPortal('{{ $student->id }}', '{{ $term->id }}')">
                                        <i class="bx bxs-file-pdf text-danger" style="font-size: 24px; color: #3498db;"></i>
                                        <small style="margin-bottom:20px;">Term {{ $term->term }},
                                            {{ $term->year }} Report Card</small>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="bx bx-book-bookmark text-muted display-4"></i>
                                <p class="text-muted mt-3">No report cards available yet for
                                    {{ $student->getFullNameAttribute() }}.</p>
                            </div>
                        @endforelse
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bx bx-book-bookmark text-muted display-4"></i>
                        <p class="text-muted mt-3">No students associated with this sponsor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function pdfReportCardPopupJuniorParentPortal(studentId, selectedTermId) {
            var url =
                "{{ route('assessment.junior-pdf-report-card-parent', ['id' => 'tempId', 'selectedTermId' => 'tempSelectedTermId']) }}";
            url = url.replace('tempId', studentId).replace('tempSelectedTermId', selectedTermId);
            window.open(url, 'PDFWindow', 'width=800,height=1000');
        }
    </script>
@endsection
