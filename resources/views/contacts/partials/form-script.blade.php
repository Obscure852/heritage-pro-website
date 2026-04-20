<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeLoadingButtons();
        initializeContactPeopleForm();
    });

    function initializeLoadingButtons() {
        document.querySelectorAll('form').forEach(function(form) {
            const loadingButton = form.querySelector('button[type="submit"].btn-loading');
            if (!loadingButton) {
                return;
            }

            form.addEventListener('submit', function(event) {
                if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
                    return;
                }

                const submitButton = event.submitter && event.submitter.matches('button[type="submit"].btn-loading')
                    ? event.submitter
                    : loadingButton;

                submitButton.classList.add('loading');
                submitButton.disabled = true;
            });
        });
    }

    function initializeContactPeopleForm() {
        const rowsContainer = document.getElementById('people-rows');
        const addRowButton = document.getElementById('add-person-row');
        const template = document.getElementById('person-row-template');

        if (!rowsContainer || !addRowButton || !template) {
            return;
        }

        let nextIndex = Array.from(rowsContainer.querySelectorAll('.person-row'))
            .map((row) => Number(row.dataset.rowIndex || 0))
            .reduce((max, current) => Math.max(max, current), -1) + 1;

        function syncPrimaryFlags() {
            const rows = rowsContainer.querySelectorAll('.person-row');
            let checkedRadio = rowsContainer.querySelector('.primary-person:checked');

            if (!checkedRadio && rows.length > 0) {
                checkedRadio = rows[0].querySelector('.primary-person');
                checkedRadio.checked = true;
            }

            rows.forEach((row) => {
                const radio = row.querySelector('.primary-person');
                const hidden = row.querySelector('.is-primary-input');
                hidden.value = radio.checked ? '1' : '0';
            });
        }

        function bindRowEvents(row) {
            const removeButton = row.querySelector('.remove-person-row');
            const radio = row.querySelector('.primary-person');

            removeButton.addEventListener('click', function() {
                row.remove();
                syncPrimaryFlags();
            });

            radio.addEventListener('change', syncPrimaryFlags);
        }

        Array.from(rowsContainer.querySelectorAll('.person-row')).forEach(bindRowEvents);
        syncPrimaryFlags();

        addRowButton.addEventListener('click', function() {
            const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex));
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();

            const row = wrapper.firstElementChild;
            rowsContainer.appendChild(row);
            bindRowEvents(row);
            syncPrimaryFlags();
            nextIndex += 1;
        });

        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', syncPrimaryFlags);
        }
    }
</script>
