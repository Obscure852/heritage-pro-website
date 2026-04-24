@php($importTabIcons = ['users' => 'bx bx-user', 'leads' => 'bx bx-user-voice', 'customers' => 'bx bx-building-house', 'contacts' => 'bx bx-id-card'])

<div class="crm-tabs">
    @foreach ($entityTabs as $entityKey => $entityDefinition)
        <a href="{{ route('crm.settings.imports.' . $entityKey) }}" @class(['crm-tab', 'is-active' => $activeImportEntity === $entityKey])>
            <i class="{{ $importTabIcons[$entityKey] ?? 'bx bx-grid-alt' }}"></i>
            <span>{{ $entityDefinition['label'] }}</span>
        </a>
    @endforeach
</div>
