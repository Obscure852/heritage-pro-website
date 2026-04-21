<section class="section blog">
    <div class="container">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="max-width: 580px;">
                <span class="eyebrow">From the Heritage Pro blog</span>
                <h2 style="margin-top: 14px;">Ideas and playbooks for school leaders.</h2>
            </div>
            <a href="#" class="btn btn-secondary">All articles →</a>
        </div>
        <div class="blog-grid">
            @foreach ($site['blog_articles'] as $article)
                <article class="blog-card">
                    <div class="blog-cover {{ $article['cover'] }}"></div>
                    <span class="blog-tag">{{ $article['tag'] }}</span>
                    <h4>{{ $article['title'] }}</h4>
                    <p>{{ $article['description'] }}</p>
                    <div class="blog-meta">{{ $article['meta'] }}</div>
                </article>
            @endforeach
        </div>
    </div>
</section>
