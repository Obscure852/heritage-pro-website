<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                const submitter = event.submitter;
                const submitBtn = submitter && submitter.classList.contains('btn-loading')
                    ? submitter
                    : form.querySelector('button[type="submit"].btn-loading');

                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                }
            });
        });
    });
</script>
