<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeHouseChoices();
        initializeHouseFormValidation();
    });

    function initializeHouseChoices() {
        if (typeof Choices === 'undefined') {
            return;
        }

        const selectElements = document.querySelectorAll('select.form-select[data-trigger]');

        selectElements.forEach(function(element) {
            if (element.dataset.choicesInitialized === 'true') {
                return;
            }

            new Choices(element, {
                searchEnabled: true,
                removeItemButton: false,
                shouldSort: false,
                itemSelectText: '',
                classNames: {
                    containerOuter: 'choices'
                },
                searchFields: ['label', 'value'],
                searchPlaceholderValue: 'Type to search...',
                searchResultLimit: 10
            });

            element.dataset.choicesInitialized = 'true';
        });
    }

    function initializeHouseFormValidation() {
        const forms = document.querySelectorAll('form.needs-validation[data-house-form]');

        if (!forms.length) {
            return;
        }

        Array.prototype.slice.call(forms).forEach(function(form) {
            if (form.dataset.houseValidationInitialized === 'true') {
                return;
            }

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();

                    const firstInvalidElement = form.querySelector(':invalid');

                    if (firstInvalidElement) {
                        firstInvalidElement.focus();
                        firstInvalidElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }
                } else {
                    const submitBtn = form.querySelector('button[type="submit"].btn-loading');

                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        submitBtn.disabled = true;
                    }
                }

                form.classList.add('was-validated');
            }, false);

            form.dataset.houseValidationInitialized = 'true';
        });
    }
</script>
