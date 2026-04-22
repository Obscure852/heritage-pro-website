<div class="crm-discussions-nav" style="margin-bottom: 0;">
    <a href="{{ route('crm.discussions.app.direct.create', request()->query()) }}" class="crm-discussions-nav-link {{ ($active ?? '') === 'direct' ? 'active' : '' }}">
        <i class="bx bx-message-square-dots"></i> Direct message
    </a>
    <a href="{{ route('crm.discussions.app.bulk.create', request()->query()) }}" class="crm-discussions-nav-link {{ ($active ?? '') === 'bulk' ? 'active' : '' }}">
        <i class="bx bx-group"></i> Group chat
    </a>
</div>
