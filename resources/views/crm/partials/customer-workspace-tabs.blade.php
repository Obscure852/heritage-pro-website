<div class="crm-tabs">
    <a href="{{ route('crm.leads.index') }}" @class(['crm-tab', 'is-active' => request()->routeIs('crm.leads.*')])>
        <i class="bx bx-user-voice"></i>
        <span>Leads</span>
    </a>
    <a href="{{ route('crm.customers.index') }}" @class(['crm-tab', 'is-active' => request()->routeIs('crm.customers.*')])>
        <i class="bx bx-building-house"></i>
        <span>Customers</span>
    </a>
</div>
