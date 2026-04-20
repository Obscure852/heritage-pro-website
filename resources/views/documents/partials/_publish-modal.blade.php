{{-- Publish Document Modal --}}
<div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 3px;">
            <div class="modal-header" style="border-bottom: 1px solid #e5e7eb;">
                <h5 class="modal-title" id="publishModalLabel" style="font-weight: 600;">
                    <i class="fas fa-globe me-2" style="color: #10b981;"></i> Publish Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                {{-- Help text --}}
                <div class="help-text" style="background: #f0fdf4; border-left-color: #10b981; margin-bottom: 20px; padding: 12px; border-radius: 0 3px 3px 0;">
                    <div style="font-weight: 600; color: #374151; margin-bottom: 4px;">Choose Visibility</div>
                    <div style="color: #6b7280; font-size: 13px; line-height: 1.4;">
                        Select who can access this document after publication.
                    </div>
                </div>

                {{-- Visibility Radio Buttons --}}
                <div class="mb-3">
                    <div class="form-check mb-3" style="padding: 12px 12px 12px 36px; border: 1px solid #e5e7eb; border-radius: 3px; transition: background 0.15s;">
                        <input class="form-check-input" type="radio" name="publish-visibility" id="visibility-internal" value="internal" checked>
                        <label class="form-check-label" for="visibility-internal" style="cursor: pointer;">
                            <strong style="color: #1f2937;">Internal</strong>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">All authenticated staff members can view this document.</div>
                        </label>
                    </div>

                    <div class="form-check mb-3" style="padding: 12px 12px 12px 36px; border: 1px solid #e5e7eb; border-radius: 3px; transition: background 0.15s;">
                        <input class="form-check-input" type="radio" name="publish-visibility" id="visibility-roles" value="roles">
                        <label class="form-check-label" for="visibility-roles" style="cursor: pointer;">
                            <strong style="color: #1f2937;">Specific Roles</strong>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Only users with selected roles can view this document.</div>
                        </label>
                    </div>

                    <div class="form-check mb-3" style="padding: 12px 12px 12px 36px; border: 1px solid #e5e7eb; border-radius: 3px; transition: background 0.15s;">
                        <input class="form-check-input" type="radio" name="publish-visibility" id="visibility-public" value="public">
                        <label class="form-check-label" for="visibility-public" style="cursor: pointer;">
                            <strong style="color: #1f2937;">Public</strong>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Anyone with the link can view this document, including non-authenticated users.</div>
                        </label>
                    </div>
                </div>

                {{-- Role selector (shown when "Specific Roles" is selected) --}}
                <div id="publish-roles-container" style="display: none; padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 3px;">
                    <label class="form-label fw-semibold" style="font-size: 13px;">Select Roles</label>
                    <div id="publish-roles-list" style="max-height: 200px; overflow-y: auto;">
                        @isset($availableRoles)
                            @foreach($availableRoles as $role)
                                <div class="form-check mb-2">
                                    <input class="form-check-input publish-role-checkbox" type="checkbox" value="{{ $role->id }}" id="publish-role-{{ $role->id }}">
                                    <label class="form-check-label" for="publish-role-{{ $role->id }}" style="font-size: 13px; cursor: pointer;">
                                        {{ $role->name }}
                                        @if($role->description)
                                            <small class="text-muted d-block" style="font-size: 11px;">{{ $role->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        @endisset
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm btn-loading" id="publish-submit-btn" style="border-radius: 3px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                    <span class="btn-text"><i class="fas fa-globe"></i> Publish</span>
                    <span class="btn-spinner d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Publishing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var documentId = {{ $document->id }};

    // Toggle role selector visibility
    var visibilityRadios = document.querySelectorAll('input[name="publish-visibility"]');
    var rolesContainer = document.getElementById('publish-roles-container');

    visibilityRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'roles') {
                rolesContainer.style.display = 'block';
            } else {
                rolesContainer.style.display = 'none';
            }
        });
    });

    // Publish button handler
    var publishBtn = document.getElementById('publish-submit-btn');
    if (publishBtn) {
        publishBtn.addEventListener('click', function() {
            var visibility = document.querySelector('input[name="publish-visibility"]:checked').value;
            var roles = [];

            if (visibility === 'roles') {
                document.querySelectorAll('.publish-role-checkbox:checked').forEach(function(cb) {
                    roles.push(parseInt(cb.value));
                });

                if (roles.length === 0) {
                    Swal.fire('Error', 'Please select at least one role.', 'error');
                    return;
                }
            }

            publishBtn.classList.add('loading');
            publishBtn.disabled = true;

            $.ajax({
                url: '/documents/' + documentId + '/workflow/publish',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                contentType: 'application/json',
                data: JSON.stringify({
                    visibility: visibility,
                    roles: roles.length > 0 ? roles : null
                }),
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('publishModal')).hide();
                    Swal.fire({
                        toast: true, position: 'top-end', icon: 'success',
                        title: response.message || 'Document published successfully!',
                        showConfirmButton: false, timer: 2000
                    });
                    setTimeout(function() { location.reload(); }, 1000);
                },
                error: function(xhr) {
                    publishBtn.classList.remove('loading');
                    publishBtn.disabled = false;
                    var msg = xhr.responseJSON?.message || 'Failed to publish document.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        });
    }
})();
</script>
