<section id="section-{{ $sectionData['section']->key }}" class="section-panel mb-4">
    <div class="section-panel-header">
        <div class="section-panel-title">{{ $sectionData['section']->label }}</div>
        <p class="section-panel-subtitle mb-0">Signature rows resolve their image path from the signer on the user record.</p>
    </div>
    <div class="section-panel-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Step</th>
                        <th>Scope</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Signer</th>
                        <th>Signature</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($plan->signatures as $signature)
                        <tr>
                            <td>{{ $signature->approval_step_key }}</td>
                            <td>{{ $signature->review?->period_key ? $viewService->periodLabel($signature->review->period_key) : 'Plan Level' }}</td>
                            <td>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $signature->role_type)) }}</td>
                            <td><span class="badge bg-light text-dark">{{ ucfirst($signature->status) }}</span></td>
                            <td>{{ $signature->signer?->full_name ?? 'Pending' }}</td>
                            <td>{{ $signature->resolved_signature_path ?? 'No signature on file' }}</td>
                            <td class="text-end">
                                @if ($reviewService->canSignSignature($plan, $signature, auth()->user()))
                                    <form method="POST" action="{{ route('staff.pdp.plans.signatures.sign', [$plan, $signature]) }}">
                                        @csrf
                                        @include('pdp.partials.submit-button', [
                                            'label' => 'Sign',
                                            'loadingText' => 'Signing...',
                                            'icon' => 'bx bx-pen',
                                            'variant' => 'btn-outline-primary',
                                        ])
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted">No signature steps are configured for this section.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
