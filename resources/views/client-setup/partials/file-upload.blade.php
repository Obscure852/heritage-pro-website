<div class="crm-wizard-file-upload" data-file-upload>
    <label for="{{ $id }}">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="file"
        class="crm-wizard-file-input"
        data-file-input
        @if (! empty($accept)) accept="{{ $accept }}" @endif
        @if (! empty($required)) required @endif
    >
    <label for="{{ $id }}" class="crm-wizard-file-dropzone" data-file-dropzone>
        <span class="crm-wizard-file-dropzone-icon" aria-hidden="true"><i class="bx bx-cloud-upload"></i></span>
        <span class="crm-wizard-file-dropzone-copy">
            <strong>Choose a file</strong>
            <small>or drag and drop it here</small>
        </span>
        <span class="crm-wizard-file-browse">Browse files</span>
    </label>
    <span class="crm-wizard-file-name" data-file-name aria-live="polite">No file selected</span>
    <small class="crm-muted">{{ $help ?? 'Upload supporting evidence when the implementation scope requires it.' }}</small>
</div>
