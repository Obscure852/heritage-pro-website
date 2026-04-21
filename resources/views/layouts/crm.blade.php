<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Heritage Pro CRM') | Heritage Pro CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Heritage Pro CRM Workspace">
    <meta name="author" content="Platinum Developers">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layouts.crm-head-css')
</head>
<body class="crm-body pace-done" data-sidebar="light" data-layout="vertical" data-sidebar-size="lg">
    <div id="layout-wrapper">
        @include('layouts.crm-topbar')
        @include('layouts.crm-sidebar')

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    @php
                        $routeName = request()->route()?->getName();
                        $modules = collect(config('heritage_crm.modules', []))
                            ->map(fn (array $module, string $key) => $module + ['key' => $key]);

                        $activeModule = $modules->first(function (array $module) use ($routeName) {
                            foreach ($module['match'] ?? [$module['route']] as $pattern) {
                                if (\Illuminate\Support\Str::is($pattern, (string) $routeName)) {
                                    return true;
                                }
                            }

                            return false;
                        });

                        $activeChild = collect($activeModule['children'] ?? [])->first(function (array $child) use ($routeName) {
                            foreach ($child['match'] ?? [$child['route']] as $pattern) {
                                if (\Illuminate\Support\Str::is($pattern, (string) $routeName)) {
                                    return true;
                                }
                            }

                            return false;
                        });

                        $heading = trim($__env->yieldContent('crm_heading', 'Heritage Pro CRM'));
                        $breadcrumbTitle = trim($__env->yieldContent('crm_breadcrumb_title'));
                        $moduleLabel = $activeChild['label'] ?? ($activeModule['label'] ?? $heading);

                        if ($breadcrumbTitle === '') {
                            if ($routeName === 'crm.dashboard') {
                                $breadcrumbTitle = 'Dashboard';
                            } else {
                                $breadcrumbTitle = $moduleLabel;
                            }
                        }

                        $breadcrumbOne = trim($__env->yieldContent('crm_breadcrumb_1'));
                        $breadcrumbOneUrl = trim($__env->yieldContent('crm_breadcrumb_1_url'));
                        $breadcrumbTwo = trim($__env->yieldContent('crm_breadcrumb_2'));
                        $breadcrumbTwoUrl = trim($__env->yieldContent('crm_breadcrumb_2_url'));

                        if ($breadcrumbOne === '') {
                            $breadcrumbOne = 'CRM';
                            $breadcrumbOneUrl = route('crm.dashboard');
                        }
                    @endphp

                    @include('components.breadcrumb', [
                        'title' => $breadcrumbTitle,
                        'li_1' => $breadcrumbOne,
                        'li_1_url' => $breadcrumbOneUrl,
                        'li_2' => $breadcrumbTwo !== '' ? $breadcrumbTwo : null,
                        'li_2_url' => $breadcrumbTwoUrl !== '' ? $breadcrumbTwoUrl : null,
                    ])

                    <div class="crm-page-header">
                        <div>
                            <h1 class="crm-page-title">@yield('crm_heading', 'Heritage Pro CRM')</h1>
                            <p class="crm-page-subtitle">@yield('crm_subheading', 'Manage customers, contacts, requests, pipeline settings, and internal communication from one workspace.')</p>
                        </div>

                        @hasSection('crm_actions')
                            <div class="crm-page-tools">
                                @yield('crm_actions')
                            </div>
                        @endif
                    </div>

                    @include('crm.partials.flash')
                    @yield('content')
                </div>
            </div>

            @include('layouts.crm-footer')
        </div>
    </div>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var shellRoot = document.getElementById('layout-wrapper');

            if (window.jQuery && typeof window.jQuery.fn.metisMenu === 'function') {
                window.jQuery('#side-menu').metisMenu();
            }

            var menuToggle = document.getElementById('vertical-menu-btn');
            if (menuToggle) {
                menuToggle.addEventListener('click', function () {
                    document.body.classList.toggle('sidebar-enable');

                    if (window.innerWidth >= 992) {
                        document.body.classList.toggle('vertical-collpsed');
                    }
                });
            }

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function () {
                    var submitBtn = form.querySelector('button[type="submit"].btn-loading');

                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });

            document.querySelectorAll('.crm-form input, .crm-form textarea, .crm-filter-form input, .crm-filter-form textarea').forEach(function (field) {
                if (field.hasAttribute('placeholder')) {
                    return;
                }

                if (['checkbox', 'radio', 'hidden', 'submit', 'button', 'file'].indexOf(field.type) !== -1) {
                    return;
                }

                var fieldId = field.getAttribute('id');
                var label = fieldId ? document.querySelector('label[for="' + fieldId + '"]') : null;
                var labelText = label ? label.textContent.replace('*', '').trim() : '';

                if (labelText !== '') {
                    field.setAttribute('placeholder', 'Enter ' + labelText.toLowerCase());
                }
            });

            function debounce(callback, wait) {
                var timeout;

                return function () {
                    var args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        callback.apply(null, args);
                    }, wait);
                };
            }

            function hidePanel(panel, trigger) {
                if (!panel || !trigger) {
                    return;
                }

                panel.hidden = true;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            function showPanel(panel, trigger) {
                if (!panel || !trigger) {
                    return;
                }

                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }

            function wireFloatingPanel(triggerId, panelId, options) {
                var trigger = document.getElementById(triggerId);
                var panel = document.getElementById(panelId);

                if (!trigger || !panel) {
                    return {hide: function () {}};
                }

                trigger.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    var willShow = panel.hidden;

                    if (options && Array.isArray(options.closeOthers)) {
                        options.closeOthers.forEach(function (closeItem) {
                            closeItem.hide();
                        });
                    }

                    if (willShow) {
                        showPanel(panel, trigger);
                        if (options && typeof options.onOpen === 'function') {
                            options.onOpen();
                        }
                    } else {
                        hidePanel(panel, trigger);
                    }
                });

                panel.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                return {
                    hide: function () {
                        hidePanel(panel, trigger);
                    },
                    panel: panel,
                    trigger: trigger
                };
            }

            var searchInput = document.getElementById('crm-global-search');
            var searchResults = document.getElementById('crm-search-results');
            var shortcutHint = document.getElementById('crm-search-shortcut-hint');
            var isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform);

            if (shortcutHint) {
                shortcutHint.innerHTML = isMac ? '<kbd>⌘</kbd><span>+</span><kbd>K</kbd>' : '<kbd>Ctrl</kbd><span>+</span><kbd>Space</kbd>';
            }

            function renderSearchResults(payload) {
                if (!searchResults) {
                    return;
                }

                if (!payload.sections || payload.sections.length === 0) {
                    searchResults.innerHTML = '<div class="no-results">No matching CRM records found.</div>';
                    searchResults.hidden = false;
                    return;
                }

                searchResults.innerHTML = payload.sections.map(function (section) {
                    return '<div class="search-section">' +
                        '<div class="section-header"><i class="' + section.icon + '"></i><span>' + section.label + '</span></div>' +
                        section.items.map(function (item) {
                            return '<a class="result-item" href="' + item.url + '">' +
                                '<div class="result-name">' + item.label + '</div>' +
                                '<div class="result-details">' + (item.secondary || '') + '</div>' +
                            '</a>';
                        }).join('') +
                    '</div>';
                }).join('');

                searchResults.hidden = false;
            }

            var searchRequest = debounce(function () {
                var term = searchInput.value.trim();

                if (term.length < 2) {
                    searchResults.hidden = true;
                    searchResults.innerHTML = '';
                    return;
                }

                searchResults.hidden = false;
                searchResults.innerHTML = '<div class="loading-results"><span class="loading-spinner"></span><span>Searching CRM...</span></div>';

                fetch('{{ route('crm.search') }}?q=' + encodeURIComponent(term), {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(renderSearchResults)
                    .catch(function () {
                        searchResults.innerHTML = '<div class="no-results">Search is temporarily unavailable.</div>';
                    });
            }, 220);

            if (searchInput && searchResults) {
                searchInput.addEventListener('input', searchRequest);
                searchInput.addEventListener('focus', function () {
                    if (searchResults.innerHTML !== '') {
                        searchResults.hidden = false;
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                var openSearch = (isMac && event.metaKey && event.key.toLowerCase() === 'k')
                    || (!isMac && event.ctrlKey && event.code === 'Space')
                    || (!isMac && event.altKey && event.code === 'Space');

                if (openSearch && searchInput) {
                    event.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }

                if (event.key === 'Escape' && searchResults) {
                    searchResults.hidden = true;
                }
            });

            var presenceCount = document.getElementById('crm-presence-count');
            var presencePanelCount = document.getElementById('crm-presence-panel-count');
            var presenceList = document.getElementById('crm-presence-list');
            var presenceSearch = document.getElementById('crm-presence-search');

            function renderPresenceUsers(payload) {
                if (!presenceCount || !presencePanelCount || !presenceList) {
                    return;
                }

                presenceCount.textContent = payload.online_count || 0;
                presencePanelCount.textContent = (payload.online_count || 0) + ' online';

                if (!payload.users || payload.users.length === 0) {
                    presenceList.innerHTML = '<div class="staff-presence-empty">No other CRM users are online right now.</div>';
                    return;
                }

                presenceList.innerHTML = payload.users.map(function (user) {
                    return '<div class="staff-presence-item">' +
                        '<span class="crm-initial-avatar">' + user.initials + '</span>' +
                        '<div class="staff-presence-meta">' +
                            '<div class="staff-presence-name">' + user.name + '</div>' +
                            '<div class="staff-presence-role">' + user.role + '</div>' +
                            '<div class="staff-presence-last-seen">Last active ' + user.last_seen_label + '</div>' +
                        '</div>' +
                        '<a href="' + user.discussion_url + '" class="btn btn-primary btn-sm">Message</a>' +
                    '</div>';
                }).join('');
            }

            function fetchPresence(searchTerm) {
                var query = searchTerm ? '?q=' + encodeURIComponent(searchTerm) : '';

                return fetch('{{ route('crm.presence.launcher') }}' + query, {
                    headers: {
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        renderPresenceUsers(payload);
                        return payload;
                    })
                    .catch(function () {
                        if (presenceList) {
                            presenceList.innerHTML = '<div class="staff-presence-empty">Unable to load online CRM users.</div>';
                        }
                    });
            }

            var presencePanelHandle = wireFloatingPanel('crm-presence-trigger', 'crm-presence-panel', {
                onOpen: function () {
                    fetchPresence(presenceSearch ? presenceSearch.value.trim() : '');
                }
            });

            var launcherPanelHandle = wireFloatingPanel('crm-module-launcher-trigger', 'crm-module-launcher-menu', {
                closeOthers: [presencePanelHandle]
            });

            var userPanelHandle = wireFloatingPanel('crm-user-dropdown', 'crm-user-dropdown-panel', {
                closeOthers: [presencePanelHandle, launcherPanelHandle]
            });

            if (presencePanelHandle && launcherPanelHandle && presencePanelHandle.trigger) {
                presencePanelHandle.trigger.addEventListener('click', function () {
                    launcherPanelHandle.hide();
                    userPanelHandle.hide();
                });
                launcherPanelHandle.trigger.addEventListener('click', function () {
                    presencePanelHandle.hide();
                    userPanelHandle.hide();
                });
            }

            if (presenceSearch) {
                presenceSearch.addEventListener('input', debounce(function () {
                    fetchPresence(presenceSearch.value.trim());
                }, 220));
            }

            function sendHeartbeat() {
                fetch('{{ route('crm.presence.heartbeat') }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        path: window.location.pathname
                    })
                }).catch(function () {});
            }

            sendHeartbeat();
            fetchPresence('');

            var pollSeconds = {{ (int) config('heritage_crm.presence.launcher_poll_seconds', 45) }};
            window.setInterval(function () {
                sendHeartbeat();
                fetchPresence(presenceSearch ? presenceSearch.value.trim() : '');
            }, pollSeconds * 1000);

            document.addEventListener('click', function (event) {
                if (searchResults && searchInput && !searchResults.contains(event.target) && event.target !== searchInput) {
                    searchResults.hidden = true;
                }

                presencePanelHandle.hide();
                launcherPanelHandle.hide();
                userPanelHandle.hide();
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
