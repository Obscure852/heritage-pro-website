@php
    $studentTotal = $houses->sum(fn($house) => $house->students->count());
    $userTotal = $houses->sum('users_count');
@endphp

<div id="housesTableSummary"
    data-house-count="{{ $houses->count() }}"
    data-student-count="{{ $studentTotal }}"
    data-user-count="{{ $userTotal }}">
</div>

<div class="card-shell">
    <div class="card-body">
        <div class="table-responsive">
            <table id="houses" class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>House</th>
                        <th>Leadership</th>
                        <th>Counts</th>
                        <th>Classes</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($housesWithClasses as $index => $houseData)
                        @php
                            $house = $houseData['house'];
                            $classes = $houseData['classes'];
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="house-table-name">
                                    <span class="house-color-swatch house-card-swatch" style="background: {{ $house->color_code }};"></span>
                                    <div class="house-table-name-copy">
                                        <div class="fw-semibold">{{ $house->name }}</div>
                                        <div class="activity-meta-pills">
                                            <span class="summary-chip house-chip"
                                                style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.14) }};">
                                                {{ strtoupper($house->color_code) }}
                                            </span>
                                            <span class="summary-chip pill-muted">
                                                <i class="fas fa-calendar-alt"></i> {{ $house->year }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="small">
                                <div class="fw-semibold">{{ $house->houseHead->fullName ?? 'Not assigned' }}</div>
                                <div class="house-table-meta">
                                    Assistant: {{ $house->houseAssistant->fullName ?? 'Not assigned' }}
                                </div>
                            </td>
                            <td>
                                <div class="house-count-stack">
                                    <div class="house-count-item"><strong>{{ $house->students->count() }}</strong> students</div>
                                    <div class="house-count-item"><strong>{{ $house->users_count }}</strong> users</div>
                                </div>
                            </td>
                            <td>
                                @if ($classes->isNotEmpty())
                                    @php
                                        $classLimit = 6;
                                        $visibleClasses = $classes->take($classLimit);
                                        $hiddenClasses = $classes->slice($classLimit);
                                        $hiddenCount = $hiddenClasses->count();
                                        $hiddenTitle = $hiddenClasses->pluck('name')->filter()->implode(', ');
                                    @endphp
                                    <div class="house-classes">
                                        @foreach ($visibleClasses as $class)
                                            <span class="summary-chip house-chip"
                                                style="--house-color: {{ $house->color_code }}; --house-color-soft: {{ $house->colorWithAlpha(0.12) }};">
                                                {{ $class->name ?? 'Unassigned' }}
                                            </span>
                                        @endforeach
                                        @if ($hiddenCount > 0)
                                            <span class="summary-chip house-chip-more"
                                                title="{{ $hiddenTitle }}">
                                                +{{ $hiddenCount }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="summary-empty">No active classes in this house yet.</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="action-buttons">
                                    <a href="{{ route('house.house-view', ['houseId' => $house->id]) }}"
                                        class="btn btn-sm btn-outline-info" title="View House">
                                        <i class="bx bx-show"></i>
                                    </a>

                                    @if (!session('is_past_term'))
                                        @can('manage-houses')
                                            <a href="{{ route('house.open-house', ['id' => $house->id]) }}"
                                                class="btn btn-sm btn-outline-primary" title="Allocate Students">
                                                <i class="bx bx-layer"></i>
                                            </a>

                                            <a href="{{ route('house.open-house-users', ['id' => $house->id]) }}"
                                                class="btn btn-sm btn-outline-secondary" title="Allocate Users">
                                                <i class="bx bx-user-plus"></i>
                                            </a>

                                            <a href="{{ route('house.edit-house', $house->id) }}"
                                                class="btn btn-sm btn-outline-warning" title="Edit House">
                                                <i class="bx bx-edit"></i>
                                            </a>

                                            <a href="{{ route('house.delete-house', $house->id) }}"
                                                class="btn btn-sm btn-outline-danger" onclick="return confirmDeleteHouse()"
                                                title="Delete House">
                                                <i class="bx bx-trash"></i>
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div><i class="fas fa-home empty-state-icon"></i></div>
                                    <p class="mb-0">No houses are available for the selected term.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
