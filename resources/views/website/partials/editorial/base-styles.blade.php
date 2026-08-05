/* Heritage Pro — editorial design system.
   Tokens and primitives shared by every page rendered through
   layouts/website-editorial. Section-level styles live in home-styles. */

:root {
    --hp-bg: #FAFAFD;
    --hp-surface: #FFFFFF;
    --hp-tint: #F1F1F8;
    --hp-ink: #16162B;
    --hp-navy: #232160;
    --hp-navy-deep: #1A1950;
    --hp-muted: #55556B;
    --hp-muted-2: #6C6C82;
    --hp-muted-3: #6E6E88;
    --hp-gold: #96631E;
    --hp-gold-rule: #C08A3C;
    --hp-gold-soft: #EBC891;
    --hp-line: #E3E3EC;
    --hp-line-soft: #EBEBF3;
    --hp-line-softer: #F0F0F7;
    --hp-line-input: #D3D3E1;
    --hp-line-link: #C4C4D8;
    --hp-on-navy: #FAFAFD;
    --hp-on-navy-body: #C3C1EA;
    --hp-on-navy-muted: #A6A4D6;
    --hp-on-navy-line: rgba(251, 249, 244, 0.18);
    --hp-chart: #CBCAE8;
    --hp-placeholder: #E6E6F0;

    --hp-serif: 'Source Serif 4', Georgia, 'Times New Roman', serif;
    --hp-sans: Archivo, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    --hp-mono: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace;

    --hp-gutter: 56px;
    --hp-section-gap: 84px;
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    -webkit-text-size-adjust: 100%;
}

body {
    margin: 0;
    background: var(--hp-bg);
    color: var(--hp-ink);
    font-family: var(--hp-sans);
    font-size: 16px;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

a {
    color: inherit;
    text-decoration: none;
}

a:hover {
    color: var(--hp-navy);
}

img {
    display: block;
    max-width: 100%;
}

input,
textarea,
select,
button {
    font-family: inherit;
    font-size: inherit;
    color: inherit;
}

h1,
h2,
h3,
h4,
p,
blockquote,
figure,
dl,
dd {
    margin: 0;
}

:focus-visible {
    outline: 2px solid var(--hp-navy);
    outline-offset: 3px;
}

.hp-visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    margin: -1px;
    padding: 0;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.hp-skip {
    position: absolute;
    left: -9999px;
    top: 0;
    z-index: 50;
    padding: 12px 18px;
    background: var(--hp-navy);
    color: var(--hp-on-navy);
    font-size: 14px;
}

.hp-skip:focus {
    left: 12px;
    top: 12px;
    color: var(--hp-on-navy);
}

/* ── Layout primitives ─────────────────────────────────────────── */

.hp-page {
    background: var(--hp-bg);
    color: var(--hp-ink);
    overflow-x: hidden;
}

.hp-band {
    padding-left: var(--hp-gutter);
    padding-right: var(--hp-gutter);
}

.hp-band--tint {
    background: var(--hp-tint);
    border-top: 1px solid var(--hp-line);
    border-bottom: 1px solid var(--hp-line);
}

.hp-band--navy {
    background: var(--hp-navy);
    color: var(--hp-on-navy);
}

.hp-section {
    padding-top: var(--hp-section-gap);
}

.hp-section--flush {
    padding-top: 0;
}

.hp-intro {
    max-width: 62ch;
    margin: 0 auto;
    text-align: center;
}

.hp-intro--narrow {
    max-width: 52ch;
}

/* ── Typography ────────────────────────────────────────────────── */

.hp-eyebrow {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--hp-gold);
}

.hp-eyebrow--onnavy {
    color: var(--hp-gold-soft);
}

.hp-label {
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--hp-muted-3);
}

.hp-kicker {
    font-family: var(--hp-serif);
    font-size: 15px;
    font-style: italic;
    color: var(--hp-gold);
}

.hp-kicker--onnavy {
    color: var(--hp-gold-soft);
}

.hp-h1 {
    margin: 26px auto 0;
    max-width: 20ch;
    font-family: var(--hp-serif);
    font-size: clamp(38px, 5.2vw, 62px);
    line-height: 1.05;
    letter-spacing: -0.022em;
    font-weight: 400;
    text-wrap: pretty;
}

.hp-h2 {
    margin: 18px 0 0;
    font-family: var(--hp-serif);
    font-size: clamp(29px, 3.4vw, 40px);
    line-height: 1.12;
    letter-spacing: -0.02em;
    font-weight: 400;
    text-wrap: pretty;
}

.hp-h2--sm {
    font-size: clamp(27px, 3vw, 36px);
    line-height: 1.14;
}

