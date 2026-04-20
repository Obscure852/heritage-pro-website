@php
    $idleSession = app(\App\Services\Auth\IdleSessionService::class);
    $idleLogoutMethodNormalized = strtoupper($idleLogoutMethod ?? 'GET');
    $idleModalId = $idleModalId ?? 'idle-session-warning-modal-' . $idleGuard;
    $idleCountdownId = $idleCountdownId ?? 'idle-session-countdown-' . $idleGuard;
    $idleLogoutFormId = $idleLogoutFormId ?? 'idle-session-logout-form-' . $idleGuard;
    $idleLastActivityAt = $idleLastActivityAt ?? $idleSession->lastActivityTimestamp(request()->session(), $idleGuard);
    $idleConfig = [
        'guard' => $idleGuard,
        'userId' => $idleUserId,
        'timeoutSeconds' => $idleSession->timeoutSeconds(),
        'warningSeconds' => $idleSession->warningSeconds(),
        'activityUrl' => $idleActivityRoute,
        'loginUrl' => $idleLoginRoute,
        'logoutUrl' => $idleLogoutRoute,
        'logoutMethod' => $idleLogoutMethodNormalized,
        'logoutFormId' => $idleLogoutFormId,
        'modalId' => $idleModalId,
        'countdownId' => $idleCountdownId,
        'csrfToken' => csrf_token(),
        'initialLastActivityAt' => $idleLastActivityAt,
    ];
@endphp

<style>
    .idle-session-modal-dialog {
        margin-top: max(4rem, 8rem);
        margin-right: auto;
        margin-left: auto;
    }

    @media (max-width: 767.98px) {
        .idle-session-modal-dialog {
            margin-top: 4rem;
        }
    }
</style>

<div class="modal fade" id="{{ $idleModalId }}" tabindex="-1" aria-hidden="true" data-idle-session-modal>
    <div class="modal-dialog idle-session-modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Session Expiring Soon</h5>
            </div>
            <div class="modal-body">
                <p class="mb-2">You will be logged out in <strong id="{{ $idleCountdownId }}">60 seconds</strong> due to inactivity.</p>
                <p class="mb-0 text-muted">Select <strong>Stay Signed In</strong> to continue this session.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-idle-session-logout>Log Out Now</button>
                <button type="button" class="btn btn-primary" data-idle-session-keepalive>Stay Signed In</button>
            </div>
        </div>
    </div>
</div>

@if ($idleLogoutMethodNormalized === 'POST')
    <form id="{{ $idleLogoutFormId }}" action="{{ $idleLogoutRoute }}" method="POST" style="display: none;">
        @csrf
    </form>
@endif

