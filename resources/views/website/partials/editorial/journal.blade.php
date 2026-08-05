@php
    $posts = [
        [
            'meta' => 'Guide · 8 min',
            'title' => 'Cutting your term reporting cycle from three weeks to one day',
            'image' => 'images/website/students-classroom.webp',
            'alt' => 'Learners raising their hands in a classroom',
        ],
        [
            'meta' => 'Checklist · 5 min',
            'title' => 'What every board should ask before approving an SIS',
            'image' => 'images/website/students-laptop.webp',
            'alt' => 'Two learners working together on a laptop',
        ],
        [
            'meta' => 'Playbook · 11 min',
            'title' => 'Running admissions season without a single lost application',
            'image' => null,
            'alt' => null,
        ],
    ];
@endphp
<section id="journal" class="hp-section hp-band">
    <div class="hp-headrow">
        <div>
            <p class="hp-eyebrow">VIII. The journal</p>
            <h2 class="hp-h2 hp-h2--sm">Ideas and playbooks for school leaders.</h2>
        </div>
        <a href="#journal" class="hp-link">All writing</a>
    </div>
    <div class="hp-grid-3 hp-journal">
        @foreach ($posts as $post)
            <a href="#journal" class="hp-post">
                @if ($post['image'])
                    <div class="hp-post__media">
                        <img src="{{ asset($post['image']) }}" alt="{{ $post['alt'] }}" width="520" height="340" loading="lazy">
                    </div>
                @else
                    <div class="hp-post__media hp-post__media--mark" aria-hidden="true"><span>Heritage&nbsp;Pro</span></div>
                @endif
                <p class="hp-label hp-post__meta">{{ $post['meta'] }}</p>
                <h3 class="hp-post__title">{{ $post['title'] }}</h3>
            </a>
        @endforeach
    </div>
</section>
