<li>
    <strong>{{ $node['name'] }}</strong> - {{ $node['title'] }}
    @if (!empty($node['children']))
        <ul>
            @foreach ($node['children'] as $child)
                @include('staff.partials.organogram-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
