@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php
$buttonStyles = match($color) {
    'success', 'green' => 'background-color: #10b981;',
    'error', 'red' => 'background-color: #ef4444;',
    default => 'background-color: #4e73df;',
};
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 30px auto; padding: 0; text-align: center; width: 100%;">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener" style="{{ $buttonStyles }} border-radius: 3px; color: #ffffff; display: inline-block; font-weight: 500; font-size: 14px; padding: 10px 20px; text-decoration: none;">{{ $slot }}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
