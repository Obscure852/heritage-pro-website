@php
    $crmUser = auth()->user();
    $moduleRegistry = app(\App\Services\Crm\CrmModuleRegistry::class);
    $moduleGroups = $moduleRegistry->groupedSidebarModulesFor($crmUser);
@endphp
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                @foreach ($moduleGroups['workspace'] as $module)
                    @php($children = $moduleRegistry->childrenFor($crmUser, $module))
                    @php($isActive = request()->routeIs(...$moduleRegistry->matchPatterns($module)))
                    @if ($children->isNotEmpty())
                        <li class="{{ $isActive ? 'mm-active' : '' }}">
                            <a href="javascript:void(0);" class="has-arrow {{ $isActive ? 'active' : '' }}">
                                <i class="{{ $module['icon'] }}"></i>
                                <span>{{ $module['label'] }}</span>
                                @if (($module['key'] ?? null) === 'discussions')
                                    <span class="crm-sidebar-badge is-alert" id="crm-sidebar-discussions-badge" hidden>0</span>
                                @endif
                            </a>
                            <ul class="sub-menu {{ $isActive ? 'mm-show' : '' }}" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                                @foreach ($children as $child)
                                    <li>
                                        <a href="{{ route($child['route']) }}" class="crm-sidebar-child-link {{ request()->routeIs(...($child['match'] ?? [$child['route']])) ? 'active' : '' }}">
                                            <span>{{ $child['label'] }}</span>
                                            @if (($module['key'] ?? null) === 'discussions' && in_array($child['key'] ?? null, ['app', 'email', 'whatsapp'], true))
                                                <span class="crm-sidebar-badge is-alert" data-crm-discussion-channel-badge="{{ $child['key'] }}" hidden>0</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li>
                            <a href="{{ route($module['route']) }}" class="{{ $isActive ? 'active' : '' }}">
                                <i class="{{ $module['icon'] }}"></i>
                                <span>{{ $module['label'] }}</span>
                                @if (($module['key'] ?? null) === 'discussions')
                                    <span class="crm-sidebar-badge is-alert" id="crm-sidebar-discussions-badge" hidden>0</span>
                                @endif
                            </a>
                        </li>
                    @endif
                @endforeach

                @if (! empty($moduleGroups['administration']))
                    <li class="menu-title mt-3">
                        <span>Administration</span>
                    </li>

                    @foreach ($moduleGroups['administration'] as $module)
                        @php($children = $moduleRegistry->childrenFor($crmUser, $module))
                        @php($isActive = request()->routeIs(...$moduleRegistry->matchPatterns($module)))
                        @if ($children->isNotEmpty())
                            <li class="{{ $isActive ? 'mm-active' : '' }}">
                                <a href="javascript:void(0);" class="has-arrow {{ $isActive ? 'active' : '' }}">
                                    <i class="{{ $module['icon'] }}"></i>
                                    <span>{{ $module['label'] }}</span>
                                    @if (($module['key'] ?? null) === 'discussions')
                                        <span class="crm-sidebar-badge is-alert" id="crm-sidebar-discussions-badge" hidden>0</span>
                                    @endif
                                </a>
                                <ul class="sub-menu {{ $isActive ? 'mm-show' : '' }}" aria-expanded="{{ $isActive ? 'true' : 'false' }}">
                                    @foreach ($children as $child)
                                        <li>
                                            <a href="{{ route($child['route']) }}" class="crm-sidebar-child-link {{ request()->routeIs(...($child['match'] ?? [$child['route']])) ? 'active' : '' }}">
                                                <span>{{ $child['label'] }}</span>
                                                @if (($module['key'] ?? null) === 'discussions' && in_array($child['key'] ?? null, ['app', 'email', 'whatsapp'], true))
                                                    <span class="crm-sidebar-badge is-alert" data-crm-discussion-channel-badge="{{ $child['key'] }}" hidden>0</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li>
                                <a href="{{ route($module['route']) }}" class="{{ $isActive ? 'active' : '' }}">
                                    <i class="{{ $module['icon'] }}"></i>
                                    <span>{{ $module['label'] }}</span>
                                    @if (($module['key'] ?? null) === 'discussions')
                                        <span class="crm-sidebar-badge is-alert" id="crm-sidebar-discussions-badge" hidden>0</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>

    </div>
</div>
