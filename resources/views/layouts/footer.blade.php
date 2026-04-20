<style>
    .flashing-icon {
        color: red;
        animation: flashing 1s infinite;
    }

    @keyframes flashing {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }
</style>
<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>
                    document.write(new Date().getFullYear())
                </script> © Heritage  Developers.
            </div>
            <div class="col-sm-6">
                @php
                    $currentYear = date('Y');
                    $nextYear = $currentYear + 1;
                    $heritage = '<a href="https://www.heritagepro.co">Heritage Pro School Management System</a>';
                    $latestLicense = \App\Models\License::where('year', $currentYear)->first();
                    $nextYearLicense = \App\Models\License::where('year', $nextYear)->first();
                    $schoolName = $latestLicense ? $latestLicense->name : null;

                    $showTimeIcon = false;
                    if ($latestLicense) {
                        $endDate = \Carbon\Carbon::parse($latestLicense->end_date);
                        $currentDate = \Carbon\Carbon::now();
                        $diffInMonths = $endDate->diffInMonths($currentDate);

                        if ($diffInMonths <= 2 && !$nextYearLicense) {
                            $showTimeIcon = true;
                        }
                    }
                @endphp
                <div class="text-sm-end d-none d-sm-block">
                    @if (isset($schoolName))
                        <p>{{ $schoolName }}.
                            @if ($showTimeIcon)
                                <i class="bx bxs-time flashing-icon" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="System license expires soon"></i>
                            @endif
                        </p>
                    @else
                        <p>{!! $heritage !!}.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
