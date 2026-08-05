{{-- Renders a config-defined block array. Supported types: heading, paragraph,
     list, pull. Pass anchors => true to give headings ids for a contents index. --}}
<div class="hp-prose">
    @foreach ($blocks as $block)
        @switch($block['type'])
            @case('heading')
                <h2 @if ($anchors ?? false) id="{{ \Illuminate\Support\Str::slug($block['text']) }}" @endif>{{ $block['text'] }}</h2>
                @break

            @case('list')
                @if ($block['ordered'] ?? false)
                    <ol>
                        @foreach ($block['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ol>
                @else
                    <ul>
                        @foreach ($block['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
                @break

            @case('pull')
                <blockquote class="hp-prose__pull">{{ $block['text'] }}</blockquote>
                @break

            @default
                <p>{{ $block['text'] }}</p>
        @endswitch
    @endforeach
</div>
