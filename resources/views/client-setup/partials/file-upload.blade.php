<div class="crm-wizard-file-upload" data-file-upload>
    <label for="{{ $id }}">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="file" class="form-control" @if (! empty($accept)) accept="{{ $accept }}" @endif>
    <small class="crm-muted">{{ $help ?? 'Upload supporting evidence when the implementation scope requires it.' }}</small>
</div>
