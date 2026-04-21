<section id="faq" class="section faq">
    <div class="container container-narrow">
        <div class="center">
            <span class="eyebrow">Frequently asked</span>
            <h2 style="margin-top: 14px;">Answers for cautious administrators.</h2>
        </div>
        <div class="faq-list">
            @foreach ($site['faq_items'] as $index => $item)
                <div @class(['faq-item', 'open' => $index === 0])>
                    <button type="button" class="faq-q" data-faq-toggle>
                        <span>{{ $item['question'] }}</span>
                        @include('website.partials.icon', ['name' => 'chevron', 'size' => 18])
                    </button>
                    <div class="faq-a" @if ($index !== 0) hidden @endif>{{ $item['answer'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
