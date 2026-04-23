<div class="crm-attendance-legend crm-inline" style="flex-wrap: wrap; gap: 8px; margin-bottom: 16px;">
    @foreach ($codes as $code)
        <span class="crm-pill" style="background: {{ $code->color }}20; color: {{ $code->color }}; font-size: 11px; padding: 4px 10px; font-weight: 600;">
            {{ $code->code }} — {{ $code->label }}
        </span>
    @endforeach
</div>
