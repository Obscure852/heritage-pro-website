<script>
    (function() {
        if (window.__pdpThemeScriptInitialized) {
            return;
        }

        window.__pdpThemeScriptInitialized = true;

        function applyCommentBankSelection(select) {
            if (!select) {
                return;
            }

            const targetId = select.getAttribute('data-comment-target');
            const textarea = targetId ? document.getElementById(targetId) : null;

            if (!textarea) {
                return;
            }

            const nextValue = (select.value || '').trim();
            textarea.value = nextValue;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            textarea.dispatchEvent(new Event('change', { bubbles: true }));

            if (nextValue !== '' && !textarea.disabled && !textarea.readOnly) {
                textarea.focus();
            }
        }

        function syncCommentBankSelectionFromTextarea(textarea) {
            if (!textarea) {
                return;
            }

            const selectId = textarea.getAttribute('data-comment-bank-select');
            const select = selectId ? document.getElementById(selectId) : null;

            if (!select) {
                return;
            }

            const textareaValue = (textarea.value || '').trim();
            const selectedOptionValue = (select.value || '').trim();

            if (textareaValue === '') {
                select.value = '';
                return;
            }

            const matchingOption = Array.from(select.options).find(function(option) {
                return (option.value || '').trim() === textareaValue;
            });

            if (matchingOption) {
                select.value = matchingOption.value;
                return;
            }

            if (selectedOptionValue !== '' && selectedOptionValue !== textareaValue) {
                select.value = '';
            }
        }

        function bindSubmitStates(root) {
            root.querySelectorAll('.pdp-theme form').forEach(function(form) {
                if (form.dataset.pdpSubmitBound === 'true') {
                    return;
                }

                form.dataset.pdpSubmitBound = 'true';
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                });
            });
        }

        function boot() {
            bindSubmitStates(document);
            document.querySelectorAll('[data-comment-bank-select]').forEach(function(select) {
                const targetId = select.getAttribute('data-comment-target');
                const textarea = targetId ? document.getElementById(targetId) : null;

                if (!textarea) {
                    return;
                }

                if ((textarea.value || '').trim() === '' && (select.value || '').trim() !== '') {
                    applyCommentBankSelection(select);
                    return;
                }

                syncCommentBankSelectionFromTextarea(textarea);
            });
        }

        window.pdpApplyCommentBankSelection = applyCommentBankSelection;
        document.addEventListener('change', function(event) {
            const select = event.target.closest('[data-comment-bank-select]');
            if (select) {
                applyCommentBankSelection(select);
            }
        });
        document.addEventListener('input', function(event) {
            const textarea = event.target.closest('[data-comment-bank-textarea]');

            if (textarea) {
                syncCommentBankSelectionFromTextarea(textarea);
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', boot);
        } else {
            boot();
        }
    })();
</script>
