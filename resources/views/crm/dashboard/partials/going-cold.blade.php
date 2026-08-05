<section class="crm-card">
    <div class="crm-card-title">
        <div>
            <p class="crm-kicker">Going cold</p>
            <h2>Untouched for {{ $goingColdDays }} days</h2>
            <p>Open sales work with no logged contact recently. Oldest first.</p>
        </div>
    </div>

    @if ($goingCold->isEmpty())
        <div class="crm-empty">Every open sales request has been contacted recently.</div>
    @else
        <div class="crm-table-wrap">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Account</th>
                        <th>Owner</th>
                        <th>Last contact</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($goingCold as $request)
                        <tr>
                            <td><a href="{{ route('crm.requests.show', $request) }}">{{ $request->title }}</a></td>
                            <td>{{ $request->customer?->company_name ?: $request->lead?->company_name ?: 'Unassigned' }}</td>
                            <td>{{ $request->owner?->name ?: 'Not assigned' }}</td>
                            <td>
                                @if ($request->last_contact_at)
                                    <span class="crm-pill muted">{{ $request->last_contact_at->diffForHumans() }}</span>
                                @else
                                    <span class="crm-pill danger">Never contacted</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
