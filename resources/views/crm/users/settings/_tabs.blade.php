<div class="crm-tabs crm-tabs-top">
    <a href="{{ route('crm.users.settings.departments') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'departments'])>
        <i class="bx bx-building-house"></i>
        <span>Departments</span>
    </a>
    <a href="{{ route('crm.users.settings.positions') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'positions'])>
        <i class="bx bx-briefcase-alt-2"></i>
        <span>Positions</span>
    </a>
    <a href="{{ route('crm.users.settings.filters') }}" @class(['crm-tab', 'is-active' => ($activeSection ?? '') === 'filters'])>
        <i class="bx bx-slider-alt"></i>
        <span>Custom filters</span>
    </a>
</div>
