<div style="padding: 20px; background: #fff;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
        <div style="font-family: var(--font-display); font-size: 16px; font-weight: 700;">Fees — Term 2</div>
        <span style="margin-left: auto; padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; background: var(--success-50); color: var(--success-700);">● 78% collected</span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 16px;">
        @foreach ([['BWP 412,400', 'Collected', 'var(--success-700)'], ['BWP 84,200', 'Outstanding', 'var(--warning-600)'], ['BWP 28,600', 'Overdue', 'var(--danger-700)']] as $metric)
            <div style="padding: 12px 14px; border: 1px solid var(--border-1); border-radius: 10px;">
                <div style="font-family: var(--font-display); font-size: 16px; font-weight: 700; color: {{ $metric[2] }};">{{ $metric[0] }}</div>
                <div style="font-size: 11px; color: var(--fg-3);">{{ $metric[1] }}</div>
            </div>
        @endforeach
    </div>
    <div style="border: 1px solid var(--border-1); border-radius: 10px; overflow: hidden;">
        <div style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 1fr 1fr; padding: 10px 14px; background: var(--bg-subtle); font-size: 10px; font-weight: 700; color: var(--fg-2); text-transform: uppercase; letter-spacing: 0.06em;">
            <div>Student</div><div>Invoice</div><div>Due</div><div>Amount</div><div>Status</div>
        </div>
        @foreach ([['Boitumelo Mosadi', 'INV-482-T2', '28 Apr', 'BWP 12,800', 'Paid', 'ok'], ['Atang Nkhata', 'INV-215-T2', '28 Apr', 'BWP 12,800', 'Partial', 'warn'], ['Lesedi Moeti', 'INV-618-T2', '28 Apr', 'BWP 14,200', 'Paid', 'ok'], ['Kago Tshekiso', 'INV-704-T2', '14 Mar', 'BWP 12,800', 'Overdue', 'danger'], ['Naledi Pilane', 'INV-322-T2', '28 Apr', 'BWP 12,800', 'Paid', 'ok']] as $row)
            <div style="display: grid; grid-template-columns: 2fr 1.2fr 1fr 1fr 1fr; padding: 10px 14px; font-size: 11px; border-top: 1px solid var(--border-1); align-items: center;">
                <div style="font-weight: 600; color: var(--fg-1);">{{ $row[0] }}</div>
                <div style="font-family: var(--font-mono); color: var(--fg-2); font-size: 10px;">{{ $row[1] }}</div>
                <div style="color: var(--fg-3);">{{ $row[2] }}</div>
                <div style="font-weight: 600;">{{ $row[3] }}</div>
                <div>
                    <span style="padding: 2px 8px; font-size: 10px; font-weight: 700; border-radius: 999px; background: {{ $row[5] === 'ok' ? 'var(--success-50)' : ($row[5] === 'warn' ? 'var(--warning-50)' : 'var(--danger-50)') }}; color: {{ $row[5] === 'ok' ? 'var(--success-700)' : ($row[5] === 'warn' ? 'var(--warning-600)' : 'var(--danger-700)') }};">● {{ $row[4] }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>
