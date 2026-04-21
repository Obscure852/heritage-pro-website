@php
    $extraClass = $extraClass ?? '';
    $dotClass = $dotClass ?? '';
    $urlClass = $urlClass ?? '';
@endphp
<div class="window-chrome{{ $extraClass !== '' ? ' ' . $extraClass : '' }}">
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <span{{ $dotClass !== '' ? ' class=' . '"' . $dotClass . '"' : '' }}></span>
    <div class="url{{ $urlClass !== '' ? ' ' . $urlClass : '' }}">demo.heritagepro.net</div>
</div>
