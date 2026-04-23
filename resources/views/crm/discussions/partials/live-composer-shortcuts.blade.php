@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Array.prototype.slice.call(document.querySelectorAll('[data-live-composer-form]')).forEach(function (form) {
                    if (form.getAttribute('data-hotkeys-bound') === 'true') {
                        return;
                    }

                    form.setAttribute('data-hotkeys-bound', 'true');

                    var textarea = form.querySelector('[data-live-composer-input]');

                    if (!textarea) {
                        return;
                    }

                    textarea.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' || event.shiftKey || event.altKey || event.ctrlKey || event.metaKey || event.isComposing) {
                            return;
                        }

                        if (textarea.getAttribute('data-mention-menu-open') === 'true') {
                            return;
                        }

                        event.preventDefault();

                        if (typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                            return;
                        }

                        form.submit();
                    });
                });
            });
        </script>
    @endpush
@endonce