.hp-h3 {
    margin: 12px 0 0;
    font-family: var(--hp-serif);
    font-size: clamp(24px, 2.4vw, 30px);
    line-height: 1.18;
    letter-spacing: -0.018em;
    font-weight: 400;
    text-wrap: pretty;
}

.hp-h4 {
    margin: 0;
    font-family: var(--hp-serif);
    font-size: 20px;
    font-weight: 600;
    letter-spacing: -0.012em;
}

.hp-lead {
    margin: 18px 0 0;
    font-size: 16.5px;
    line-height: 1.62;
    color: var(--hp-muted);
}

.hp-lead--onnavy {
    color: var(--hp-on-navy-body);
}

.hp-body {
    margin: 12px 0 0;
    font-size: 14.5px;
    line-height: 1.6;
    color: var(--hp-muted);
}

.hp-rule-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--hp-gold);
}

.hp-rule-label::before,
.hp-rule-label::after {
    content: '';
    width: 26px;
    height: 1px;
    background: var(--hp-gold-rule);
}

/* ── Controls ──────────────────────────────────────────────────── */

.hp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    padding: 0 26px;
    border: 1px solid transparent;
    border-radius: 999px;
    font-size: 14.5px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.hp-btn--sm {
    height: 40px;
    padding: 0 20px;
    font-size: 13.5px;
}

.hp-btn--solid {
    background: var(--hp-navy);
    color: var(--hp-on-navy);
}

.hp-btn--solid:hover {
    background: var(--hp-navy-deep);
    color: var(--hp-on-navy);
}

.hp-btn--outline {
    border-color: var(--hp-line-input);
    background: transparent;
}

.hp-btn--outline:hover {
    border-color: var(--hp-navy);
    color: var(--hp-navy);
}

.hp-btn--invert {
    background: var(--hp-bg);
    color: var(--hp-navy);
    font-weight: 600;
}

.hp-btn--invert:hover {
    background: #FFFFFF;
    color: var(--hp-navy);
}

.hp-btn--block {
    width: 100%;
}

.hp-link {
    display: inline-block;
    font-size: 14px;
    font-weight: 500;
    color: var(--hp-navy);
    border-bottom: 1px solid var(--hp-line-link);
}

.hp-link:hover {
    border-bottom-color: var(--hp-navy);
}

.hp-link--onnavy {
    color: var(--hp-on-navy);
    border-bottom-color: rgba(251, 249, 244, 0.45);
}

.hp-link--onnavy:hover {
    color: var(--hp-on-navy);
    border-bottom-color: var(--hp-on-navy);
}

/* ── Surfaces ──────────────────────────────────────────────────── */

.hp-card {
    padding: 32px 30px 34px;
    background: var(--hp-surface);
    border: 1px solid var(--hp-line);
    border-radius: 6px;
}

.hp-card--navy {
    background: var(--hp-navy);
    border-color: var(--hp-navy);
    color: var(--hp-on-navy);
}

/* Ruled feature lists: hairline above each row, closing rule on the last. */
.hp-ruled {
    display: flex;
    flex-direction: column;
    font-size: 14px;
}

.hp-ruled > * {
    padding: 10px 0;
    border-top: 1px solid var(--hp-line-soft);
}

.hp-ruled > *:last-child {
    border-bottom: 1px solid var(--hp-line-soft);
}

.hp-card--navy .hp-ruled > *,
.hp-band--navy .hp-ruled > * {
    border-top-color: var(--hp-on-navy-line);
}

.hp-card--navy .hp-ruled > *:last-child,
.hp-band--navy .hp-ruled > *:last-child {
    border-bottom-color: var(--hp-on-navy-line);
}

/* ── Grids ─────────────────────────────────────────────────────── */

.hp-grid-2,
.hp-grid-3,
.hp-grid-4 {
    display: grid;
    gap: 28px;
}

.hp-grid-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.hp-grid-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.hp-grid-4 {
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.hp-headrow {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid var(--hp-line);
}

/* ── Responsive ────────────────────────────────────────────────── */

@media (max-width: 1080px) {
    :root {
        --hp-gutter: 36px;
        --hp-section-gap: 64px;
    }

    .hp-grid-3,
    .hp-grid-4 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 760px) {
    :root {
        --hp-gutter: 22px;
        --hp-section-gap: 52px;
    }

    .hp-grid-2,
    .hp-grid-3,
    .hp-grid-4 {
        grid-template-columns: minmax(0, 1fr);
    }

    .hp-headrow {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .hp-card {
        padding: 26px 22px 28px;
    }
}