<script>
    (function() {
        const config = @json($idleConfig);

        if (!config.userId || !config.activityUrl || !config.loginUrl || !config.logoutUrl) {
            return;
        }

        const modalEl = document.getElementById(config.modalId);
        const countdownEl = document.getElementById(config.countdownId);
        const keepAliveBtn = modalEl?.querySelector('[data-idle-session-keepalive]');
        const logoutBtn = modalEl?.querySelector('[data-idle-session-logout]');
        const storageKey = ['idle-session', config.guard, config.userId, 'last-activity'].join(':');
        const logoutKey = ['idle-session', config.guard, config.userId, 'logout'].join(':');
        const touchThrottleMs = 60000;
        const localActivityThrottleMs = 1000;
        const timeoutMs = Math.max(Number(config.timeoutSeconds) || 0, 1) * 1000;
        const warningMs = Math.max(Number(config.warningSeconds) || 0, 0) * 1000;
        let lastServerTouchAt = 0;
        let lastClientActivityAt = 0;
        let isLoggingOut = false;
        let warningVisible = false;
        let fallbackBackdrop = null;
        let modalInstance = null;
        let tickTimer = null;

        function parseSecondsTimestamp(value) {
            const timestamp = Number(value);
            return Number.isFinite(timestamp) && timestamp > 0 ? timestamp * 1000 : null;
        }

        function parseMillisecondsTimestamp(value) {
            const timestamp = Number(value);
            return Number.isFinite(timestamp) && timestamp > 0 ? timestamp : null;
        }

        function safeStorageGet(key) {
            try {
                return window.localStorage ? window.localStorage.getItem(key) : null;
            } catch (error) {
                return null;
            }
        }

        function safeStorageSet(key, value) {
            try {
                if (window.localStorage) {
                    window.localStorage.setItem(key, value);
                }
            } catch (error) {
                // Ignore localStorage failures and continue per-tab.
            }
        }

        function safeStorageRemove(key) {
            try {
                if (window.localStorage) {
                    window.localStorage.removeItem(key);
                }
            } catch (error) {
                // Ignore localStorage failures and continue per-tab.
            }
        }

        function samePath(firstUrl, secondUrl) {
            if (!firstUrl || !secondUrl) {
                return false;
            }

            try {
                const firstPath = new URL(firstUrl, window.location.origin).pathname;
                const secondPath = new URL(secondUrl, window.location.origin).pathname;

                return firstPath === secondPath;
            } catch (error) {
                return false;
            }
        }

        function isLoginResponseUrl(url) {
            return samePath(url, config.loginUrl);
        }

        function shouldRedirectForStatus(status) {
            return Number(status) === 401 || Number(status) === 419;
        }

        function ensureModalInstance() {
            if (modalInstance || !modalEl || !window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
                return modalInstance;
            }

            modalInstance = window.bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: 'static',
                keyboard: false,
            });

            return modalInstance;
        }

        function showFallbackModal() {
            if (!modalEl) {
                return;
            }

            modalEl.style.display = 'block';
            modalEl.removeAttribute('aria-hidden');
            modalEl.setAttribute('aria-modal', 'true');
            modalEl.classList.add('show');
            document.body.classList.add('modal-open');

            if (!fallbackBackdrop) {
                fallbackBackdrop = document.createElement('div');
                fallbackBackdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(fallbackBackdrop);
            }
        }

        function hideFallbackModal() {
            if (!modalEl) {
                return;
            }

            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');

            if (fallbackBackdrop) {
                fallbackBackdrop.remove();
                fallbackBackdrop = null;
            }
        }

        function showWarningModal() {
            if (!modalEl || warningVisible) {
                return;
            }

            const instance = ensureModalInstance();
            if (instance) {
                instance.show();
            } else {
                showFallbackModal();
            }

            warningVisible = true;
        }

        function hideWarningModal() {
            if (!warningVisible) {
                return;
            }

            const instance = ensureModalInstance();
            if (instance) {
                instance.hide();
            } else {
                hideFallbackModal();
            }

            warningVisible = false;
        }

        function clearIdleStorage() {
            safeStorageRemove(storageKey);
        }

        function broadcastLogout() {
            safeStorageSet(logoutKey, String(Date.now()));
        }

        function redirectToLogin() {
            if (isLoggingOut) {
                return;
            }

            isLoggingOut = true;
            clearIdleStorage();
            broadcastLogout();
            window.location.assign(config.loginUrl);
        }

        function performLogout() {
            if (isLoggingOut) {
                return;
            }

            isLoggingOut = true;
            hideWarningModal();
            clearIdleStorage();
            broadcastLogout();

            if (config.logoutMethod === 'POST') {
                const form = document.getElementById(config.logoutFormId);
                if (form) {
                    form.submit();
                    return;
                }
            }

            window.location.assign(config.logoutUrl);
        }

        function updateCountdownLabel(remainingMs) {
            if (!countdownEl) {
                return;
            }

            const remainingSeconds = Math.max(0, Math.ceil(remainingMs / 1000));
            countdownEl.textContent = remainingSeconds === 1 ? '1 second' : `${remainingSeconds} seconds`;
        }

        let lastKnownActivityAt = parseSecondsTimestamp(config.initialLastActivityAt) || Date.now();
        const storedActivityAt = parseMillisecondsTimestamp(safeStorageGet(storageKey));

        if (storedActivityAt !== null && storedActivityAt > lastKnownActivityAt) {
            lastKnownActivityAt = storedActivityAt;
        } else {
            safeStorageSet(storageKey, String(lastKnownActivityAt));
        }

        safeStorageRemove(logoutKey);

        function setLastActivity(timestamp) {
            lastKnownActivityAt = timestamp;
            safeStorageSet(storageKey, String(timestamp));

            if ((Date.now() + warningMs) < (lastKnownActivityAt + timeoutMs)) {
                hideWarningModal();
            }
        }

        async function touchServer(force) {
            if (isLoggingOut) {
                return;
            }

            const now = Date.now();
            if (!force && (now - lastServerTouchAt) < touchThrottleMs) {
                return;
            }

            lastServerTouchAt = now;

            try {
                const response = await window.fetch(config.activityUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                });

                if (shouldRedirectForStatus(response.status) || (response.redirected && isLoginResponseUrl(response.url))) {
                    redirectToLogin();
                    return;
                }

                const payload = await response.json().catch(function() {
                    return null;
                });

                if (!response.ok) {
                    return;
                }

                const touchedAt = parseSecondsTimestamp(payload?.last_activity_at) || now;
                setLastActivity(touchedAt);
            } catch (error) {
                // Ignore transient network errors and keep the local timer authoritative.
            }
        }

        function recordActivity() {
            if (isLoggingOut || warningVisible) {
                return;
            }

            const now = Date.now();
            if ((now - lastClientActivityAt) < localActivityThrottleMs) {
                return;
            }

            lastClientActivityAt = now;
            setLastActivity(now);

            if (!document.hidden) {
                touchServer(false);
            }
        }

        function evaluateTimeout() {
            if (isLoggingOut) {
                return;
            }

            const remainingMs = (lastKnownActivityAt + timeoutMs) - Date.now();

            if (remainingMs <= 0) {
                performLogout();
                return;
            }

            if (warningMs > 0 && remainingMs <= warningMs) {
                updateCountdownLabel(remainingMs);
                showWarningModal();
                return;
            }

            hideWarningModal();
        }

        function installFetchHandler() {
            if (typeof window.fetch !== 'function' || window.__idleSessionFetchWrapped) {
                return;
            }

            const originalFetch = window.fetch.bind(window);
            window.fetch = function() {
                return originalFetch.apply(window, arguments).then(function(response) {
                    if (!response) {
                        return response;
                    }

                    if (response.redirected && isLoginResponseUrl(response.url)) {
                        redirectToLogin();
                        return response;
                    }

                    if (shouldRedirectForStatus(response.status)
                        && !samePath(response.url || '', config.activityUrl)
                        && !isLoginResponseUrl(response.url || '')) {
                        redirectToLogin();
                    }

                    return response;
                });
            };

            window.__idleSessionFetchWrapped = true;
        }

        function installJQueryHandler(retries) {
            if (window.__idleSessionJQueryWrapped || typeof window.jQuery !== 'function') {
                if (!window.__idleSessionJQueryWrapped && retries > 0) {
                    window.setTimeout(function() {
                        installJQueryHandler(retries - 1);
                    }, 250);
                }

                return;
            }

            window.jQuery(document).ajaxError(function(event, jqXHR) {
                if (jqXHR && shouldRedirectForStatus(jqXHR.status)) {
                    redirectToLogin();
                }
            });

            window.__idleSessionJQueryWrapped = true;
        }

        function installAxiosHandler(retries) {
            if (window.__idleSessionAxiosWrapped || typeof window.axios !== 'function') {
                if (!window.__idleSessionAxiosWrapped && retries > 0) {
                    window.setTimeout(function() {
                        installAxiosHandler(retries - 1);
                    }, 250);
                }

                return;
            }

            window.axios.interceptors.response.use(
                function(response) {
                    return response;
                },
                function(error) {
                    if (error?.response && shouldRedirectForStatus(error.response.status)) {
                        redirectToLogin();
                    }

                    return Promise.reject(error);
                }
            );

            window.__idleSessionAxiosWrapped = true;
        }

        installFetchHandler();
        installJQueryHandler(40);
        installAxiosHandler(40);

        ['pointerdown', 'keydown', 'scroll', 'touchstart', 'focus'].forEach(function(eventName) {
            window.addEventListener(eventName, recordActivity, {
                passive: true,
            });
        });

        window.addEventListener('storage', function(event) {
            if (event.key === storageKey && event.newValue) {
                const syncedActivityAt = parseMillisecondsTimestamp(event.newValue);
                if (syncedActivityAt !== null && syncedActivityAt > lastKnownActivityAt) {
                    lastKnownActivityAt = syncedActivityAt;
                    hideWarningModal();
                }
            }

            if (event.key === logoutKey && event.newValue) {
                redirectToLogin();
            }
        });

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                const syncedActivityAt = parseMillisecondsTimestamp(safeStorageGet(storageKey));
                if (syncedActivityAt !== null && syncedActivityAt > lastKnownActivityAt) {
                    lastKnownActivityAt = syncedActivityAt;
                }

                evaluateTimeout();
            }
        });

        keepAliveBtn?.addEventListener('click', async function() {
            if (isLoggingOut) {
                return;
            }

            keepAliveBtn.disabled = true;

            try {
                await touchServer(true);
                hideWarningModal();
            } finally {
                keepAliveBtn.disabled = false;
            }
        });

        logoutBtn?.addEventListener('click', function() {
            performLogout();
        });

        tickTimer = window.setInterval(evaluateTimeout, 1000);
        evaluateTimeout();
    })();
</script>
