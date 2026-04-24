<div class="browser-light-surface" style="padding: 20px; background: #fff;">
    <div style="display: flex; gap: 14px; align-items: center; margin-bottom: 20px;">
        <div style="width: 56px; height: 56px; border-radius: 999px; background: var(--brand-gradient); color: #fff; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-weight: 700; font-size: 18px;">BM</div>
        <div>
            <div style="font-family: var(--font-display); font-size: 18px; font-weight: 700;">Boitumelo Mosadi</div>
            <div style="font-size: 12px; color: var(--fg-3);">STU-2025-0482 · Form 3R · House: Khama · Admitted Jan 2023</div>
        </div>
        <div style="margin-left: auto; display: flex; gap: 6px;">
            <span style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; background: var(--success-50); color: var(--success-700);">Active</span>
            <span style="padding: 4px 10px; font-size: 11px; font-weight: 700; border-radius: 999px; background: var(--success-50); color: var(--success-700);">Paid</span>
        </div>
    </div>
    <div style="display: flex; gap: 0; border-bottom: 1px solid var(--border-1); margin-bottom: 14px;">
        @foreach (['Overview', 'Academics', 'Attendance', 'Fees', 'Health', 'Documents'] as $index => $tab)
            <div style="padding: 8px 14px; font-size: 12px; font-weight: 600; color: {{ $index === 0 ? 'var(--brand-indigo-500)' : 'var(--fg-3)' }}; border-bottom: {{ $index === 0 ? '2px solid var(--brand-indigo-500)' : '2px solid transparent' }}; margin-bottom: -1px;">{{ $tab }}</div>
        @endforeach
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12px;">
        @foreach ([['Date of birth', '14 March 2009'], ['Omang / ID', '409031214'], ['Guardian', 'Keneilwe Mosadi · +267 7123 4567'], ['Address', 'Plot 4820, Gaborone West'], ['Blood group', 'O+'], ['Allergies', 'Penicillin']] as $detail)
            <div style="padding: 10px 12px; background: var(--bg-subtle); border-radius: 8px;">
                <div style="font-size: 10px; color: var(--fg-3); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 3px;">{{ $detail[0] }}</div>
                <div style="font-weight: 600; color: var(--fg-1);">{{ $detail[1] }}</div>
            </div>
        @endforeach
    </div>
    <div style="margin-top: 14px; padding: 10px 12px; background: var(--brand-indigo-50); border-radius: 8px; font-size: 12px; color: var(--brand-indigo-700); display: flex; gap: 8px; align-items: center;">
        <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--brand-indigo-500);"></span>
        Form-teacher note added 2 Mar — Boitumelo elected form prefect for Term 1.
    </div>
</div>
