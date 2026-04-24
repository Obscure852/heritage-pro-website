@php
    $extraClass = $extraClass ?? '';
    $dotClass = $dotClass ?? '';
    $urlClass = $urlClass ?? '';
    $url = $url ?? 'app.heritagepro.net';
@endphp
<div class="window-chrome{{ $extraClass !== '' ? ' ' . $extraClass : '' }}">
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <div class="url{{ $urlClass !== '' ? ' ' . $urlClass : '' }}">{{ $url }}</div>
</div>
