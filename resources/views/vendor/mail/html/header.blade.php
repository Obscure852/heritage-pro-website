@props(['url'])
<tr>
<td class="header" style="padding: 0; text-align: center;">
<table align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%); background-color: #4e73df; border-radius: 3px 3px 0 0; margin: 0 auto;">
<tr>
<td align="center" style="padding: 18px 20px;">
<a href="{{ $url }}" style="color: #ffffff; font-size: 18px; font-weight: 600; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
</table>
</td>
</tr>
