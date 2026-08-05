/* Heritage Pro — journal index and article pages.
   Loaded alongside home-styles, which supplies .hp-post and .hp-journal. */

.hp-post__standfirst {
    margin: 10px 0 0;
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--hp-muted);
}

.hp-journal--index {
    margin-top: 52px;
    row-gap: 48px;
}

/* ── Article ───────────────────────────────────────────────────── */

.hp-article {
    padding-top: var(--hp-section-gap);
}

.hp-article__head {
    max-width: 46rem;
    margin: 0 auto;
    text-align: center;
}

.hp-article__title {
    margin: 16px 0 0;
    font-family: var(--hp-serif);
    font-size: clamp(32px, 4.2vw, 50px);
    line-height: 1.1;
    letter-spacing: -0.022em;
    font-weight: 400;
    text-wrap: pretty;
}

.hp-article__standfirst {
    margin: 22px 0 0;
    font-family: var(--hp-serif);
    font-size: clamp(18px, 1.9vw, 21px);
    line-height: 1.5;
    color: var(--hp-muted);
}

.hp-article__byline {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-top: 26px;
    padding-top: 20px;
    border-top: 1px solid var(--hp-line);
    font-size: 13px;
    color: var(--hp-muted-2);
}

.hp-article__figure {
    max-width: 62rem;
    margin: 48px auto 0;
    border-radius: 6px;
    overflow: hidden;
}

.hp-article__figure img {
    width: 100%;
    height: clamp(220px, 34vw, 420px);
    object-fit: cover;
}

/* ── Prose ─────────────────────────────────────────────────────── */

.hp-prose {
    max-width: 40rem;
    margin: 0 auto;
    padding-top: 48px;
    font-size: 17px;
    line-height: 1.72;
    color: #26263C;
}

.hp-prose > * + * {
    margin-top: 22px;
}

.hp-prose h2 {
    margin-top: 46px;
    font-family: var(--hp-serif);
    font-size: clamp(23px, 2.4vw, 27px);
    line-height: 1.24;
    letter-spacing: -0.016em;
    font-weight: 600;
    text-wrap: pretty;
}

.hp-prose h2 + * {
    margin-top: 14px;
}

.hp-prose ul,
.hp-prose ol {
    margin-left: 0;
    padding-left: 0;
    list-style: none;
}

.hp-prose li {
    position: relative;
    padding-left: 30px;
}

.hp-prose li + li {
    margin-top: 12px;
}

.hp-prose ul li::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 0.72em;
    width: 5px;
    height: 5px;
    border-radius: 999px;
    background: var(--hp-gold-rule);
}

.hp-prose ol {
    counter-reset: hp-prose-count;
}

.hp-prose ol li {
    counter-increment: hp-prose-count;
}

.hp-prose ol li::before {
    content: counter(hp-prose-count) '.';
    position: absolute;
    left: 0;
    top: 0;
    font-family: var(--hp-mono);
    font-size: 13px;
    line-height: 1.95;
    color: var(--hp-gold);
}

.hp-prose__pull {
    margin: 40px 0;
    padding: 4px 0 4px 28px;
    border-left: 2px solid var(--hp-gold-rule);
    font-family: var(--hp-serif);
    font-size: clamp(20px, 2.1vw, 23px);
    line-height: 1.42;
    letter-spacing: -0.012em;
    color: var(--hp-ink);
}

.hp-article__foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 20px 32px;
    max-width: 40rem;
    margin: 56px auto 0;
    padding-top: 26px;
    border-top: 1px solid var(--hp-line);
    font-size: 14.5px;
    color: var(--hp-muted);
}

@media (max-width: 760px) {
    .hp-prose {
        font-size: 16.5px;
    }

    .hp-article__foot {
        flex-direction: column;
        align-items: flex-start;
    }
}
